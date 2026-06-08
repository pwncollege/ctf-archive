FROM node:20-bullseye

RUN apt-get update && apt-get install -y \
    cron \
    sudo \
    perl \
    sqlite3 \
    openssh-server \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /var/run/sshd && chmod 0755 /var/run/sshd
RUN ssh-keygen -A
RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config
RUN echo "    StrictHostKeyChecking no" >> /etc/ssh/ssh_config
RUN echo "    UserKnownHostsFile=/dev/null" >> /etc/ssh/ssh_config

RUN useradd -m -s /bin/bash romaric && \
    useradd -m -s /bin/bash abdul && \
    echo "abdul:shellfish" | chpasswd

# pwn.college convention: real flag is placed at /flag at runtime, root-only.
# Default it to a self-contained test value so the image works standalone.
RUN echo "BtSCTF{rehost_test_5f3a91c27e}" > /flag && chown root:root /flag && chmod 400 /flag
# The privesc chain reads /root/flag.txt as root; point it at /flag.
RUN ln -sf /flag /root/flag.txt

RUN sqlite3 /home/romaric/creds.db "CREATE TABLE members (id INTEGER, user TEXT, status TEXT, note TEXT, backup_key TEXT);" && \
    sqlite3 /home/romaric/creds.db "INSERT INTO members VALUES (3, 'Abdul', 'Photographer', 'Photos in /home/abdul', 'f2efc9d909cdf9b75e97fff27a0ea4c3dd201f6c7a16ad9731a633daba91f205');" && \
    chown romaric:romaric /home/romaric/creds.db

WORKDIR /home/romaric/web
COPY . .

RUN npm install --force && npm run build
RUN chown -R romaric:romaric /home/romaric/web

RUN mkdir -p /opt/scripts
RUN printf '#!/bin/bash\ncp -r /home/abdul/photos /tmp/backup_photos 2>/dev/null\n' > /opt/scripts/backup.sh
RUN chown abdul:abdul /opt/scripts/backup.sh && chmod 775 /opt/scripts/backup.sh
RUN echo "* * * * * root /opt/scripts/backup.sh" >> /etc/crontab

EXPOSE 8000 22

RUN printf '#!/bin/bash\nservice ssh start\ncron\nsu romaric -c "npm run start -- -p 8000"\n' > /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
