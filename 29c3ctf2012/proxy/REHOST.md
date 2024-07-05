# REHOSTING

Files can be found here: [29c3 CTF 2012](https://shell-storm.org/repo/CTF/29c3/Exploitation/proxy/)

## Challenge Setup
There is a dependency of `libpam.so.0` on the binary executable `proxy`.
Check using this command if its met:
```
ldd proxy
```

### Essential Path Setting
The files is not recognized by default so use this command to change the path:
```
patchelf --set-rpath /challenge /challenge/name_of_challenge_binary
```

Replace the `name_of_challenge_binary` with the actualy name of the binary executable file.

Note: I used .init file to make sure the path change is done at the start of the environment with root access.