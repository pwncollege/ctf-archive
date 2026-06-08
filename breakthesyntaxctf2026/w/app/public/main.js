// ── State ──────────────────────────────────────────────
let me = null;

// ── Helpers ────────────────────────────────────────────
const $ = id => document.getElementById(id);
const show = id => $(id).classList.remove('hidden');
const hide = id => $(id).classList.add('hidden');
function setMsg(id, text, isErr = false) {
  const el = $(id);
  el.textContent = text;
  el.className = 'msg ' + (isErr ? 'err' : 'ok');
}
function mountBio(viewId) {
  const bio = $('bio-editor');
  if (me && me.loggedIn) {
    $(viewId).prepend(bio);
    bio.classList.remove('hidden');
  } else {
    bio.classList.add('hidden');
  }
}

async function renderWasm(canvas, wasmBase64) {
  const W = canvas.width, H = canvas.height;
  const bytes = Uint8Array.from(atob(wasmBase64), c => c.charCodeAt(0));
  const { instance } = await WebAssembly.instantiate(bytes, {});
  instance.exports.fill(0, W, H, 255, 0, 0);
  const pixels = new Uint8ClampedArray(instance.exports.memory.buffer, 0, W * H * 4);
  canvas.getContext('2d').putImageData(new ImageData(pixels, W), 0, 0);
}

async function api(method, path, body) {
  const opts = { method, headers: {} };
  const token = localStorage.getItem('token');
  if (token) opts.headers['Authorization'] = 'Bearer ' + token;
  if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
  const r = await fetch(path, opts);
  return r.json();
}

function createPostCard({ id, name, author }) {
  const post = document.createElement('div');
  post.className = 'post';
  post.onclick = () => navigate('?id=' + id);

  const avatar = document.createElement('div');
  avatar.className = 'post-avatar';
  avatar.textContent = author[0].toUpperCase();

  const body = document.createElement('div');
  body.className = 'post-body';

  const header = document.createElement('div');
  header.className = 'post-header';

  const authorName = document.createElement('span');
  authorName.className = 'post-author';
  authorName.textContent = author;

  const handle = document.createElement('span');
  handle.className = 'post-handle';
  handle.textContent = '@' + author;

  const postName = document.createElement('div');
  postName.className = 'post-name';
  postName.textContent = name;

  const canvas = document.createElement('canvas');
  canvas.width = 320;
  canvas.height = 240;
  canvas.style.display = 'block';
  canvas.style.width = '100%';
  canvas.style.marginTop = '0.5rem';
  canvas.style.borderRadius = '8px';
  canvas.style.imageRendering = 'pixelated';

  header.append(authorName, handle);
  body.append(header, postName, canvas);
  post.append(avatar, body);

  return { post, body, canvas, handle };
}

// ── Router ─────────────────────────────────────────────
async function init() {
  me = await api('GET', '/api/me');
  buildNav();

  if (me.loggedIn) {
    bioStatus = me.status || '';
    updateBioDisplay();
  }

  const params = new URLSearchParams(location.search);
  if (params.has('id')) {
    await showViewer(params.get('id'));
  } else if (params.has('profile')) {
    await showProfile(params.get('profile'));
  } else {
    me.loggedIn ? await showFeed() : showAuth();
  }
}

function navigate(url) {
  // Hide all views before re-rendering
  ['auth-view', 'feed-view', 'viewer-view', 'profile-view'].forEach(hide);
  history.pushState({}, '', url || '/');
  init();
}
window.navigate = navigate;

// ── Nav ────────────────────────────────────────────────
function buildNav() {
  const el = $('nav-right');
  el.innerHTML = '';
  if (me.loggedIn) {
    const span = document.createElement('span');
    span.className = 'nav-user';
    span.textContent = '@' + me.username;
    span.onclick = () => navigate('?profile=' + me.username);
    const btn = document.createElement('button');
    btn.className = 'btn outline sm';
    btn.textContent = 'Log out';
    btn.onclick = () => doLogout();
    el.append(span, btn);
  }
  $('nav-brand').onclick = () => navigate('');
}

// ── Auth ───────────────────────────────────────────────
function showAuth() { show('auth-view'); }

window.switchTab = (tab) => {
  $('login-form').classList.toggle('hidden', tab !== 'login');
  $('register-form').classList.toggle('hidden', tab !== 'register');
  $('tab-login').classList.toggle('active', tab === 'login');
  $('tab-register').classList.toggle('active', tab === 'register');
};

window.doLogin = async () => {
  const d = await api('POST', '/api/login', { username: $('l-user').value, password: $('l-pass').value });
  if (d.ok) { localStorage.setItem('token', d.token); navigate(''); }
  else setMsg('login-msg', d.error || 'Error', true);
};

window.doRegister = async () => {
  const d = await api('POST', '/api/register', { username: $('r-user').value, password: $('r-pass').value });
  if (d.ok) { setMsg('register-msg', 'Account created! Log in now.'); window.switchTab('login'); }
  else setMsg('register-msg', d.error || 'Error', true);
};

