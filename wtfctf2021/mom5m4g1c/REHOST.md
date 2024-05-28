# REHOSTING

Link to files: [wtfCTF 2021](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/wtf/2021/pwn/MoM5m4g1c)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.
Since this challenge has a lot of files so make sure to download everything.
Use this:
```
ldd mom5m4g1c
```

## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash
# Create a symbolic link if it doesn't already exist
if [ ! -L /challenge/.flag.txt ]; then
    ln -s /flag /challenge/.flag.txt
fi
```
This gives the flag files executable access:
```
# Ensure the flag file and the symlink have the correct permissions
chmod 644 /flag
chmod 644 /challenge/.flag.txt
```