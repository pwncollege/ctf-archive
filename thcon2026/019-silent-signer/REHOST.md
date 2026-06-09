# REHOSTING

Files can be found here: [THCon 2K26 CTF](https://ctftime.org/event/3186)

## Challenge Setup
The handout `sst-fwsign` is the firmware-signing binary that loads an embedded eBPF checker to validate the signing token.
The original challenge yields a static flag (solved offline from the artifacts), so this rehost adds `flagCheck` and `.flag.sha256`.
