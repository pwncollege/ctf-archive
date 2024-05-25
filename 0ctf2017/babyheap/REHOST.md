# REHOSTING

## Overview
This document provides detailed instructions on how to rehost the Baby Heap challenge from 0CTF 2017.

Follow these steps to set up the challenge environment on your local machine.

## Pre-requisites

Before you begin, ensure you have the following software installed:
- Ubuntu 20.04 LTS
- Python

## Challenge Setup

The challenge can be found here: [Github](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/0CTF/2017/Quals/pwn/babyheap)

After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd babyheap
```

If this gives the file name then the libc file is recognized and the challenge should work.

### Troubleshooting
If the libc file is not recognized then use this command to change the path:
```
patchelf --set-rpath /challenge /challenge/name_of_challenge_binary
```

Replace the `name_of_challenge_binary` with the actualy name of the binary executable file.


