#!/opt/pwn.college/python

from ctypes import CDLL, c_buffer
libc = CDLL('/lib/x86_64-linux-gnu/libc.so.6')
buf1 = c_buffer(512)
buf2 = c_buffer(512)
libc.gets(buf1)
if b'pwn.college' in bytes(buf2):
    print(open('/flag', 'r').read())
