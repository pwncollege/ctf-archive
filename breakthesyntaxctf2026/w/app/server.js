const express = require('express');
const jwt = require('jsonwebtoken');
const multer = require('multer');
const crypto = require('crypto');

const app = express();
app.use(express.json());
app.use(express.static('public'));

const JWT_SECRET = crypto.randomBytes(32).toString('hex');

const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD;
if (!ADMIN_PASSWORD) { console.error('ADMIN_PASSWORD not set'); process.exit(1); }


const users = new Map();

const posts = new Map();

const upload = multer({ storage: multer.memoryStorage(), limits: { fileSize: 512 * 1024 } });
const WASM_MAGIC = Buffer.from([0x00, 0x61, 0x73, 0x6d]);

function hashPassword(password) {
  const salt = crypto.randomBytes(16).toString('hex');
  const hash = crypto.scryptSync(password, salt, 64).toString('hex');
  return salt + ':' + hash;
}

function verifyPassword(password, stored) {
  const [salt, hash] = stored.split(':');
  return crypto.scryptSync(password, salt, 64).toString('hex') === hash;
}

users.set('admin', { password: hashPassword(ADMIN_PASSWORD) });

function getUser(req) {
  const auth = req.headers.authorization || '';
  const match = auth.match(/^Bearer\s+(.+)$/);
  if (!match) return null;
  try { return jwt.verify(match[1], JWT_SECRET).username; }
  catch { return null; }
}


app.post('/api/register', (req, res) => {
  const { username, password } = req.body || {};
  if (!username || !password) return res.status(400).json({ error: 'Missing fields' });
  if (!/^[a-z0-9_]{3,20}$/.test(username)) return res.status(400).json({ error: 'Username must be 3–20 chars: a-z 0-9 _' });
  if (users.has(username)) return res.status(400).json({ error: 'Username taken' });
  users.set(username, { password: hashPassword(password) });
  res.json({ ok: true });
});

app.post('/api/login', (req, res) => {
  const { username, password } = req.body || {};
  const user = users.get(username);
  if (!user || !verifyPassword(password, user.password)) return res.status(401).json({ error: 'Invalid credentials' });
  const token = jwt.sign({ username }, JWT_SECRET);
  res.json({ ok: true, username, token });
});

app.post('/api/logout', (req, res) => {
  res.json({ ok: true });
});

app.get('/api/me', (req, res) => {
  const username = getUser(req);
  if (!username) return res.json({ loggedIn: false });
  res.json({ loggedIn: true, username });
});


app.get('/api/profile/:username', (req, res) => {
  if (!users.has(req.params.username)) return res.status(404).json({ error: 'User not found' });
  res.json({ username: req.params.username });
});


app.post('/api/post', upload.single('wasm'), (req, res) => {
  const username = getUser(req);
  if (!username) return res.status(401).json({ error: 'Not logged in' });
  if (!req.file) return res.status(400).json({ error: 'No WASM file uploaded' });
  if (!req.file.buffer.slice(0, 4).equals(WASM_MAGIC))
    return res.status(400).json({ error: 'Not a valid WASM file (bad magic bytes)' });
  const name = (req.body.name || 'Untitled');
  const wasm = req.file.buffer.toString('base64');
  const id = crypto.randomBytes(8).toString('hex');
  posts.set(id, { wasm, name, author: username });
  res.json({ id });
});

app.get('/api/post/:id', (req, res) => {
  const s = posts.get(req.params.id);
  if (!s) return res.status(404).json({ error: 'Not found' });
  res.json({ wasm: s.wasm, name: s.name, author: s.author });
});

app.get('/api/feed', (req, res) => {
  if (!getUser(req)) return res.status(401).json({ error: 'Not logged in' });
  const list = [...posts.entries()]
    .map(([id, s]) => ({ id, name: s.name, author: s.author, wasm: s.wasm }))
    .reverse();
  res.json(list);
});


app.post('/api/publish', async (req, res) => {
  const username = getUser(req);
  if (!username) return res.status(401).json({ error: 'Not logged in' });
  const { id } = req.body || {};
  if (!id) return res.status(400).json({ error: 'Missing id' });
  const s = posts.get(id);
  if (!s) return res.status(404).json({ error: 'Post not found' });
  if (s.author !== username) return res.status(403).json({ error: 'Not your post' });
  try {
    const url = `http://localhost:8000/?id=${id}`;
    const r = await fetch('http://localhost:3000/visit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ url }),
    });
    res.json(await r.json());
  } catch {
    res.status(500).json({ error: 'Bot unreachable' });
  }
});

app.listen(8000, () => console.log('Server :8000'));
