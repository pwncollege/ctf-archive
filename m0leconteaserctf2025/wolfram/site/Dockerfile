FROM debian:latest

USER root
WORKDIR /root/flask
# USERS
RUN addgroup --system appgroup --gid 1000 && adduser --ingroup appgroup --system appuser --uid 1000

# install dependencies
RUN apt-get update && apt-get install --no-install-recommends --upgrade -y python3 python3-pip && apt-get clean && rm -rf /var/lib/apt/lists/*
# init flask dependencies
COPY "requirements.txt" "requirements.txt"
RUN python3 -m pip install --no-cache-dir --upgrade --break-system-packages -r "requirements.txt" && rm "requirements.txt"
# clean some dependencies after build
RUN apt-get purge -y python3-pip && apt-get clean && rm -rf /var/lib/apt/lists/*

# init flask
RUN chmod o=rX "/root" "/root/flask"
COPY --chmod="o=rX" "flask/" "./"
# init https
# COPY --chmod="444" "flask/key.pem" "/run/secrets/site_key"
# COPY --chmod="444" "flask/cert.pem" "/run/secrets/site_cert"

USER appuser:appgroup
WORKDIR /root/flask
EXPOSE 8080
ENTRYPOINT [ "/usr/local/bin/gunicorn", "server:app", \
    "--bind", "0.0.0.0:8080", \
    "--workers", "8", \
    "--threads", "8", \
    "--preload", \
    "--keyfile", "/run/secrets/site_key", \
    "--certfile", "/run/secrets/site_cert" \
]
