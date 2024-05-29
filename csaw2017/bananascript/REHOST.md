# REHOSTING

Link to files: [CSAW 2017](https://github.com/osirislab/CSAW-CTF-2017-Quals/tree/master/rev/bananascript)

## Challenge Setup
This challenge does not require any additional files except the bananascript binary execuatble. The binary file can be re-compiled with the python files it was originally made with which can be found in the link provided above.

## Running Challenge
As the challenge uses two files to run the challenge, which are `monekyDo` and `banana.script`. The usage for `monekyDo` is `monkeyDo [script]`. Run it like this-
```
monkeyDo banana.script
```

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can input the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```