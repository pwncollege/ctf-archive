# REHOSTING

Files can be found here: [PlaidCTF 2020](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/PlaidCTF/2020/pwn/emojidb/bin)

## Challenge Setup
There are no libc files for this challenge but there are some scripts which run the challenge. Since the challenge works without the scripts and the scripts used have paths set for a different environment we are going to ignore them.

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```