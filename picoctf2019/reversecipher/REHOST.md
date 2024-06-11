# REHOSTING

Files can be found here: [picoCTF 2019](hhttps://github.com/sajjadium/ctf-archives/tree/main/ctfs/picoCTF/2019/rev/reverse_cipher)

## Challenge Setup
There is only 1 file which is `rev` that does not have any dependencies.

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```

## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag.txt 2>/dev/null
```