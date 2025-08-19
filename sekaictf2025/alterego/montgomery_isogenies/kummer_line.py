"""
Implementation of the Kummer Line of Montgomery Curves and the corresponding
Kummer Points used for x-only Montgomery curve arithmetic

Taken from:
https://github.com/jack4818/KummerIsogeny

===========================================================================

INFO: Construction

A KummerLine can be constructed straight from a Montgomery curve:

E = EllipticCurve(F, [0,A,0,1,0])
K = KummerLine(E)

Or, it can be constructed from the Montgomery coefficient

K = KummerLine(F, A)

Additionally, we allow A = (A : C) to be stored projectively and
we can construct this by

K = KummerLine(F, [A, C])

A KummerPoint can be constructed from coordinates

xP = K(X, Z)

Where x(P) = (X : Z) is the x-coordinate in projective XZ-coordinates

A KummerPoint can also be made straight from an elliptic curve point

E = EllipticCurve(F, [0,A,0,1,0])
P = E.random_point()

K = KummerLine(E)
xP = K(P)

===========================================================================

INFO: Usage

The methods of the KummerLine class are fairly straight-forward. Currently
missing is a check for whether two curves are isomorphic over the base field.

For this, we need an isomorphism between KummerLines which is a TODO.

For the points, scalar multiplication is performed by n*xP

Additionally, one can call `xP.double()` to perform x-only point addition
and xP.add(xQ, xPQ) to perform differential addition to recover xP + xQ
where xPQ = x(P - Q).

The 3 point ladder `xQ.ladder_3_pt(xP, xPQ, m) computes xP + [m]xQ

xP.multiples() generates values [l]xP by repeated differential addition. This
is used for isogeny computations where we want to collect the the first d points
for an isogeny of degree ell = 2d+1.
"""
import cypari2

pari = cypari2.Pari()

from sage.all import cached_method, Integer, EllipticCurve

from sage.structure.element import RingElement
from sage.schemes.elliptic_curves.ell_generic import EllipticCurve_generic
from sage.schemes.elliptic_curves.ell_point import EllipticCurvePoint_field

from utilities.fast_roots import sqrt_Fp2

# =================================================== #
#     Class for the Kummer Line x(x^2 + Ax + 1)       #
# =================================================== #


class KummerLine:
    def __init__(self, *args):
        self._curve = None

        # Allow the creation of the Kummer Line from an EllipticCurve
        if len(args) == 1:
            (curve,) = args
            if not isinstance(curve, EllipticCurve_generic):
                raise TypeError("not an elliptic curve")
            ainvs = curve.a_invariants()
            A, C = ainvs[1], 1
            if ainvs != (0, A, 0, 1, 0):
                raise ValueError("Must use Montgomery model")
            self._curve = curve
            self._base_ring = curve.base_ring()

        # Allow the creation of the Kummer line from a base field and coeffs.
        elif len(args) == 2:
            base_ring, curve_constants = args
            # Extract curve constants
            if isinstance(curve_constants, Integer) or len(curve_constants) == 1:
                A = curve_constants
                C = 1
            elif len(curve_constants) == 2:
                A, C = curve_constants
            else:
                raise ValueError(
                    "The Montgomery coefficient must either be a single scalar a, or\
                    a tuple [A, C] representing a = A/C."
                )
            self._base_ring = base_ring
        else:
            raise ValueError(
                "A Kummer Line must be constructed from either a Montgomery curve, or\
                    a base field and tuple representing the coefficient A/C = [A, C]"
            )

        # init variables
        self._A = self._base_ring(A)
        self._C = self._base_ring(C)

        self._A = pari(A)
        self._C = pari(C)

        # Make sure the curve is not singular
        if (self._A**2 - 4 * self._C**2) == 0:
            raise ValueError(
                f"Constants {curve_constants} do not define a Montgomery curve"
            )

    def __eq__(self, other):
        """
        Test equality of two curves
        """
        if self.base_ring() != other.base_ring():
            return False
        return self._A * other._C == other._A * self._C

    def __repr__(self):
        """
        String representation of the class
        """
        if self.a():
            return f"Kummer line of the Montgomery curve y^2 = x^3 + {self.a()}*x^2 + x over {self.base_ring()}"
        else:
            return f"Kummer line of the Montgomery curve y^2 = x^3 + x over {self.base_ring()}"

    def __call__(self, coords):
        """
        Create a Kummer Point with this Kummer Line as the parent
        """
        return KummerPoint(self, coords)

    def base_ring(self):
        """
        Return the base ring of the Kummer Line
        """
        return self._base_ring

    def extract_constants(self):
        """
        Return the Montgomery coefficient A as a tuple
        representing the projective form (A : C)
        """
        return self._A, self._C
    
    def AC(self):
        return self.extract_constants()

    def AC24(self):
        A,C = self.AC()
        return A+2*C, 4*C

    def zero(self):
        """
        Return the identity point on the Kummer Line
        """
        return self(None)

    def curve(self):
        """
        Lift the Kummer Line to an elliptic curve as a
        SageMath EllipticCurve
        """
        if not self._curve:
            self._curve = self.montgomery_curve()
        return self._curve

    @cached_method
    def montgomery_curve(self):
        """
        Compute the Montgomery Curve associated with the
        Kummer Line
        """
        F = self.base_ring()
        a = self.a()
        return EllipticCurve(F, [0, a, 0, 1, 0])

    @cached_method
    def short_weierstrass_curve(self):
        """
        Compute the Isomorphic curve in the short Weierstrass model
        associated with the Kummer Line
        """
        F = self.base_ring()
        A = self.a()

        A_sqr = A * A
        A_cube = A * A_sqr
        a = 1 - A_sqr / 3
        b = (2 * A_cube - 9 * A) / 27
        return EllipticCurve(F, [a, b])

    @cached_method
    def j_invariant(self):
        """
        Compute the j-invariant of the Kummer Line
        """
        j_num = 256 * (self._A**2 - 3 * self._C**2) ** 3
        j_den = self._C**4 * (self._A**2 - 4 * self._C**2)
        return j_num / j_den

    @cached_method
    def a(self):
        """
        Compute the Montgomery coefficient as a value
        in the base field
        """
        return self._A / self._C


