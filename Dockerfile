FROM alpine:3.19

ARG BUILD_DATE
ARG VERSION
ARG VCS_REF

LABEL \
    org.opencontainers.image.title="CoreCreds" \
    org.opencontainers.image.description="CoreCreds ist ein extrem leichtgewichtiger, datenschutzfreundlicher und hochsicherer Generator für Passwörter, Passphrasen und Benutzernamen." \
    org.opencontainers.image.url="https://github.com/RonDevHub/CoreCreds" \
    org.opencontainers.image.source="https://commitcloud.net/RonDevHub/CoreCreds" \
    org.opencontainers.image.documentation="https://github.com/RonDevHub/CoreCreds" \
    org.opencontainers.image.version=$VERSION \
    org.opencontainers.image.revision=$VCS_REF \
    org.opencontainers.image.licenses="MIT" \
    org.opencontainers.image.created="$BUILD_DATE" \
    org.opencontainers.image.authors="RonDevHub <ron.dev@posteo.de>"

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