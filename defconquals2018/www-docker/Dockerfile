from ubuntu:xenial

run apt-get update && apt-get -y upgrade

run DEBIAN_FRONTEND=noninteractive apt-get -y install scrot python-dev python-pip libxdo-dev xvfb xserver-xephyr libsdl2-2.0-0 libsdl2-dev libpng16-dev xorg openbox libpcap-dev libosmesa6 mesa-utils rsync tcpdump dnsutils

run mkdir -p /opt/www/
copy previous requirements.txt document_inspector_ready_good.png login_screen_good.png login_desktop_good.png www_ready_good.png previous.cfg previous-hacker.cfg Rev_1.2.BIN Rev_2.5_v66.BIN Rev_3.3_v74.BIN simple_dns_server.py /opt/www
add --chmod=0644 https://github.com/o-o-overflow/chall-www/raw/master/deployment/NS33_2GB.dd.tar.gz /opt/www/

run pip install dnslib
run pip install -r /opt/www/requirements.txt

run useradd -ms /bin/bash hacker
run useradd -ms /bin/bash www

run mkdir /home/www/.previous && chown www:www /home/www/.previous
run mkdir /home/www/hd && chown www:www /home/www/hd && chmod 700 /home/www/hd

run cp /opt/www/previous.cfg /home/www/.previous

copy frontend.py /opt/www
