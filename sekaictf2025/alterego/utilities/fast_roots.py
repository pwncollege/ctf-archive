# ============================================ #
#     Fast square root and quadratic roots     #
# ============================================ #


def sqrt_Fp2(a):
    """
    Efficiently computes the sqrt
    of an element in Fp2 using that
    we always have a prime p such that
    p ≡ 3 mod 4.
    """
    Fp2 = a.parent()
    p = Fp2.characteristic()
    i = Fp2.gen()  # i = √-1

    a1 = a ** ((p - 3) // 4)
    x0 = a1 * a
    alpha = a1 * x0

    if alpha == -1:
        x = i * x0
    else:
        b = (1 + alpha) ** ((p - 1) // 2)
        x = b * x0

    return x


def quadratic_roots(b, c):
    """
    Computes roots to the quadratic polynomial

        f = x^2 + b * x + c

    Using the quadratic formula

    Just like in school!
    """
    d2 = b**2 - 4 * c
    d = sqrt_Fp2(d2)
    return ((-b + d) / 2, -(b + d) / 2)
