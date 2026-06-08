# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original 64-bit PIE binary `drop` plus a bundled `libc.so.6` and `ld-linux-x86-64.so.2` (the binary requires a newer glibc than the base image provides). Before launch, patch the binary to use the bundled loader/libc:

```
patchelf --set-interpreter /challenge/ld-linux-x86-64.so.2 --set-rpath /challenge drop
```

`drop` is the only setuid binary (`chmod u+s`); it runs as root and the exploit reads `/flag` directly (no flagCheck). The first mmap is at a fixed address filled with a deterministic `rand()` stream (seed = `floor(time/5)*5`); replay that stream (using the **same** bundled glibc's `rand()`) to locate ROP gadgets, build a chain that mprotects the page RWX and reads syscall-only shellcode that opens and dumps `/flag`. ~50% of seeds lack a `syscall;ret` gadget, so retry until a usable seed is found. The shellcode uses raw syscalls (no shell), so the setuid-root euid is preserved when reading `/flag`.

## Validation
Validated on pwn.college: the setuid binary read the real `/flag` (`pwn.college{...}`) via the rand-seeded ROP + syscall shellcode (retry-until-good-seed).
