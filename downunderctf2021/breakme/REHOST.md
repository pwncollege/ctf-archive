# REHOSTING

Link to files: [DownUnderCTF 2021](https://github.com/sajjadium/ctf-archives/blob/main/ctfs/DownUnderCTF/2021/crypto/Break_Me/aes-ecb.py)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file taht exists is the `aes-ecb.py` python file.

## Flag Permissions
This challenge officially uses `flag.txt` file in the current working directory for the flag but as [`pwn.college`](https//:pwn.college.com) uses `/flag`, we changed the file to use that custom flag instead which made us provide python and the source file the permissions to open the flag. The restriction on python was it can only run the source file as sudo to open the flag. This is the bash script written for it and we make sure it is run before every new challenge is started:
```
#!/bin/bash
echo "hacker ALL=(ALL:ALL) NOPASSWD: /challenge/aes-ecb.py" > /etc/sudoers.d/hacker
echo "hacker ALL=(ALL:ALL) NOPASSWD: /usr/bin/python aes-ecb.py" > /etc/sudoers.d/hacker

chmod 0440 /etc/sudoers.d/hacker

chmod 4755 /usr/bin/sudo

sudo -u root /challenge/aes-ecb.py

sudo chmod +x aes-ecb.py