import jwt
import datetime
from function import F
from igen import IGen
from dataclasses import asdict
from secrets import token_hex
from lookup import lookup_challenge

from secret import SECRET_KEY
THRESHOLD = 0.01

def captcha_challenge(token: str, expiry: datetime.date) -> str:
    payload = {
        "challenge": token,
        "exp": expiry
    }

    return jwt.encode(payload, SECRET_KEY, algorithm="HS256")


def captcha_verify(token: str, answer: float) -> bool:
    try:
        decoded = jwt.decode(token, SECRET_KEY, algorithms=["HS256"])

        challenge_token = decoded.get("challenge")
        if not challenge_token:
            print("Challenge token missing in JWT.")
            return False

        lc = lookup_challenge.get(challenge_token)
        if not lc:
            print("No function found for the given challenge token.")
            return False
        
        integral = lc.integral

        print(f"Expected answer: {integral.solve()}, User answer: {answer}")
        # 1% relative error allowed 
        return abs(integral.solve() - answer) / max(abs(integral.solve()), 1e-9) < THRESHOLD
    except jwt.ExpiredSignatureError:
        print("Token has expired.")
        return False
    except jwt.InvalidTokenError:
        print("Invalid token.")
        return False


def token_new():
    payload = {
        "count": 0,
        "last_id": None,
        "exp": datetime.datetime.now(datetime.UTC) + datetime.timedelta(minutes=10)
    }
    return jwt.encode(payload, SECRET_KEY, algorithm="HS256")

def token_payload(token: str) -> dict:
    if not token:
        return {"count": 0, "last_id": None}
    try:
        return jwt.decode(token, SECRET_KEY, algorithms=["HS256"])
    except (jwt.ExpiredSignatureError, jwt.InvalidTokenError):
        return {"count": 0, "last_id": None}

def token_inc(current_payload: dict, solved_id: str) -> str:
    new_payload = {
        "count": current_payload.get('count', 0) + 1,
        "last_id": solved_id,
        "exp": datetime.datetime.now(datetime.UTC) + datetime.timedelta(minutes=10)
    }
    return jwt.encode(new_payload, SECRET_KEY, algorithm="HS256")
