# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original static, non-PIE, not-stripped PPC64 big-endian (ELFv1) binary `speed4` plus the bundled `qemu-ppc64` (qemu-user) used to run the foreign-architecture binary on the (x86-64) host:

```
./qemu-ppc64 ./speed4
```

The shellcode opens a **relative** `flag` file. For a setuid-root /flag rehost (DIRECT-PWN), make **`qemu-ppc64` setuid-root** (`chown root:root qemu-ppc64; chmod u+s qemu-ppc64`) — qemu-user runs the emulated PPC64 process with the host euid, so the shellcode's raw `open` syscall executes as root. Then make the relative path resolve to the real flag: `ln -sf /flag /challenge/flag` (deploy-time). The shellcode reads `/flag` via the symlink with raw syscalls (no shell -> euid preserved, unlike a `system()` win).

## Exploit
`main` caps shellcode at `0x400`, `mmap`s an RWX page, copies the input, flips it RX, and jumps to it. The bundled `qemu-ppc64` breaks libc's `openat`/absolute-path opens (SIGSYS), so the shellcode reuses the in-binary `sc` instruction at `0x10023b20` and invokes raw syscalls: `open("flag",0,0)` (#5), `read(fd,buf,0x100)` (#3), `write(1,buf,n)` (#4), with a `./flag` fallback.

## Validation
Validated on pwn.college (setuid-root `qemu-ppc64` + `/challenge/flag -> /flag` symlink): the PPC64 raw-syscall shellcode read the real root-only `/flag` (`pwn.college{practice}`). Note `/flag` absolute opens died with SIGSYS under this qemu, but the relative `flag` symlink path works. The bundled binary + `qemu-ppc64` are the exact distributed artifacts (md5-verified against the original `files.tgz`).
