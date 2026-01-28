FROM php:8.1-fpm

ARG PROJECT_PATH
ARG USER_DOCKER_ID
ARG GROUP_DOCKER_ID

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    nano

RUN apt-get update && apt-get install -y libonig-dev \
    && docker-php-ext-install mbstring

ADD .docker/php/php.ini /usr/local/etc/php/php.ini

RUN mkdir -p ~/.ssh && \
    ssh-keyscan github.com >> ~/.ssh/known_hosts

# Composer
ENV COMPOSER_HOME=/var/www/.composer
ENV COMPOSER_CACHE_DIR=/var/www/.composer/cache
RUN mkdir -p $COMPOSER_CACHE_DIR && chmod -R 777 $COMPOSER_CACHE_DIR
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chown -R ${USER_DOCKER_ID}:${GROUP_DOCKER_ID} ${PROJECT_PATH}
RUN chmod -R 755 ${PROJECT_PATH}

WORKDIR ${PROJECT_PATH}

USER "${USER_DOCKER_ID}:${GROUP_DOCKER_ID}"