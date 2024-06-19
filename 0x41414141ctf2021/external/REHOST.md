# REHOSTING

Files can be found here: [0x41414141 CTF](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/0x41414141/2021/pwn/external)

## Challenge Setup
this challenge has a binary execuatble with one libc dependency which is `libc-2.28.so`. To check if the dependencies are met we can run this command:
```
ldd external
```

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```

## Dependency Troubleshooting
If the libc files are used as dependencies by the challenge so we might runto problems where the files are not recognized so we can use this command to patch that:
```
patchelf --set-rpath /challenge /challenge/external
```