# ====================================================== #
#  Class for points on the Kummer Line x(x^2 + Ax + 1)   #
# ====================================================== #


class KummerPoint:
    def __init__(self, parent, coords):
        # Ensure the parent is the right type
        if not isinstance(parent, KummerLine):
            raise TypeError("not a Montgomery Kummer line")

        R = parent.base_ring()

        # Point at infinity
        if coords is None:
            coords = (Integer(1), Integer(0))
        # Construct point from P on an elliptic curve in Montgomery form
        elif isinstance(coords, EllipticCurvePoint_field):
            # Make sure point's parent curve matches with Kummer Line
            a = parent.a()
            assert coords.curve().a_invariants() == (0, a, 0, 1, 0)
            coords = coords[0], coords[2]
        # Construct from X coordinate only
        elif isinstance(coords, RingElement):
            coords = (coords,)
        # Construct from a tuple (X : Z)
        else:
            coords = tuple(coords)

        # Sanitise the input coordinates
        if len(coords) == 1:
            coords += (R.one(),)
        if len(coords) != 2:
            raise ValueError("not a point on ℙ¹")
        coords = tuple(map(pari, map(R, coords)))

        # TODO: we should make sure the coordinates
        #       are on the curve!
        # Write something like `parent.is_x_coord(X, Z)`
        # which checks if x^3 + A*x^2 + x is a square in
        # the base field...
        self._base_ring = R
        self._parent = parent
        self._X, self._Z = coords

    def __repr__(self):
        return f"[{self._X} : {self._Z}]"
        # return f"Kummer Point [{self._X} : {self._Z}] on {self._parent}"

    def __bool__(self):
        """
        A point represents False if it is the point at infinity and
        True otherwise
        """
        return bool(self._Z)

    def __eq__(self, other):
        """
        Equality of two Kummer Points
        """
        if not isinstance(other, KummerPoint):
            raise ValueError("Can only compare equality between to Kummer Points")
        if self._parent != other._parent:
            return False
        return self._X * other._Z == other._X * self._Z

    def is_zero(self):
        """
        A Kummer Point is considered Zero if it is the identity point
        on the parent curve
        """
        return self._Z == 0

    def base_ring(self):
        """
        Get the base ring of the Kummer Point coordinates
        """
        return self._base_ring

    def parent(self):
        """
        Get the Kummer Line of which this point is constructed on
        """
        return self._parent

    def XZ(self):
        """
        Return the projective (X : Z) coordinates of the point
        """
        return self._X, self._Z

    def X(self):
        return self._X
    def Z(self):
        return self._Z

    def __iter__(self):
        return iter(self.XZ())

    def x(self):
        r""" """
        if not self._Z:
            raise ValueError("The identity point has no valid x-coordinate")
        if self._Z == 1:
            return self._base_ring(self._X)
        return self._base_ring(self._X / self._Z)

    @cached_method
    def curve_point(self):
        """
        Deterministically lift an x-coordinate
        taking the smallest y-coordinate as the
        chosen root.
        """
        # Get the Montgomery curve and constant A
        L = self.parent()
        E = L.curve()
        A = L.a()

        # Compute y2, assume x is a valid coordinate
        x = self.x()
        y2 = x * (x**2 + A * x + 1)
        y2 = self.base_ring()(y2)
        y = sqrt_Fp2(y2)
        return E(x, y)

    # =================================== #
    # Addition and multiplication helpers #
    # =================================== #

    @staticmethod
    def xDBL(X, Z, A, C):
        """
        function for Montgomery doubling with projective curve constant

        Input:  projective point P = (X:Z), curve constants (A:C)
        Output: projective point [2]P = (X2:Z2)

        Cost: 4M + 2S + 8a
        """

        t0 = X - Z
        t1 = X + Z
        t0 *= t0
        t1 *= t1
        Z2 = C * t0
        Z2 = Z2 + Z2
        Z2 = Z2 + Z2
        X2 = Z2 * t1
        t1 = t1 - t0
        t0 = C + C
        t0 = t0 + A
        t0 *= t1
        Z2 = Z2 + t0
        Z2 *= t1

        return X2, Z2

    @staticmethod
    def xADD(XP, ZP, XQ, ZQ, xPQ, zPQ):
        """
        function for Montgomery differential addition

        Input:  projective coordinates P = (XP : ZP),
                Q=(XQ : ZQ), and their difference
                x(P-Q) = (xPQ : zPQ)
        Output: coordinates of sum P + Q = (XQP : ZQP)

        Cost: 4M + 2S + 6a
        """
        t0 = XP + ZP
        t1 = XP - ZP
        XP = XQ - ZQ
        ZP = XQ + ZQ
        t0 *= XP
        t1 *= ZP
        ZP = t0 - t1
        XP = t0 + t1
        ZP = ZP * ZP
        XQP = XP * XP
        ZQP = xPQ * ZP
        XQP = XQP * zPQ

        return XQP, ZQP

    @staticmethod
    def xDBLADD(XP, ZP, XQ, ZQ, xPQ, zPQ, A24, C24):
        """
        function for step in Montgomery ladder
        simultaneous doubling and differential addition

        Input: projective coordinates P=(XP:ZP) and Q=(XQ:ZQ),
               projective difference P-Q=(xPQ:zPQ) and
               curve constant A24/C24=(A+2C)/4C.
        Output: projective coordinates of 2P=(X2P:Z2P)
                and Q+P=(XQP:ZQP)

        Cost: 8M + 4S + 8A
        """

        t0 = XP + ZP
        t1 = XP - ZP
        X2P = t0 * t0
        t2 = XQ - ZQ
        XQP = XQ + ZQ
        t0 *= t2
        Z2P = t1 * t1
        t1 *= XQP
        t2 = X2P - Z2P
        Z2P *= C24
        X2P *= Z2P
        XQP = A24 * t2
        ZQP = t0 - t1
        Z2P = XQP + Z2P
        XQP = t0 + t1
        Z2P *= t2
        ZQP *= ZQP
        XQP *= XQP
        ZQP = xPQ * ZQP
        XQP = XQP * zPQ

        return X2P, Z2P, XQP, ZQP

    @staticmethod
    def xTPL(XP, ZP, A2):
        """
        SIKE algorithm 46

        Fast x-only tripling
        """
        t1 = XP * XP
        t2 = ZP * ZP
        t3 = t1 + t2
        t4 = A2 * ((XP + ZP) ** 2 - t3) + t3
        t3 = (t1 - t2) ** 2
        t1 = (4 * t1 * t4 - t3) ** 2
        t2 = (4 * t2 * t4 - t3) ** 2

        return XP * t2, ZP * t1

    # =================================== #
    # Addition and multiplication methods #
    # =================================== #

    def _double(self):
        """
        Returns [2] self
        """
        X, Z = self.XZ()
        A, C = self._parent.extract_constants()
        X2, Z2 = self.xDBL(X, Z, A, C)
        return self._parent((X2, Z2))

    def double(self):
        """
        Wrapper function which deals with the doubling of
        the identity

        Returns [2] * self
        """
        # Deal with identity
        if not self._Z:
            return self
        return self._double()

    def _double_iter(self, n):
        """
        Returns [2^n] self, faster than generic
        multiplication as we only need doubling,
        no addition.
        """
        X, Z = self.XZ()
        A, C = self._parent.extract_constants()
        for _ in range(n):
            X, Z = self.xDBL(X, Z, A, C)
        return self._parent((X, Z))

    def double_iter(self, n):
        """
        Wrapper function which deals with the repeated
        doubling

        Returns [2^n] * self
        """
        # Deal with identity
        if not self._Z:
            return self
        return self._double_iter(n)

    def _triple_iter(self, n):
        """
        Returns [3^n] self, faster than generic
        multiplication as we only need doubling,
        no addition.
        """
        X, Z = self.XZ()
        A, C = self._parent.extract_constants()
        A = A / (2 * C)
        for _ in range(n):
            X, Z = self.xTPL(X, Z, A)
        return self._parent((X, Z))

    def triple_iter(self, n):
        """
        Wrapper function which deals with the repeated
        tripling

        Returns [3^n] * self
        """
        # Deal with identity
        if not self._Z:
            return self
        return self._triple_iter(n)

    def _add(self, Q, PQ):
        """
        Performs differential addition assuming
        P, Q and PQ are all not the point at
        infinity
        """
        XP, ZP = self.XZ()
        XQ, ZQ = Q.XZ()
        XPQ, ZPQ = PQ.XZ()

        X_new, Z_new = self.xADD(XP, ZP, XQ, ZQ, XPQ, ZPQ)
        return self._parent((X_new, Z_new))

    def add(self, Q, PQ):
        """
        Function to perform differential addition and
        handle the cases when P, Q or PQ are the points
        at infinity
        """
        # Adding O + Q = Q
        if not self._Z:
            return Q

        # Adding P + O = P
        if not Q._Z:
            return self

        # Difference is the identity
        # so P = Q and P+Q = [2]P
        if not PQ._Z:
            return self._double()

        return self._add(Q, PQ)

    def __mul__(self, m):
        """
        Montgomery-ladder to compute [m]P

        Input: coordinates of P=(XP:ZP)
               scalar factor m, curve constants (A:C)
        Output: KummerPoint [m]P=(X0:Z0)
        """
        if not isinstance(m, (int, Integer)):
            try:
                m = Integer(m)
            except:
                raise TypeError(f"Cannot coerce input scalar {m = } to an integer")

        # If m is zero, return identity
        if not m:
            return self.parent().zero()

        # [m]P = [-m]P for x-only
        m = abs(m)

        # Extract base field and coefficients
        R = self.base_ring()
        XP, ZP = self.XZ()

        # Initialise for loop
        X0, Z0 = R.one(), R.zero()
        X1, Z1 = XP, ZP

        # Converting parameters for projective DBLADD -> (A24:C24)=(A+2C:4C)
        A, C = self.parent().extract_constants()
        A24 = C + C
        C24 = pari(A24 + A24)
        A24 = pari(A24 + A)

        # Montgomery-ladder
        for bit in bin(m)[2:]:
            if bit == "0":
                X0, Z0, X1, Z1 = self.xDBLADD(X0, Z0, X1, Z1, XP, ZP, A24, C24)
            else:
                X1, Z1, X0, Z0 = self.xDBLADD(X1, Z1, X0, Z0, XP, ZP, A24, C24)

        return self._parent((X0, Z0))

    def __rmul__(self, m):
        return self * m

    def __imul__(self, m):
        self = self * m
        return self

    def ladder_3_pt(self, xP, xPQ, m):
        """
        Function to compute xP + [m]xQ using x-only
        arithmetic. Very similar to the Montgomery ladder above

        Note: self = xQ
        """
        if not isinstance(m, (int, Integer)):
            try:
                m = Integer(m)
            except:
                raise TypeError(f"Cannot coerce input scalar {m = } to an integer")

        # If m is zero, return xP
        if not m:
            return xP

        # [m]P = [-m]P for x-only
        m = abs(m)

        # Converting parameters for projective DBLADD -> (A24:C24)=(A+2C:4C)
        A, C = self.parent().extract_constants()
        A24 = C + C
        C24 = pari(A24 + A24)
        A24 = pari(A24 + A)

        # Extract out coordinates
        XQ, ZQ = self.XZ()
        XP, ZP = xP.XZ()
        XPQ, ZPQ = xPQ.XZ()

        # Montgomery-ladder
        for bit in bin(m)[:1:-1]:
            if bit == "1":
                XQ, ZQ, XP, ZP = self.xDBLADD(XQ, ZQ, XP, ZP, XPQ, ZPQ, A24, C24)
            else:
                XQ, ZQ, XPQ, ZPQ = self.xDBLADD(XQ, ZQ, XPQ, ZPQ, XP, ZP, A24, C24)
        return self._parent((XP, ZP))

    def multiples(self):
        """
        A generator of points [l]P for self = P
        Stops when it has generated the full subgroup generated by P
        (without the identity point).

        NOTE: this is implemented to make Vélu-like computations easy
        """
        yield self
        R = self._double()
        # Order 2 case
        if not R:
            return

        # Odd order case
        Q = self
        while R:
            yield R
            S = R.add(self, Q)
            Q, R = R, S

        return



