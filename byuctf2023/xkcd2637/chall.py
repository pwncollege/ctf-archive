#!/usr/bin/python3

import random

# helper function
def num_to_roman(number):
    retval = ""
    num = [1, 4, 5, 9, 10, 40, 50, 90,
        100, 400, 500, 900, 1000]
    sym = ["I", "IV", "V", "IX", "X", "XL",
        "L", "XC", "C", "CD", "D", "CM", "M"]
    i = 12
     
    while number:
        div = number // num[i]
        number %= num[i]
 
        while div:
            retval += sym[i]
            div -= 1
        i -= 1

    return retval.replace("I","1").replace("V","5").replace("X","10").replace("L","50").replace("C","100").replace("D","500").replace("M","1000")


# consts
flag = "byuctf{just_over_here_testing_your_programming_skills_:)}"
REPS = 500


# print intro
print("Get", REPS, "problems correct to get the flag!")


# generate problems
for _ in range(REPS):
    # generate random numbers
    num1 = random.randint(1, 35)
    num2 = random.randint(1, 35)

    operation = random.choice(["+", "*"])
    answer = eval(str(num1) + operation + str(num2))

    # convert to roman numerals
    print(num_to_roman(num1), operation, num_to_roman(num2), "=", end=" ")
    test = input()

    if test != num_to_roman(answer):
        print("Incorrect")
        exit(0)

print("Flag:", flag)