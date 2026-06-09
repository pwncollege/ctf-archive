A static, non-PIE PPC64 big-endian (ELFv1) binary. It reads a length, then that many bytes as shellcode (up to `0x400`), `mmap`s an RWX page, copies your shellcode there, flips it RX, and jumps to it. Intended shellcode execution — write PPC64 syscall shellcode that opens and dumps the flag.

---
**Author:** Midnight Sun CTF 2026 Team
