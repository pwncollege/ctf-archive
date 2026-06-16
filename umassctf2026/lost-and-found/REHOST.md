# REHOSTING

Files can be found here: [UMass CTF 2026](https://ctftime.org/event/2937)

## Challenge Setup
The large VM appliance for this entry (`ctf-vm.ova`, ~406 MB) is stored outside the git repo at `../ctf-archive-external/umassctf2026/lost-and-found/` relative to the `ctf-archive` repo root (kept out of git because it exceeds the archive's normal file-size envelope). Copy that bundle into the target deployment directory before launching the challenge (a typical pwn.college layout is `/challenge/lost-and-found/`).

This rehost ships `flagCheck` + `.flag.sha256` in-repo. The original challenge yields a static flag; `flagCheck` validates the original flag and prints `/flag` on success.

## Runtime Notes
- The challenge is solved by examining the recovered ext4 filesystem inside `ctf-vm.ova`; publish the copied appliance for download/offline inspection rather than starting a long-running service.

## Validation
The original flag was confirmed on pwn.college against the built `flagCheck` (correct flag -> `/flag`, wrong -> reject).
