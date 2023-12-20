ARG RR_VERSION
ARG RR_IMAGE=spiralscout/roadrunner:${RR_VERSION}
ARG PHP_IMAGE_VERSION
ARG PHP_IMAGE=php:${PHP_IMAGE_VERSION}

FROM ${RR_IMAGE} as rr

FROM ${PHP_IMAGE}

# Copy Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy RoadRunner
COPY --from=rr /usr/bin/rr /usr/bin/rr

RUN apk update && \
    apk add --no-cache linux-headers libzip && \
    apk add --no-cache --virtual build-deps libzip-dev $PHPIZE_DEPS && \
    docker-php-ext-install zip sockets opcache && \
#    pecl install xdebug && \
#    docker-php-ext-enable xdebug && \
    apk del build-deps && \
    rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apk/* /usr/src/php*

WORKDIR /data
CMD ["/usr/bin/rr", "serve"]
