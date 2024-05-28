# REHOSTING

Link to files: [0CTF 2018](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/0CTF/2018/Quals/pwn/Zer0FS)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.
Since this challenge has a lot of files so make sure to download everything.
Use this:
```
ldd zerofs
```
### Adding A Custum Binary Executable
This challenge has binary file which needs root access to run so I made another binary file which just runs the zerofs challenge with root access.

### Changing User Permissions

For adding hacker (the user id for pwn.college):
```
echo "hacker ALL=(ALL:ALL) NOPASSWD: /challenge/zerofs" > /etc/sudoers.d/hacker
chmod 0440 /etc/sudoers.d/hacker

chmod 4755 /usr/bin/sudo
```

Giving zerofs binary execuatble root access:
```
sudo -u root /challenge/zerofs
```

Giving the binary for run_zerofs executable access:
```
sudo chmod +x run_zerofs
```