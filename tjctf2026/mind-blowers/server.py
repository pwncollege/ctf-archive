#!/usr/local/bin/python
import pickle
import socket
import threading
import io
import base64


BLOCKED_NAMES = {
    "eval", "exec", "compile", "__import__", "open",
    "breakpoint", "input", "exit", "quit",
}

class RestrictedUnpickler(pickle.Unpickler):
    def find_class(self, module, name):
        if module != "builtins":
            raise pickle.UnpicklingError("banned")
        if name in BLOCKED_NAMES:
            raise pickle.UnpicklingError("blocked")
        return super().find_class(module, name)


def safe_loads(data):
    return RestrictedUnpickler(io.BytesIO(data)).load()


def handle_connection(conn):
    try:
        conn.sendall(b"=== Rick's Mind Blower Server v3 ===\n")
        conn.sendall(b"Only safe memories allowed now!!!!\n")
        conn.sendall(b"Upload a memory (base64 encoded) > ")

        data = conn.recv(8192).strip()
        if not data:
            return

        try:
            raw = base64.b64decode(data)
        except Exception:
            conn.sendall(b"thats not base64\n")
            return

        result = safe_loads(raw)
        conn.sendall(f"Here is your memory: {result}\n".encode())

    except pickle.UnpicklingError as e:
        conn.sendall(f"blocked: {e}\n".encode())
    except Exception as e:
        conn.sendall(f"error: {e}\n".encode())
    finally:
        conn.close()


def main():
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    server.bind(("0.0.0.0", 5000))
    server.listen(5)
    print("Mind Blower Server v3 on port 5000")

    while True:
        conn, addr = server.accept()
        thread = threading.Thread(target=handle_connection, args=(conn,))
        thread.daemon = True
        thread.start()


if __name__ == "__main__":
    main()
