FROM composer:2.8.8 AS composer
FROM php:7.4.33-apache-bullseye

RUN apt-get update -y && \
    apt-get install -y git zlib1g-dev libpng-dev openssl libssl-dev libpcap0.8

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

RUN docker-php-ext-install gd

# Pin PHP memory_limit so the mPDF php://filter memory-exhaustion oracle is
# deterministic (the image base default of 128M moves the oracle's blow-up
# margin out of calibration; 80M reproduces the original challenge behaviour).
RUN echo "memory_limit = 80M" > /usr/local/etc/php/conf.d/zz-challenge.ini

RUN composer require mpdf/mpdf:8.2.6
RUN composer require fakerphp/faker:1.12.0
RUN composer require symfony/process:4.1

COPY app/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

RUN printf '%s\n' \
    '<Directory "/var/www/html/uploads">' \
    '    php_admin_flag engine off' \
    '    <FilesMatch "\\.ph(p[0-9]?|tml)$">' \
    '        Require all denied' \
    '    </FilesMatch>' \
    '</Directory>' \
    > /etc/apache2/conf-available/uploads-no-php.conf && \
    a2enconf uploads-no-php

# The flag is read from /flag. /flag.txt is the path the application LFI targets;
# keep it pointing at /flag so the flag has a single source of truth. The value
# below is a self-contained build-time default for rehosting/testing; replace
# /flag on deploy.
RUN echo "BtSCTF{rehost_test_44cf0167}" > /flag && \
    chmod 444 /flag && \
    ln -sf /flag /flag.txt

EXPOSE 80
CMD ["apache2-foreground"]
