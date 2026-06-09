# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original 32-bit x86 binary `speed3-77900a50226a796ec914ed1a5037344c` plus the bundled `libc.so.6` and `ld-linux.so.2` (32-bit glibc 2.35). The binary was built against glibc 2.34+, so it WILL NOT run on the common 2.31 base image's native 32-bit libc (`GLIBC_2.34 not found`). Patchelf to the bundled loader/libc before launch (REQUIRED, not optional):

```
patchelf --set-interpreter /challenge/ld-linux.so.2 --set-rpath /challenge speed3-77900a50226a796ec914ed1a5037344c
```

Make it setuid-root (`chmod u+s`) so the exploit reads the real root-only `/flag` (no flagCheck — this is DIRECT-PWN).

## Exploit (euid-preserving — validated on pwn.college)
`fgets(buf,128)` then `printf(buf)` (format string, arg index **7**) then `exit(0)`. The shipped win at `0x080491d6` runs `system("/bin/bash")`, which **drops euid** under setuid-root, so it is NOT used. Instead, the validated chain preserves euid:

1. Pass 1 (one printf): overwrite `exit@GOT` (`0x0804c01c`) -> `main` (`0x8049240`) to loop, and leak `%2$p` = `libc_base + 0x226620`.
2. Pass 2: write a ROP at `buf+0x5c` = `setuid(0); system("/bin/sh")` and overwrite `exit@GOT` -> libc `add esp,0x7c ; ret` (`libc+0x123309`). `exit(0)`'s call lands esp at `buf-0x20`; `add esp,0x7c` -> `buf+0x5c`, ret runs the ROP.
3. `setuid(0)` makes ruid==euid==0, so the spawned shell keeps root; `cat /flag`.

Bundled-libc (2.35) offsets used: `setuid 0xddd20`, `system 0x47cd0`, `/bin/sh 0x1b90d5`, `pop esi;ret 0x21479`, `add esp,0x7c;ret 0x123309`, leak-anchor `0x226620`.

## Validation
Validated on the pwn.college practice container (setuid-root, glibc 2.31 base + bundled 2.35 via patchelf): the exploit read the root-only `/flag` (`pwn.college{practice}`). The bundled binary/libs are the exact distributed artifacts.
