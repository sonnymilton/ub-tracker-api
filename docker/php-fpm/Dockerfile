FROM php:7.3-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends vim curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget zip unzip \
    gnupg gnupg1 gnupg2 \
    libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring  \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

COPY docker/php-fpm/conf.d/xdebug.ini $PHP_INI_DIR/conf.d

ARG XDEBUG_REMOTE_PORT

RUN echo "xdebug.remote_port = $XDEBUG_REMOTE_PORT" >> $PHP_INI_DIR/conf.d/xdebug.ini && cat $PHP_INI_DIR/conf.d/xdebug.ini
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

ARG APP_PATH
ARG APP_ENV

COPY . $APP_PATH
RUN composer install -d $APP_PATH && composer dump-env $APP_ENV -d $APP_PATH

RUN rm -rf /var/lib/apt/lists/* \
    && echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen  \
    && locale-gen

EXPOSE 9000

CMD ["php-fpm"]
