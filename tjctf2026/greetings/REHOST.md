# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2809)

## Challenge Setup
This rehost ships the original 64-bit PIE binary `greetings` plus the bundled `libc.so.6` and `ld-linux-x86-64.so.2` (the binary requires a newer glibc than the base image provides). Before launch, patch the binary to use the bundled loader/libc:

```
patchelf --set-interpreter /challenge/ld-linux-x86-64.so.2 --set-rpath /challenge greetings
```

`greetings` is the only setuid binary (`chmod u+s`); it runs as root and the exploit reads `/flag` directly (no flagCheck). The vulnerability is an unchecked `fgets` size into a 64-byte stack buffer with an RWE stack; the intended solve is a 1/16 blind partial-overwrite of the saved RIP into a `jmp rax` gadget that lands on stack shellcode reading `/flag`.

## Validation
Validated on pwn.college: the setuid binary read the real `/flag` (`pwn.college{...}`) via the blind partial-overwrite + stack shellcode.
