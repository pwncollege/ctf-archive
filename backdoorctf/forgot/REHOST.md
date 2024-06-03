# REHOSTING

Link to files: [BackdoorCTF](https://github.com/smokeleeteveryday/CTF_WRITEUPS/tree/master/2015/BACKDOORCTF/pwnable/forgot/challenge)

## Challenge Setup
This challenge just has file called `forgot` which do not have any dependencies.


## Flag Symlink
This challenge uses a `flag` file to provide the flag for the hacker, so we can create a symlink between the `flag` and `/flag` which our location for pwn.college flag by using these commands-
```
#!/bin/bash

# Attempt to create a symbolic link
ln -s /flag ./flag 2>/dev/null
```

We have it in the `.init` file so the link is created between the files as soon as the challenge is started for each new environment.