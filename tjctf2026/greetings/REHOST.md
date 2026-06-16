# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2809)

## Challenge Setup
This rehost ships the original 64-bit PIE binary `greetings` plus the bundled `libc.so.6` and `ld-linux-x86-64.so.2` (the binary requires a newer glibc than the base image provides). Before launch, patch the binary to use the bundled loader/libc:

```
patchelf --set-interpreter /challenge/ld-linux-x86-64.so.2 --set-rpath /challenge greetings
```

`greetings` is the only setuid binary (`chmod u+s`); it runs as root and reads the root-only `/flag` directly (no flagCheck).

## Validation
Validated on pwn.college: the setuid binary read the real `/flag` (`pwn.college{...}`).
