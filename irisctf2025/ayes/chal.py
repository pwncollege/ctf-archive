#!/usr/bin/exec-suid -- /usr/bin/python3

import aes
import secrets

print("Oh no! I dropped a bit. Where was it again...?")
bit = int(input("> "))

bits = list(bin(int.from_bytes(bytes(aes.s_box), "big"))[2:].rjust(256 * 8, '0'))
bits[bit] = "1" if bits[bit] == "0" else "0"
aes.s_box = int(''.join(bits), 2).to_bytes(256, "big")

print("Got it, thanks! Have some encryptions, as a gift.")

key = secrets.token_bytes(16)
a = aes.AES(key)
for _ in range(2**12):
    encrypted = a.encrypt_block(bytes.fromhex(input("> ")))
    print(encrypted.hex())

    if encrypted == key:
        print("Really? I guess you've earned this.")
        with open("/flag") as f:
            print(f.read())
        exit()

print("Why are we still here?")
