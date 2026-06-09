# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/2773)

## Challenge Setup
This rehost ships the original statically-linked, not-stripped AArch64 (ARM64) binary `speed5` plus the bundled `qemu-aarch64` (qemu-user) used to run the foreign-architecture binary on the (x86-64) host:

```
./qemu-aarch64 ./speed5
```

At startup the binary `open`s a **relative** `./flag` and copies it into an in-process `flag_region` (errors with "Error loading flag" if missing). For a setuid-root /flag rehost (DIRECT-PWN), make **`qemu-aarch64` setuid-root** (`chown root:root qemu-aarch64; chmod u+s qemu-aarch64`) so the emulated process inherits host euid root, and create `ln -sf /flag /challenge/flag` (deploy-time) so the relative `./flag` open resolves to the real flag. No shell is spawned (the program itself reads the flag with its root euid; the shellcode only exfiltrates the in-memory copy) -> euid preserved.

## Exploit
Protocol: prompts `shellcode size:` then `shellcode:`. The size is capped and the shellcode passes a **forbidden-byte filter** ("Forbidden byte 0x.. detected"). `flag_region` is passed to the shellcode in `x0`. The working exploit submits AArch64 shellcode that reads bytes out of `flag_region` (using the binary's own `read`/`write` helper sites resolved via in-binary addresses) and writes them to stdout up to the `}` terminator. All symbols are present, so `nm speed5` surfaces `main`, `execute_shellcode`, and `flag_region` directly.

## Validation
Validated on pwn.college (setuid-root `qemu-aarch64` + `/challenge/flag -> /flag` symlink): the filter-passing AArch64 shellcode exfiltrated the real root-only `/flag` (`pwn.college{practice}`). Shellcode (192 bytes, no 0xd4): built from the recorded `.S` via `clang --target=aarch64-linux-gnu`. The bundled binary + `qemu-aarch64` are the exact distributed artifacts (md5-verified against the original `files.tgz`).
