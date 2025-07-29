#!/usr/bin/exec-suid -- /usr/bin/python3
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad
from Crypto.Random import random

seed = random.randint(0, 10 ** 6)
def get_random_number():
    global seed 
    seed = int(str(seed * seed).zfill(12)[3:9])
    return seed

def encrypt(message):
    key = b''
    for i in range(8):
        key += (get_random_number() % (2 ** 16)).to_bytes(2, 'big')
    cipher = AES.new(key, AES.MODE_ECB)
    ciphertext = cipher.encrypt(pad(message, AES.block_size))
    return key.hex(), ciphertext.hex()


print("Thanks for using our encryption service! To get the start guessing, type 1. To encrypt a message, type 2.")
print("You will need to guess the key (you get 250 guesses for one key). You will do this 3 times!")

for i in range(3):
    seed = random.randint(0, 10 ** 6)
    print("Find the key " + str(i + 1) + " of 3!")
    key = encrypt(b"random text to initalize key")[0]
    while True:
        print("What would you like to do (1 - guess the key, 2 - encrypt a message)?")
        user_input = int(input())
        if(user_input == 1):
            break

        print("What is your message?")
        message = input()
        key, ciphertext = encrypt(message.encode())
        print("Here is your encrypted message:", ciphertext)
    print("You have 250 guesses to find the key!")
    
    found = False
    for j in range(250):
        print("What is your guess (in hex)?")
        guess = str(input()).lower()
        if guess == key:
            print("You found the key!")
            found = True
            break
        else:
            print("That is not the key!")

    if not found:
        print("You did not find the key!")
        exit(0)


flag = open('/flag', 'r').read();
print("Here is the flag:", flag)

