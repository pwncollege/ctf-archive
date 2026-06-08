FROM python:3.12-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app/ /app/

ENV CHAINED_HOST=0.0.0.0 \
    CHAINED_PORT=5000

EXPOSE 5000
CMD ["python", "app.py"]
