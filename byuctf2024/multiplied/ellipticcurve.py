#!/opt/pwn.college/python

def legendre(a, p):
    return pow(a, (p - 1) // 2, p)

def tonelli(n, p):
    q = p - 1
    s = 0
    while q % 2 == 0:
        q //= 2
        s += 1
    if s == 1:
        return pow(n, (p + 1) // 4, p)
    for z in range(2, p):
        if p - 1 == legendre(z, p):
            break
    c = pow(z, q, p)
    r = pow(n, (q + 1) // 2, p)
    t = pow(n, q, p)
    m = s
    t2 = 0
    while (t - 1) % p != 0:
        t2 = (t * t) % p
        for i in range(1, m):
            if (t2 - 1) % p == 0:
                break
            t2 = (t2 * t2) % p
        b = pow(c, 1 << (m - i - 1), p)
        r = (r * b) % p
        c = (b * b) % p
        t = (t * c) % p
        m = i
    return r

class CurvePoint:
    def __init__ (self, x, y, curve):
        self.curve = curve
        self.x = x
        self.y = y
    def __str__ (self):
        return f"({self.x}, {self.y})"
    __repr__ = __str__
    def __eq__ (self, other):
        return str(self) == str(other)
    def __add__ (self, other):
        x1,y1 = self.x, self.y
        x2,y2 = other.x, other.y
        if x1 == 0 and y1 == 1:
            return other
        if x2 == 0 and y2 == 1:
            return self
        if x1 == x2 and y2 == self.curve.p - y1: #-y1
            return CurvePoint(0, 1, self.curve)
        if x1 == x2 and y1 == y2:
            lam = ((3*pow(x1,2,self.curve.p) + self.curve.a) * pow(2*y1, -1, self.curve.p))%self.curve.p
        else:
            lam = ((y2-y1) * pow(x2-x1, -1, self.curve.p)) % self.curve.p
        x3 = (lam**2 - x1 - x2) % self.curve.p
        y3 = (lam * (x1 - x3) - y1) % self.curve.p
        return CurvePoint(x3, y3, self.curve)
    def __mul__ (self, n):
        if type(n) != int:
            raise ValueError("You can only multiply by a scalar")
        Q = self
        R = CurvePoint(0,1,self.curve)
        if n == 0: return R
        if n == 1: return self
        while n > 0:
            if n % 2 == 1:
                R = R + Q
            Q = Q + Q
            n //= 2
        return R

class EllipticCurve:
    # for the curve x**3 + ax + b % p
    def __init__ (self, a, b, p):
        self.a = a
        self.b = b
        self.p = p
    def point(self, x):
        pt = (x**3 + self.a*x + self.b) % self.p
        if legendre(pt, self.p) != 1:
            return None # in the case that there is no quadratic residue
        y = tonelli(pt, self.p)
        return CurvePoint(x, y, self) # eventually have a point class built, too
    def __str__ (self):
        return f"y^2 = x**3 + {self.a}x + {self.b} % {self.p}"
