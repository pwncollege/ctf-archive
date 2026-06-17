# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/3137)

## Challenge Setup
This rehost ships the original 32-bit binary `a.out` plus its custom runtime `libponi.so` and the bundled `ld-linux.so.2`. The original binary uses relative interpreter/runpath (`./ld-linux.so.2`, runpath `.`), which is unsafe for a setuid binary, so before launch patch it to absolute paths:

```
patchelf --set-interpreter /challenge/ld-linux.so.2 --set-rpath /challenge a.out
```

`a.out` is the only setuid binary (`chmod u+s`); it runs as root and reads the root-only `/flag` directly (no flagCheck).

## Validation
Validated on pwn.college: the setuid binary read the real `/flag` (`pwn.college{...}`).
