#!/usr/bin/env sage
from Crypto.Util.number import bytes_to_long
from sage.all import RealField, sqrt

def spiral(x, phi, iterations=81):
    R = x.parent()
    for i in range(iterations):
        r = R(i) / R(iterations)
        x = r * sqrt(x*x + 1) + (1 - r) * (x + phi)
    return x

def mask(s, rate=0.13):
    n = len(s)
    k = int(n * rate)
    out = list(s)
    for i in range(k):
        out[(i * n) // k] = '?'
    return ''.join(out)

flag = bytes_to_long(b'0xL4ugh{?????????????????????}')
flen = len(str(flag))
R = RealField(256)
phi = R((1 + sqrt(5)) / 2)
x = R(flag) * R(10) ** (-flen)

x = str(spiral(x, phi)).replace('.', '')
masked = mask(x)
print(masked) # ?7086013?3756162?51694057?5285516?54803756?9202316?39221780?4895755?50591029