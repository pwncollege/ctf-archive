FROM python:3.14-slim

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1
ENV HOME=/home/ctf

WORKDIR /app

RUN apt-get update \
&& apt-get install -y gcc libc6-dev curl \
&& rm -rf /var/lib/apt/lists/*

COPY main.py /app/main.py
COPY templates /app/templates
COPY readflag.c /tmp/readflag.c

RUN mkdir -p /app/uploads \
    && mkdir -p /app/.venv \
    && flag="bctf{rehost_test_$(tr -dc 'a-f0-9' </dev/urandom | head -c 12)}" \
    && printf '%s\n' "$flag" > /flag \
    && chown root:root /flag \
    && chmod 0400 /flag \
    && gcc /tmp/readflag.c -O2 -o /readflag \
    && chown root:root /readflag \
    && chmod 4755 /readflag \
    && rm -f /tmp/readflag.c \
    && useradd -m -d /home/ctf -s /usr/sbin/nologin ctf \
    && mkdir -p /home/ctf/.prefect \
    && chown ctf /app/uploads \
    && chown ctf /app/.venv \
    && chown -R ctf:ctf /home/ctf

USER ctf

RUN python3 -m venv --system-site-packages /app/.venv \
    && /app/.venv/bin/pip install --no-cache-dir \
        starlette \
        uvicorn \
        jinja2 \
        python-multipart \
        prefect

EXPOSE 8000

CMD ["/app/.venv/bin/python", "main.py"]
