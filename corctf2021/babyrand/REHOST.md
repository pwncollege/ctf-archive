# REHOSTING

Link to files: [corCTF 2021](https://github.com/Crusaders-of-Rust/corCTF-2021-public-challenge-archive/tree/main/crypto/babyrand)

## Challenge Setup
The challenge consists of only 1 file, `script.py` which does not have any dependencies.

## Flag Permissions
This challenge officially uses `flag.txt` file in the current working directory for the flag but as [`pwn.college`](https//:pwn.college.com) uses `/flag`, we changed the file to use that custom flag instead which made us provide python and the source file the permissions to open the flag. The restriction on python was it can only run the source file as sudo to open the flag. This is the bash script written for it and we make sure it is run before every new challenge is started:
```
#!/bin/bash

echo "hacker ALL=(ALL:ALL) NOPASSWD: /challenge/script.py" > /etc/sudoers.d/hacker
echo "hacker ALL=(ALL:ALL) NOPASSWD: /usr/bin/python script.py" > /etc/sudoers.d/hacker

chmod 0440 /etc/sudoers.d/hacker
chmod 4755 /usr/bin/sudo

touch /challenge/output.txt
sudo python /challenge/script.py >> /challenge/output.txt

rm /challenge/script.py
```