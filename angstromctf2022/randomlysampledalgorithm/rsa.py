#!/opt/pwn.college/python

from Crypto.Util.number import getStrongPrime

with open('/flag','r') as file:
    f = file.read().strip().encode()

m = int.from_bytes(f,'big')
p = getStrongPrime(512)
q = getStrongPrime(512)
n = p*q
e = 65537
c = pow(m,e,n)
print("n =",n)
print("e =",e)
print("c =",c)
print("phi =",(p-1)*(q-1))
