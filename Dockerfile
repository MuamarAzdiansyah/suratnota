# Gunakan image PHP dengan Apache
#FROM php:7.4-apache

# Install ekstensi mysqli
#RUN docker-php-ext-install mysqli

# Salin kode aplikasi dari src ke dalam container
#COPY ./src /var/www/html/

# Set izin akses yang sesuai
#RUN chown -R www-data:www-data /var/www/html

# Ekspose port 80
#EXPOSE 80

# Gunakan image PHP dengan Apache
FROM php:7.4-apache

# Install paket yang diperlukan dan ekstensi mysqli
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli

# Salin kode aplikasi dari src ke dalam container
COPY ./src /var/www/html/

# Set izin akses yang sesuai
RUN chown -R www-data:www-data /var/www/html

# Ekspose port 80
EXPOSE 80

# Aktifkan mod_rewrite jika diperlukan
RUN a2enmod rewrite
