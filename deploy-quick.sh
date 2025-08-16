#!/bin/bash

# 实验室管理系统一键部署脚本
# 使用方法: curl -fsSL https://raw.githubusercontent.com/havocera/lab-management-system/main/deploy-quick.sh | bash

set -e

echo "🚀 开始部署实验室管理系统..."

# 检查Docker是否安装
if ! command -v docker &> /dev/null; then
    echo "❌ Docker未安装，请先安装Docker"
    echo "Ubuntu/Debian: sudo apt-get update && sudo apt-get install docker.io"
    echo "CentOS/RHEL: sudo yum install docker"
    exit 1
fi

# 检查Docker是否运行
if ! docker info &> /dev/null; then
    echo "❌ Docker服务未启动，尝试启动..."
    sudo systemctl start docker
    sudo systemctl enable docker
fi

# 停止现有容器
echo "🛑 停止现有容器..."
docker stop lab-management-system 2>/dev/null || true
docker rm lab-management-system 2>/dev/null || true

# 拉取最新镜像
echo "📦 拉取最新镜像..."
docker pull ghcr.io/havocera/lab-management-system:latest

# 启动新容器
echo "🎯 启动应用容器..."
docker run -d \
  --name lab-management-system \
  -p 80:80 \
  --restart unless-stopped \
  -v lab_mysql_data:/var/lib/mysql \
  ghcr.io/havocera/lab-management-system:latest

# 等待启动
echo "⏳ 等待应用启动..."
sleep 15

# 检查状态
if docker ps | grep -q lab-management-system; then
    echo "✅ 部署成功!"
    echo "🌐 访问地址: http://$(curl -s ifconfig.me || echo 'YOUR_SERVER_IP')"
    echo "📊 容器状态: $(docker ps --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}' | grep lab-management-system)"
else
    echo "❌ 部署失败，请检查日志:"
    docker logs lab-management-system
    exit 1
fi

# 清理旧镜像
echo "🧹 清理旧镜像..."
docker image prune -f > /dev/null

echo ""
echo "🎉 部署完成!"
echo "📝 管理命令:"
echo "  查看日志: docker logs lab-management-system"
echo "  重启应用: docker restart lab-management-system"
echo "  停止应用: docker stop lab-management-system"
echo "  更新应用: curl -fsSL https://raw.githubusercontent.com/havocera/lab-management-system/main/deploy-quick.sh | bash"