# REHOSTING

Files can be found here: [angstromctf](https://github.com/blairsec/challenges/blob/master/angstromctf/2024/pwn/heapify)

## Challenge Setup
this challenge has a binary execuatble with two libc dependencies which are `ld-linux-x86-64.so.2` and `libc.so.6`. To check if the dependencies are met we can run this command:
```
ldd heapify
```

## Flag Symlink
This challenge uses a `flag.txt` file to provide the flag for the hacker, so we can create a symlink between the `flag` and `/flag` which our location for pwn.college flag by using these commands-
```
#!/bin/bash

# Attempt to create a symbolic link
ln -s /flag /challenge/flag.txt 2>/dev/null
```

We have it in the `.init` file so the link is created between the files as soon as the challenge is started for each new environment.

## Dependency Troubleshooting
If the libc files are used as dependencies by the challenge so we might runto problems where the files are not recognized so we can use this command to patch that:
```
patchelf --set-rpath /challenge /challenge/heapify
```