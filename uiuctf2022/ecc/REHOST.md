# REHOSTING

Link to files: [UIUICTF 2022](https://github.com/sigpwny/UIUCTF-2022-Public/tree/main/crypto/elliptic_clock_crypto)

## Challenge Setup
This challenge does not have linked files or dependencies. The only file that we need to run is the `ecc.py`. It does not produce the `output.txt` but I used `.init` file that runs as sudo for every challenge. I put this code in for it to produce `output.txt`:

```
#!/bin/bash

/challenge/ecc.py >> /challenge/output.txt
```