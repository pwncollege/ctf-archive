import os

# pwn.college convention: the real flag is placed at /flag at runtime.
# Fall back to a build-time default so the app is self-contained.
def _load_flag():
    try:
        with open("/flag") as f:
            return f.read().strip()
    except OSError:
        return "BtSCTF2026{flag_not_found}"

FLAG = _load_flag()
SECRET_KEY = os.environ.get("SECRET_KEY", "veong8Aej2oos6ohhieng4yuPhae8eas")