# """
# Implementation of the Kummer Line of Montgomery Curves and the corresponding 
# Kummer Points used for x-only Montgomery curve arithmetic

# Taken from:
# https://github.com/jack4818/KummerIsogeny

# ===========================================================================

# INFO: Construction

# A KummerLine can be constructed straight from a Montgomery curve:

# E = EllipticCurve(F, [0,A,0,1,0])
# K = KummerLine(E)

# Or, it can be constructed from the Montgomery coefficient

# K = KummerLine(F, A)

# Additionally, we allow A = (A : C) to be stored projectively and
# we can construct this by

# K = KummerLine(F, [A, C])

# A KummerPoint can be constructed from coordinates

# xP = K(X, Z)

# Where x(P) = (X : Z) is the x-coordinate in projective XZ-coordinates

# A KummerPoint can also be made straight from an elliptic curve point

# E = EllipticCurve(F, [0,A,0,1,0])
# P = E.random_point()

# K = KummerLine(E)
# xP = K(P)

# ===========================================================================

# INFO: Usage

# The methods of the KummerLine class are fairly straight-forward. Currently
# missing is a check for whether two curves are isomorphic over the base field.

# For this, we need an isomorphism between KummerLines which is a TODO.

# For the points, scalar multiplication is performed by n*xP

