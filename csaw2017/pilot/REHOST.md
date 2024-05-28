# REHOSTING

## Overview
The files for the challenge can be found here: [CSAW 2017](https://github.com/osirislab/CSAW-CTF-2017-Quals/tree/master/pwn/pilot)

## Challenge Setup
The only file needed is the binary file for the challenge.

### Flag Linking
As the flag being called is `/challenge/flag` but our pwn.college flag is in `/flag` so we use this command to create a link ebtween them.
```
ln -s /flag /challenge/flag 2>/dev/null
```