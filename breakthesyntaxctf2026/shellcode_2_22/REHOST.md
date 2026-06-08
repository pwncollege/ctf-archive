# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/3137)

## Challenge Setup
This rehost ships the original 64-bit binary `a.out` plus the bundled glibc-2.43 in `_glibc243/` (`libc.so.6` + `ld-linux-x86-64.so.2`); the ret2libc offsets are hardcoded against that exact libc. Before launch, patch the binary to use the bundled loader/libc:

```
patchelf --set-interpreter /challenge/ld-linux-x86-64.so.2 --set-rpath /challenge a.out
```

(The bundled libc/ld are shipped under `_glibc243/`; copy them next to the binary, e.g. `cp _glibc243/* /challenge/`, before patching.)

`a.out` is the only setuid binary (`chmod u+s`); it runs as root and the exploit reads `/flag` directly (no flagCheck). The challenge is a weird-machine restricted to a 4-byte alphabet that pivots the stack and ret2libc's. For a setuid target the final ROP must call `setuid(0)` before `system("/bin/sh")` (with a `ret` for 16-byte stack alignment), since otherwise `/bin/sh` drops the setuid privilege; the recovered shell then reads `/flag`.

## Validation
Validated on pwn.college: the setuid binary read the real `/flag` (`pwn.college{...}`) via the weird-machine pivot + `setuid(0); system("/bin/sh")`.
