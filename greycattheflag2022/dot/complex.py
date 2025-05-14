#!/usr/bin/exec-suid -- /usr/bin/python3
class ComplexVector():
    def __init__(self, vec):
        for i in vec:
            assert isinstance(i, ComplexNumber)
        self.internal = vec
        self.n = len(vec)

    def __str__(self):
        return f"({', '.join(map(str,self.internal))})"

    def __mul__(self, other):
        assert self.n == other.n
        res = ComplexNumber(0, 0)
        for i in range(self.n):
            res += (~self.internal[i]) * other.internal[i]
        return res

    def __eq__(self, __o: object):
        if not isinstance(__o, ComplexVector):
            return False
        if self.n != __o.n:
            return False
        for i in range(self.n):
            if self.internal[i] != __o.internal[i]:
                return False
        return True

class ComplexNumber():
    def __init__(self, a, b):
        self.a = a
        self.b = b
    
    def __str__(self):
        return f'{self.a} + {self.b}i'

    def __mul__(self, other):
        return ComplexNumber(
            self.a * other.a - self.b * other.b, 
            self.a * other.b + self.b * other.a
        )

    def __add__(self, other):
        return ComplexNumber(self.a + other.a, self.b + other.b)

    def __invert__(self):
        return ComplexNumber(self.a, -self.b)

    def __eq__(self, __o: object):
        if isinstance(__o, ComplexNumber):
            return self.a == __o.a and self.b == __o.b
        return False