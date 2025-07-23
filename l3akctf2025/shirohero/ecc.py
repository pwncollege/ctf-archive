#!/usr/bin/exec-suid -- /usr/bin/python3
import random
from hashlib import sha3_256, sha256
from Crypto.Util.number import bytes_to_long, inverse
from Crypto.Cipher import AES
from Crypto.Util.Padding import unpad, pad
from prng import xorshiro256, MASK64     
import hashlib
import os

class ECDSA:
    """ECDSA implementation for secp256k1 curve"""
    # parameters
    p  = 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F
    a  = 0
    b  = 7
    Gx = 55066263022277343669578718895168534326250603453777594175500187360389116729240
    Gy = 32670510020758816978083085130507043184471273380659243275938904335757337482424
    n  = 0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141
    G  = (Gx, Gy)

    @staticmethod   
    def digest(msg: bytes) -> int:
        """Hash a message and return as integer"""
        return bytes_to_long(sha256(msg).digest())

    @staticmethod
    def point_add(P, Q):
        """Add two points on the elliptic curve"""
        if P == (None, None): 
            return Q
        if Q == (None, None): 
            return P
        (x1, y1), (x2, y2) = P, Q
        if x1 == x2 and (y1 + y2) % ECDSA.p == 0: return (None, None)
        if P == Q:
            l = (3 * x1 * x1) * inverse(2 * y1, ECDSA.p) % ECDSA.p
        else:
            l = (y2 - y1) * inverse(x2 - x1, ECDSA.p) % ECDSA.p
        x3 = (l * l - x1 - x2) % ECDSA.p
        y3 = (l * (x1 - x3) - y1) % ECDSA.p
        return (x3, y3)

    @staticmethod
    def scalar_mult(k, P):
        R = (None, None)
        while k:
            if k & 1: R = ECDSA.point_add(R, P)
            P = ECDSA.point_add(P, P)
            k >>= 1
        return R

    @staticmethod
    def gen_keypair():
        d = random.randint(1, ECDSA.n - 1)         
        Q = ECDSA.scalar_mult(d, ECDSA.G)          
        return d, Q                                 

    @staticmethod
    def ecdsa_sign(h: int, d: int, prng: xorshiro256):
        while True:
            k = prng() % ECDSA.n
            if not k:
                continue
            x, _ = ECDSA.scalar_mult(k, ECDSA.G)
            if x is None:  
                continue
            r = x % ECDSA.n
            if not r:
                continue
            s = (inverse(k, ECDSA.n) * (h + r * d)) % ECDSA.n
            if s:
                return r, s

    @staticmethod
    def ecdsa_verify(h, Q, sig):
        r, s = sig
        if not (1 <= r < ECDSA.n and 1 <= s < ECDSA.n):
            return False
        w  = inverse(s, ECDSA.n)
        u1 = (h * w) % ECDSA.n
        u2 = (r * w) % ECDSA.n
        x, _ = ECDSA.point_add(ECDSA.scalar_mult(u1, ECDSA.G), ECDSA.scalar_mult(u2, Q))
        if x is None:  
            return False
        return (x % ECDSA.n) == r

    
