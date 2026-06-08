FROM wordpress:6-php8.1-apache

COPY plugins/ /usr/src/wordpress/wp-content/plugins/
COPY vendor/ /usr/src/wordpress/vendor/

RUN mkdir -p /usr/src/wordpress/wp-content/uploads && \
    chown www-data:www-data /usr/src/wordpress/wp-content/uploads

RUN chown -R www-data:www-data /usr/src/wordpress/wp-content/plugins

COPY wp-config.php /usr/src/wordpress/wp-config.php

# Flag lives at /flag.txt (read by the post-exploit webshell). Override at build
# time with `--build-arg FLAG=...` or by replacing /flag.txt in the running
# container. A default test flag is baked in.
ARG FLAG="BtSCTF{rehost_test_default}"
RUN echo "$FLAG" > /flag.txt && \
    chmod 444 /flag.txt
