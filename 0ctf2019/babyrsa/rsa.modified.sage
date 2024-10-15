#!/usr/bin/env sage
# coding=utf-8

from pubkey import P, n, e
from secret import flag
from os import urandom
import binascii

R.<a> = GF(2^2049)

def encrypt(m):
    global n
    assert len(m) <= 256
    # Convert bytes to hex string
    m_hex = binascii.hexlify(m).decode('utf-8')
    m_int = Integer(m_hex, 16)
    m_poly = P(R.fetch_int(m_int))
    c_poly = pow(m_poly, e, n)
    c_int = R(c_poly).integer_representation()
    c_hex = format(c_int, '0256x')
    c = binascii.unhexlify(c_hex)
    return c

if __name__ == '__main__':
    ptext = flag + urandom(256-len(flag))
    ctext = encrypt(ptext)
    with open('flag.enc', 'wb') as f:
        f.write(ctext)

