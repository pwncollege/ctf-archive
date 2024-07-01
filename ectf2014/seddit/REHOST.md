# REHOSTING

Files can be found here: [ECTF 2014](https://github.com/pwncollege/ctf-archive/tree/main/ectf2014/seddit)

## Challenge Setup
The file when extracted has a dependecy of a libc file which is `libcrypto.so.1.0.0`. You can check the dependency by using this command:
```
ldd seddit
```
The file is part of the folder mentioned in the link above.
## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```

## Dependency Issues
After doenloading the file you need use `patchelf` to make sure the depency is met and you can do that by using this command:
```
patchelf --set-rpath /challenge/ /challenge/seddit
```