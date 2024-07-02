FROM ubuntu:22.04
RUN apt-get update && apt-get -y install socat
RUN mkdir -p /chroot/home/user
COPY ./syscalls /chroot/home/user
COPY ./flag.txt /chroot/home/user
CMD socat TCP-LISTEN:1337,reuseaddr,fork EXEC:"/chroot/home/user/syscalls"
