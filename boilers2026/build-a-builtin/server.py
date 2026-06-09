#!/usr/bin/env python3
import socketserver
import subprocess
import sys


class Handler(socketserver.StreamRequestHandler):
    def handle(self):
        self.wfile.write(b"code > ")
        self.wfile.flush()

        code = self.rfile.readline(4096)
        if not code:
            return

        proc = subprocess.run(
            [sys.executable, "/app/chal.py"],
            input=code,
            capture_output=True,
            check=False,
        )
        stdout = proc.stdout
        if stdout.startswith(b"code > "):
            stdout = stdout[len(b"code > ") :]

        self.wfile.write(stdout)
        if proc.stderr:
            self.wfile.write(proc.stderr)
        self.wfile.flush()


class Server(socketserver.ThreadingTCPServer):
    allow_reuse_address = True


if __name__ == "__main__":
    with Server(("0.0.0.0", 5000), Handler) as server:
        server.serve_forever()
