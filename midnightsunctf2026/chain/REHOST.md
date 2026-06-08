# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original 32-bit no-PIE binary `chain` plus a bundled
`libc.so.6` and `ld-linux.so.2`. The exact libc is **32-bit glibc 2.35-0ubuntu3.13
(amd64 `libc6-i386` package, Ubuntu 22.04)** — re-derived from the writeup's leaked
symbols and verified on-box (`free` at offset `0x97c10`, `printf` at `0x57520`,
`system` at `0x47cd0`, `setuid` at `0xddd20`). The base image's libc is the wrong
version, so before launch patch the binary to use the bundled loader/libc:

```
patchelf --set-interpreter /challenge/ld-linux.so.2 --set-rpath /challenge chain
```

`chain` is the only setuid binary (`chmod u+s`); it runs as root and the exploit
reads `/flag` directly (no flagCheck).

## Exploit / setuid note
The loop is `malloc(0x100) -> fgets(s) -> printf(s) -> free(s)`; the `printf(s)`
format string gives an arbitrary read/write (Partial RELRO, no PIE). Read
`printf@GOT` to defeat libc ASLR, then overwrite `free@GOT`.

Because `/flag` is root-only (mode 400) and the binary is setuid-root, a naive
`free@GOT -> system; "cat /flag"` **fails** — `system()` runs `/bin/sh` (dash),
which drops euid 0 -> ruid at startup, so `cat` runs unprivileged ("Permission
denied"). The cure is to call `setuid(0)` in-process first:

1. Overwrite `free@GOT -> setuid` and, in the *same* `printf`, zero the saved `s`
   slot (`[ebp-0x10]` of the printf/free frame) with a count-0 `%n` write, so the
   trailing `free()` reloads `0` -> `free(0) == setuid(0)`. Now `ruid==euid==0`.
2. Overwrite `free@GOT -> system` (keeping `s` zeroed so that frame's
   `free(0) == system(NULL)` is harmless).
3. Send `cat /flag` — it now runs via `system()` as root and prints `/flag`.

The exploit is self-calibrating (no hard-coded stack addresses) and reads the real
`/flag` reliably.

## Validation
Validated on pwn.college: the setuid binary read the real `/flag`
(`pwn.college{...}`) on 3/3 independent runs via the format-string GOT-overwrite +
in-process `setuid(0)` privilege-preservation described above.
