# REHOSTING

Link to files: [vsCTF](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/vsCTF/2022/pwn/PrivateBank)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.
Since this challenge has a lot of files so make sure to download everything.
Use this:
```
ldd privatebank
```

## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag.txt 2>/dev/null
```