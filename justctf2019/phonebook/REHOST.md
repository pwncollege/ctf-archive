# REHOSTING

Files can be found here: [justCTF 2019](https://github.com/justcatthefish/justctf-2019/blob/master/challenges/pwn_phonebook/public/phonebook)

## Challenge Setup
The file `phonebook` has a dependency on the libc file `libc.so.6` but it gives seg fault if pathcelf is used so if you are on Ubuntu 20.04 then it would work without patching the binary.

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```