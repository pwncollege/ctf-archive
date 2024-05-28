# REHOSTING

## Overview
The files for the challenge can be found here: [0CTF 2019 Quals](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/0CTF/2019/Quals/pwn/flropyd)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd flropyd
```
If this gives the file name then the libc file is recognized and the challenge should work.

### Troubleshooting
If the libc file is not recognized then use this command to change the path:
```
patchelf --set-rpath /challenge /challenge/name_of_challenge_binary
```

Replace the `name_of_challenge_binary` with the actualy name of the binary executable file.


