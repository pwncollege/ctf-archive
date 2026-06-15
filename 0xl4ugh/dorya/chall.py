from Crypto.PublicKey import RSA
from Crypto.Util.number import bytes_to_long, inverse

FLAG = b"0xL4ugh{long_redacted_flag_AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA}"
KEY_BITS = 1024
X = 7 
def boke(m, coeffs):
    a, b, c_coef, d = coeffs

    key = RSA.generate(KEY_BITS, e=X)

    num = a * m + b
    den = c_coef * m + d
    den_inv = inverse(den, key.n)
    padded_m = (num * den_inv) % key.n

    c = pow(padded_m, key.e, key.n)

    coeffs[0] += 2**1024
    coeffs[1] += 4**1024
    coeffs[2] += 6**1024
    coeffs[3] += 8**1024

    return {"n": key.n, "c": c}


m = bytes_to_long(FLAG)
out = []
coeffs = [1 * 2**1024, 3 * 2**1024, 3 * 2**1024, 7 * 2**1024]

for i in range(7):
    out.append(boke(m, coeffs))

with open("out.txt", "w") as fp:
    print(out, file=fp)