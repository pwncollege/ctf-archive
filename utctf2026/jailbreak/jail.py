import sys

_ENC = [0x37, 0x36, 0x24, 0x2e, 0x23, 0x25, 0x39, 0x32, 0x3b, 0x1d, 0x28, 0x23, 0x73, 0x2e, 0x1d, 0x71, 0x31, 0x21, 0x76, 0x32, 0x71, 0x1d, 0x2f, 0x76, 0x31, 0x36, 0x71, 0x30, 0x3f]
_KEY = 0x42

def _secret():
    return ''.join(chr(b ^ _KEY) for b in _ENC)

BANNED = [
    "import", "os", "sys", "system", "eval",
    "open", "read", "write", "subprocess", "pty",
    "popen", "secret", "_enc", "_key"
]

SAFE_BUILTINS = {
    "print": print,
    "input": input,
    "len": len,
    "str": str,
    "int": int,
    "chr": chr,
    "ord": ord,
    "range": range,
    "type": type,
    "dir": dir,
    "vars": vars,
    "getattr": getattr,
    "setattr": setattr,
    "hasattr": hasattr,
    "isinstance": isinstance,
    "enumerate": enumerate,
    "zip": zip,
    "map": map,
    "filter": filter,
    "list": list,
    "dict": dict,
    "tuple": tuple,
    "set": set,
    "bool": bool,
    "bytes": bytes,
    "hex": hex,
    "oct": oct,
    "bin": bin,
    "abs": abs,
    "min": min,
    "max": max,
    "sum": sum,
    "sorted": sorted,
    "reversed": reversed,
    "repr": repr,
    "hash": hash,
    "id": id,
    "callable": callable,
    "iter": iter,
    "next": next,
    "object": object,
}

# _secret is in globals but not documented - players must find it
GLOBALS = {"__builtins__": SAFE_BUILTINS, "_secret": _secret}

print("=" * 50)
print("  Welcome to PyJail v1.0")
print("  Escape to get the flag!")
print("=" * 50)
print()

while True:
    try:
        code = input(">>> ")
    except EOFError:
        break

    blocked = False
    for word in BANNED:
        if word.lower() in code.lower():
            print(f"  [BLOCKED] Nice try!")
            blocked = True
            break

    if blocked:
        continue

    try:
        exec(compile(code, "<jail>", "exec"), GLOBALS)
    except Exception as e:
        print(f"  [ERROR] {e}")