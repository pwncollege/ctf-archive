import random
from sage.all import EllipticCurve, GF
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad

FLAG = b"flag{a4b03ecc-9f3e-47ff-b749-b650f713504c}"

p = 14537114296651069957
a = -30
b = 56
E = EllipticCurve(GF(p), [a,b])
G = E.gens()[0]

n_a = random.randint(1, (p-1))
P1 = n_a*G
n_b = random.randint(1, (p-1))
P2 = n_b*G

S1 = (P1*n_b).xy()[0]
S2 = (P2*n_a).xy()[0]

assert S1 == S2

cipher = AES.new(str(S1).encode()[:16], AES.MODE_ECB)
flag = cipher.encrypt(pad(FLAG, AES.block_size)).hex()

print(f"Generator: {G}")
print(f"Alice Public key: {P1}")
print(f"Bob Public key: {P2}")
print(f"Encrypted flag: {flag}")
