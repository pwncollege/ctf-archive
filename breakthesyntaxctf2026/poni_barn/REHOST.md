# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/3137)

## Challenge Setup
This is an AArch64 bare-metal kernel booted under `qemu-system-aarch64` (available on the base image):

```
qemu-system-aarch64 -M virt -cpu cortex-a53 -m 128M -nographic -monitor none -nic none -kernel rom_patched.elf
```

The flag is baked statically into the ROM page (file offset `0x33000`, a 40-byte `BtSCTF{...}` slot), NOT in `/flag`. Because the flag is static-in-ROM, this rehost ships a chosen static flag injected into `rom_patched.elf` plus a `flagCheck` and `.flag.sha256` (wavy-style): the exploit recovers the static flag from the ROM, and `flagCheck` validates it and prints `/flag` on success.

The MMU bug: option "pick a pony" range-checks the index with a *signed* compare, so a value that is negative as signed-64 but huge as unsigned passes; `x19 = 0x40020000 + (idx<<4)` then yields an arbitrary 16-byte-aligned pointer. Pointing it at the flag PTE slot (`0x40021118`), overwriting that PTE with a user-readable mapping of physical `0x40023000`, flushing the TLB, then peeking the now-readable flag page recovers the static flag.

## Validation
Validated on pwn.college: `qemu-system-aarch64` v10.x is present; the MMU/PTE exploit recovered the injected static flag from the ROM, and the built `flagCheck` accepted it and printed the real `/flag` (`pwn.college{...}`) while rejecting a wrong flag.
