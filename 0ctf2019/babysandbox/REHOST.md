# REHOSTING

## Overview
The files for the challenge can be found here: [0CTF 2019 Quals](https://github.com/infernalheaven/luasandbox/tree/master)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd plang
```
If this gives the file name then the libc file is recognized and the challenge should work.

### Essential Path Setting
The files is not recognized by default so use this command to change the path:
```
patchelf --set-rpath /challenge /challenge/name_of_challenge_binary
```

Replace the `name_of_challenge_binary` with the actualy name of the binary executable file.

Note: I used .init file to make sure the path change is done at the start of the environment with root access.


