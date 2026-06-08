FROM python:3.10

USER root
RUN apt-get update -y && \
    apt-get install -y socat curl

RUN useradd -m -s /bin/bash user
WORKDIR /app
RUN dd if=/dev/urandom bs=4 count=1 2>/dev/null | od -An -tu4 | tr -d ' ' > ./serverseed
COPY chall.py run.sh flag /app/

RUN chmod +x /app/chall.py /app/run.sh

USER user

EXPOSE 5000

CMD ["socat", \
     "TCP-LISTEN:5000,reuseaddr,fork", \
     "EXEC:/app/run.sh,stderr"]
