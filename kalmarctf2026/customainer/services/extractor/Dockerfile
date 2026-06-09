FROM python:3.11-slim
ARG TEST_FLAG=kalmar{{rehost_test_a783ee033bea}}

RUN useradd --system --no-create-home --shell /bin/false --uid 1001 extractor

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .
RUN printf '%s' "$TEST_FLAG" > /flag && chmod 444 /flag && chown root:root /flag

RUN chmod -R 555 /app

EXPOSE 5000

USER extractor
CMD ["python", "app.py"]
