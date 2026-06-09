#!/usr/bin/env python3

from email.quoprimime import unquote
import os
import re
from http.server import HTTPServer, BaseHTTPRequestHandler
import http.client
from socketserver import ThreadingMixIn

class ThreadedHTTPServer(ThreadingMixIn, HTTPServer):
    daemon_threads = True

BACKEND_HOST = os.environ.get("BACKEND_HOST", "backend")
BACKEND_PORT = int(os.environ.get("BACKEND_PORT", 80))
LISTEN     = int(os.environ.get("LISTEN_PORT", 5555))

FORWARD_HEADERS = ["User-Agent", "Accept", "Accept-Language", "Cookie", "Content-Type", "Content-Length"]

cache = {}

HOP_BY_HOP = frozenset(("transfer-encoding", "connection",
                         "keep-alive", "server", "date"))

CACHE_EXTENSIONS = {".css", ".js", ".png", ".jpg", ".jpeg", ".ico", ".svg", ".txt"}

class Handler(BaseHTTPRequestHandler):

    def _send(self, status, headers, body, tag=None):
        self.send_response(status)
        for n, v in headers:
            if n.lower() not in HOP_BY_HOP:
                self.send_header(n, v)
        if tag:
            self.send_header("X-Cache", tag)
        self.end_headers()
        if body:
            self.wfile.write(body)

    def _cacheable_path(self):
        path_only = self.path.split("?")[0]
        result = any(path_only.lower().endswith(ext) for ext in CACHE_EXTENSIONS)
        return result

    def _upstream(self, method, body=None):

            key = self.path.lower()

            if method == "GET" and self._cacheable_path() and key in cache:
                e = cache[key]
                self._send(e["status"], e["headers"], e["body"], "HIT")
                return
            
            clean_path = re.split(r'%0d%0a', self.path, flags=re.IGNORECASE)[0]

            conn = http.client.HTTPConnection(BACKEND_HOST, BACKEND_PORT, timeout=3)
            
            try:
                fwd = {"Host": BACKEND_HOST}
                for h in FORWARD_HEADERS:
                    v = self.headers.get(h)
                    if v:
                        fwd[h] = v

                conn.request(method, clean_path, body=body, headers=fwd)
                resp = conn.getresponse()
                
                status = resp.status
                raw_headers = resp.getheaders()
                r_body = resp.read()

                hdrs = []
                header_dict = {}
                for name, value in raw_headers:
                    if name.lower() not in HOP_BY_HOP:
                        hdrs.append((name, value))

                    lname = name.lower()
                    if lname not in header_dict:
                        header_dict[lname] = value.lower()
                cc_header = header_dict.get("cache-control", "")
                should_skip_cache = "no-store" in cc_header or "no-cache" in cc_header

                if method == "GET" and self._cacheable_path() and not should_skip_cache:
                    cache[key] = {"status": status, "headers": hdrs, "body": r_body}
                    tag = "MISS"
                else:
                    tag = None

                self._send(status, hdrs, r_body, tag)

            except Exception as e:
                self.send_error(502, f"Upstream Protocol Error: {e}")

            finally:
                conn.close()

    def do_GET(self):
        self._upstream("GET")

    def do_POST(self):
        cl = int(self.headers.get("Content-Length", 0))
        body = self.rfile.read(cl) if cl else None
        self._upstream("POST", body)

if __name__ == "__main__":
    ThreadedHTTPServer(("0.0.0.0", LISTEN), Handler).serve_forever()