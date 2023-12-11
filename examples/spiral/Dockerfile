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

RUN apk update && apk add --no-cache \
  linux-headers \
  libzip-dev \
  unzip \
  && apk add $PHPIZE_DEPS \
  && docker-php-ext-install zip sockets opcache


#RUN pecl install xdebug \
#  && docker-php-ext-enable xdebug

WORKDIR /data
CMD ["/usr/bin/rr", "serve"]
