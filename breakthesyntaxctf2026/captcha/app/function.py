import math
import random
from dataclasses import dataclass

@dataclass
class F:
    def __init__(self, display, function):
        self.display = display
        self.function = function

    def __call__(self, x):
        return self.function(x)

    def latex(self):
        return self.display
    
    @staticmethod 
    def complex():
        top_list = [F.rand() for _ in range(3)]
        bottom_list = [F.rand() for _ in range(3)]

        def compose(fs):
            res_latex = fs[-1].latex()
            res_fn = fs[-1].function
            
            for i in range(len(fs) - 2, -1, -1):
                outer = fs[i]
                
                res_latex = outer.latex().replace("x", f"({res_latex})")
                res_fn = lambda x, inner=res_fn, out=outer: out(inner(x))
                
            return res_latex, res_fn

        top_lat, top_f = compose(top_list)
        bot_lat, bot_f = compose(bottom_list)

        return F(fr"\frac{{{top_lat}}}{{{bot_lat}}}", lambda x: top_f(x) / bot_f(x))

    @staticmethod
    def rand():
        return random.choice(f_pool)


f_pool = [
    F("x", lambda x: x),
    F(r"\frac{1}{x}", lambda x: 1/x),
    F("x+1", lambda x: x + 1),
    F("x^2", lambda x: x**2),
    F("x^3", lambda x: x**3),
    F("x^4", lambda x: x**4),
    F(r"\sqrt{x}", math.sqrt),
    F(r"\sin{x}+2", lambda x: math.sin(x) + 2),
    F(r"\cos{x}+2", lambda x: math.cos(x) + 2),
]
