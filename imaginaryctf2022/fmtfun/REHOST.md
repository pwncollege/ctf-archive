# REHOSTING

Files can be found here: [ImaginaryCTF 2022](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/ImaginaryCTF/2022/pwn/FormatStringFun)

## Challenge Setup
this challenge has a binary execuatble with a libc dependency which is `libc.so.6`. To check if the dependencies are met we can run this command:
```
ldd fmt_fun
```


## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag.txt 2>/dev/null
```


## Dependency Troubleshooting
If the libc files are used as dependencies by the challenge so we might runto problems where the files are not recognized so we can use this command to patch that:
```
patchelf --set-rpath /challenge /challenge/leftright
```
