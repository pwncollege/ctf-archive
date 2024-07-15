# REHOSTING

Files can be found here: [picoCTF 2019](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/picoCTF/2019/pwn/seed-sPRiNG)

## Challenge Setup
There are no dependecy files for `seed_spring`.

## Flag Check
As this challenge has its own custom flag so we use a simple flag check binary where the hacker can inputs the challenge flag and get the pwn.college flag.
Command to run flag check-
```
/challenge/flagCheck
```

### Flag Linking
As the flag being called is `/challenge/flag.txt` but our pwn.college flag is in `/flag` so we use this command to create a link ebtween them.
```
ln -s /flag /challenge/flag.txt 2>/dev/null
```