window.doLogout = async () => { await api('POST', '/api/logout'); localStorage.removeItem('token'); navigate(''); };


async function showFeed() {
  show('feed-view');
  mountBio('feed-view');

  const feed = await api('GET', '/api/feed');
  const list = $('feed-list');
  list.innerHTML = '';

  if (!feed.length) {
    list.innerHTML = '<p style="color:#536471;font-size:0.88rem;text-align:center;padding:2rem">No posts yet.</p>';
    return;
  }

  for (const s of feed) {
    const { post, body, canvas } = createPostCard(s);

    if (s.author === me.username) {
      const wrap = document.createElement('div');
      wrap.style.marginTop = '0.5rem';
      wrap.onclick = e => e.stopPropagation();
      const btn = document.createElement('button');
      btn.className = 'btn sm outline';
      btn.textContent = 'Publish';
      const msg = document.createElement('span');
      msg.className = 'msg';
      btn.onclick = () => {
        api('POST', '/api/publish', { id: s.id }).then(d => {
          if (d.ok) { msg.textContent = 'Admin is on their way!'; msg.className = 'msg ok'; }
          else { msg.textContent = d.error || 'Error'; msg.className = 'msg err'; }
        });
      };
      wrap.append(btn, msg);
      body.appendChild(wrap);
    }

    list.appendChild(post);
    renderWasm(canvas, s.wasm).catch(() => { });
  }
}


let bioShadow;
let bioStatus = '';

function updateBioDisplay() {
  const body = bioShadow.querySelector('.bio-body');
  body.textContent = bioStatus || 'No notes yet.';
  body.classList.toggle('empty', !bioStatus);
}

function initBioEditor() {
  bioShadow = $('bio-editor').attachShadow({ mode: 'closed' });
  bioShadow.innerHTML = `
    <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
      .card {
        background: #000; border: 1px solid #2f3336; border-radius: 16px;
        padding: 1.25rem; margin: 1rem 1rem 0; position: relative;
      }
      .label {
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em;
        text-transform: uppercase; color: #536471; margin-bottom: 0.7rem;
      }
      .bio-body { font-size: 0.98rem; line-height: 1.65; color: #e7e9ea; word-break: break-word; }
      .bio-body.empty { color: #2f3336; font-style: italic; }
      .edit-btn {
        position: absolute; top: 1.25rem; right: 1.25rem;
        background: none; border: 1px solid #2f3336; color: #536471;
        padding: 0.25rem 0.65rem; border-radius: 20px; cursor: pointer; font-size: 0.78rem;
        transition: border-color 0.15s, color 0.15s;
      }
      .edit-btn:hover { border-color: #e7e9ea; color: #e7e9ea; }
      .editor { display: none; }
      .editor.open { display: block; }
      .form-row { margin-bottom: 0.85rem; }
      textarea {
        width: 100%; background: #000; border: 1px solid #2f3336;
        color: #e7e9ea; padding: 0.65rem 0.9rem; border-radius: 9px;
        font-size: 0.9rem; font-family: inherit; transition: border-color 0.15s; resize: vertical;
      }
      textarea:focus { outline: none; border-color: #1d9bf0; }
      .btn-row { display: flex; gap: 0.5rem; }
      .btn {
        display: inline-flex; align-items: center; background: #e7e9ea; color: #000; border: none;
        padding: 0.35rem 0.9rem; border-radius: 20px; cursor: pointer;
        font-size: 0.8rem; font-family: inherit; font-weight: 700; transition: background 0.15s;
      }
      .btn:hover { background: #d0d3d6; }
      .btn.blue { background: #1d9bf0; color: #fff; }
      .btn.blue:hover { background: #1a8cd8; }
      .btn.outline { background: none; color: #e7e9ea; border: 1px solid #536471; }
      .btn.outline:hover { background: #16181c; border-color: #e7e9ea; }
    </style>
    <div class="card">
      <div class="label">Your Notes</div>
      <div class="bio-body empty">No notes yet.</div>
      <button class="edit-btn">Edit</button>
    </div>
    <div class="editor">
      <div class="card">
        <div class="label">Edit Notes</div>
        <div class="form-row">
          <textarea rows="3" maxlength="280" placeholder="What's on your mind?"></textarea>
        </div>
        <div class="btn-row">
          <button class="btn blue save-btn">Save</button>
          <button class="btn outline cancel-btn">Cancel</button>
        </div>
      </div>
    </div>`;

  bioShadow.querySelector('.edit-btn').onclick = () => {
    bioShadow.querySelector('textarea').value = bioStatus;
    bioShadow.querySelector('.editor').classList.add('open');
  };
  bioShadow.querySelector('.cancel-btn').onclick = () => {
    bioShadow.querySelector('.editor').classList.remove('open');
  };
  bioShadow.querySelector('.save-btn').onclick = () => {
    bioStatus = bioShadow.querySelector('textarea').value;
    me.status = bioStatus;
    updateBioDisplay();
    bioShadow.querySelector('.editor').classList.remove('open');
  };
}


