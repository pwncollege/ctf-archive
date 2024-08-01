# REHOSTING

Link to files: [DownUnderCTF 2020](https://github.com/DownUnderCTF/Challenges_2020_public/blob/master/pwn/shellthis/challenge/shellthis)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file taht exists is the `shellthis` binary executable.

## Flag Linking
This challenge calls a flag file in the current directory so had to link the flag with /flag which is the pwn.college text:
Checks if the file flag exists and if not then it creates a link between flag and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag 2>/dev/null
```