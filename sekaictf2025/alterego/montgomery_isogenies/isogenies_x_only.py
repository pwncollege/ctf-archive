"""
Underneath everything, our isogenies are on the Kummer line

L : x^3 + Ax^2 + x

And we perform our x-only isogenies by working with the x-coordinates
represented projectively as x(P) = (X : Z) which we call KummerPoints

However, for FESTA(+) we always need the full point eventually for either
additions or the (2,2)-isogeny, so we need a way to recover the full point.

The trick we use is that we always evaluate our isogenies on torsion bases, 
so we can use the Weil pairing to recover phi(P) up to an overall sign.

This file takes elliptic curves and points on these curves, maps them to the
Kummer line, performs fast x-only isogeny computations and then lifts the
result back to full points on the codomain curves.
"""

# Sage imports
from sage.all import gcd, randint
from sage.structure.element import RingElement

# Local Imports
from montgomery_isogenies.kummer_line import KummerLine
from montgomery_isogenies.kummer_isogeny import KummerLineIsogeny
from utilities.supersingular import torsion_basis
from utilities.pairing import weil_pairing_pari

# =========================================================== #
#    Compute an isogeny and codomain using x-only algorithms  #
# =========================================================== #

def _random_isogeny_x_only(E, D):
    """
    Helper function to compute one step in the isogeny
    chain for `random_isogeny_x_only`. 
    """
    # Compute a random point of order D to act as our
    # isogeny kernel
    k = randint(0, D)
    P, Q = torsion_basis(E, D)
    K = P + k*Q

    # Map curve and kernel to Kummer Line
    L = KummerLine(E)
    xK = L(K[0])

    # Use x-only arithmetic to compute an isogeny 
    # and codomain
    phi = KummerLineIsogeny(L, xK, D)

    # Compute the curve from the Kummer Line
    codomain = phi.codomain().curve()

    # Speed up SageMath by setting the order of the curve
    p = E.base_ring().characteristic()
    codomain.set_order((p+1)**2, num_checks=0)
    
    return phi, codomain

def random_isogeny_x_only(E, D):
    """
    Computes a D-degree isogeny from E using
    x-only arithmetic and returns the KummerIsogeny
    together with the codomain curve.

    When D does not divide the available torsion,
    the isogeny is computed in steps with the 
    helper function _random_isogeny_x_only
    """
    deg = 1
    phi_list = []
    p = E.base_field().characteristic()

    # Compute isogenies of degree gcd(D // deg, p+1)
    # until an isogeny of degree D is computed
    while deg != D:
        next_deg = gcd(D // deg, p+1)
        phi, E = _random_isogeny_x_only(E, next_deg)
        deg *= next_deg

        phi_list.append(phi)
    
    # Create a composite x-only isogeny from factors
    phi = KummerLineIsogeny.from_factors(phi_list)
    return phi, E

def isogeny_from_scalar_x_only(E, D, m, basis=None):
    """
    Computes a D-degree isogeny from E using
    x-only arithmetic and returns the KummerIsogeny
    together with the codomain curve.

    The isogeny has a kernel K which is computed from
    the canonical basis E[D] = <P,Q> and given scalar(s)
    of the form:
        K = P + [m]Q     or     K = [a]P + [b]Q
    depending on whether m is a scalar, or a length two 
    tuple of scalars
    """
    # Allow a precomputed basis
    if not basis:
        P, Q = torsion_basis(E, D)
    else:
        P, Q = basis

    # Allow either an integer or tuple of integers
    if isinstance(m, RingElement) or isinstance(m, int):
        K = P + m*Q
    else:
        assert len(m) == 2
        K = m[0]*P + m[1]*Q

    # Map curve and kernel to Kummer Line
    L = KummerLine(E)
    xK = L(K)

    # Use x-only arithmetic to compute an isogeny 
    # and codomain
    phi = KummerLineIsogeny(L, xK, D)

    # Compute the curve from the Kummer Line
    codomain = phi.codomain().curve()

    # Speed up SageMath by setting the order of the curve
    p = E.base_ring().characteristic()
    codomain.set_order((p+1)**2, num_checks=0)

    return phi, codomain

# ================================================= #
#    Evaluate an x-only isogeny on a torsion basis  #
# ================================================= #

def lift_image_to_curve(P, Q, ximP, ximQ, n, d):
    """
    Given the torsion basis <P, Q> = E[n]
    and the x-coordinates of the images x(phi(P))
    and x(phi(P)) of a degree d-isogeny compute 
    the image of the full points up to an overall sign:
        ±phi(P), ±phi(Q)
    """
    # Lift the points to the curve
    imPb = ximP.curve_point()
    imQb = ximQ.curve_point()
    
    # Compute two pairings
    pair_E0 = weil_pairing_pari(P, Q, n)
    pair_E1 = weil_pairing_pari(imPb, imQb, n)
    
    # Correct the sign
    if pair_E0**d != pair_E1:
        imQb = -imQb

    return imPb, imQb

def evaluate_isogeny_x_only(phi, P, Q, n, d):
    """
    Given an x-only isogeny phi degree d, and the torsion basis
    <P,Q> = E[n], compute the image of the torsion basis up to
    and overall sign: ±phi(P), ±phi(Q)

    Does this by evaluating KummerPoints with a KummerIsogeny
    and lifts them back to the curve using the Weil pairing 
    trick in `lift_image_to_curve`
    """
    # Domain of isogeny
    L0 = phi.domain()

    # Extract x-coordinates from points and convert to KummerPoints
    xP, xQ = L0(P[0]), L0(Q[0])
    
    # Evaluate the isogeny
    ximP, ximQ = phi(xP), phi(xQ)
    
    # Use Weil pairing trick to get y-coordinate back
    imP, imQ = lift_image_to_curve(P, Q, ximP, ximQ, n, d)
    
    return imP, imQ