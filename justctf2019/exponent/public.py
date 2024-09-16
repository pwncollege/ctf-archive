#!/opt/pwn.college/python
import sys
import signal
from gmpy2 import isqrt, next_prime, gcd, lcm, invert, is_prime
from random import randint
from private import get_pq, get_ed


def handler(signum, frame):
    print('Time limit exceeded. Good bye!')
    sys.stdout.flush()
    sys.exit(1)

signal.signal(signal.SIGALRM, handler)
signal.alarm(45)

for i in range(250):
    print("Question", str(i+1) + '/250')
    p, q = get_pq()
    e, d = get_ed(p, q)
    a = randint(2, p*q)
    m = randint(gcd(p-1, q-1), max(gcd(p-1, q-1)*10, int(isqrt(isqrt(p)))))

    assert pow(a, e*d, p*q) == a, "It's not you, it's me."

    print(p, q, e, d%m, m)
    dc = int(input())

    assert dc < (p*q), "Too big dc."
    assert d%m == dc%m, "Incorrect residue."
    assert pow(a, e*dc, p*q) == a, "Incorrect exponent."

with open('/flag') as flag:
    print(flag.read().strip())
