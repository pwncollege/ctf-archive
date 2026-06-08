from function import F
import random

class IGen:
    def __init__(self, f : F):
        self.f = f
        self.a = random.randint(1, 10)
        self.b = random.randint(11, 20)
        
    def latex(self):
        a, b = self.a, self.b
        return f"\\int_{{{a}}}^{{{b}}} {self.f.latex()} \\, dx"

    def solve(self) -> float:
        f, a, b = self.f, self.a, self.b
        n = 10000
        h = (b - a) / n
        total = 0.5 * (f(a) + f(b))
        for i in range(1, n):
            total += f(a + i * h)
        return total * h