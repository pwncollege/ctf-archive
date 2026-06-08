# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2809)

## Challenge Setup
This rehost ships the original 64-bit PIE binary `Ox78` plus the bundled glibc-2.34 `libc.so.6` and `ld-linux-x86-64.so.2` (the House-of-Apple-2 offsets are hardcoded against glibc 2.34). Before launch, patch the binary to use the bundled loader/libc:

```
patchelf --set-interpreter /challenge/ld-linux-x86-64.so.2 --set-rpath /challenge Ox78
```

`Ox78` is the only setuid binary (`chmod u+s`); it runs as root and the exploit reads `/flag` directly (no flagCheck). The binary opens `/tmp/test.txt` read-only on startup, so that file must exist. The intended solve is a double-stage FSOP / House-of-Apple-2 that pivots through `setcontext` to `execve`; for a setuid target the final exec must be a direct `execve("/bin/cat", ["/bin/cat","/flag"])` (a `/bin/sh` shell would drop the setuid privilege).

## Validation
Validated on pwn.college: the setuid binary read the real `/flag` (`pwn.college{...}`) via the FSOP chain calling `execve("/bin/cat","/flag")`.
