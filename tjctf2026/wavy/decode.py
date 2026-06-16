from pathlib import Path

from Crypto.Util.number import long_to_bytes

M = 0xfffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f


def encrypt(val, key):
    amp = val % M
    p = M

    def chebyshev_pair(n):
        if n == 0:
            return 1, amp

        tn, tn1 = chebyshev_pair(n // 2)
        t2n = (2 * tn * tn - 1) % p
        t2n1 = (2 * tn * tn1 - amp) % p

        if n % 2 == 0:
            return t2n, t2n1

        t2n2 = (2 * amp * t2n1 - t2n) % p
        return t2n1, t2n2

    return chebyshev_pair(key)[0]

flag_bytes = Path("flag.enc").read_bytes()

base_val = 0x1337C0DE
frequency_key = 10**25

secret = encrypt(base_val, frequency_key)
secret_bytes = long_to_bytes(secret)
keystream = (secret_bytes * ((len(flag_bytes) + len(secret_bytes) - 1) // len(secret_bytes)))[:len(flag_bytes)]

encrypted = bytes(a ^ b for a, b in zip(flag_bytes, keystream))

print(encrypted)
