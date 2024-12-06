# REHOSTING

Files can be found here: [angstromctf](https://2024.angstromctf.com/challenges#:~:text=25025-,presidential,-pwn100200)

## Challenge Setup
This challenge just has a python file and the things that needed to change was getting the flag using this:
```
flag = open("/flag", "r").read().open()
```

And we also needed the right previleges in the shell it open on successful completion of the challenge so these were added:
```
import os

try:
    os.setgid(0)
    os.setuid(0)
except PermissionError as e:
    sys.exit(1)
```