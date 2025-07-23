#!/usr/bin/exec-suid -- /usr/bin/python3
from app import generate_good_card, generate_bad_card
import random


strings = None
with open("strings.txt") as f:
    strings = f.readlines()

flag = ""
with open('flag.txt', 'r') as f:
    flag = f.read().strip()

if __name__ == '__main__':
    print("""I'll provide you a PAN, date as MMYY, CSC, and a CVV.
You just need to reply with 1 if its valid and 0 if it's invalid.
I'm counting on you. And be sure to keep track of your answers so we don't need to check these again.
          """)
    padding = ''.join(random.choices('01', k=8))
    b = padding + '0' + bin(int.from_bytes(bytes(flag, 'utf-8'), "big"))[2:]
    for i in b:
        if i == '1':
            print("PAN: %s, date: %s, code: %s, cvv: %s" % (generate_good_card()))
            print(strings[33], end="")
            user_input = int(input())
            if user_input == 1:
                print(strings[user_input + random.randint(18,27)], end="")
            elif user_input == 0:
                print(strings[user_input + random.randint(8,10)], end="")
                exit(0)
            else:
                print("Hey, that's not a valid number! Come back when you can follow instructions.")
                exit(0)    
            
        else:
            print("PAN: %s, date: %s, code: %s, cvv: %s" % (generate_bad_card()))
            print(strings[33], end="")
            user_input = int(input())
            if user_input == 0:
                print(strings[user_input + random.randint(23,32)], end="")
            elif user_input == 1:
                print(strings[user_input + random.randint(8,10)], end="")
                exit(0)
            else:
                print("Hey, that's not a valid number! Come back when you can follow instructions.")
                exit(0)   
    print(strings[34])
    


    
