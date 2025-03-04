# REHOSTING

Link to files: [UIUICTF 2022](https://github.com/sigpwny/UIUCTF-2022-Public/tree/main/crypto/asr)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file that we need to run is the `chal.py`. It does not produce the `chal.txt` but I used `.init` file that runs as sudo for every challenge. I put this code in for it to produce `chal.txt`:

```
#!/bin/bash

/challenge/chal >> /challenge/chal.txt
```