"I'm trying to test my FSOP prevention mechanism so I can share it with my coworkers who know nothing about security. It should be foolproof right?"

The program leaks a `FILE` structure and a libc pointer, then lets you overwrite the structure in two stages guarded by a homemade FSOP "prevention" check.

---
**Author:** TJCTF 2026
