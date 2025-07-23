from Crypto.Util.number import  bytes_to_long, long_to_bytes
from sage.all import *

a = 121547024516589838748345110141426750807 
b = 7914092104289289683791712783390872437 
p = 13291510692868661437224070910406918137926842040211728641528067393501968123210518869652984806112658744065588422523015753882654205841020261730856999798869673

pt1="L3AK{test_"
pt2="flag}"

E = EllipticCurve(Zmod(p), [a, b])
p,q=E.random_element(),E.random_element()
u=bytes_to_long(pt1.encode())*p
v=bytes_to_long(pt2.encode())*q

# I will help u <3
print(p,u,q,v)