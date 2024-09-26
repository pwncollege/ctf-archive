#!/opt/pwn.college/python
from hashlib import sha256

with open("/flag","rb") as f:
    HHH = sha256()
    HHH.update(f.read())
    flag = HHH.hexdigest()
print(flag)
