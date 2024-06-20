# REHOSTING

Files can be found here: [HITCON CTF 2017](https://github.com/sajjadium/ctf-archives/blob/main/ctfs/HITCON/2017/Quals/sakura)

## Challenge Setup
This challenge has only one file which is `sakura` which has a `libcrypto.so.1.0.0` dependency which cannot be found in the archived repo but you can access it from the same folder this rehost document is in.

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```

### Essential Path Setting
The files is not recognized by default so use this command to change the path:
```
patchelf --set-rpath /challenge /challenge/name_of_challenge_binary
```

Replace the `name_of_challenge_binary` with the actualy name of the binary executable file.

Note: I used .init file to make sure the path change is done at the start of the environment with root access.