# REHOSTING

Link to files: [AccessDeniedCTF 2022](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/AccessDenied/2022/crypto/ECC)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file that exists is the `chal.py`. The other file we have is the `output.txt`.

## Instaalling Tinyec
This challenge requires `tinyhec` which can be installed using this command:
```
pip install tinyec
```

This command works assuming you already have pip installed.

## Flag Permissions
This challenge officially uses `flag.txt` file in the current working directory for the flag but as [`pwn.college`](https//:pwn.college.com) uses `/flag`, we changed the file to use that custom flag instead which made us provide python and the source file the permissions to open the flag. The restriction on python was it can only run the source file as sudo to open the flag. This is the bash script written for it and we make sure it is run before every new challenge is started:
```
#!/bin/bash
echo "hacker ALL=(ALL:ALL) NOPASSWD: /challenge/chal.py" > /etc/sudoers.d/hacker
echo "hacker ALL=(ALL:ALL) NOPASSWD: /usr/bin/python chal.py" > /etc/sudoers.d/hacker

chmod 0440 /etc/sudoers.d/hacker

chmod 4755 /usr/bin/sudo

sudo -u root /challenge/chal.py

sudo chmod +x chal.py