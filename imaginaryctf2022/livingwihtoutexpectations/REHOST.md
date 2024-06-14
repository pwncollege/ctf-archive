# REHOSTING

Files can be found here: [ImaginaryCTF 2022](https://github.com/sajjadium/ctf-archives/tree/main/ctfs/ImaginaryCTF/2022/crypto/LivingWithoutExpectations)

## Challenge Setup
There are no dependecy files for `lwe.py` and `output.txt`.


## Flag Linking
This challenge calls a flag.txt file in the current directory so had to link the flag.txt with /flag which is the pwn.college text:
Checks if the file flag.txt exists and if not then it creates a link between flag.txt and /flag
```
#!/bin/bash

# Attempt to create a symboliclink
ln -s /flag /challenge/flag.txt 2>/dev/null
```


