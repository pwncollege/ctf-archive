# REHOSTING

Link to files: [UIUICTF 2023](https://github.com/sigpwny/UIUCTF-2023-Public/blob/main/challenges/crypto/at_home/challenge/chal.py)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file that we need to run is the `chal.py`. It does not produce the `chal.txt` but I used `.init` file that runs as sudo for every challenge. I put this code in for it to produce `chal.txt`:

```
#!/bin/bash

python /challenge/chal.py >> /challenge/chal.txt
```