#!/opt/pwn.college/python

from Crypto.Util.number import inverse, bytes_to_long, getPrime, isPrime
from math import gcd
# from secret import flag
with open('/flag', 'r') as file:
    flag = file.read().strip()

PBITS = 512
e = 0x10001

def stage_one(data: bytes):
    m = bytes_to_long(data)
    p = getPrime(PBITS)
    q = getPrime(PBITS)
    b = 7
    n = p**b * q
    print(f"p = {p}")
    print(f"e = {e}")
    print(f"dp = {inverse(e, p-1)}")
    print(f"b = {b}")
    print(f"ct = {pow(m, e, n)}\n")

def stage_two(data: bytes):
    m = bytes_to_long(data)
    p = getPrime(PBITS)
    q = p + 2
    while not isPrime(q):
        q += 2
    n = p * q
    print(f"n = {n}")
    print(f"e = {e}")
    print(f"ct = {pow(m, e, n)}\n")

#Encoding

flag_bytes = flag.encode()

print("=== Stage 1 ===")
stage_one(flag_bytes[:len(flag_bytes)//2])
print("=== Stage 2 ===")
stage_two(flag_bytes[len(flag_bytes)//2:])

#Original
#print("=== Stage 1 ===")
#stage_one(flag[:len(flag)//2])
#print("=== Stage 2 ===")
#stage_two(flag[len(flag)//2:])
