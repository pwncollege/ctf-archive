FROM ubuntu:24.04

RUN apt-get update && apt-get install -y socat

RUN useradd rev

ADD ./deploy/prob /home/rev/prob
ADD ./deploy/target /home/rev/target

RUN chmod +x /home/rev/prob
RUN chown rev:rev /home/rev

WORKDIR /home/rev
USER rev
EXPOSE 5555

CMD ["socat", "TCP-LISTEN:5555,reuseaddr,fork", "EXEC:./prob,nofork,stderr"]
