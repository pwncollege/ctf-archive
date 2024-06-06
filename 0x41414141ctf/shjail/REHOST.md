# REHOSTING

No files available to dpownload on github. You can download the files from this ctf archive here: https://github.com/pwncollege/ctf-archive/tree/main/0x41414141ctf/shjail 

## Challenge Setup
Only one file for `shjail.sh`.

## Flag Symlink
This challenge uses a `flag.txt` file to provide the flag for the hacker, so we can create a symlink between the `flag` and `/flag` which our location for pwn.college flag by using these commands-
```
#!/bin/bash

# Attempt to create a symbolic link
ln -s /flag /challenge/flag.txt 2>/dev/null
```

We have it in the `.init` file so the link is created between the files as soon as the challenge is started for each new environment.
