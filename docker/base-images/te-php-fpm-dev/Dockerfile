ARG PHP_VERSION=7.3.4

FROM php:${PHP_VERSION}-fpm-alpine3.9

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		file \
		gettext \
		git \
		imagemagick \
	;



# Install additional modules
#ARG XDEBUG_VERSION=2.6.0

RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
#        postgresql-dev \
        freetype-dev \
        imagemagick-dev \
#        libjpeg-turbo-dev \
#        libpng-dev \
        zlib-dev \
    ; \
    \
#	docker-php-ext-configure gd \
#            --with-gd \
#            --with-freetype-dir=/usr/include/ \
#            --with-png-dir=/usr/include/ \
#            --with-jpeg-dir=/usr/include/ ; \
    docker-php-ext-configure zip --with-libzip; \
	docker-php-ext-install -j$(nproc) \
		intl \
		bcmath \
#		pdo_mysql \
#		pdo_pgsql \
#		mysqli \
		zip \
#		gd \
    ; \
    pecl install \
##        xdebug-${XDEBUG_VERSION} \
        imagick \
#        redis \
    ; \
    pecl clear-cache; \
    docker-php-ext-enable \
        opcache \
#        bcmath \
#        pdo_mysql \
#        pdo_pgsql \
#        mysqli \
#        xdebug \
	    imagick \
#	    redis \
    ; \
    \
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --no-cache --virtual .php-phpexts-rundeps $runDeps; \
    \
    apk del .build-deps

    # hack
#    RUN apk --update add imagemagick

# Install Composer
#-------------------------------------
COPY --from=composer:1.8.0 /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1
# add composer parallel install plugin
RUN set -eux; \
	composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
	composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"
#-------------------------------------

#######################################
#######################################

#Copy Entry point
COPY entrypoint-app.sh /usr/local/bin/entrypoint-app
RUN chmod +x /usr/local/bin/entrypoint-app
#	&& dos2unix /usr/local/bin/entrypoint-app

# Create app directory
WORKDIR /app

ENTRYPOINT ["entrypoint-app"]
CMD ["php-fpm"]