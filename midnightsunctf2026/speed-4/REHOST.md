# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original static, non-PIE, not-stripped PPC64 big-endian (ELFv1) binary `speed4` plus the bundled `qemu-ppc64` (qemu-user) used to run the foreign-architecture binary on the (x86-64) host:

```
./qemu-ppc64 ./speed4
```

The challenge opens a **relative** `flag` file from its working directory. For a setuid-root /flag rehost (DIRECT-PWN), make **`qemu-ppc64` setuid-root** (`chown root:root qemu-ppc64; chmod u+s qemu-ppc64`) — qemu-user runs the emulated PPC64 process with the host euid, so it reads the root-only flag as root. Then make the relative path resolve to the real flag: `ln -sf /flag /challenge/flag` (deploy-time). Note `/flag` absolute opens fail with SIGSYS under this qemu, so the relative `flag` symlink path is required.

## Validation
Validated on pwn.college (setuid-root `qemu-ppc64` + `/challenge/flag -> /flag` symlink): the setuid run read the real root-only `/flag` (`pwn.college{practice}`). The bundled binary + `qemu-ppc64` are the exact distributed artifacts (md5-verified against the original `files.tgz`).
