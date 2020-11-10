#FROM php:7.3-fpm
FROM php:7.4-fpm
COPY redis /usr/src/php/ext/redis
COPY sources.list /etc/apt/sources.list
RUN  rm -Rf /var/lib/apt/lists/* \
        && apt-get update && apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev \
	libmagickwand-6.q16-dev --no-install-recommends \
    && docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-configure gd --with-jpeg=/usr/include --with-freetype=/usr/include/freetype2/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install -j$(nproc) gd \
#    && pecl install imagick \
#    && docker-php-ext-install  imagick \
  #  && docker-php-ext-install imagick-3.1.2 \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install -j$(nproc) redis \
    && docker-php-ext-install -j$(nproc) mysqli \
        && docker-php-ext-install -j$(nproc) opcache \
