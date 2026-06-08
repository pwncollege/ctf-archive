#!/usr/local/bin/python3
import secrets
import sys
import select

FLAG_PATH = "flag.txt"
MAX_QUERIES = 2100
TIMEOUT = 120


def read_flag():
    with open(FLAG_PATH, "rb") as fh:
        return fh.read().strip()


def miller_rabin(n, iterations=32):
    if n < 2:
        return False
    small_primes = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29]
    for p in small_primes:
        if n == p:
            return True
        if n % p == 0:
            return False

    d = n - 1
    r = 0
    while d % 2 == 0:
        d //= 2
        r += 1

    for _ in range(iterations):
        a = secrets.randbelow(n - 3) + 2
        x = pow(a, d, n)
        if x == 1 or x == n - 1:
            continue
        for __ in range(r - 1):
            x = pow(x, 2, n)
            if x == n - 1:
                break
        else:
            return False
    return True


def generate_prime(bits):
    if bits < 2:
        raise ValueError("bit length must be at least 2")
    while True:
        candidate = secrets.randbits(bits) | 1 | (1 << (bits - 1))
        if miller_rabin(candidate):
            return candidate


def egcd(a, b):
    if b == 0:
        return a, 1, 0
    g, x1, y1 = egcd(b, a % b)
    return g, y1, x1 - (a // b) * y1


def modinv(a, m):
    g, x, _ = egcd(a, m)
    if g != 1:
        raise ValueError("modular inverse does not exist")
    return x % m


def bytes_to_long(data):
    return int.from_bytes(data, "big")


def input_with_timeout(prompt="", timeout=TIMEOUT):
    sys.stdout.write(prompt)
    sys.stdout.flush()
    ready, _, _ = select.select([sys.stdin], [], [], timeout)
    if not ready:
        raise TimeoutError
    return sys.stdin.readline().rstrip("\n")


def main():
    flag = read_flag()
    message = bytes_to_long(flag)

    p = generate_prime(256)
    q = generate_prime(256)
    while q == p:
        q = generate_prime(256)

    n = p * q
    phi = (p - 1) * (q - 1)
    e = 65537
    d = modinv(e, phi)

    if message >= n:
        raise ValueError("Flag does not fit in the modulus")

    ciphertext = pow(message, e, n)

    print("=== bit-leak secure monitor ===")
    print("we only log whether decrypted messages are even or odd.")
    print("maybe that's still too much information...")
    print()
    print("RSA parameters:")
    print(f"n = {n}")
    print(f"e = {e}")
    print(f"ciphertext = {ciphertext}")
    print()
    print(f"you may ask for up to {MAX_QUERIES} parity queries.")

    queries = 0
    while queries < MAX_QUERIES:
        print("\n[1] Query parity oracle\n[Q] Quit")
        try:
            choice = input_with_timeout("> ").strip().lower()
        except TimeoutError:
            print("\ntimed out.")
            return

        if choice == "1":
            try:
                raw = input_with_timeout("ciphertext = ").strip()
            except TimeoutError:
                print("\ntimed out.")
                return
            try:
                candidate = int(raw, 10)
            except ValueError:
                print("please provide a valid integer ciphertext")
                continue
            if not 0 <= candidate < n:
                print("ciphertext must be in the range [0, n)")
                continue
            parity = pow(candidate, d, n) & 1
            print(f"lsb = {parity}")
            queries += 1
        elif choice == "q":
            print("bye.")
            return
        else:
            print("invalid option")

    print("\nyou're out of queries. bye!")


if __name__ == "__main__":
    main()
