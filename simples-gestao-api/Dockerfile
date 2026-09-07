FROM serversideup/php:8.4-fpm-nginx

USER root

# Instala extensões necessárias para o PostgreSQL e Laravel
RUN install-php-extensions pdo_pgsql bcmath intl opcache

WORKDIR /var/www/html

# Copia os arquivos do projeto com ownership do www-data
COPY --chown=www-data:www-data . /var/www/html

# Copia o script de deploy para o entrypoint.d do S6-overlay
COPY --chmod=755 scripts/00-laravel-deploy.sh /etc/entrypoint.d/99-laravel-deploy.sh

# Garante permissões adequadas em storage e cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Instala as dependências de produção do Composer usando o PHP 8.4 nativo
USER www-data
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
