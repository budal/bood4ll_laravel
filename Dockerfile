FROM php:8.1-fpm

ARG UID
ARG USER
ARG APPDIR
ARG REDIS_LIB_VERSION=5.3.7

ENV UID=${UID}
ENV USER=${USER}
ENV APPDIR=${APPDIR}

RUN apt update && apt install -y --no-install-recommends apt-utils supervisor

COPY ./docker/supervisord/supervisord.conf /etc/supervisor
COPY ./docker/supervisord/conf /etc/supervisord.d/

RUN apt install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libpq-dev

RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql session xml mbstring exif bcmath zip iconv simplexml pcntl gd fileinfo

RUN pecl install redis && docker-php-ext-enable redis 
# RUN pecl install redis-${REDIS_LIB_VERSION} && docker-php-ext-enable redis 

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

RUN apt install -y nginx

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/sites /etc/nginx/sites-available

RUN useradd -G www-data,root -u $UID -d /home/${USER} ${USER}
RUN mkdir -p /home/${USER}/.composer && \
    chown -R ${USER}:${USER} /home/${USER}

RUN apt clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -fsSL https://deb.nodesource.com/setup_current.x | bash -
RUN apt install -y nodejs

WORKDIR ${APPDIR}

RUN chmod 775 -R .
RUN chown -R www-data:${USER} .

COPY --chown=www-data:${USER} ./app .

RUN chmod 777 -R ./bootstrap/cache

RUN composer install
RUN php artisan key:generate

RUN npm install

COPY ./run.sh /tmp    
RUN chmod +x /tmp/run.sh
ENTRYPOINT ["/tmp/run.sh"]

# USER ${USER}