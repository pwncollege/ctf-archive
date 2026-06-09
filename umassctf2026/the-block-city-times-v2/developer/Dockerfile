FROM node:20-slim

ARG TEST_FLAG=UMASS{{rehost_test_2e259a16}}

RUN apt-get update && apt-get install -y --no-install-recommends \
    chromium \
    fonts-freefont-ttf \
    ca-certificates \
  && rm -rf /var/lib/apt/lists/*

RUN printf '%s' "$TEST_FLAG" > /flag && chmod 444 /flag

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

WORKDIR /app

COPY package.json .
RUN npm install

COPY report-api.js .
COPY trigger-server.js .

EXPOSE 9001
CMD ["node", "trigger-server.js"]
