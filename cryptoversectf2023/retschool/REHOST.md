# REHOSTING

Files can be found here: [Cryptoverse CTF 2023](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/Cryptoverse/2023/pwn/ret2school)

## Challenge Setup
This challenge has only one file which has `library` dependencies which can be completed using the steps given below.

## Dependency Troubleshooting
If the libc files are used as dependencies by the challenge so we might run into problems where the files are not recognized so we can use this command to patch that:
```
patchelf --set-rpath /challenge /challenge/ret2school
```