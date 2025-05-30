# Install dependencies and create final image.
FROM php:8.3-fpm-alpine AS gewisweb_web_development_base
WORKDIR /code

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        curl-dev \
        freetype-dev \
        icu-dev \
        imagemagick-dev \
        libjpeg-turbo-dev \
        libmemcached-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        linux-headers \
        openldap-dev \
        sqlite-dev \
        tzdata \
    && apk add --no-cache --virtual .runtime-deps \
        git \
        ghostscript \
        icu-data-full \
        imagemagick \
        nano \
        nodejs \
        npm \
        openssh \
        poppler-utils \
        rsync \
        sshpass \
        unzip \
        zip \
    && docker-php-ext-configure \
        gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        calendar \
        curl \
        exif \
        gd \
        intl \
        opcache \
        pgsql \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip \
# START OF IMAGICK PATCH
    # && pecl install imagick \
    # Install Imagick from source as building it for PHP 8.3 keeps intermittently failing. This has been extensively
    # documented at https://github.com/Imagick/imagick/issues/640. We do not need to patch anything, we will just use
    # the current HEAD of the master branch (this is still 28f27044e435a2b203e32675e942eb8de620ee58).
    && mkdir -p /usr/src/php/ext/imagick \
    && curl -fsSL https://github.com/Imagick/imagick/archive/28f27044e435a2b203e32675e942eb8de620ee58.tar.gz | tar xvz -C "/usr/src/php/ext/imagick" --strip 1 \
    && docker-php-ext-install imagick \
    # && docker-php-ext-enable imagick \
# END OF IMAGICK PATCH
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -r /tmp/pear \
    && runtimeDeps="$( \
            scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
        )" \
    && apk add --virtual .gewisweb-runtime-deps $runtimeDeps \
    && cp /usr/share/zoneinfo/Europe/Amsterdam /etc/localtime \
    && echo 'Europe/Amsterdam' > /etc/timezone \
    && apk del .build-deps

RUN sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/g' /etc/ImageMagick-7/policy.xml

RUN addgroup -g 1000 web-user && adduser -u 1000 -G web-user -D web-user
RUN chown -R web-user:web-user /code
USER web-user

# Install composer and dependencies.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./composer.json ./composer.lock ./

# We need to be able to use the Composer plugins for several patches, however, these are disabled when running non-
# interactively as root. As such, we re-enable this functionality to ensure this works. The alternative is becoming
# www-data while installing the dependencies, however, this would allow for modification of the PHP files at runtime.
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install

# Create the final image.
FROM gewisweb_web_development_base AS gewisweb_web_development

COPY --chown=web-user:web-user ./docker/web/development/php.ini /usr/local/etc/php/conf.d/default.ini
COPY --chown=web-user:web-user ./docker/web/development/php-fpm.conf /usr/local/etc/php-fpm.d/zz-gewisweb.conf
COPY --chown=web-user:web-user ./config/autoload/local.development.php.dist ./config/autoload/local.php
COPY --chown=web-user:web-user ./config/autoload/doctrine.local.development.php.dist ./config/autoload/doctrine.local.php
COPY --chown=web-user:web-user ./config/autoload/gewisdb.local.php.dist ./config/autoload/gewisdb.local.php
COPY --chown=web-user:web-user ./config/autoload/laminas-developer-tools.local.php.dist ./config/autoload/laminas-developer-tools.local.php

USER root
COPY --chown=web-user:web-user ./docker/web/development/crontab /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab && crontab /etc/cron.d/crontab

USER web-user
COPY --chown=web-user:web-user ./docker/web/development/docker-entrypoint.sh ./docker-entrypoint.sh
RUN chmod 0775 ./docker-entrypoint.sh

COPY --chown=web-user:web-user . /code

USER web-user
RUN cp -R ./vendor/fortawesome/font-awesome/sprites/. ./public/sprites/fontawesome
RUN cp -R ./vendor/fortawesome/font-awesome/webfonts/. ./public/fonts/fontawesome

ENV PHP_IDE_CONFIG="serverName=gewis.nl"

RUN composer dump-autoload

ARG GIT_COMMIT
ENV GIT_COMMIT=${GIT_COMMIT}

ENTRYPOINT ["/bin/sh", "/code/docker-entrypoint.sh"]
