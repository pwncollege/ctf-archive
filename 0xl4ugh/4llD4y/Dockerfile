FROM node:24-alpine

WORKDIR /app

RUN apk add --no-cache supervisor

COPY app.js package.json ./
RUN npm install --production

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY init.sh /init.sh
RUN chmod +x /init.sh

EXPOSE 3000


ENTRYPOINT ["/init.sh"]