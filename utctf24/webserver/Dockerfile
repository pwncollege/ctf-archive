FROM ubuntu:24.04

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential socat libseccomp-dev \
    && rm -rf /var/lib/apt/lists/*

ARG FLAG
ARG USER=ctfuser
ENV USER=$USER
ENV FLAG=$FLAG

RUN useradd -m $USER

WORKDIR /challenge
COPY start.sh /start.sh
RUN chmod 755 /start.sh && \
    echo "$FLAG" > /home/$USER/flag.txt && \
    chown root:root /home/$USER/flag.txt && \
    chmod 644 /home/$USER/flag.txt

EXPOSE 9000

WORKDIR /challenge
COPY src/. ./src/.
COPY Makefile .
RUN make
COPY server-data/ .

# ensure ctfuser owns everything in /challenge
RUN chown -R $USER:$USER /challenge

WORKDIR /challenge
CMD ["/start.sh"]

