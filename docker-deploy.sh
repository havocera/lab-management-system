#!/bin/bash

# ������ Docker �r,

echo "=== ������ Docker �r ==="

# ��/&X(���χ�
if [ ! -f .env ]; then
    echo "6����Mn��..."
    cp .env.example .env
    echo "�� .env ���n��pn���v�Mn"
fi

# \bv d�	�h
echo "\b�	�h..."
docker-compose down

# �\��		
read -p "/&�Docker\�? (y/N): " cleanup
if [[ $cleanup =~ ^[Yy]$ ]]; then
    echo "�\�..."
    docker-compose down --rmi all
    docker system prune -f
fi

# ��v/��
echo "��v/��..."
docker-compose up --build -d

# I��/�
echo "I��/�..."
sleep 30

# >:��
echo "=== �� ==="
docker-compose ps

echo ""
echo "=== �r� ==="
echo "M��0@: http://localhost"
echo "�API0@: http://localhost:8080"
echo ""
echo "���: docker-compose logs -f [service_name]"
echo "\b�: docker-compose down"
echo "�/�: docker-compose restart"