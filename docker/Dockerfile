# Stick to 7.2 until Xdebug is stable with 7.3
FROM php:7.2

ARG TIMEZONE

WORKDIR /usr/local/etc

# SERVER SETUP
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    curl \
    git \
    unzip \
    # https://github.com/tianon/docker-brew-debian/issues/49
    gnupg
# https://stackoverflow.com/a/42344810/2516943
RUN ln -fs "/usr/share/zoneinfo/$TIMEZONE" /etc/localtime \
    && dpkg-reconfigure --frontend noninteractive tzdata

# OPCACHE EXTENSION
RUN docker-php-ext-install -j$(nproc) opcache

# APCU EXTENSION
RUN pecl channel-update pecl.php.net && pecl install apcu
COPY php/apcu.ini php/conf.d/

# INTL EXTENSION WITH UPDATED ICU LIB
# from https://github.com/docker-library/php/issues/455#issuecomment-309921509
RUN curl -fsS -o /tmp/icu.tgz -L http://download.icu-project.org/files/icu4c/60.1/icu4c-60_1-src.tgz \
    && tar -zxf /tmp/icu.tgz -C /tmp \
    && cd /tmp/icu/source \
    && ./configure --prefix=/usr/local \
    && make \
    && make install \
    && rm -rf /tmp/icu*
ENV PHP_CPPFLAGS="$PHP_CPPFLAGS -std=c++11"
RUN docker-php-ext-configure intl --with-icu-dir=/usr/local \
    && docker-php-ext-install intl

# XDEBUG
RUN pecl channel-update pecl.php.net && pecl install xdebug
COPY php/xdebug.ini php/conf.d/

# CONFIGURATION
# =============
COPY php/php.ini php/
RUN sed -i 's@{%TIMEZONE%}@'$TIMEZONE'@' php/php.ini

# INSTALL COMPOSER
# (using sync because of https://github.com/docker/docker/issues/9547)
# ================
COPY php/install-composer.sh .
RUN chmod +x install-composer.sh \
    && sync \
    && ./install-composer.sh \
    && rm install-composer.sh

WORKDIR "/app"
