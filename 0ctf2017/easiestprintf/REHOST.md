# REHOSTING

Link to files: [0CTF 2017](https://poning.me/2017/03/23/EasiestPrintf/)

## Challenge Setup
The files are located on the web page in the link given above, after clicking on the file name it should automatically download. As the file `easiestprintf` has a dependency on the file `libc.so.6` so we need to download that as well.

After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.
Use this:
```
ldd subtraction
```

If the dependency does not exist use `patchelf` to change the dependecy location to `/challenge`. This command can be used:
```
patchelf --set-rpath /challenge /challenge/easiestprintf
```
