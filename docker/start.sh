#!/bin/bash

echo "Iniciando containers Docker..."
docker-compose up -d

echo "Aguardando MySQL iniciar..."
sleep 30

echo "Instalando dependências do Composer..."
docker-compose exec app composer install --no-dev --optimize-autoloader

echo "Gerando chave da aplicação..."
docker-compose exec app php artisan key:generate

echo "Executando migrations..."
docker-compose exec app php artisan migrate --force

echo "Configurando permissões..."
docker-compose exec app chmod -R 775 storage
docker-compose exec app chmod -R 775 bootstrap/cache

echo "Aplicação disponível em: http://localhost:8000"
echo "PHPMyAdmin disponível em: http://localhost:8080"