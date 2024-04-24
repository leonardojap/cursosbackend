# Dockerfile
FROM php:8.2.12-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    build-essential \
    default-mysql-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    libzip-dev \
    libonig-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Node.js
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs

# Instalar extensiones
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/
RUN docker-php-ext-install gd

# Instalar composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar el directorio existente a /var/www
COPY ./ /var/www


# Instalar dependencias de PHP y Node
RUN composer install
RUN npm install

# Copiar el archivo de ambiente de ejemplo y generar la llave
RUN cp .env.example .env
RUN php artisan key:generate

# Copiar los permisos de los directorios
RUN chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache

# Cambiar el usuario actual a www
USER www-data

# Exponer el puerto 9000 y empezar
EXPOSE 9000
CMD ["php-fpm"]
