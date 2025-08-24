#!/bin/bash

# žŒ¤¡ûß Docker èr,

echo "=== žŒ¤¡ûß Docker èr ==="

# Àå/&X(¯ƒØÏ‡ö
if [ ! -f .env ]; then
    echo "6¯ƒØÏMn‡ö..."
    cp .env.example .env
    echo "÷‘ .env ‡ö¾n¨„pn“ÆŒvÖMn"
fi

# \bv d°	¹h
echo "\b°	¹h..."
docker-compose down

# ç„\Ïï		
read -p "/&ç„Docker\Ï? (y/N): " cleanup
if [[ $cleanup =~ ^[Yy]$ ]]; then
    echo "ç\Ï..."
    docker-compose down --rmi all
    docker system prune -f
fi

# „úv/¨¡
echo "„úv/¨¡..."
docker-compose up --build -d

# I…¡/¨
echo "I…¡/¨..."
sleep 30

# >:¡¶
echo "=== ¡¶ ==="
docker-compose ps

echo ""
echo "=== èrŒ ==="
echo "Mï¿î0@: http://localhost"
echo "ïAPI0@: http://localhost:8080"
echo ""
echo "åå×: docker-compose logs -f [service_name]"
echo "\b¡: docker-compose down"
echo "Í/¡: docker-compose restart"