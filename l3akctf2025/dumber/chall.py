from Crypto.Util.number import  bytes_to_long, long_to_bytes
from sage.all import *

a,b,p = ?,?,?

pt1="L3AK{test_"
pt2="flag}"

E = EllipticCurve(Zmod(p), [a, b])
p,q=E.random_element(),E.random_element()
u=bytes_to_long(pt1.encode())*p
v=bytes_to_long(pt2.encode())*q

# I will help u <3
print(p,u,q,v)