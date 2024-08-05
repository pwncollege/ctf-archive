# REHOSTING

Files can be found here: [Cryptogod](https://github.com/MasonCompetitiveCyber/PatriotCTF2022-Public/tree/main/Crypto/Cryptogod)

## Challenge Setup
There are no dependency files for `cryptogod.py` or `server.py`

## Flag Permissions
This challenge officially uses their own flag in their file which is visible to user but since [`pwn.college`](https//:pwn.college.com) uses `/flag`, we changed the file to use that custom flag instead which made us provide python and the source file the permissions to open the flag. The restriction on python was it can only run the source file as sudo to open the flag. This is the bash script written for it and we make sure it is run before every new challenge is started:
```
#!/bin/bash

echo "hacker ALL=(ALL:ALL) NOPASSWD: /challenge/cryptogod.py" > /etc/sudoers.d/hacker
echo "hacker ALL=(ALL:ALL) NOPASSWD: /usr/bin/python cryptogod.py" > /etc/sudoers.d/hacker

chmod 0440 /etc/sudoers.d/hacker

chmod 4755 /usr/bin/sudo

sudo -u root /challenge/cryptogod.py

sudo chmod +x cryptogod.py