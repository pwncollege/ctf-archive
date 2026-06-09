FROM node:20-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
    chromium \
    fonts-freefont-ttf \
    ca-certificates \
  && rm -rf /var/lib/apt/lists/*

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

WORKDIR /app

ARG TEST_FLAG='UMASS{{rehost_test_9d4a2c1b}}'

COPY package.json .
RUN npm install

RUN printf '%s' "$TEST_FLAG" > /flag

COPY report-api.js .
COPY trigger-server.js .

EXPOSE 9001
CMD ["node", "trigger-server.js"]
