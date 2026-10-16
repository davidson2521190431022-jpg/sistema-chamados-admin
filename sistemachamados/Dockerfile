FROM php:8.2-cli

# Instala dependências do sistema e extensão pdo_mysql
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql

# Copia o executável do Composer oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copia os arquivos da aplicação
COPY . .

# Instala as dependências e gera o autoloader do PSR-4
RUN composer install --no-interaction --optimize-autoloader

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
