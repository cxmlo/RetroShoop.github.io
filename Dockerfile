FROM dunglas/frankenphp

RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /app

COPY . /app
