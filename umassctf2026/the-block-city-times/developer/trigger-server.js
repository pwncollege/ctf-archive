const express    = require('express');
const { spawn }  = require('child_process');
const path       = require('path');

const PORT = process.env.PORT || 9001;
const app  = express();
app.use(express.json());

app.post('/report', (req, res) => {
  const endpoint = req.body?.endpoint || '/api/config';
  const child    = spawn('node', [path.join(__dirname, 'report-api.js')], {
    env: { ...process.env, REPORT_ENDPOINT: endpoint },
  });

  let stdout = '';
  let stderr = '';
  child.stdout.on('data', d => { stdout += d; });
  child.stderr.on('data', d => { stderr += d; });

  child.on('close', code => {
    let report = null;
    try { report = JSON.parse(stdout.trim()); } catch (_) {}
    res.json({ success: code === 0 && report !== null, report, log: stderr.trim() });
  });
});

app.listen(PORT, () => console.log(`Report trigger server on :${PORT}`));
