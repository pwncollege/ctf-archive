FROM cybersecnatlab/socaz:debian-bookworm

# Update and install packages
RUN apt-get update
RUN apt-get install -y --no-install-recommends qemu-system-x86 python3

# Copy challenge entrypoint
RUN mkdir /src
COPY bootloader.bin /src
COPY flash.img /src
COPY entrypoint.py /src
COPY entrypoint.sh /src

RUN mkdir /instances

RUN chmod +x /src/entrypoint.sh
RUN chmod +x /src/entrypoint.py

WORKDIR /src

# Socat listening port
EXPOSE 1337

# Change entrypoint to launch challenge
CMD ["./entrypoint.sh"]