# REHOSTING

Link to files: [PatriotCTF](https://github.com/MasonCompetitiveCyber/PatriotCTF2023/blob/main/pwn/softshell)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd softshell
```

If this gives the file name then the libc file is recognized and the challenge should work.

Alternatively you can also download the softshell.c file and use this command to compile the binary compatible with your system:
```
gcc -o softshell softshell.c
```
Then give it appropriate permissions:
```
chmod +x softshell
```

### Flag Path Change
Since the challenge calls flag.txt in the directory the challenge is in, we need to make sure the flag.txt is linked to /flag where our flag is:
```
ln -s /flag /challenge/flag.txt 2>/dev/null
```
This creates a symlink between the flag.txt which the challenge calls and /flag which is where the pwn.college flag is kept.