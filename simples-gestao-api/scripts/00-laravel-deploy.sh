#!/usr/bin/env bash
echo "==> [Render Deploy] Iniciando deploy do Laravel..."

cd /var/www/html

echo "==> [Render Deploy] Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> [Render Deploy] Executando migrations no PostgreSQL..."
php artisan migrate --force

echo "==> [Render Deploy] Verificando dados iniciais de demonstração..."
php artisan db:seed --force

echo "==> [Render Deploy] Otimizando aplicação para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [Render Deploy] Deploy do Laravel finalizado com sucesso!"
