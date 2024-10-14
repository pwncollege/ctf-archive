# REHOSTING

Link to files: [UIUICTF 2023](https://github.com/sigpwny/UIUCTF-2023-Public/blob/main/challenges/pwn/chainmail/challenge/chal.c)

## Challenge Setup
This challenge has a `Makefile` and `Dockerfile`, We used the `Makefile` to make my own `chal` since the `chal` file provided was made on `ubuntu 24.04` and `pwn.college` is based on `ubuntu 20.04` so it gave libc errors. We then made the docker image for the challenge by using docker build. Thats all you need for the challenge to run. You can also use `chal` given by the organizers but make sure to have the `flag.txt` file in the right place, We put it in the `/challenge/flag.txt` for `pwn.college` using this script:

```
#!/bin/bash

ln -s /flag /challenge/flag.txt
```