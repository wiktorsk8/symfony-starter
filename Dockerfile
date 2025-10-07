FROM dunglas/frankenphp:latest

WORKDIR /app
# PHP
RUN install-php-extensions \
	pdo_mysql \
	gd \
	intl \
	zip \
	opcache

RUN apt-get update && apt-get install -y unzip libzip-dev \
    && docker-php-ext-install zip \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


COPY . .

RUN chown -R www-data:www-data /app

USER www-data

EXPOSE 8000