# Additionally, one can call `xP.double()` to perform x-only point addition
# and xP.add(xQ, xPQ) to perform differential addition to recover xP + xQ
# where xPQ = x(P - Q).

# The 3 point ladder `xQ.ladder_3_pt(xP, xPQ, m) computes xP + [m]xQ

# xP.multiples() generates values [l]xP by repeated differential addition. This
# is used for isogeny computations where we want to collect the the first d points
# for an isogeny of degree ell = 2d+1. 
# """
# from sage.all import cached_method, Integer, EllipticCurve

# from sage.structure.element import RingElement
# from sage.schemes.elliptic_curves.ell_generic import EllipticCurve_generic
# from sage.schemes.elliptic_curves.ell_point import EllipticCurvePoint_field

# from utilities.fast_sqrt import sqrt_Fp2

# # =================================================== #
# #     Class for the Kummer Line x(x^2 + Ax + 1)       #
# # =================================================== #

# class KummerLine:
#     def __init__(self, *args):
#         self._curve = None

#         # Allow the creation of the Kummer Line from an EllipticCurve
#         if len(args) == 1:
#             curve, = args
#             if not isinstance(curve, EllipticCurve_generic):
#                 raise TypeError('not an elliptic curve')
#             ainvs = curve.a_invariants()
#             A, C = ainvs[1], 1
#             if ainvs != (0, A, 0, 1, 0):
#                 raise ValueError('Must use Montgomery model')
#             self._curve = curve
#             self._base_ring = curve.base_ring()

