# REHOSTING

Files can be found here: [Zh3r0 CTF V2](https://github.com/zh3r0/zh3r0-ctf/blob/main/V2/crypto/b00tleg/readme.md)

## Challenge Setup
This challenge has only one file which is `challenge.py` and it has hardcoded the flag in the original challenge but we are getting the flag using the code below:

```
flag = open("/flag", "r").read().strip().encode()
```

I used encode as in the original challenge the flag was in bytes and we get it in string from `/flag`