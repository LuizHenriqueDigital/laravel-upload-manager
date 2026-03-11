#!/bin/bash

# Cores para o terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # Sem cor

echo -e "${BLUE}=== Iniciando Testes Multi-Versão (Docker) ===${NC}\n"

# Testando Versão Legada (PHP 7.3)
echo -e "${BLUE}[1/2] Testando Ambiente PHP 7.3...${NC}"
docker-compose run --rm test-legacy composer update
docker-compose run --rm test-legacy vendor/bin/pest
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✔ PHP 7.3: Sucesso!${NC}\n"
else
    echo "✘ PHP 7.3: Falhou!"
    exit 1
fi

# Testando Versão Moderna (PHP 8.2)
echo -e "${BLUE}[2/2] Testando Ambiente PHP 8.2...${NC}"
docker-compose run --rm test-modern composer update
docker-compose run --rm test-modern vendor/bin/pest
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✔ PHP 8.2: Sucesso!${NC}\n"
else
    echo "✘ PHP 8.2: Falhou!"
    exit 1
fi

echo -e "${GREEN}=== Todos os testes passaram em todas as versões! ===${NC}"