from Crypto.Util.number import getPrime, bytes_to_long

with open('/flag', 'r') as file:
    FLAG = file.read().strip()

p = getPrime(1024); q = getPrime(1024)
r = getPrime(4086); s = getPrime(4086)

N = p * q
e = 0x10001
m = bytes_to_long(FLAG.encode())
c = pow(m, e, N)

print(c)
print(r * p - s * q)
print(r)
print(s)
