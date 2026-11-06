/* CTF Archive search — embedded build. Same engine as the standalone app, with
   namespaced ids/classes (cs-*) and a DOM-ready guard so it runs inside a
   pwn.college dojo page. window.CTF_INDEX is defined just above this script. */
(function () {
  "use strict";
  const INDEX = window.CTF_INDEX;
  if (!INDEX || !document.getElementById("cs-root")) return;
  const CHALS = INDEX.challenges;
  const SOURCE_DOJO = INDEX.source_dojo || "ctf-archive";
  const DIFF_ORDER = ["beginner", "easy", "medium", "hard", "insane"];

  const $ = (id) => document.getElementById(id);
  const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
  const slugify = (s) => (s || "").toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "") || "module";

  const selected = new Map();
  let committedQuery = "";
  let suggestIdx = -1;

  // ---- synonyms + matching (identical engine to standalone) ----
  const SYN_GROUPS = [
    ["crypto", "cryptography", "cryptographic", "cryptology", "cipher", "ciphers", "encryption", "encrypted"],
    ["reverse-engineering", "rev", "reverse", "reversing", "disassembly", "decompilation"],
    ["binary-exploitation", "pwn", "exploitation", "binexp", "memory-corruption"],
    ["web", "web-exploitation", "webexp"],
    ["forensics", "forensic", "dfir"],
    ["steganography", "stego", "steg", "image-stego"],
    ["osint", "recon", "reconnaissance"],
    ["packet-capture", "pcap", "network-forensics", "network-capture", "wireshark"],
    ["heap", "glibc-heap", "heap-exploitation", "heap-overflow"],
    ["format-string", "fmt-string", "fmt", "format", "formatstring", "printf"],
    ["rop", "return-oriented-programming", "return-oriented", "ret2libc", "ropchain"],
    ["buffer-overflow", "stack-overflow", "bof", "stack-smashing", "overflow"],
    ["sql-injection", "sqli", "sqlinjection"],
    ["cross-site-scripting", "xss"],
    ["server-side-request-forgery", "ssrf"],
    ["server-side-template-injection", "ssti", "template-injection"],
    ["path-traversal", "directory-traversal", "lfi", "file-inclusion"],
    ["deserialization", "deserialisation", "insecure-deserialization", "unpickle"],
    ["prng", "rng", "mersenne-twister", "mt19937", "random-number-generator"],
    ["aes", "rijndael"],
    ["elliptic-curve", "ecc", "ecdsa", "ecdlp"],
    ["jwt", "json-web-token"],
    ["race-condition", "toctou"],
    ["pyjail", "python-jail", "jail-escape", "sandbox-escape", "jail"],
    ["use-after-free", "uaf"],
    ["integer-overflow", "int-overflow"],
    ["command-injection", "rce", "remote-code-execution", "os-command-injection"],
    ["shellcode", "shellcoding"],
  ];
  const SYN_INDEX = {};
  for (const g of SYN_GROUPS) for (const m of g) (SYN_INDEX[m] = SYN_INDEX[m] || []).push(...g);

  function expandConcept(concept) {
    const c = concept.trim().toLowerCase();
    const vars = new Set([c, c.replace(/\s+/g, "-"), c.replace(/-/g, " ")]);
    const grp = SYN_INDEX[c] || SYN_INDEX[c.replace(/\s+/g, "-")];
    if (grp) for (const m of grp) vars.add(m);
    return [...vars].filter(Boolean);
  }

  let prepped = false;
  function prep() {
    if (prepped) return;
    prepped = true;
    for (const r of CHALS) {
      const hay = [r.name, r.id.replace(/[-_]/g, " "), r.event, r.module, r.category,
        r.summary, r.tags.join(" "), r.tags.join(" ").replace(/-/g, " "), r.prerequisites.join(" ")].join(" ").toLowerCase();
      const tok = new Set(hay.split(/[^a-z0-9]+/).filter(Boolean));
      for (const t of r.tags) tok.add(t);
      for (const p of r.prerequisites) tok.add(p);
      const tagToks = new Set();
      for (const t of r.tags) t.split("-").forEach((x) => x && tagToks.add(x));
      r._hay = hay; r._tok = tok; r._tagset = new Set(r.tags); r._tagToks = tagToks; r._cat = r.category.toLowerCase();
    }
  }
  function tokenMatchInSet(t, set) {
    if (set.has(t)) return true;
    if (t.length >= 4) for (const st of set) if (st.length >= 4 && (st.startsWith(t) || t.startsWith(st))) return true;
    return false;
  }
  function conceptScore(concept, r) {
    let best = 0;
    for (const v of expandConcept(concept)) {
      const vspace = v.replace(/-/g, " "), vhyph = v.replace(/\s+/g, "-");
      const vtoks = v.split(/[^a-z0-9]+/).filter(Boolean);
      const matched = r._hay.includes(vspace) || r._tok.has(vhyph) || (vtoks.length && vtoks.every((tk) => tokenMatchInSet(tk, r._tok)));
      if (!matched) continue;
      let s = 5;
      if (r._tagset.has(vhyph)) s = 100;
      if (r._cat === v || r._cat === vhyph) s = Math.max(s, 75);
      if (vtoks.some((tk) => r._tagToks.has(tk))) s = Math.max(s, 45);
      if (r.name.toLowerCase().includes(vspace)) s = Math.max(s, 40);
      if (r._hay.includes(vspace)) s = Math.max(s, 12);
      best = Math.max(best, s);
    }
    return best;
  }
  const byScore = (a, b) => b[0] - a[0] || DIFF_ORDER.indexOf(a[1].difficulty) - DIFF_ORDER.indexOf(b[1].difficulty) || a[1].name.localeCompare(b[1].name);
  function rankConcept(concept) {
    const scored = [];
    for (const r of CHALS) { const s = conceptScore(concept, r); if (s > 0) scored.push([s, r]); }
    scored.sort(byScore);
    return scored.map((x) => x[1]);
  }
  function matches(q) {
    prep();
    const concepts = q.split(",").map((s) => s.trim()).filter(Boolean);
    if (!concepts.length) return [];
    if (concepts.length === 1) return rankConcept(concepts[0]);
    const lists = concepts.map(rankConcept);
    const seen = new Set(), out = [];
    for (let i = 0, more = true; more; i++) {
      more = false;
      for (const list of lists) if (i < list.length) { more = true; const r = list[i]; if (!seen.has(r.path)) { seen.add(r.path); out.push(r); } }
    }
    return out;
  }
  function count() { return Math.max(1, Math.min(100, parseInt($("cs-count").value, 10) || 10)); }

  function activeTopic() {
    const c = committedQuery.split(",").map((s) => s.trim()).filter(Boolean);
    return c.length ? c[0].split(/\s+/)[0] : null;
  }

  // ---- suggestions ----
  function renderSuggest() {
    const q = $("cs-q").value.trim();
    const box = $("cs-suggest");
    if (!q) { box.classList.remove("cs-show"); box.innerHTML = ""; return; }
    const list = matches(q).slice(0, 8);
    if (!list.length) { box.innerHTML = '<li style="color:var(--lg)">no matches</li>'; box.classList.add("cs-show"); return; }
    box.innerHTML = list.map((r, i) =>
      `<li data-path="${esc(r.path)}" class="${i === suggestIdx ? "cs-active" : ""}">
        <span class="cs-s-name">${esc(r.name)}</span>
        <span class="cs-s-meta">${esc(r.category)} · <span class="cs-diff cs-diff-${r.difficulty}">${r.difficulty}</span></span>
      </li>`).join("");
    box.classList.add("cs-show");
    box.querySelectorAll("li[data-path]").forEach((li) =>
      li.addEventListener("mousedown", (e) => { e.preventDefault(); commit($("cs-q").value); }));
  }

  // ---- commit / results ----
  function commit(q) {
    q = (q || "").trim();
    $("cs-suggest").classList.remove("cs-show");
    suggestIdx = -1;
    if (!q) return;
    committedQuery = q;
    const all = matches(q);
    const shown = all.slice(0, count());
    const meta = $("cs-meta");
    if (!all.length) { meta.innerHTML = `No challenges match “${esc(q)}”. Try a broader topic.`; $("cs-results").innerHTML = ""; return; }
    meta.innerHTML = `Top <b>${shown.length}</b> of ${all.length} for “<b>${esc(q)}</b>” <span class="cs-sel-all" id="cs-selall">▸ select all shown</span>`;
    $("cs-selall").addEventListener("click", () => {
      const topic = activeTopic();
      shown.forEach((r) => selected.set(r.path, { rec: r, topic }));
      renderResults(shown); renderTray(); toast(`Selected ${shown.length}`);
    });
    renderResults(shown);
  }

  function renderResults(list) {
    $("cs-results").innerHTML = list.map((r) => {
      const sel = selected.has(r.path);
      return `<div class="cs-item ${sel ? "cs-selected" : ""}" data-path="${esc(r.path)}">
        <div class="cs-item-header">
          <input type="checkbox" class="cs-cb" ${sel ? "checked" : ""} />
          <span class="cs-chal-name">${esc(r.name)}</span>
          <span class="cs-chal-right">
            <span class="cs-cat">${esc(r.category)}</span>
            <span class="cs-diff cs-diff-${r.difficulty}">${r.difficulty}</span>
            <span>${esc(r.event)}</span>
            <span class="cs-caret">▸</span>
          </span>
        </div>
        <div class="cs-item-body">
          ${r.summary ? `<p class="cs-summary">${esc(r.summary)}</p>` : ""}
          <div class="cs-tags">${r.tags.map((t) => `<span class="cs-tag" data-tag="${esc(t)}">${esc(t)}</span>`).join("")}</div>
          <div class="cs-path">${esc(r.module)} / ${esc(r.id)}${r.solve_count != null ? "  ·  " + r.solve_count + " solves" : ""}${r.points != null ? "  ·  " + r.points + " pts" : ""}</div>
        </div>
      </div>`;
    }).join("");

    $("cs-results").querySelectorAll(".cs-item").forEach((item) => {
      const path = item.dataset.path;
      const cb = item.querySelector(".cs-cb");
      item.querySelector(".cs-item-header").addEventListener("click", (e) => { if (e.target === cb) return; item.classList.toggle("cs-open"); });
      cb.addEventListener("click", (e) => { e.stopPropagation(); toggleSelect(path, cb.checked); });
      item.querySelectorAll(".cs-tag").forEach((t) =>
        t.addEventListener("click", (e) => { e.stopPropagation(); $("cs-q").value = t.dataset.tag; commit(t.dataset.tag); }));
    });
  }

  function toggleSelect(path, on) {
    if (on) { const rec = CHALS.find((r) => r.path === path); selected.set(path, { rec, topic: activeTopic() || rec.category }); }
    else selected.delete(path);
    const item = $("cs-results").querySelector(`.cs-item[data-path="${CSS.escape(path)}"]`);
    if (item) item.classList.toggle("cs-selected", on);
    renderTray();
  }

  // ---- dojo.yml ----
  function groups() {
    const mode = $("cs-group").value;
    const g = {};
    for (const it of selected.values()) {
      let key = mode === "single" ? ($("cs-dojoname").value || "Challenges") : mode === "category" ? it.rec.category : (it.topic || it.rec.category);
      (g[key] = g[key] || []).push(it);
    }
    return g;
  }
  function yamlScalar(s) { return /[:#{}\[\],&*?|<>=!%@`"']/.test(s) ? JSON.stringify(s) : s; }
  function buildYaml() {
    const id = slugify($("cs-dojoid").value);
    const name = $("cs-dojoname").value || "My CTF Course";
    const out = [
      `id: ${id}  # change to your own unique id`,
      `name: ${yamlScalar(name)}  # change to your course name`,
      `type: hidden  # unlisted: share the link to grant access — change to 'public' to list it for everyone`,
      `# Challenges imported from the ${SOURCE_DOJO} dojo.`,
      "",
      "modules:",
    ];
    const seen = new Set();
    for (const [key, items] of Object.entries(groups())) {
      out.push(`  - id: ${slugify(key)}`, `    name: ${yamlScalar(key)}`, `    challenges:`);
      for (const it of items) {
        if (seen.has(it.rec.path)) continue;
        seen.add(it.rec.path);
        out.push(`      - import:`, `          dojo: ${SOURCE_DOJO}`, `          module: ${it.rec.module}`, `          challenge: ${it.rec.id}`);
      }
    }
    return out.join("\n") + "\n";
  }

  function renderTray() {
    $("cs-selcount").textContent = selected.size;
    if ($("cs-stats")) $("cs-stats").textContent = `${INDEX.counts.challenges} challenges · ${INDEX.counts.events} events`;
    if (!$("cs-yaml").classList.contains("cs-hidden")) $("cs-yaml").textContent = selected.size ? buildYaml() : "# select challenges first";
  }
  function toast(msg) { const t = $("cs-toast"); t.textContent = msg; t.classList.add("cs-show"); clearTimeout(toast._t); toast._t = setTimeout(() => t.classList.remove("cs-show"), 1500); }
  function download() {
    if (!selected.size) return toast("Select challenges first");
    const blob = new Blob([buildYaml()], { type: "text/yaml" });
    const a = document.createElement("a"); a.href = URL.createObjectURL(blob); a.download = "dojo.yml";
    document.body.appendChild(a); a.click(); a.remove(); setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    toast("Downloaded dojo.yml");
  }
  async function copyYaml() {
    if (!selected.size) return toast("Select challenges first");
    try { await navigator.clipboard.writeText(buildYaml()); toast("Copied"); } catch (e) { toast("Copy failed"); }
  }

  function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }
  function init() {
    prep();
    const search = $("cs-q");
    renderTray();
    search.addEventListener("input", debounce(() => { suggestIdx = -1; renderSuggest(); }, 90));
    search.addEventListener("keydown", (e) => {
      const items = $("cs-suggest").querySelectorAll("li[data-path]");
      if (e.key === "ArrowDown") { e.preventDefault(); suggestIdx = Math.min(items.length - 1, suggestIdx + 1); renderSuggest(); }
      else if (e.key === "ArrowUp") { e.preventDefault(); suggestIdx = Math.max(-1, suggestIdx - 1); renderSuggest(); }
      else if (e.key === "Enter") { e.preventDefault(); if (suggestIdx >= 0 && items[suggestIdx]) search.value = items[suggestIdx].querySelector(".cs-s-name").textContent; commit(search.value); }
      else if (e.key === "Escape") { $("cs-suggest").classList.remove("cs-show"); }
    });
    search.addEventListener("blur", () => setTimeout(() => $("cs-suggest").classList.remove("cs-show"), 150));
    $("cs-go").addEventListener("click", () => commit(search.value));
    $("cs-count").addEventListener("change", () => { if (committedQuery) commit(committedQuery); });
    $("cs-download").addEventListener("click", download);
    $("cs-copy").addEventListener("click", copyYaml);
    $("cs-clear").addEventListener("click", () => { selected.clear(); if (committedQuery) commit(committedQuery); renderTray(); toast("Cleared"); });
    $("cs-preview").addEventListener("click", () => { $("cs-yaml").classList.toggle("cs-hidden"); renderTray(); });
    ["cs-group", "cs-dojoid", "cs-dojoname"].forEach((id) => $(id).addEventListener("input", renderTray));
  }

  window.__ctf = { buildYaml, selected, matches, commit };
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
