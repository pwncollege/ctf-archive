#!/opt/pwn.college/python

# Problem by rec, have fun.
import os
import signal
from utils import Sparrow, fault, noise

# Original Challenge
#flag = os.getenv('FLAG')

flag = open("/flag", "r").read().strip()

patience = 1337 + 1337
sec = os.urandom(16)
spr = Sparrow(key=os.urandom(16))


def proof_of_work():
    import random, string, hashlib

    ss = ''.join(random.choices(string.ascii_letters + string.digits, k=20))
    sh = hashlib.sha256(ss.encode()).hexdigest()
    print(f"|    sha256(XXXX + {ss[4:]}) == {sh}")
    prefix = input("|    XXXX>")
    return prefix == ss[:4]


def oracle(t: int) -> dict:
    s, es, cs = os.urandom(16), str(), str()
    spr.st(s)
    for _ in range(t):
        e = os.urandom(16)
        c = noise(spr, fault(spr, e).encrypt(sec))
        es += e.hex()
        cs += c.hex()
    spr.ed()
    return {"s": s.hex(), "e": es, "c": cs}


if __name__ == "__main__":
    try:
        assert proof_of_work()
        signal.alarm(777)

        print('|  Good luck ')
        while patience > 0:
            print('|  Menu:\n|    [H]it\n|    [S]tand\n|    [Q]uit')
            inp = input('|  >').lower()
            if inp == 'h':
                chaos = max(int(input('|  chaos>')), 1)
                if (patience := patience - chaos) >= 0:
                    print(oracle(chaos))
            elif inp == 's':
                if all([
                    bytes.fromhex(input('|  key>')) == spr.key, 
                    bytes.fromhex(input('|  sec>')) == sec
                ]):
                    print('|  Okay ')
                    print('|  🏁', flag)
                    break
            else:
                raise Exception
            patience >>= 1
        else:
            print('|  Nah ')
    except Exception as e:
        print(e)
        print('|  Bye ')
        exit()
