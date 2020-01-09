FROM php:7.4-alpine

RUN set -xe; \
    apk add --no-cache gmp-dev $PHPIZE_DEPS; \
    docker-php-ext-install gmp; \
    pecl install pcov; \
    docker-php-ext-enable pcov; \
    apk del $PHPIZE_DEPS

COPY build.sh /build.sh
RUN chmod 0644 /build.sh
ENTRYPOINT /build.sh
