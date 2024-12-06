# REHOSTING

Files can be found here: [angstromctf](https://github.com/blairsec/challenges/tree/master/angstromctf/2024/pwn/bap)

## Challenge Setup
this challenge has a binary execuatble with two libc dependencies which are `ld-linux-x86-64.so.2` and `libc.so.6`. To check if the dependencies are met we can run this command:
```
ldd bap
```

## Flag Symlink
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