#!/usr/bin/exec-suid -- /usr/bin/python3

from Crypto.PublicKey import RSA

if __name__ == '__main__':
	key = RSA.generate(1024)
	with open('/challenge/key.pem', 'wb') as f:
		f.write(key.export_key())
