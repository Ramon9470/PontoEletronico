FROM php:8.4-fpm

# Argumentos que virão do docker-compose.yml
ARG user
ARG uid

# Instala dependências do Sistema Operacional
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    default-mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Extensões do PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instala Redis Vital para performance em produção
RUN pecl install redis && docker-php-ext-enable redis

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criação do Usuário do Sistema 
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Define o diretório de trabalho
WORKDIR /var/www

USER $user