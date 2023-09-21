FROM php:8.1-fpm

ARG NODE_MAJOR=20

ARG UID
ARG USER

ENV UID=${UID}
ENV USER=${USER}

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

RUN useradd -G www-data,root -u $UID -d /home/${USER} ${USER}
RUN mkdir -p /home/${USER}/.composer && \
    chown -R ${USER}:${USER} /home/${USER}

RUN apt update && apt install -y --no-install-recommends \
    apt-utils \
    # ca-certificates \
    # gnupg \
    supervisor 

# RUN mkdir -p /etc/apt/keyrings
# RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

# RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_MAJOR}.x nodistro main" |  tee /etc/apt/sources.list.d/nodesource.list

# RUN apt update

RUN apt install -y \
    # nodejs \
    # nginx \
    # git \
    curl \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zlib1g-dev \
    zip

RUN docker-php-ext-install \
    bcmath \
    exif \
    fileinfo \
    gd \
    iconv \
    mbstring \
    pcntl \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    session \
    simplexml \
    xml \
    zip

RUN pecl install redis && docker-php-ext-enable redis 

RUN apt clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# COPY ./docker/supervisord/supervisord.conf /etc/supervisor
# COPY ./docker/supervisord/conf /etc/supervisord.d/

# COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
# COPY ./docker/nginx/sites /etc/nginx/sites-available

# COPY ./docker/nginx/run.sh /tmp    
# RUN chmod +x /tmp/run.sh

# RUN chmod 777 -R /var/run
# RUN chmod 777 -R /var/log/supervisor

WORKDIR /var/www

COPY ./app/.env.example .env
COPY ./app .

RUN chown -R ${USER}:${USER} .

USER ${USER}

RUN composer install
RUN php artisan key:generate

# RUN npm install

# ENTRYPOINT ["/tmp/run.sh"]

# CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]