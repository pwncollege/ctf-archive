# REHOSTING

Link to files: [0CTF 2018](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/0CTF/2018/Finals/pwn/freenote2018)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd freenote2018
```

## Dependency Troubleshooting
If the libc files are used as dependencies by the challenge so we might runto problems where the files are not recognized so we can use this command to patch that:
```
patchelf --set-rpath /challenge /challenge/freenote2018
```