#         # Allow the creation of the Kummer line from a base field and coeffs.
#         elif len(args) == 2:
#             base_ring, curve_constants = args
#             # Extract curve constants
#             if isinstance(curve_constants, Integer) or len(curve_constants) == 1:
#                 A = curve_constants
#                 C = 1
#             elif len(curve_constants) == 2:
#                 A, C = curve_constants
#             else:
#                 raise ValueError(
#                     "The Montgomery coefficient must either be a single scalar a, or\
#                     a tuple [A, C] representing a = A/C."
#                 )
#             self._base_ring = base_ring
#         else:
#             raise ValueError(
#                 "A Kummer Line must be constructed from either a Montgomery curve, or\
#                     a base field and tuple representing the coefficient A/C = [A, C]"
#                 )

#         # init variables
#         self._A = self._base_ring(A)
#         self._C = self._base_ring(C)

#         # Make sure the curve is not singular
#         if (self._A**2 - 4*self._C**2).is_zero():
#             raise ValueError(f"Constants {curve_constants} do not define a Montgomery curve")
    
#     def __eq__(self, other):
#         """
#         Test equality of two curves
#         """
#         if self.base_ring() != other.base_ring():
#             return False
#         return self._A * other._C == other._A * self._C

#     def __repr__(self):
#         """
#         String representation of the class
#         """
#         if self.a():
#             return f'Kummer line of the Montgomery curve y^2 = x^3 + {self.a()._coeff_repr()}*x^2 + x over {self.base_ring()}'
#         else:
#             return f'Kummer line of the Montgomery curve y^2 = x^3 + x over {self.base_ring()}'
        
