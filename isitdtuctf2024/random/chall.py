#!/run/workspace/bin/python
import random
from secret import FLAG
flag  = random.randbytes(random.randint(13, 1337))
#flag += open("/flag", "rb").read()
flag += FLAG
flag += random.randbytes(random.randint(13, 1337))
random.seed(flag)
print(len(flag) < 1337*1.733 and [random.randrange(0, int(0x13371337*1.337)) for _ in range(0x13337)])
