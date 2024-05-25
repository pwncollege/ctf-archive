# REHOSTING

Link to files: [PatriotCTF](https://github.com/MasonCompetitiveCyber/PatriotCTF2023/blob/main/pwn/breakfastclub)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd breakfastclub
```

If this gives the file name then the libc file is recognized and the challenge should work.

Alternatively you can also download the breakfastclub.c file and use this command to compile the binary compatible with your system:
```
gcc -o breakfastclub breakfastclub.c
```
Then give it appropriate permissions:
```
chmod +x breakfastclub
```

### Flag Path Change
Note: I have these in the .init file as I want the link to be there everytime the environment is run.
Since the challenge calls flag.txt in the directory the challenge is in, we need to make sure the flag.txt is linked to /flag where our flag is:
```
ln -s /flag /challenge/flag.txt 2>/dev/null
```
This creates a symlink between the flag.txt which the challenge calls and /flag which is where the pwn.college flag is kept.
As in this challenge the bianry needs access to flag.txt and we don't want users to see the file we run these commands:
```
chmod 644 /challenge/flag.txt
```
Then we hide the file to some extent:
```
mv /challenge/flag.txt /challenge/.flag.txt
```