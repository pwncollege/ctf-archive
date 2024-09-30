FROM selenium/standalone-firefox:latest
ENV SE_START_NO_VNC=false
ENV SE_START_VNC=false
# ENV SE_START_XVFB=false
# who tf sets these ffs
ENV PWD=""
ENV HOME=""

USER root
WORKDIR /root/admin
# USERS
RUN addgroup --system "appgroup" --gid "1001" && "adduser" --ingroup "appgroup" --system "appuser" --uid "1001"

# install dependencies
RUN apt-get update && apt-get install --no-install-recommends --upgrade -y python3 python3-pip socat sudo && apt-get clean && rm -rf /var/lib/apt/lists/*
# RUN apt-get update && apt-get install --no-install-recommends --upgrade -y python3 python3-pip socat wget && apt-get clean && rm -rf /var/lib/apt/lists/*
RUN python3 -m pip install --no-cache-dir --upgrade "selenium" --break-system-packages
# clean some dependencies after build
RUN apt-get purge -y python3-pip wget && apt-get clean && rm -rf /var/lib/apt/lists/*

# init admin
# RUN wget "https://github.com/mozilla/geckodriver/releases/download/v0.33.0/geckodriver-v0.33.0-linux64.tar.gz" -O "-" | tar -xzvf "-"
RUN chmod o=rX -- "/root" "/root/admin"
COPY --chmod="o=rx" "hashcash.py" "review.py" "entrypoint.sh" "review.sh" "/root/admin/"

ENV SE_OPTS="--host 127.0.0.1"
ENV SE_BIND_HOST="true"
ENV SE_NODE_MAX_SESSIONS=16
ENV SE_NODE_OVERRIDE_MAX_SESSIONS=true

# USER appuser:appgroup
WORKDIR /root/admin
EXPOSE 8000
ENTRYPOINT [ "/root/admin/entrypoint.sh" ]
