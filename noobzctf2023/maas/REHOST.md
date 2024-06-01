# REHOSTING

Link to files: [n00bzCTF 2023](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/n00bzCTF/2023/crypto/MaaS)

## Challenge Setup
This challenge does not require any additional files, just the chall.py file is what is needed.

## Flag Check
As this challenge has its own custom `flag.txt` so we can link the `flag.txt` with chall.py opens with our own `/flag` so the hacker can get the real flag. This is the command being used-

```
ln -s /flag /challenge/flag.txt 2>/dev/null
```

This has been put into the `.init` file so the link can be created when a new challenge is started.