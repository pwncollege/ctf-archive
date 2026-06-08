FROM node:18-alpine

# Install python and create virtual environment
RUN apk add --no-cache python3 py3-pip && \
    python3 -m venv /opt/venv

# Activate virtual environment and install apprise
RUN . /opt/venv/bin/activate && \
    pip install --no-cache-dir apprise

# Add virtual environment to PATH
ENV PATH="/opt/venv/bin:$PATH"

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

RUN mkdir -p uploads

EXPOSE 3000

CMD ["node", "server.js"]
