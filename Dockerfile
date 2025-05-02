FROM php:8.3.21RC1-fpm-alpine3.20@sha256:c4ab395ebf2afc8d01006b1726f24f2f1427aac0469407c229db7dbac8a23b0e

RUN apk update && apk add --no-cache nginx supervisor bash curl

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
