# REHOSTING

Link to files: [corCTF 2021](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/corCTF/2021/pwn/Chainblock)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd chainblock
```
If this gives the file name then the libc file is recognized and the challenge should work.

### Troubleshooting
If the libc file is not recognized then use this command to change the path:
```
patchelf --set-rpath /challenge /challenge/name_of_challenge_binary
```

Replace the `name_of_challenge_binary` with the actualy name of the binary executable file.

