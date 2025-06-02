# REHOSTING

Files can be found here: [ångstromCTF 2016](https://github.com/blairsec/challenges/tree/master/angstromctf/2016/crypto/help_center)

## Challenge Setup
This challenge has only one file which which is `server.py` which does not have any dependencies.

## String Error
This challenge required some changes due to `Typeerror` where changing the variables to be bytes and processing the user input made it work with `pwn.college` flag.

Changes:
```
line = input().strip()

try:
    prompt = bytes.fromhex(line)   
except ValueError:
    print("  [!] could not parse your input as hex—try again.")
    continue
```