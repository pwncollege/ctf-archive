import hashlib
from pathlib import Path

P = 2**448 - 2**224 - 1
D = (-39081) % P
L = int(
    "3ffffffffffffffffffffffffffffffffffffffffffffffffffffff"
    "7cca23e9c44edb49aed63690216cc2728dc58f552378c292ab5844f3",
    16,
)

BASE_X = int(
    "4F1970C66BED0DED221D15A622BF36DA9E146570470F1767EA6DE324"
    "A3D3A46412AE1AF72AB66511433B80E18B00938E2626A82BC70CC05E",
    16,
)
BASE_Y = int(
    "693F46716EB6BC248876203756C9C7624BEA73736CA3984087789C1E"
    "05A0C2D73AD3FF1CE67C39C4FDBD132C4ED7C8AD9808795BF230FA14",
    16,
)

IDENTITY = (0, 1)
BASE = (BASE_X, BASE_Y)

PUB_LEFT = bytes.fromhex("e9b90d9dcc8cbed89899cbd92c0982d19f71a53ea83c422052274c4ce69380379d1ba991fdf9cd132e41f6b69f1973420dcf4767668b6c9780")
PUB_RIGHT = bytes.fromhex("577f79bab6f1f3953cd5e431232848fce102828266122218257da5b17f137c6b339c2f9775a23ae50acb46e8cdb84dc289bd2139c45368c580")
MSG = bytes.fromhex("73657373696f6e3d676f6c64696c6f636b733b6d6f64653d666173742d7665726966793b7469636b65743d02000000")
NONCE = bytes.fromhex("219aa996b4efccbaf59beda01949a7f1")


def inv(x: int):
    return pow(x, P - 2, P)


def sqrt_mod(a: int):
    if a == 0:
        return 0
    x = pow(a, (P + 1) // 4, P)
    if (x * x - a) % P != 0:
        raise ValueError("not a square")
    return x


def is_on_curve(Q):
    x, y = Q
    return (x * x + y * y - 1 - D * x * x * y * y) % P == 0


def point_add(Q1, Q2):
    x1, y1 = Q1
    x2, y2 = Q2
    t = (x1 * x2 * y1 * y2) % P

    den_x = (1 + D * t) % P
    den_y = (1 - D * t) % P

    x3 = ((x1 * y2 + y1 * x2) * inv(den_x)) % P
    y3 = ((y1 * y2 - x1 * x2) * inv(den_y)) % P
    return (x3, y3)


def point_neg(Q):
    x, y = Q
    return ((-x) % P, y)


def scalar_mul(Q, n: int):
    R = IDENTITY
    T = Q
    k = n
    while k > 0:
        if k & 1:
            R = point_add(R, T)
        T = point_add(T, T)
        k >>= 1
    return R


def encode_point(Q):
    x, y = Q
    out = bytearray(y.to_bytes(57, "little"))
    out[-1] &= 0x7F
    out[-1] |= (x & 1) << 7
    return bytes(out)


def decode_point(data: bytes):
    if len(data) != 57:
        raise ValueError("bad point length")

    sign = data[56] >> 7
    y = int.from_bytes(data, "little") & ((1 << 455) - 1)

    if y >= P:
        raise ValueError("bad y")

    y2 = (y * y) % P
    num = (1 - y2) % P
    den = (1 - D * y2) % P
    x2 = (num * pow(den, P - 2, P)) % P

    x = sqrt_mod(x2)
    if (x & 1) != sign:
        x = (-x) % P

    Q = (x, y)
    if not is_on_curve(Q):
        raise ValueError("invalid point")
    return Q


def effective_pub():
    left = decode_point(PUB_LEFT)
    right = decode_point(PUB_RIGHT)
    return encode_point(point_add(left, right))


def challenge_scalar(R_enc: bytes, A_enc: bytes, msg: bytes):
    h = hashlib.shake_256(R_enc + A_enc + msg).digest(114)
    return int.from_bytes(h, "little") % L


def verify(msg: bytes, sig: bytes) -> bool:
    if len(sig) != 114:
        return False

    try:
        A_enc = effective_pub()
        A = decode_point(A_enc)
        R = decode_point(sig[:57])
    except Exception:
        return False

    S = int.from_bytes(sig[57:], "little")
    if S >= L:
        return False

    k = challenge_scalar(sig[:57], A_enc, msg)
    lhs = scalar_mul(BASE, S)
    rhs = point_add(R, scalar_mul(A, k))
    return lhs == rhs


def xor_bytes(a: bytes, b: bytes):
    return bytes(x ^ y for x, y in zip(a, b))


def load_ct() -> bytes:
    path = Path("flag.enc")
    if not path.exists():
        raise FileNotFoundError("flag.enc not found (put it next to handout.py)")
    return path.read_bytes()


def decrypt_with_signature(sig: bytes):
    if not verify(MSG, sig):
        raise ValueError("signature rejected")
    ct = load_ct()
    stream = hashlib.shake_256(sig + b"|goldilocks|" + MSG + NONCE).digest(len(ct))
    return xor_bytes(ct, stream)


if __name__ == "__main__":
    print("== Goldilocks ==")
    print("PUB_LEFT  =", PUB_LEFT.hex())
    print("PUB_RIGHT =", PUB_RIGHT.hex())
    print("MSG       =", MSG.hex())
    print("NONCE     =", NONCE.hex())
    print("CT        = <load from flag.enc>")
    print()
    print("Recover a valid signature and decrypt the payload.")
