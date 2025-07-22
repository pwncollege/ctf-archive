#!/usr/bin/exec-suid -- /usr/bin/python3

from secrets import randbits
from prng import xorshiro256
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad, unpad
from ecc import ECDSA
from Crypto.Util.number import bytes_to_long, long_to_bytes
import hashlib
flag = open("/flag", "rb").read()
state = [randbits(64) for _ in range(4)]
prng = xorshiro256(state)
leaks = [prng.next_raw() for _ in range(4)]
print(f"PRNG leaks: {[hex(x) for x in leaks]}")
Apriv, Apub = ECDSA.gen_keypair()
print(f"public_key = {Apub}")
msg = b"My favorite number is 0x69. I'm a hero in your mother's bedroom, I'm a hero in your father's eyes. What am I?"
H = bytes_to_long(msg)
sig = ECDSA.ecdsa_sign(H, Apriv, prng)                  
print(f"Message = {msg}")
print(f"Hash = {H}")
print(f"r, s = {sig}")
key = hashlib.sha256(long_to_bytes(Apriv)).digest()
iv = randbits(128).to_bytes(16, "big")
cipher = AES.new(key, AES.MODE_CBC, iv)
ciphertext = iv.hex() + cipher.encrypt(pad(flag, 16)).hex()
print(f"ciphertext = {ciphertext}")
with open("output.txt", "w") as f:
    f.write(f"PRNG leaks: {[hex(x) for x in leaks]}\n")
    f.write(f"public_key = {Apub}\n")
    f.write(f"Message = {msg}\n")
    f.write(f"Hash = {H}\n")
    f.write(f"r, s = {sig}\n")
    f.write(f"ciphertext = {ciphertext}\n")




