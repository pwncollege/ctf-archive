# REHOSTING

Link to files: [PatriotCTF](https://github.com/MasonCompetitiveCyber/PatriotCTF2023/blob/main/pwn/guessinggame)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd guessinggame
```

If this gives the file name then the libc file is recognized and the challenge should work.

Alternatively you can also download the guessinggame.c file and use this command to compile the binary compatible with your system:
```
gcc -o guessinggame guessinggame.c
```
Then give it appropriate permissions:
```
chmod +x guessinggame
```
### Flag Length
Since the flag that Patriot CTF uses is 35 characters long which is half of the pwn.college flag, so we need to change the variable length in the c file in two places. The first one is flag variable where the lenght is 35 to 65 and then the place where the system opens flag.

### Flag Path Change
Note: I have these in the .init file as I want the link to be there everytime the environment is run.
If the libc file is not recognized then use this command to change the path:
```
ln -s /flag /challenge/flag.txt 2>/dev/null
```
This creates a symlink between the flag.txt which the challenge calls and /flag which is where the pwn.college flag is kept.
