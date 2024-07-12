# REHOSTING

Link to files: [PatriotCTF](https://github.com/MasonCompetitiveCyber/PatriotCTF2023/tree/main/pwn/bookshelfv2)

## Challenge Setup
After downloading the files, please check if the libc file is linked to the binary file provided for the challenge.

Use this:
```
ldd bookshelf
```

If this gives the file name then the libc file is recognized and the challenge should work.

Alternatively you can also download the bookshelf.c file and use this command to compile the binary compatible with your system:
```
gcc -o bookshelf bookshelf.c
```
Then give it appropriate permissions:
```
chmod +x bookshelf
```

Note: Since this challenge has its flag in the binary executable, I made a new file flag_check which just checks the flag that user gets from the challenge and calls the /flag to get the pwn.college flag.