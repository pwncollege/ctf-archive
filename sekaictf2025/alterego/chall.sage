from Crypto.Util.number import *

from random import randint
import os

from montgomery_isogenies.kummer_line import KummerLine
from montgomery_isogenies.kummer_isogeny import KummerLineIsogeny

#FLAG = os.getenv('FLAG', "SEKAI{here_is_test_flag_hehe}").encode()
FLAG = open("/flag", "r").read().strip()

proof.arithmetic(False)

MI = 3
KU = 9
MIKU = 39

ells = [3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97, 101, 103, 107, 109, 113, 127, 131, 137, 139, 149, 151, 157, 163, 167, 173, 179, 181, 191, 193, 197, 199, 211, 223, 227, 229, 233, 239, 241, 251, 257, 263, 269, 271, 277, 281, 283, 293, 307, 311, 313, 317, 331, 337, 347, 349, 353, 359, 367, 373, 587]
p = 4 * prod(ells) - 1

Fp = GF(p)
F = GF(p**2, modulus=x**2 + 1, names='i')
i = F.gen(0)
E0 = EllipticCurve(F, [1, 0])
E0.set_order((p + 1)**2)


def group_action(_C, priv, G):
    es = priv[:]
    while any(es):
        x = Fp.random_element()
        P = _C(x)
        A = _C.curve().a2()
        s = 1 if Fp(x ^ 3 + A * x ^ 2 + x).is_square() else -1

        S = [i for i, e in enumerate(es) if sign(e) == s and e != 0]
        k = prod([ells[i] for i in S])
        Q = int((p + 1) // k) * P
        for i in S:
            R = (k // ells[i]) * Q
            if R.is_zero():
                continue

            phi = KummerLineIsogeny(_C, R, ells[i])
            _C = phi.codomain()
            Q, G = phi(Q), phi(G)
            es[i] -= s
            k //= ells[i]

    return _C, G


def BEAM(base_alice_priv):
    alice_priv = base_alice_priv

    pub = 0

    for _ in range(MIKU):

        E = EllipticCurve(F, [0, pub, 0, 1, 0])
        omae_E = KummerLine(E)
        G = E.random_point()
        _G = omae_E(G)

        _final_E1, _final_G = group_action(omae_E, alice_priv, _G)
        _final_G = _final_G
        print(f"final_a2 = {_final_E1.curve().a2()}")
        print(f"{_final_G=}")

        omae_priv = list(map(int, input("your priv >").split(", ")))

        assert all([abs(pi) < 2 for pi in omae_priv])
        assert len(omae_priv) == len(ells)

        alice_priv = [ai + yi for ai, yi in zip(alice_priv, omae_priv)]
        print("updated")

        pub = _final_E1.curve().a2()
    print("FIN!")


if __name__ == "__main__":

    print("And now, it's time for the moment you've been waiting for!")

    alice_priv = [randint(MI + KU, MI * KU) for _ in ells]
    BEAM(alice_priv)

    alter_ego = list(map(int, input('ready?! here is the "alter ego" >').split(", ")))

    assert alice_priv != alter_ego
    assert len(alice_priv) == len(alter_ego)
    assert all([-MI * KU <= ai < 0 for ai in alter_ego])

    _E0 = KummerLine(E0)
    G = E0.random_point()
    _G = _E0(G)

    _alter_ego_E1, _ = group_action(_E0, alter_ego, _G)
    _alice_E1, __ = group_action(_E0, alice_priv, _G)

    if _alter_ego_E1.curve().a2() == _alice_E1.curve().a2():
        print("There you are... I've been waiting and waiting for you to come to me.")
        print(FLAG)
    else:
        print("YOU CANT FIND MY ALTER EGO....")
        exit()
