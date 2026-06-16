# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original statically-linked, not-stripped AArch64 (ARM64) binary `speed5` plus the bundled `qemu-aarch64` (qemu-user) used to run the foreign-architecture binary on the (x86-64) host:

```
./qemu-aarch64 ./speed5
```

At startup the binary `open`s a **relative** `./flag` and copies it into an in-process buffer (errors with "Error loading flag" if missing). For a setuid-root /flag rehost (DIRECT-PWN), make **`qemu-aarch64` setuid-root** (`chown root:root qemu-aarch64; chmod u+s qemu-aarch64`) so the emulated process inherits host euid root, and create `ln -sf /flag /challenge/flag` (deploy-time) so the relative `./flag` open resolves to the real flag.

## Validation
Validated on pwn.college (setuid-root `qemu-aarch64` + `/challenge/flag -> /flag` symlink): the setuid run read the real root-only `/flag` (`pwn.college{practice}`). The bundled binary + `qemu-aarch64` are the exact distributed artifacts (md5-verified against the original `files.tgz`).
