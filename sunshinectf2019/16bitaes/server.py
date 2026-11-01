#!/usr/bin/env python3
from Crypto.Cipher import AES
import random
import string
import sys

sys.stdout.reconfigure(line_buffering=True)

flag = open('flag.txt', 'r').read()

key = random.choice(string.ascii_letters + string.digits) + random.choice(string.ascii_letters + string.digits)

encryptor = AES.new((key * 8).encode(), AES.MODE_ECB)

def pad(data):
    return data + (16 - len(data)) * 'x'

print("Welcome, I'm using an AES-128 cipher with a 16-bit key in ECB mode.\n\nI'll give you some help: give me some text, and I'll tell you what it looks like\n")
data = input('Your text: ')
print(encryptor.encrypt(pad(data).encode()).hex())

random_text = ''.join(random.choice(string.ascii_letters + string.digits) for i in range(16))
print('\nOk, now encrypt this text with the same key: ' + random_text)
encrypted_guess = input('Encrypted: ')
try:
    ct = encryptor.encrypt(pad(random_text).encode())
    if encrypted_guess.encode() == ct or bytes.fromhex(encrypted_guess) == ct:
        print('\nCorrect! The flag is ' + flag)
    else:
        print('Wrong!')
except Exception:
    print('Wrong!')
