FROM alpine:3.19

RUN apk add --no-cache \
    nginx \
    php82 \
    php82-fpm \
    php82-json \
    php82-openssl \
    php82-mbstring \
    && ln -sf /usr/bin/php82 /usr/bin/php

RUN mkdir -p /run/nginx /run/php

COPY nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/html
COPY . .

RUN chown -R nginx:nginx /var/www/html

EXPOSE 80

CMD ["sh", "-c", "php-fpm82 && nginx -g 'daemon off;'"]