FROM python:3.12.7-slim

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

RUN mv git .git
RUN mv env .env
RUN mkdir -p static

# Default test flag. Replace /flag at deploy time (e.g. compose volume) with the real flag.
RUN echo 'BtSCTF{rehost_test_pokecollector}' > /flag

ENV APP_PORT=8000

CMD ["sh", "-c", "uvicorn main:app --host 0.0.0.0 --port ${APP_PORT}"]
