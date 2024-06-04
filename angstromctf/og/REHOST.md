# REHOSTING

Files can be found here: [angstromctf](https://github.com/blairsec/challenges/tree/master/angstromctf/2024/pwn/og)

## Challenge Setup
this challenge has a binary execuatble with a libc dependency which is `libc.so.6`. To check if the dependencies are met we can run this command:
```
ldd leftright
```

## Flag Symlink
This challenge uses a `flag.txt` file to provide the flag for the hacker, so we can create a symlink between the `flag` and `/flag` which our location for pwn.college flag by using these commands-
```
#!/bin/bash

# Attempt to create a symbolic link
ln -s /flag /challenge/flag.txt 2>/dev/null
```

We have it in the `.init` file so the link is created between the files as soon as the challenge is started for each new environment.

## Konsole Error
This challenge's writeup talks about a konsole error which can be fixed by this code:
```
context.terminal=["konsole", "-e"]
```
or you can install konsole:
```
sudo apt-get install konsole
```

## Dependency Troubleshooting
If the libc files are used as dependencies by the challenge so we might runto problems where the files are not recognized so we can use this command to patch that:
```
patchelf --set-rpath /challenge /challenge/leftright
```