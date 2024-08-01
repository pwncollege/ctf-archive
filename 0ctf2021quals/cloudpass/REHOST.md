# REHOSTING

Link to files: [0CTF 2021 Quals](https://github.com/sajjadium/ctf-archives/blob/main/ctfs/0CTF/2021/Quals/crypto/cloudpass/task.py)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file taht exists is the `task.py` binary executable.

## Flag Permissions
This challenge officially uses `flag.txt` file in the current working directory for the flag but as [`pwn.college`](https//:pwn.college.com) uses `/flag`, we changed the file to use that custom flag instead which made us provide python and the source file the permissions to open the flag. The restriction on python was it can only run the source file as sudo to open the flag. This is the bash script written for it and we make sure it is run before every new challenge is started:
```
#!/bin/bash

pip install pykeepass

echo "hacker ALL=(ALL:ALL) NOPASSWD: /challenge/task.py" > /etc/sudoers.d/hacker
echo "hacker ALL=(ALL:ALL) NOPASSWD: /usr/bin/python task.py" > /etc/sudoers.d/hacker

chmod 0440 /etc/sudoers.d/hacker
chmod 4755 /usr/bin/sudo
```