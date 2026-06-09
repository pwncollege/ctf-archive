FROM python:3.12-slim
ARG TEST_FLAG="UMASS{{rehost_test_b716ae51}}"
WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt
COPY . .
RUN printf '%s\n' "$TEST_FLAG" > /flag
CMD ["python", "backend.py"]
