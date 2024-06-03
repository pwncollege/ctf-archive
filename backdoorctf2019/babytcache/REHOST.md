# REHOSTING

Link to files: [BackdoorCTF 2019](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/BackdoorCTF/2019/Baby_Tcache)

## Challenge Setup
This challenge just has a binary execuatble file called `babyt_cache` which only has a `libc.so.6` dependency. To check for the same use this command:
```
ldd babyt_cache
```

If the dependency does not exist use `patchelf` to change the dependecy location to `/challenge`. This command can be used:
```
patchelf --set-rpath /challenge /challenge/babyt_cache
```

## Flag Symlink
This challenge uses a `flag.txt` file to provide the flag for the hacker, so we can create a symlink between the `flag.txt` and `/flag` which our location for pwn.college flag by using these commands-
```
#!/bin/bash

# Attempt to create a symbolic link
ln -s /flag /challenge/flag.txt 2>/dev/null
```

We have it in the `.init` file so the link is created between the files as soon as the challenge is started for each new environment.