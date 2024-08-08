# REHOSTING

Files can be found here: [Codegate CTF 2013](https://shell-storm.org/repo/CTF/CodeGate-2013/Vulnerable/100/)

## Challenge Setup
This challenge has only one file which which is `94dd6790cbf7ebfc5b28cc289c480e5e` which has a dependency on `libcrypto.so.1.0.0` which is in the challenge directory as well.


## Dependency Issues
After doenloading the file you need use `patchelf` to make sure the depency is met and you can do that by using this command:
```
patchelf --set-rpath /challenge/ /challenge/seddit
```