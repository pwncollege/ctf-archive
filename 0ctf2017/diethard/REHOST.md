# REHOSTING

Link to files: [0CTF 2017](https://github.com/david942j/ctf-writeups/tree/master/0ctf-quals-2017/diethard)

## Challenge Setup
As this challenge calls /challenge/flag so we are going to create a symlink between /challenge/flag and /flag with this command:
```
#!/bin/bash

ln -s /flag /challenge/flag 2>/dev/null
```