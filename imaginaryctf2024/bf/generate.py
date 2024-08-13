import random
import sympy

with open('/flag', 'r') as flag_file:
    flag = flag_file.read().strip()

code=""
equations=[]
for i in flag:
	rand=random.randint(30,70)
	while sympy.isprime(rand):
		rand=random.randint(30,70)
	RHS = eval(f"ord(i)+{rand}")
	equations.append(f"{ord(i)}+{rand}={RHS}")
	factors = sympy.divisors(rand)
	best_factors=[factors[int(len(factors)/2)]]
	best_factors.append(int(rand/best_factors[0]))
	fac1=best_factors[0]
	fac2=best_factors[1]
	assert fac1*fac2==rand
	code += f",>>{'+'*fac1}[<{'+'*fac2}>-]<[-<+>]<{'-'*RHS}[><]"

print(code)
