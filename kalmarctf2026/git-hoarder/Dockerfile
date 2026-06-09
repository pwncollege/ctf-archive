FROM archlinux:latest

RUN pacman --noconfirm -Sy socat python3 git

ARG TEST_FLAG=kalmar{rehost_test_7f3c2a91}

RUN mkdir /app
COPY clone.py /app/clone.py
RUN printf '%s\n' "$TEST_FLAG" > /flag && chmod 0444 /flag
RUN useradd -m -k user:user user
USER user
WORKDIR /tmp

CMD socat TCP-LISTEN:1337,reuseaddr,fork EXEC:"python3 /app/clone.py",stderr
