#!/usr/bin/env python3
import os, sys, base64
from Crypto.Cipher import AES
from Crypto.Random import get_random_bytes

BS=16
FLAG=open("flag.txt","rb").read().strip()
M=256
L=1024

def pad(m):
    p=BS-(len(m)%BS)
    return m+bytes([p])*p

class O:
    def __init__(self):
        self.k=get_random_bytes(16)
        self.pl=int.from_bytes(os.urandom(1),"big")%97
        n=L//BS+1
        self.n=n
        self.q=list(range(n))
        for i in range(n-1,0,-1):
            j=int.from_bytes(os.urandom(2),"big")%(i+1)
            self.q[i],self.q[j]=self.q[j],self.q[i]

    def e(self, idx, u):
        if idx<0 or idx>=self.n:
            return None
        u=u[:M]
        p=os.urandom(self.pl)
        m=p+u+FLAG
        if len(m)>L:
            m=m[:L]
        else:
            m+=os.urandom(L-len(m))
        c=AES.new(self.k,AES.MODE_ECB).encrypt(pad(m))
        b=[c[i:i+BS] for i in range(0,len(c),BS)]
        out=[b[i] for i in self.q]
        return out[idx]

o=O()

while True:
    sys.stdout.write("> ")
    sys.stdout.flush()
    l=sys.stdin.readline()
    if not l:
        break
    l=l.strip()
    if not l:
        print("error")
        continue
    if l.lower() == "exit":
        break
    if ":" in l:
        a,h=l.split(":",1)
    else:
        parts=l.split(None,1)
        if len(parts)==1:
            a,h=parts[0],""
        else:
            a,h=parts[0],parts[1]
    try:
        idx=int(a,10)
        u=bytes.fromhex(h) if h else b""
    except Exception:
        print("error")
        continue
    blk=o.e(idx,u)
    if blk is None:
        print("error")
    else:
        print(base64.b64encode(blk).decode())
