FROM python:3.14.4-slim

WORKDIR /app

COPY chal.py /app/chal.py
COPY server.py /app/server.py

RUN python3 - <<'PY' && \
    chmod 755 /app/chal.py /app/server.py && \
    chmod 444 /flag
from pathlib import Path
from secrets import token_hex

Path("/flag").write_text(f"bctf{{rehost_test_{token_hex(8)}}}")
PY

EXPOSE 5000

CMD ["python3", "/app/server.py"]
