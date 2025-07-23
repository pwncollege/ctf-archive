#!/usr/bin/python3
import numpy as np
import random
from Crypto.Cipher import DES

strings = None
with open("strings.txt") as f:
    strings = f.readlines()

CK_A = bytes.fromhex(strings[1][6:])
CK_B = bytes.fromhex(strings[2][6:])
cipher_A = DES.new(CK_A, DES.MODE_ECB)
cipher_B = DES.new(CK_B, DES.MODE_ECB)


def generate_PAN():
    PAN = list(np.random.randint(low=0,high=10,size=random.randint(12,18)))
    sum = 0
    for i, num in enumerate(PAN[::-1]):
        if i % 2:
            sum += num
        else:
            temp = num * 2
            for digit in str(temp):
                sum += int(digit)
                
    PAN.append((10 - (sum % 10)) % 10)
    return "".join(str(i) for i in PAN)

def generate_date():
    date = [random.randint(1,12), random.randint(25,99)]
    date = "".join(str(i) for i in date)
    if len(date) < 4:
        return '0' + date
    return date

def generate_service_code():
    return "".join(str(i) for i in list(np.random.randint(low=0,high=10,size=3)))

def generate_cvv(PAN, date, code):
    key = (PAN + date + code)
    key += '0' * (32 - len(key))
    f_half = key[:16]
    s_half = key[16:]
    step1 = cipher_A.encrypt(bytes.fromhex(f_half))
    step2 = bytes(a ^ b for a,b in zip(step1, bytes.fromhex(s_half)))
    step3 = cipher_A.encrypt(step2)
    step4 = cipher_B.decrypt(step3)
    step5 = cipher_A.encrypt(step4)
    result = "".join(i for i in step5.hex() if i.isdigit())[:3]
    return result

def generate_good_card():
    PAN = generate_PAN()
    date = generate_date()
    code = generate_service_code()
    cvv = generate_cvv(PAN, date, code)
    return PAN, date, code, cvv


def generate_bad_card():
    PAN, date, code, cvv = generate_good_card()
    rand = random.randint(1,3)
    if rand == 1:
        return PAN[6:] + PAN[:6], date, code, cvv
    elif rand == 2:
        temp = str(random.randint(1000,9999))
        while temp == date:
            temp = str(random.randint(1000,9999))
        return PAN, temp, code, cvv
    elif rand == 3:
        temp = str(random.randint(100,999))
        while temp == code:
            temp = str(random.randint(100,999))
        return PAN, date, temp, cvv
    else:
        temp = str(random.randint(100,999))
        while temp == cvv:
            temp = str(random.randint(100,999))
        return PAN, date, code, temp
