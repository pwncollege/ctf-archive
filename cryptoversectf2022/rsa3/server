#!/opt/pwn.college/python

from Crypto.Util.number import *
import random
# from secret import flag
with open('/flag', 'r') as file:
    flag = file.read().strip()

es = [17, 19, 23, 29, 31, 63357]
e = random.choice(es)
p = getPrime(1024)
q = getPrime(1024)
n = p * q
# m = bytes_to_long(flag)
## Encoded
m = bytes_to_long(flag.encode())
c = pow(m, e, n)

if not random.randint(0, 10):
    c = (1 << len(bin(c)[2:])) | c

print(f"n = {n}\ne = {e}\nc = {c}")
