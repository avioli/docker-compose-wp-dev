FROM wordpress:4.7-php7.0-fpm

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        zlib1g-dev \
        && docker-php-ext-install -j$(nproc) \
            iconv \
            mcrypt \
            mysql \
            mysqli \
            pdo \
        && docker-php-ext-configure \
            gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
        && docker-php-ext-install -j$(nproc) \
            gd