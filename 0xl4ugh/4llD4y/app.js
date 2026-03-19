import express from 'express';
import { Window } from 'happy-dom';
import { nest } from 'flatnest';

const app = express();
app.use(express.json({ limit: '1mb' }));



app.post('/config', (req, res) => {
  const incoming = typeof req.body === 'object' && req.body ? req.body : {};
  try {
    nest(incoming);
  } catch (error) {
    return res.status(400).json({ error: 'invalid config', details: error.message });
  }

  return res.json({ message: 'configuration applied' });
});

app.post('/render', (req, res) => {
    try{
        const html = typeof req.body?.html === 'string' ? req.body.html : '';
        const window = new Window({ console});
        window.document.write(html);
        const output = window.document.documentElement.outerHTML;
        res.type('html').send(output);

    }
    catch(e){
        console.log("Error ", e)
        res.json({"Error": e})
    }
});

app.listen(3000, '0.0.0.0', () => {
    console.log('Happy DOM listening on http://localhost:3000');
});