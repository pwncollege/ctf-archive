# REHOSTING

Link to files: [AccessDeniedCTF 2022](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/AccessDenied/2022/pwn/ret2system)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file that exists is the `ret2system`, which is the binary execuatble. You can also look at the `ret2system.c` file which is the source file for the challenge.

## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Create a symbolic link if it doesn't already exist
ln -s /flag /challenge/flag.txt
```