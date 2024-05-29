# REHOSTING

Link to files: [CSAW 2017](https://github.com/osirislab/CSAW-CTF-2017-Quals/tree/master/misc/cvv)

## Challenge Setup
This challenge does not require any additional files except the cvv binary execuatble. The binary file can be re-compiled with the python files it was originally made with which can be found in the link provided above.

## Flag Location Change
This challenge gets `flag.txt` from the current directory so we are going to create a symlink between `flag.txt` and `/flag` so the hacker gets the pwn.college flag:
```
#!/bin/bash

ln -s /flag /challenge/flag
```