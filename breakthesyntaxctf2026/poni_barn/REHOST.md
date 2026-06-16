# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/3137)

## Challenge Setup
This is an AArch64 bare-metal kernel booted under `qemu-system-aarch64` (available on the base image):

```
qemu-system-aarch64 -M virt -cpu cortex-a53 -m 128M -nographic -monitor none -nic none -kernel rom_patched.elf
```

The flag is baked statically into the ROM page (file offset `0x33000`, a 40-byte `BtSCTF{...}` slot), NOT in `/flag`. Because the flag is static-in-ROM, this rehost ships a chosen static flag injected into `rom_patched.elf` plus a `flagCheck` and `.flag.sha256` (wavy-style): `flagCheck` validates the flag and prints the real `/flag` on success.

## Validation
Validated on pwn.college: `qemu-system-aarch64` v10.x is present; the built `flagCheck` accepted the injected static flag and printed the real `/flag` (`pwn.college{...}`) while rejecting a wrong flag.
