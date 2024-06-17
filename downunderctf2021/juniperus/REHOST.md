# REHOSTING

Link to files: [DownUnderCTF 2021](https://github.com/pwncollege/ctf-archive/tree/main/downunderctf2021/juniperus)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file taht exists is the `shell` binary executable.

## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Create a symbolic link if it doesn't already exist
ln -s /flag /challenge/flag.txt
```