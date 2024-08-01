# REHOSTING

Link to files: [wtfCTF 2021](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/wtf/2021/pwn/MoM5m4g1c)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.
Since this challenge has a lot of files so make sure to download everything.
Use this:
```
ldd mom5m4g1c
```

### Flag Linking
As the flag being called is `/challenge/flag` but our pwn.college flag is in `/flag` so we use this command to create a link between them.
```
ln -s /flag /challenge/flag 2>/dev/null
```