#     def __call__(self, coords):
#         """
#         Create a Kummer Point with this Kummer Line as the parent
#         """
#         return KummerPoint(self, coords)
        
#     def base_ring(self):
#         """
#         Return the base ring of the Kummer Line
#         """
#         return self._base_ring

#     def extract_constants(self):
#         """
#         Return the Montgomery coefficient A as a tuple 
#         representing the projective form (A : C)
#         """
#         return self._A, self._C
    
#     def zero(self):
#         """
#         Return the identity point on the Kummer Line
#         """
#         return self(None)
    
#     def curve(self):
#         """
#         Lift the Kummer Line to an elliptic curve as a 
#         SageMath EllipticCurve
#         """
#         if not self._curve:
#             self._curve = self.montgomery_curve()
#         return self._curve
    
#     @cached_method
#     def montgomery_curve(self):
#         """
#         Compute the Montgomery Curve associated with the
#         Kummer Line
#         """
#         F = self.base_ring()
#         a = self.a()
#         return EllipticCurve(F, [0,a,0,1,0])
    
#     @cached_method
#     def short_weierstrass_curve(self):
#         """
#         Compute the Isomorphic curve in the short Weierstrass model
#         associated with the Kummer Line
#         """
#         F = self.base_ring()
#         A = self.a()

#         A_sqr  = A*A
#         A_cube = A*A_sqr
#         a = 1 - A_sqr/3
#         b = (2*A_cube - 9*A) / 27
#         return EllipticCurve(F, [a,b])
    
#     @cached_method
#     def j_invariant(self):
#         """
#         Compute the j-invariant of the Kummer Line
#         """
#         j_num = 256 * (self._A**2 - 3*self._C**2)**3
#         j_den = self._C**4 * (self._A**2 - 4*self._C**2)
#         return j_num / j_den

#     @cached_method
#     def a(self):
#         """
#         Compute the Montgomery coefficient as a value
#         in the base field
#         """
#         return self._A / self._C

# # ====================================================== #
# #  Class for points on the Kummer Line x(x^2 + Ax + 1)   #
# # ====================================================== #

# class KummerPoint:
#     def __init__(self, parent, coords):
#         # Ensure the parent is the right type
#         if not isinstance(parent, KummerLine):
#             raise TypeError('not a Montgomery Kummer line')

#         R = parent.base_ring()

#         # Point at infinity
#         if coords is None:
#             coords = (Integer(1), Integer(0))
#         # Construct point from P on an elliptic curve in Montgomery form
#         elif isinstance(coords, EllipticCurvePoint_field):
#             # Make sure point's parent curve matches with Kummer Line
#             a = parent.a()
#             assert coords.curve().a_invariants() == (0,a,0,1,0)
#             coords = coords[0], coords[2]
#         # Construct from X coordinate only
#         elif isinstance(coords, RingElement):
#             coords = coords,
#         # Construct from a tuple (X : Z)
#         else:
#             coords = tuple(coords)

#         # Sanitise the input coordinates
#         if len(coords) == 1:
#             coords += R.one(),
#         if len(coords) != 2:
#             raise ValueError('not a point on ℙ¹')
#         coords = tuple(map(R, coords))

#         # TODO: we should make sure the coordinates
#         #       are on the curve!
#         # Write something like `parent.is_x_coord(X, Z)`
#         # which checks if x^3 + A*x^2 + x is a square in
#         # the base field...
#         self._base_ring = R
#         self._parent = parent
#         self._X, self._Z = coords

#     def __repr__(self):
#         return f'Kummer Point [{self._X} : {self._Z}] on {self._parent}'

#     def __bool__(self):
#         """
#         A point represents False if it is the point at infinity and 
#         True otherwise
#         """
#         return bool(self._Z)
    
#     def __eq__(self, other):
#         """
#         Equality of two Kummer Points
#         """
#         if not isinstance(other, KummerPoint):
#             raise ValueError("Can only compare equality between to Kummer Points")
#         if self._parent != other._parent:
#             return False
#         return self._X * other._Z == other._X * self._Z

#     def is_zero(self):
#         """
#         A Kummer Point is considered Zero if it is the identity point 
#         on the parent curve
#         """
#         return self._Z.is_zero()

#     def base_ring(self):
#         """
#         Get the base ring of the Kummer Point coordinates
#         """
#         return self._base_ring
    
