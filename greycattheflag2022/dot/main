#!/opt/pwn.college/python
from complex import ComplexVector, ComplexNumber
from secrets import randbits

v1 = []

n = 3
bits = 64

#FLAG = <REDACTED>

with open('/flag','r') as file:
    FLAG = file.read().strip().encode()

def gen():
    global v1
    v1 = ComplexVector([
        ComplexNumber(randbits(bits), randbits(bits)) for _ in range(n)
    ])

def welcome():
    print('''
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
 o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o   o | o
---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
    ''')
    print("I have v1, you have v2. Uh! \(^ヮ^)/ <v1, v2>")
    print("Guess my v1 and I give you flag\n", flush=True)

def inputToVec(userIn):
    v = list(map(lambda x : list(map(int, x.strip().split())), userIn.split(',')))
    if (len(v) != n):
        print(f"Your vector must have {n} entries")
        exit(0)
    for i in v:
        if i[0].bit_length() >= 400 or i[1].bit_length() >= 400:
            print("Why your vector so big ヾ( ￣O￣)ツ")
            exit(0)     
    return ComplexVector(list(map(lambda x : ComplexNumber(x[0], x[1]), v)))

if __name__ == '__main__':
    welcome()
    print("Generating v1...", flush=True)
    gen()
    print("Finish generation!\n", flush=True)
    print("Split real and imaginary part of an entry by space and split entries by comma")
    print("Example: `1 2, 3 4, 5 6` for vector (1 + 2i, 3 + 4i, 5 + 6i)\n")
    userIn = input("Enter v2: ")
    v2 = inputToVec(userIn)
    print(f"Re(<v1, v2>) = {(v1 * v2).a}")
    print("I only give you real part because you can imagine the imaginary part (҂ `з´)\n")
    userIn = input("Enter v1: ")
    guess = inputToVec(userIn)
    if (guess == v1):
        print("FLAG for you!", flush=True)
        print(FLAG)
    else:
        print("...")
