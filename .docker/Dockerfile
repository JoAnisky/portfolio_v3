# ============================
# 1. Base commune
# ============================
FROM php:8.4-apache AS base

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y --no-install-recommends \
    locales git unzip libicu-dev g++ libpng-dev libxml2-dev \
    libzip-dev libonig-dev libxslt-dev libpq-dev wget curl bash nano \
    && echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql intl zip dom mbstring xsl gd \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer install
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Installer Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Config PHP
COPY .docker/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# ============================
# 2. Dépendances PHP
# ============================
FROM base AS deps

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-progress || true

# ============================
# 3. Dev
# ============================
FROM base AS dev

RUN adduser --uid 1000 --disabled-password --gecos "" dev
USER dev

COPY --chown=1000:1000 . .

# ============================
# 4. Prod
# ============================
FROM deps AS prod

ARG APP_ENV=prod
ARG APP_SECRET=dummysecret
ENV APP_ENV=${APP_ENV}
ENV APP_SECRET=${APP_SECRET}

COPY --from=deps /var/www/html/vendor /var/www/html/vendor
COPY --chown=www-data:www-data . .

RUN mkdir -p var/cache var/log public/uploads/screenshots \
    && chown -R www-data:www-data var public/uploads

RUN composer install --no-dev --optimize-autoloader --no-progress \
    && php bin/console cache:clear --env=${APP_ENV} --no-warmup || true \
    && php bin/console assets:install public --env=${APP_ENV} --no-interaction \
    && php bin/console cache:warmup --env=${APP_ENV} || true

RUN chown -R www-data:www-data /var/www/html

USER www-data
