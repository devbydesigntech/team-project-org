#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}Starting Dreamers Laravel Assessment Setup...${NC}\n"

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${BLUE}Creating .env file from .env.example...${NC}"
    cp .env.example .env
else
    echo -e "${GREEN}.env file already exists.${NC}"
fi

# Build and start Docker containers
echo -e "\n${BLUE}Building Docker containers...${NC}"
docker-compose build

echo -e "\n${BLUE}Starting Docker containers...${NC}"
docker-compose up -d

# Wait for MySQL to be ready
echo -e "\n${BLUE}Waiting for MySQL to be ready...${NC}"
sleep 10

# Install Composer dependencies
echo -e "\n${BLUE}Installing Composer dependencies...${NC}"
docker-compose exec app composer install

# Generate application key
echo -e "\n${BLUE}Generating application key...${NC}"
docker-compose exec app php artisan key:generate

# Run database migrations
echo -e "\n${BLUE}Running database migrations...${NC}"
docker-compose exec app php artisan migrate

# Seed database
echo -e "\n${BLUE}Seeding database...${NC}"
docker-compose exec app php artisan db:seed

# Set proper permissions
echo -e "\n${BLUE}Setting proper permissions...${NC}"
docker-compose exec app chmod -R 777 storage bootstrap/cache

echo -e "\n${GREEN}Setup complete!${NC}"
echo -e "${GREEN}Application is running at: http://localhost:8000${NC}"
echo -e "${BLUE}To view logs: docker-compose logs -f${NC}"
echo -e "${BLUE}To stop: docker-compose down${NC}\n"
