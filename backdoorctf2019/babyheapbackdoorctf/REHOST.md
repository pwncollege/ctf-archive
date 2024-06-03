# REHOSTING

Link to files: [BackdoorCTF 2019](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/BackdoorCTF/2019/Baby_Heap)

## Challenge Setup
This challenge just has a binary execuatble file called `babyt_cache` which only has a `libc.so.6` dependency. To check for the same use this command:
```
ldd babyt_cache
```

If the dependency does not exist use `patchelf` to change the dependecy location to `/challenge`. This command can be used:
```
patchelf --set-rpath /challenge /challenge/babyt_cache
```