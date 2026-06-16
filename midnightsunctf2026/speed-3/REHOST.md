# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original 32-bit x86 binary `speed3-77900a50226a796ec914ed1a5037344c` plus the bundled `libc.so.6` and `ld-linux.so.2` (32-bit glibc 2.35). The binary was built against glibc 2.34+, so it WILL NOT run on the common 2.31 base image's native 32-bit libc (`GLIBC_2.34 not found`). Patchelf to the bundled loader/libc before launch (REQUIRED, not optional):

```
patchelf --set-interpreter /challenge/ld-linux.so.2 --set-rpath /challenge speed3-77900a50226a796ec914ed1a5037344c
```

Make it setuid-root (`chmod u+s`) so it reads the real root-only `/flag` (no flagCheck — this is DIRECT-PWN).

## Validation
Validated on the pwn.college practice container (setuid-root, glibc 2.31 base + bundled 2.35 via patchelf): the setuid binary read the root-only `/flag` (`pwn.college{practice}`). The bundled binary/libs are the exact distributed artifacts.
