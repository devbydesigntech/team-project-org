#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}Installing fresh Laravel 12 application...${NC}\n"

# Create Laravel project
echo -e "${BLUE}Creating Laravel project...${NC}"
docker-compose run --rm app composer create-project laravel/laravel:^12.0 temp

# Move files from temp to current directory
echo -e "${BLUE}Moving files...${NC}"
docker-compose run --rm app bash -c "shopt -s dotglob && mv temp/* ./ && rmdir temp"

# Copy .env.example to .env with Docker settings
echo -e "${BLUE}Configuring .env for Docker...${NC}"
cp .env.example .env

echo -e "\n${GREEN}Laravel 12 installed successfully!${NC}"
echo -e "${BLUE}Run './setup.sh' to complete the setup.${NC}\n"
