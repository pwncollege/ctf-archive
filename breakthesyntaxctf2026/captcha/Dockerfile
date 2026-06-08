FROM python:3.12-slim

# pwn.college convention: real flag is placed at /flag at runtime.
# Default it to a self-contained test value so the image works standalone.
RUN echo "BtSCTF2026{rehost_test_5f3a91c27e}" > /flag && chmod 644 /flag

COPY ./app /app
COPY ./requirements.txt /tmp/requirements.txt

WORKDIR /app
RUN pip install -r /tmp/requirements.txt
RUN pip install gunicorn

EXPOSE 5000

CMD ["gunicorn", "--workers", "3", "--bind", "0.0.0.0:5000", "main:app"]