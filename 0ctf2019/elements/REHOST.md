# REHOSTING

## Overview
The files for the challenge can be found here: [0CTF 2019 Quals](https://github.com/david942j/ctf-writeups/tree/master/0ctf-quals-2019/elements)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd elements
```
If this gives the file name then the libc file is recognized and the challenge should work.

### Flag Binary
This challenge uses its own flag so it was necessary to create a binary executable where the hacker can input the flag from the challenge and get the pwn.college flag. After making a simple binary executable which checks the flag and opens `/flag` in the current folder which is `/challenge`, it provides hackers with a way to access the flag to complete the challenge.

Code used to make the binary executable from the python file:
Installing necessary tools to make sure the binary works:
```
pip install pyinstaller
```
Then used pyinstaller to get the binary file which would located in the `dist` folder after the next command is run:
```
pyinstaller --onefile flag_check.py
```
This should provide a binary named `flag_check` in the `dist` folder. This binary can be used to access the flag to cpolve the challenge.



