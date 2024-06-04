# REHOSTING

Files can be found here: [angstromctf](https://github.com/blairsec/challenges/tree/master/angstromctf/2024/crypto/tss2)

## Challenge Setup
Only two files `tss2.py` and `key.txt` which is used by tss2.py.

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

## fastecdsa error
The challenge python file requires fastecdsa so we use this command to install it:
```
pip install fastecdsaA
```