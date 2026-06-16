from Crypto.Util.number import *

PBITS, NDAT = 371, 13

with open("flag.txt", "rb") as f:
	m = int.from_bytes(f.read())

N = getPrime(PBITS) * getPrime(PBITS)
e = getRandomRange(731, N)
print(f"{N, e = }")

lcg = lambda s: (s * 3 + 1337) % N

for i in range(NDAT):
	print(pow(m, e, N) + pow(m, e:=lcg(e), N))
