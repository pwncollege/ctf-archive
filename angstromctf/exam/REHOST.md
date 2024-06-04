# REHOSTING

Files can be found here: [angstromctf](https://github.com/blairsec/challenges/tree/master/angstromctf/2024/pwn/exam)

## Challenge Setup
The challenge only has one file `exam` so it does not have any dependencies.

## Flag Symlink
This challenge uses a `flag.txt` file to provide the flag for the hacker, so we can create a symlink between the `flag` and `/flag` which our location for pwn.college flag by using these commands-
```
#!/bin/bash

# Attempt to create a symbolic link
ln -s /flag /challenge/flag.txt 2>/dev/null
```

We have it in the `.init` file so the link is created between the files as soon as the challenge is started for each new environment.