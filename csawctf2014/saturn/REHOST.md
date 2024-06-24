# REHOSTING

Files can be found here: [CSAW CTF 2014](https://github.com/pwncollege/ctf-archive/blob/main/csawctf2014/saturn/saturn)

## Challenge Setup
This challenge has only one file which which is `saturn` which does not have any dependencies.

Note: If you check the dependencies of `saturn` by using this command:
```
ldd saturn
```
You might find a libc file missing but that is part of the challenge and hacker to figure out themselves.

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```