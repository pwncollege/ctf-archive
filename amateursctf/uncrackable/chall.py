import os
import hashlib

flag = open("/flag", "rb").read()
assert len(flag) == 47 # just an fyi

class stream():
    def __init__(self, seed = os.urandom(8)):
        self.state = hashlib.sha256(str(seed).encode()).digest()[:len(flag)]
    
    def next(self):
        out = self.state[0]
        self.state = self.state[1:] + bytes([(out + 1) % 256])
        return out
    
    def get_bytes(self, num):
        return bytes(self.next()for _ in range(num))


def xor(a, b):
    assert len(a) == len(b)
    return bytes(i^j for i,j in zip(a,b))

def encrypt(x):
    return xor(x := x.strip(), rng.get_bytes(len(x)))

rng = stream()
open("out.txt", "w").write(b''.join(encrypt(os.urandom(2))for _ in range(10000)).hex() + encrypt(flag).hex())
