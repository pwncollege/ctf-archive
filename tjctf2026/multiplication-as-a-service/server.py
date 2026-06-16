#!/usr/local/bin/python3
import os
import sys
import select

P = 10007
A = 2
B = 3

def mod_inv(x, p):
    return pow(x % p, -1, p)

def point_add(P1, P2):
    if P1 is None:
        return P2
    if P2 is None:
        return P1

    x1, y1 = P1
    x2, y2 = P2

    if x1 == x2 and (y1 + y2) % P == 0:
        return None

    if x1 == x2 and y1 == y2:
        s = (3 * x1 * x1 + A) * mod_inv(2 * y1, P)
    else:
        s = (y2 - y1) * mod_inv(x2 - x1, P)
    s %= P

    x3 = (s * s - x1 - x2) % P
    y3 = (s * (x1 - x3) - y1) % P
    return (x3, y3)

def scalar_mul(k, P1):
    result = None
    addend = P1

    while k > 0:
        if k & 1:
            result = point_add(result, addend)
        addend = point_add(addend, addend)
        k >>= 1
    return result

def input_with_timeout(prompt="", timeout=10):
    sys.stdout.write(prompt)
    sys.stdout.flush()
    ready, _, _ = select.select([sys.stdin], [], [], timeout)
    if ready:
        return sys.stdin.buffer.readline().rstrip(b"\n")
    raise Exception

input = input_with_timeout

def main():
    flag_path = os.path.join(os.path.dirname(__file__), "flag.txt")
    with open(flag_path, "rb") as f:
        flag = f.read().strip()

    secret_d = int.from_bytes(flag, "big")

    while True:
        sys.stdout.write("=== multiplication as a service ===\n")
        sys.stdout.write("We compute Q = d * P on a curve over F_p.\n")
        sys.stdout.write(f"Curve: y^2 = x^3 + {A}x + {B} over p={P}\n")
        sys.stdout.flush()

        while True:
            try:
                x_raw = input("x = ").decode("utf-8").strip()
                y_raw = input("y = ").decode("utf-8").strip()
            except Exception:
                return

            try:
                x = int(x_raw)
                y = int(y_raw)
            except ValueError:
                sys.stdout.write("Invalid integers.\n")
                sys.stdout.flush()
                continue

            Q = scalar_mul(secret_d, (x % P, y % P))

            if Q is None:
                sys.stdout.write("Q = inf\n")
            else:
                qx, qy = Q
                sys.stdout.write(f"Q = {qx} {qy}\n")
            sys.stdout.flush()

if __name__ == "__main__":
    main()
