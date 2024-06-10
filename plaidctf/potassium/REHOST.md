# REHOSTING

Files can be found here: [PlaidCTF 2018](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/PlaidCTF/2018/potassium)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge. There was 1 libc files for the challenge which is `libc-2.23.so`.

Use this:
```
ldd potassium
```

If this gives the file name then the libc file is recognized and the challenge should work.

## Dependency Troubleshooting
If the libc files are used as dependencies by the challenge so we might runto problems where the files are not recognized so we can use this command to patch that:
```
patchelf --set-rpath /challenge /challenge/potassium
```