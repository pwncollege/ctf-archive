from dataclasses import dataclass
import datetime
import jwt

from igen import IGen
from secret import SECRET_KEY

@dataclass
class LookupEntry:
    integral: IGen
    expiry: datetime.time

lookup_challenge : dict[str, LookupEntry] = {}

revoked_tokens = set()

def is_revoked(token_jwt):
    return token_jwt in revoked_tokens

def token_payload(token: str) -> dict:
    if not token:
        return {"count": 0, "last_id": None, "exp": 0}
    try:
        return jwt.decode(token, SECRET_KEY, algorithms=["HS256"])
    except (jwt.ExpiredSignatureError, jwt.InvalidTokenError):
        return {"count": 0, "last_id": None, "exp": 0}

def revoke_token(token_jwt):
    if token_jwt:
        revoked_tokens.add(token_jwt)

def lookup_cleanup():
    keys_to_delete = [key for key, entry in lookup_challenge.items() if entry.expiry < datetime.datetime.now(datetime.timezone.utc)]
    for key in keys_to_delete:    
        del lookup_challenge[key]

    keys_to_delete = [key for key in revoked_tokens if token_payload(key).get('exp', 0) < datetime.datetime.now(datetime.timezone.utc).timestamp()]
    for key in keys_to_delete:
        revoked_tokens.remove(key)
