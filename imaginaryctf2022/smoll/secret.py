#!/opt/pwncollege/python
from Crypto.Util.number import isPrime
import random
from math import prod
NBITS = 512

random.seed("848c895e7a650b6d51ecff9976ce5d7e")

def gen_prime(ubound):
    while True:
        p = random.randrange((ubound - 1)//2) * 2 + 1
        if isPrime(p):
            return p

def gen_full(shared):
    while True:
        p = 2 * shared
        while p.bit_length() < NBITS:
            p *= gen_prime(shared)**random.randrange(1, 6)
        p += 1

        if isPrime(p):
            return p

while True:
    shared = random.randrange(2**18, 2**19) * 2 + 1
    if isPrime(shared):
        break

p = gen_full(shared)
q = gen_full(shared)
assert p != q
