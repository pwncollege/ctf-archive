FROM node:22-slim

RUN apt-get update && apt-get install -y chromium --no-install-recommends && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY app/package.json app/package-lock.json ./
RUN npm install --omit=dev
COPY app/ ./

# The flag is read from /flag by the admin bot (FLAG_FILE) at runtime. The value
# below is a self-contained build-time default for rehosting/testing; replace
# /flag on deploy.
RUN echo "BtSCTF{rehost_test_6822e2c3}" > /flag && chmod 444 /flag

EXPOSE 8000
CMD ["sh", "start.sh"]
