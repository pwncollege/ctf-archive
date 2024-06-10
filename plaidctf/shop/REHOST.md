# REHOSTING

Files can be found here: [PlaidCTF 2018](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/PlaidCTF/2018/shop)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge. There are 1 libc file for the challenge which is `libc.so.6`.

Use this:
```
ldd shop
```

If this gives the file name then the libc file is recognized and the challenge should work.

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```

## Dependency Troubleshooting
If the libc files are used as dependencies by the challenge so we might runto problems where the files are not recognized so we can use this command to patch that:
```
patchelf --set-rpath /challenge /challenge/shop
```