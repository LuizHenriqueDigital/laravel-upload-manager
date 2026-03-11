#!/bin/bash

# Cores para o terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # Sem cor

echo -e "${BLUE}=== Iniciando Testes ===${NC}\n"

# Testando Versão Moderna (PHP 8.2)
echo -e "${BLUE}[1/1] Testando Ambiente PHP 8.2...${NC}"
docker-compose run --rm test-modern composer update
docker-compose run --rm test-modern vendor/bin/pest
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✔ PHP 8.2: Sucesso!${NC}\n"
else
    echo "✘ PHP 8.2: Falhou!"
    exit 1
fi

echo -e "${GREEN}=== Todos os testes passaram em todas as versões! ===${NC}"