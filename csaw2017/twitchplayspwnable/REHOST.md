# REHOSTING

Link to files: [CSAW 2017](https://github.com/osirislab/CSAW-CTF-2017-Quals/tree/master/misc/twitchplayspwnable)

## Challenge Setup
This challenge does not require any additional files except the twitchplayspwnable binary execuatble. The binary file can be re-compiled with the python files it was originally made with which can be found in the link provided above.

## Flag Location
This challenge gets the flag from the working directory in the form of a text file so to link that with the pwn.college flag we use this command which I have used in the .init file so it runs automatically whenever the challenge is run-
```
ln -s /flag /challenge/flag.txt
```