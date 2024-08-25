# REHOSTING

Files can be found here: [canis-lupus-familiaris-bernardus](https://github.com/BASHing-thru-challenges/HSCTF-2021-Writeups/blob/main/crypto/canis-lupus-familiaris-bernardus/README.md)

## Challenge Setup
There are no dependency files for `canis-lupus-familiaris-bernardus.py`.

## Flag Permissions
This challenge officially uses `flag.txt` file in the current working directory for the flag but as [`pwn.college`](https//:pwn.college.com) uses `/flag`, we changed the file to use that custom flag instead which made us provide python and the source file the permissions to open the flag. The restriction on python was it can only run the source file as sudo to open the flag. This is the bash script written for it and we make sure it is run before every new challenge is started:

```
#!/bin/bash
echo "hacker ALL=(ALL:ALL) NOPASSWD: /challenge/main.py" > /etc/sudoers.d/hacker
echo "hacker ALL=(ALL:ALL) NOPASSWD: /usr/bin/python main.py" > /etc/sudoers.d/hacker

chmod 0440 /etc/sudoers.d/hacker

chmod 4755 /usr/bin/sudo

sudo -u root /challenge/main.py

sudo chmod +x main.py

