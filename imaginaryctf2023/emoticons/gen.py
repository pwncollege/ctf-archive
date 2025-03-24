#!/usr/bin/exec-suid -- /usr/bin/python3 -I

import random

emojis = [n for n in "🌸🍔🐳🚀🌞🎉🍦🎈🐶🍕🌺🎸⚡️🦋🌼🎁"]
m = open("text.txt", "r").read()

#########################
# Using pwn.college flag
flag = open("/flag", "r").read().strip()

m = m.format(flag)
m = m.encode().hex()
#########################

random.shuffle(emojis)

for e, c in zip(emojis, "0123456789abcdef"):
  m = m.replace(c, e)

open("out.txt", "w").write(m)
