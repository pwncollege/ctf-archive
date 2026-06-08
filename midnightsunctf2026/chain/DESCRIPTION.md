A tiny 32-bit service loops forever: it prints `f5b: `, reads a line, prints it back, and frees it. The printed line is passed straight to `printf` as the format string. No stack overflow, just one classic mistake — turn that format string into an arbitrary read/write, defeat libc ASLR, and take control.

---
**Author:** Midnight Sun CTF 2026 Team
