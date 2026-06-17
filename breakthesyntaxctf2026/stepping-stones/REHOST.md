# REHOSTING

Files can be found here: [Live CTF](https://ctftime.org/event/3137)

## Challenge Setup
This rehost ships the original challenge artifact(s). The original challenge yields a static flag, so this rehost adds `flagCheck` and `.flag.sha256`; `flagCheck` validates the original flag and prints `/flag` on success.

## Validation
The original flag was confirmed on pwn.college against the built `flagCheck` (correct flag -> `/flag`, wrong -> reject).
