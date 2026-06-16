import base64

from Crypto.Cipher import AES
from Crypto.Util.strxor import strxor

flag = open("../flag.txt").read().strip()

s = ""
for c in flag:
    s += c * 2
flag = s

cipher = AES.new(b"lasagna!" * 2, AES.MODE_ECB)
result = b"\0" * len(flag)

for i in range(len(result)):
    ciphertext = cipher.encrypt(flag[i].encode() * 16)
    layer = b"\0" * i + ciphertext
    if len(layer) < len(result):
        layer += b"\0" * (len(result) - len(layer))
    if len(layer) > len(result):
        layer = layer[len(result):] + layer[len(layer)-len(result):len(result)]
    result = strxor(result, layer)

print(base64.b64encode(result).decode())