#     def parent(self):
#         """
#         Get the Kummer Line of which this point is constructed on
#         """
#         return self._parent
    
#     def XZ(self):
#         """
#         Return the projective (X : Z) coordinates of the point
#         """
#         return self._X, self._Z

#     def x(self):
#         r"""
#         """
#         if not self._Z:
#             raise ValueError("The identity point has no valid x-coordinate")
#         if self._Z.is_one():
#             return self._X
#         return self._X / self._Z
    
#     @cached_method
#     def curve_point(self):
#         """
#         Deterministically lift an x-coordinate
#         taking the smallest y-coordinate as the 
#         chosen root.
#         """
#         # Get the Montgomery curve and constant A
#         L = self.parent()
#         E = L.curve()
#         A = L.a()

#         # Compute y2, assume x is a valid coordinate
#         x = self.x()
#         y2 = x * (x**2 + A*x + 1)
#         y = sqrt_Fp2(y2)
#         return E(x, y)

#     # =================================== #
#     # Addition and multiplication helpers #
#     # =================================== #

#     @staticmethod
#     def xDBL(X, Z, A, C):
#         """
#         function for Montgomery doubling with projective curve constant
        
#         Input:  projective point P = (X:Z), curve constants (A:C) 
#         Output: projective point [2]P = (X2:Z2)

#         Cost: 4M + 2S + 8a
#         """

#         t0 = X - Z      
#         t1 = X + Z
#         t0 = t0**2
#         t1 = t1**2
#         Z2 = C * t0
#         Z2 = Z2 + Z2
#         Z2 = Z2 + Z2
#         X2 = Z2 * t1
#         t1 = t1 - t0
#         t0 = C + C
#         t0 = t0 + A
#         t0 = t0 * t1
#         Z2 = Z2 + t0
#         Z2 = Z2 * t1
        
#         return X2, Z2

#     @staticmethod
#     def xADD(XP, ZP, XQ, ZQ, xPQ, zPQ):
#         """
#         function for Montgomery differential addition
        
#         Input:  projective coordinates P = (XP : ZP), 
#                 Q=(XQ : ZQ), and their difference 
#                 x(P-Q) = (xPQ : zPQ) 
#         Output: coordinates of sum P + Q = (XQP : ZQP)

#         Cost: 4M + 2S + 6a
#         """
#         t0 = XP + ZP     
#         t1 = XP - ZP   
#         XP = XQ - ZQ   
#         ZP = XQ + ZQ   
#         t0 = XP * t0   
#         t1 = ZP * t1   
#         ZP = t0 - t1   
#         XP = t0 + t1   
#         ZP = ZP**2     
#         XQP = XP**2    
#         ZQP = xPQ * ZP 
#         XQP = XQP * zPQ
        
#         return XQP, ZQP

#     @staticmethod
#     def xDBLADD(XP,ZP,XQ,ZQ,xPQ,zPQ,A24,C24):
#         """
#         function for step in Montgomery ladder
#         simultaneous doubling and differential addition
        
#         Input: projective coordinates P=(XP:ZP) and Q=(XQ:ZQ), 
#                projective difference P-Q=(xPQ:zPQ) and 
#                curve constant A24/C24=(A+2C)/4C.   
#         Output: projective coordinates of 2P=(X2P:Z2P)
#                 and Q+P=(XQP:ZQP)

#         Cost: 8M + 4S + 8A
#         """
        
#         t0 = XP + ZP                  
#         t1 = XP - ZP 
#         X2P = t0**2
#         t2 = XQ - ZQ
#         XQP = XQ + ZQ
#         t0 = t0 * t2
#         Z2P = t1**2
#         t1 = t1 * XQP
#         t2 = X2P - Z2P
#         Z2P = Z2P * C24
#         X2P = X2P * Z2P
#         XQP = A24 * t2
#         ZQP = t0 - t1
#         Z2P = XQP + Z2P
#         XQP = t0 + t1
#         Z2P = Z2P * t2
#         ZQP = ZQP**2
#         XQP = XQP**2
#         ZQP = xPQ * ZQP
#         XQP = XQP * zPQ

#         return X2P, Z2P, XQP, ZQP

#     # =================================== #
#     # Addition and multiplication methods #
#     # =================================== #
#     def _double(self):
#         """
#         Returns [2] self
#         """
#         X, Z = self.XZ()
#         A, C = self._parent.extract_constants()
#         X2, Z2 = self.xDBL(X, Z, A, C)
#         return self._parent((X2, Z2))

#     def double(self):
#         """
#         Wrapper function which deals with the doubling of
#         the identity

