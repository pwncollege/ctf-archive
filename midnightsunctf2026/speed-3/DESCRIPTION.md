A 32-bit x86 service, no PIE. `main` reads 128 bytes with `fgets` into a stack buffer, then calls `printf(buffer)` — a classic format-string vulnerability — and finishes with `exit(0)`. Turn the format string into an arbitrary write to redirect control to the hidden `system("/bin/bash")` function and read the flag.

---
**Author:** Midnight Sun CTF 2026 Team