window.onFileChange = () => {
  const f = $('sn-wasm').files[0];
  const drop = $('file-drop');
  if (f) {
    $('file-label').textContent = f.name;
    drop.classList.add('selected');
    drop.classList.remove('drag');
  } else {
    $('file-label').textContent = 'Drop .wasm painter here or click to browse';
    drop.classList.remove('selected');
  }
};

window.onDragOver = (e) => {
  e.preventDefault();
  $('file-drop').classList.add('drag');
};
window.onDragLeave = () => $('file-drop').classList.remove('drag');
window.onDrop = (e) => {
  e.preventDefault();
  $('file-drop').classList.remove('drag');
  const f = e.dataTransfer.files[0];
  if (f) {
    const dt = new DataTransfer();
    dt.items.add(f);
    $('sn-wasm').files = dt.files;
    window.onFileChange();
  }
};


window.openPostEditor = () => show('post-editor');
window.closePostEditor = () => {
  hide('post-editor');
  hide('sn-link');
  $('sn-wasm').value = '';
  $('file-label').textContent = 'Drop .wasm file here or click to browse';
  $('file-drop').classList.remove('selected', 'drag');
};

window.savePost = async () => {
  const file = $('sn-wasm').files[0];
  if (!file) { setMsg('sn-msg', 'Please select a .wasm file', true); return; }

  const form = new FormData();
  form.append('name', $('sn-name').value || 'Untitled');
  form.append('wasm', file);

  setMsg('sn-msg', '');
  hide('sn-link');

  const token = localStorage.getItem('token');
  const headers = {};
  if (token) headers['Authorization'] = 'Bearer ' + token;
  const r = await fetch('/api/post', { method: 'POST', headers, body: form });
  const d = await r.json();
  if (d.id) {
    const link = location.origin + '/?id=' + d.id;
    $('sn-link').textContent = link;
    show('sn-link');
    setMsg('sn-msg', 'Posted!');
    await showFeed();
    hide('post-editor');
  } else {
    setMsg('sn-msg', d.error || 'Upload failed', true);
  }
};


async function showViewer(id) {
  show('viewer-view');
  mountBio('viewer-view');

  let post;
  try {
    post = await api('GET', `/api/post/${id}`);
    if (post.error) throw new Error(post.error);
  } catch (e) {
    $('output').textContent = 'Error: ' + e.message;
    return;
  }

  $('v-name').textContent = post.name;
  $('v-avatar').textContent = post.author[0].toUpperCase();
  $('v-author').textContent = '@' + post.author;
  $('v-author').href = '?profile=' + post.author;
  $('v-author').onclick = e => { e.preventDefault(); navigate('?profile=' + post.author); };

  if (me.loggedIn && post.author === me.username) {
    const pub = $('v-publish');
    pub.innerHTML = '';
    const btn = document.createElement('button');
    btn.className = 'btn sm outline';
    btn.textContent = 'Publish';
    const msg = document.createElement('span');
    msg.className = 'msg';
    msg.id = 'v-pub-msg';
    btn.onclick = () => doPublish(id, 'v-pub-msg');
    pub.append(btn, msg);
  } else {
    $('v-publish').innerHTML = '';
  }

  const canvas = $('canvas');
  canvas.width = 320;
  canvas.height = 240;

  try {
    await renderWasm(canvas, post.wasm);
  } catch (e) {
    const err = $('wasm-error');
    err.textContent = 'WASM error: ' + e.message;
    err.classList.remove('hidden');
  }
}

// ── Profile ────────────────────────────────────────────
async function showProfile(username) {
  show('profile-view');
  mountBio('profile-view');

  const p = await api('GET', `/api/profile/${username}`);
  if (p.error) { $('p-status-body').textContent = p.error; return; }

  $('p-username').textContent = username;
  $('p-handle').textContent = '@' + username;
  $('p-avatar').textContent = username[0].toUpperCase();

  if (p.status) {
    $('p-status-body').textContent = p.status;
  } else if (p.statusPrivate && p.status === null) {
    $('p-status-body').textContent = '';
    show('p-lock-badge');
  } else {
    $('p-status-body').textContent = 'No notes yet.';
    $('p-status-body').classList.add('status-empty');
  }
  if (p.statusPrivate && p.status !== null) show('p-lock-badge');

  if (me.loggedIn) {
    const feed = await api('GET', '/api/feed');
    const mine = feed.filter(s => s.author === username);
    const container = $('p-posts');
    container.innerHTML = '';

    if (!mine.length) {
      container.innerHTML = '<p style="color:#536471;font-size:0.88rem;text-align:center;padding:2rem">No posts yet.</p>';
    } else {
      for (const s of mine) {
        const { post, canvas, handle } = createPostCard({ ...s, author: username });
        handle.remove();
        container.appendChild(post);
        renderWasm(canvas, s.wasm).catch(() => { });
      }
    }
  }
}


window.doPublish = async (id, msgId) => {
  const d = await api('POST', '/api/publish', { id });
  if (d.ok) setMsg(msgId, 'Admin is on their way!');
  else setMsg(msgId, d.error || 'Error', true);
};

initBioEditor();
init();
