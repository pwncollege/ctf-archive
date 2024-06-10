# REHOSTING

Files can be found here: [PlaidCTF 2020](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/PlaidCTF/2020/pwn/sandybox)

## Challenge Setup
There are no dependencies so the challenge should work with an exception of flag file, the challenge requires there to be a `flag` file in the current directory so use this command to make that link with the `/flag` file which contains our flag-
```
ln -s /flag /challenge/flag
```

## Flag Symlink
This challenge uses a `flag.txt` file to provide the flag for the hacker, so we can create a symlink between the `flag.txt` and `/flag` which our location for pwn.college flag by using these commands-
```
#!/bin/bash

# Attempt to create a symbolic link
ln -s /flag /challenge/flag.txt 2>/dev/null
```

We have it in the `.init` file so the link is created between the files as soon as the challenge is started for each new environment.

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```