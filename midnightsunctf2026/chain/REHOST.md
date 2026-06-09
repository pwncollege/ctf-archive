# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original 32-bit no-PIE binary `chain` plus a bundled
`libc.so.6` and `ld-linux.so.2`. The exact libc is **32-bit glibc 2.35-0ubuntu3.13
(amd64 `libc6-i386` package, Ubuntu 22.04)**. The base image's libc is the wrong
version, so before launch patch the binary to use the bundled loader/libc:

```
patchelf --set-interpreter /challenge/ld-linux.so.2 --set-rpath /challenge chain
```

`chain` is the only setuid binary (`chmod u+s`); it runs as root and reads the
root-only `/flag` directly (no flagCheck).

## Validation
Validated on pwn.college: the setuid binary read the real `/flag`
(`pwn.college{...}`) on 3/3 independent runs.
