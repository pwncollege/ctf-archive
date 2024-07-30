# pylint: skip-file
#!/usr/bin/env python

from Crypto.Util.number import *
# from flag import FLAG

with open('/flag', 'r') as file:
    FLAG = file.read().strip()

def nextPrime(n):
    while True:
        n += (n % 2) + 1
        if isPrime(n):
            return n

f = [int(x) for x in bin(int(FLAG.encode().hex(), 16))[2:]]

f.insert(0, 0)
for i in range(len(f)-1):
    f[i] += f[i+1]

a = nextPrime(len(f))
b = nextPrime(a)

# g, h = f * a, f * b
g, h = [[_ for i in range(x) for _ in f] for x in [a, b]]

c = nextPrime(len(f) >> 2)

for arr in [g, h]:
    # arr = [0] * c + arr
    for _ in range(c):
        arr.insert(0, 0)
    for i in range(len(arr) -  c): 
        arr[i] += arr[i+c]

g, h = [int(''.join([str(item) for item in arr]), 5) for arr in [g, h]]

for _ in [g, h]:
    if _ == g:
        fname = 'g'
    else:
        fname = 'h'
    of = open(f'{fname}.enc', 'wb')
    of.write(long_to_bytes(_))
    of.close()
