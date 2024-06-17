# REHOSTING

Link to files: [DownUnderCTF 2021](https://github.com/sajjadium/ctf-archives/blob/main/ctfs/DownUnderCTF/2021/rev/flag_loader/flag_loader)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file taht exists is the `flag_loader` binary executable.

## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Create a symbolic link if it doesn't already exist
ln -s /flag /challenge/.flag.txt
```