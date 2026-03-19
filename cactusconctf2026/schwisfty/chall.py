from Crypto.Util.number import *

def banner():
    print('''
            ___
        . -^   `--,
       /# =========`-_
      /# (--====___====\\
     /#   .- --.  . --.|
    /##   |  * ) (   * ),
    |##   \    /\ \   / |
    |###   ---   \ ---  |
    |####      ___)    #|
    |######           ##|
     \##### ---------- /
      \####           (
       `\###          |
         \###         |
          \##        |
           \###.    .)
            `======/

Welcome to Schwifty RSA:
[1] Get Public keys
[2] Get Schwifty
''')
    return int(input('Enter your choice: '))

FLAG = b'flag{f2c963be-9856-483d-bd9c-9dcc3728ff5c}'

def encrypt_chunks(flag, n, e):
    chunks = [flag[i:i+16] for i in range(0, len(flag), 16)]
    ciphers = []
    for chunk in chunks:
        m = bytes_to_long(chunk)
        c = pow(m, e, n)
        ciphers.append(c)
    return ciphers

p, q = getPrime(64), getPrime(64)
n = p * q
e = 65537
d = inverse(e, (p - 1) * (q - 1))

ciphers = encrypt_chunks(FLAG, n, e)

choice = banner()
if choice == 1:
    print(f'n = {n}')
    print(f'e = {e}')
    print(f'ciphers = {ciphers}')
elif choice == 2:
    print('''
⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⠀⢀⡆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠠⣼⣿⣿⣿⣶⡆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⣿⣿⣿⣿⣿⠂⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⡀⠚⣿⣿⣿⣿⠿⣉⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠈⠉⡿⣷⡉⢻⣿⣿⣿⣿⣧⣠⣿⠋⠉⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⡇⠸⣷⣾⠁⠹⣿⣿⣿⣿⣧⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⡇⠀⠹⠏⠀⠀⠈⢿⣿⣿⣿⣧⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⡇⠀⠀⠀⠀⠀⠀⠈⣿⣿⣿⣿⡆⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠠⠤⠤⠤⠤⠤⠤⠴⠿⠿⠿⠿⠇⠀⠀⠀⠀⠀⠀⠀⠀⠀
⢀⡖⠓⢰⣛⠐⢺⠓⠀⢰⣞⠂⡴⠛⠂⣇⣰⣸⠀⡇⡶⣶⢰⣒⠒⢶⠒⢶⣠⠆
⠘⢧⡼⠸⠤⠄⠸⠀⠀⠰⣬⠇⠳⡤⠆⠇⠸⠋⠿⠳⠃⠿⠸⠁⠀⠼⠀⠨⠏⠀
''')