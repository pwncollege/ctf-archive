#!/opt/pwn.college/python
from gmpy2 import next_prime, gcd, lcm, invert, is_prime
from random import randint

def get_prime(n):
    return next_prime(randint(2**(n//2), 2**n))

__smooth_table = [2, 2, 2, 2, 3, 3, 3, 5, 5, 7]

def get_smooth(n):
    ret = 1
    while (not is_prime(ret + 1)) or ret < 2**n:
        ret *= __smooth_table[randint(0, len(__smooth_table)-1)]

    return ret+1

def get_mixed(n): # Don't know how to help that
    p = get_prime(n//2)

    q, r = 2 * get_prime(n//2), 2 * get_prime(n//2)

    while not is_prime(p*q+1):
        q = 2 * next_prime(q//2+1)

    while not is_prime(p*r+1):
        r = 2 * next_prime(r//2+1)

    return p*q+1, p*r+1

__question = 0
def get_pq():
    global __question
    __question += 1

    if __question < 70:
        return get_smooth(16), get_smooth(8)
    elif __question < 85:
        return get_smooth(256), get_smooth(128)
    elif __question < 95:
        return get_prime(512), get_prime(256)
    elif __question < 100:
        return get_prime(1024), get_prime(512)
    elif __question < 105:
        return get_smooth(1024), get_smooth(1024)
    elif __question < 130:
        return get_mixed(512)
    elif __question < 132:
        return get_mixed(1024) # Te mozna by pregenerowac, bo jako jedyne sie dlugo generuja
    else:
        return get_mixed(64)

    assert False, "Too many questions"

_e_candidate=[2,3,13,47, 257, 51337]
def get_ed(p, q):
    e = _e_candidate[randint(0, len(_e_candidate)-1)]

    phi = (p-1)*(q-1)

    while(gcd(phi, e) != 1):
        b = randint(0, 2)
        if b == 0:
            e = next_prime(e)
        else:
            e += randint(1, 5)
    d_base = invert(e, lcm(p-1,q-1))
    k = randint(0, gcd(p-1, q-1)-1)
    d = d_base + k * lcm(p-1, q-1)

    return e, d


