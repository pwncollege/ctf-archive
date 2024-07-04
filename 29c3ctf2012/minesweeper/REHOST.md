# REHOSTING

Link to files: [29c3 CTF 2012](http://dl.ctftime.org/57/193/minesweeper.rar)

## Challenge Setup
The challenge has no dependencies.

## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag.txt 2>/dev/null
```