#!/opt/pwn.college/python

from Crypto.Cipher import AES
from Crypto.Util.Padding import pad, unpad
from os import urandom

def cbc_encrypt(msg: bytes):
  msg = pad(msg, 16)
  msg = [msg[i:i+16] for i in range(0, len(msg), 16)]
  key = urandom(16)
  out = []
  for block in msg:
    cipher = AES.new(key, AES.MODE_ECB)
    next = cipher.encrypt(block)
    out.append(next)
    key = next
  out = b"".join(out)
  return key, out

def main():
  key, ct = cbc_encrypt(open("/flag", "rb").read()*3)
  print(f"{ct = }")

if __name__ == "__main__":
  main()

