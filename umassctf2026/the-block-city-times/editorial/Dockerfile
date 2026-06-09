# Use slim Node image and install system Chromium so we don't download a
# second copy via the puppeteer package.
FROM node:20-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
    chromium \
    fonts-freefont-ttf \
    ca-certificates \
  && rm -rf /var/lib/apt/lists/*

# Tell puppeteer to skip its bundled Chromium download and use the system one.
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

WORKDIR /app

COPY package.json .
RUN npm install

COPY server.js .

RUN mkdir -p uploads

EXPOSE 9000

CMD ["node", "server.js"]
