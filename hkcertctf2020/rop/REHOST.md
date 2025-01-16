# REHOSTING

Files can be found here: [HKCERTCTF 2020](https://github.com/hkcert-ctf/CTF-Challenges/tree/main/CTF-2020/4.%20Binary%20Exploitation/1.%20ROP/Challenge)

## Challenge Setup
This challenge has no dependecy files and the `rop` file should work without any issues, you might have to use the libc file given `libc-2.31.so` using this command:

```
patchelf --set-rpath /location_libc /location_challenge
```