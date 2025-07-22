#!/usr/bin/exec-suid -- /usr/bin/python3
from Crypto.Util.number import bytes_to_long, inverse
MASK64 = (1 << 64) - 1                    

def _rotl(x: int, k: int) -> int:
    return ((x << k) | (x >> (64 - k))) & MASK64

class xorshiro256:
    
    def __init__(self, seed):
        if len(seed) != 4:
            raise ValueError("seed must have four 64-bit words")
        self.s = [w & MASK64 for w in seed]


    @staticmethod
    def _temper(s1: int) -> int:
        return (_rotl((s1 * 5) & MASK64, 7) * 9) & MASK64


    def next_raw(self) -> int:
        s0, s1, s2, s3 = self.s
        t = (s1 << 17) & MASK64

        s2 ^= s0
        s3 ^= s1
        s1 ^= s2
        s0 ^= s3            
        s2 ^= t
        s3  = _rotl(s3, 45)

        self.s = [s0, s1, s2, s3]
        return s1          
    
    def randrange(self, start, stop, inclusive=False):
        if inclusive:
            return start + self.next_raw() % (stop - start + 1)
        return start + self.next_raw() % (stop - start)

    def __call__(self) -> int:
        return self._temper(self.next_raw())

