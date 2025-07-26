#!/usr/bin/exec-suid -- /usr/bin/python3
from Crypto.PublicKey import RSA

key = RSA.generate(2048)

print('Welcome to the signature generator!')
print('This service generates signatures for nonnegative integer messages.')
print('Today\'s RSA parameters are: ')
print(f'n = {key.n}')
print(f'e = {key.e}')

admit = []
i = 0
while True:
    print('Enter a message as an integer (enter 0 to stop): ',end='')
    x = int(input())
    if x == 0 and i == 0:
        print('You must request at least one signature.')
        continue
    if x == 0:
        break
    # technically not a correct hash function, but they don't need to know that
    sig = pow((x + i) % key.n, key.d, key.n)
    print('Your signature is: ', end='')
    print(sig)
    admit.append((x, sig))
    i += 1

print('Now, come up with your own pair!')
print('Enter a message: ',end='')
x = int(input())
print('Enter a signature: ',end='')
s = int(input())

if (x, s) in admit:
    print('Cannot enter a message that you already requested.')
    exit(0)

if (pow(s, key.e, key.n) - (x + i)) % key.n == 0:
    flag = open('/flag', 'r').read();
    print("Congrats! Here is the flag: ", flag)
else:
    print('Invalid signature.')