#         Returns [2] * self
#         """
#         # Deal with identity
#         if not self._Z:
#             return self
#         return self._double()
    
#     def _add(self, Q, PQ):
#         """
#         Performs differential addition assuming 
#         P, Q and PQ are all not the point at 
#         infinity
#         """
#         XP, ZP = self.XZ()
#         XQ, ZQ = Q.XZ()
#         XPQ, ZPQ = PQ.XZ()

#         X_new, Z_new = self.xADD(XP, ZP, XQ, ZQ, XPQ, ZPQ)
#         return self._parent((X_new, Z_new))

#     def add(self, Q, PQ):
#         """
#         Function to perform differential addition and
#         handle the cases when P, Q or PQ are the points
#         at infinity
#         """
#         # Adding O + Q = Q
#         if not self._Z:
#             return Q

#         # Adding P + O = P
#         if not Q._Z:
#             return self

#         # Difference is the identity
#         # so P = Q and P+Q = [2]P
#         if not PQ._Z:
#             return self._double()

#         return self._add(Q, PQ)
    
#     def __mul__(self, m):
#         """
#         Montgomery-ladder to compute [m]P

#         Input: coordinates of P=(XP:ZP) 
#                scalar factor m, curve constants (A:C)
#         Output: KummerPoint [m]P=(X0:Z0)
#         """
#         if not isinstance(m, (int, Integer)):
#             try:
#                 m = Integer(m)
#             except:
#                 raise TypeError(f"Cannot coerce input scalar {m = } to an integer")
        
#         # If m is zero, return identity
#         if not m:
#             return self.parent().zero()
        
#         # [m]P = [-m]P for x-only
#         m = abs(m)
       
#         # Extract base field and coefficients
#         R = self.base_ring()
#         XP, ZP = self.XZ()
        
#         # Initialise for loop
#         X0, Z0 = R.one(), R.zero()
#         X1, Z1 = XP, ZP
                
#         # Converting parameters for projective DBLADD -> (A24:C24)=(A+2C:4C)
#         A, C = self.parent().extract_constants()
#         A24 = C + C                     
#         C24 = A24 + A24
#         A24 = A24 + A

#         # Montgomery-ladder
#         for bit in bin(m)[2:]:
#             if bit == "0":
#                 X0, Z0, X1, Z1 = self.xDBLADD(X0, Z0, X1, Z1, XP, ZP, A24, C24)
#             else:
#                 X1, Z1, X0, Z0 = self.xDBLADD(X1, Z1, X0, Z0, XP, ZP, A24, C24)  
                
#         return self._parent((X0, Z0))   
    
#     def __rmul__(self, m):
#         return self * m
    
#     def __imul__(self, m):
#         self = self * m
#         return self
    
#     def ladder_3_pt(self, xP, xPQ, m):
#         """
#         Function to compute xP + [m]xQ using x-only
#         arithmetic. Very similar to the Montgomery ladder above

#         Note: self = xQ
#         """
#         if not isinstance(m, (int, Integer)):
#             try:
#                 m = Integer(m)
#             except:
#                 raise TypeError(f"Cannot coerce input scalar {m = } to an integer")
        
#         # If m is zero, return xP
#         if not m:
#             return xP
        
#         # [m]P = [-m]P for x-only
#         m = abs(m)

#         # Converting parameters for projective DBLADD -> (A24:C24)=(A+2C:4C)
#         A, C = self.parent().extract_constants()
#         A24 = C + C                     
#         C24 = A24 + A24
#         A24 = A24 + A

#         # Extract out coordinates
#         XQ, ZQ = self.XZ()
#         XP, ZP = xP.XZ()
#         XPQ, ZPQ = xPQ.XZ()

#         # Montgomery-ladder
#         for bit in bin(m)[:1:-1]:
#             if bit == "1":
#                 XQ, ZQ, XP, ZP = self.xDBLADD(XQ, ZQ, XP, ZP, XPQ, ZPQ, A24, C24)
#             else:
#                 XQ, ZQ, XPQ, ZPQ = self.xDBLADD(XQ, ZQ, XPQ, ZPQ, XP, ZP, A24, C24)  
#         return  self._parent((XP, ZP))
        
#     def multiples(self):
#         """
#         A generator of points [l]P for self = P
#         Stops when it has generated the full subgroup generated by P
#         (without the identity point).

#         NOTE: this is implemented to make Vélu-like computations easy
#         """
#         yield self
#         R = self.double()
#         # Order 2 case
#         if not R:
#             return
        
#         # Odd order case
#         Q = self
#         while R:
#             yield R
#             S = R.add(self, Q)
#             Q, R = R, S

#         return