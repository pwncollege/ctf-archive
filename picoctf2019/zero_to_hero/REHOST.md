# REHOSTING

Files can be found here: [picoCTF 2019](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/picoCTF/2019/pwn/zero_to_hero)

## Challenge Setup
There are 2 libc files which `zero_to_hero` needs to run, check if the dependencies are linked-
```
ldd zero_to_hero
```
Use the [path setting]() if the libc are not linked.

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