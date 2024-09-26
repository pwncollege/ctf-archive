# REHOSTING

Link to files: [R3CTF/YUANHENGCTF 2024](https://github.com/r3kapig/r3ctf-2024/tree/master/Crypto/S%F0%9D%91%AA%F0%9D%91%B7-0%CE%B5%CE%B5)

## Challenge Setup
This challenge requires you to unzip and then use `task.py`. You need to change the file name being opened by the file `task.py` since that is not provided and probably redacted.

Here I removed the redacted part and added `/flag` in the `open` function.
`task.py`:
```
with open("/flag","rb") as f:
```