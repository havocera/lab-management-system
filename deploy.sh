#!/bin/bash

# 实验室管理系统一键部署脚本
# Laboratory Management System One-Click Deployment Script

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 项目信息
PROJECT_NAME="Laboratory Management System"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="${PROJECT_DIR}/deploy.log"

# 函数：打印彩色消息
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}[$(date '+%Y-%m-%d %H:%M:%S')] ${message}${NC}" | tee -a "$LOG_FILE"
}

# 函数：检查命令是否存在
check_command() {
    if ! command -v $1 &> /dev/null; then
        print_message $RED "错误: $1 命令未找到，请先安装 $1"
        exit 1
    fi
}

# 函数：检查系统要求
check_requirements() {
    print_message $BLUE "检查系统要求..."
    
    # 检查 Docker
    check_command "docker"
    
    # 检查 Docker Compose
    if ! docker compose version &> /dev/null; then
        if ! docker-compose --version &> /dev/null; then
            print_message $RED "错误: Docker Compose 未安装"
            exit 1
        else
            COMPOSE_CMD="docker-compose"
        fi
    else
        COMPOSE_CMD="docker compose"
    fi
    
    # 检查 Git（可选）
    if command -v git &> /dev/null; then
        print_message $GREEN "Git 已安装"
    else
        print_message $YELLOW "警告: Git 未安装，无法显示版本信息"
    fi
    
    print_message $GREEN "系统要求检查完成"
}

# 函数：设置环境变量
setup_environment() {
    print_message $BLUE "设置环境变量..."
    
    if [ ! -f "${PROJECT_DIR}/.env" ]; then
        if [ -f "${PROJECT_DIR}/.env.example" ]; then
            cp "${PROJECT_DIR}/.env.example" "${PROJECT_DIR}/.env"
            print_message $GREEN "已从 .env.example 创建 .env 文件"
        else
            print_message $RED "错误: .env.example 文件不存在"
            exit 1
        fi
    fi
    
    # 生成随机密码和密钥
    if [ "$(uname)" = "Darwin" ]; then
        # macOS
        DB_ROOT_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-16)
        DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-16)
        JWT_SECRET=$(openssl rand -base64 64 | tr -d "=+/")
    else
        # Linux
        DB_ROOT_PASSWORD=$(head -c 32 /dev/urandom | base64 | tr -d "=+/" | cut -c1-16)
        DB_PASSWORD=$(head -c 32 /dev/urandom | base64 | tr -d "=+/" | cut -c1-16)
        JWT_SECRET=$(head -c 64 /dev/urandom | base64 | tr -d "=+/")
    fi
    
    # 更新 .env 文件
    sed -i.bak "s/DB_ROOT_PASSWORD=.*/DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}/" "${PROJECT_DIR}/.env"
    sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" "${PROJECT_DIR}/.env"
    sed -i.bak "s/JWT_SECRET=.*/JWT_SECRET=${JWT_SECRET}/" "${PROJECT_DIR}/.env"
    
    print_message $GREEN "环境变量设置完成"
}

# 函数：构建镜像
build_images() {
    print_message $BLUE "构建 Docker 镜像..."
    
    cd "$PROJECT_DIR"
    
    # 构建前端镜像
    print_message $YELLOW "构建前端镜像..."
    docker build -t labmanage-frontend:latest .
    
    # 构建后端镜像
    print_message $YELLOW "构建后端镜像..."
    docker build -t labmanage-backend:latest ./endtp/
    
    print_message $GREEN "Docker 镜像构建完成"
}

# 函数：启动服务
start_services() {
    print_message $BLUE "启动服务..."
    
    cd "$PROJECT_DIR"
    
    # 停止现有服务
    $COMPOSE_CMD down 2>/dev/null || true
    
    # 启动所有服务
    $COMPOSE_CMD up -d
    
    print_message $GREEN "服务启动完成"
}

# 函数：等待服务就绪
wait_for_services() {
    print_message $BLUE "等待服务就绪..."
    
    # 等待数据库启动
    print_message $YELLOW "等待数据库启动..."
    local max_attempts=60
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if $COMPOSE_CMD exec database mysqladmin ping -h localhost --silent 2>/dev/null; then
            print_message $GREEN "数据库已就绪"
            break
        fi
        attempt=$((attempt + 1))
        print_message $YELLOW "等待数据库启动... ($attempt/$max_attempts)"
        sleep 5
    done
    
    if [ $attempt -eq $max_attempts ]; then
        print_message $RED "数据库启动超时"
        exit 1
    fi
    
    # 等待后端服务
    print_message $YELLOW "等待后端服务..."
    attempt=0
    while [ $attempt -lt 30 ]; do
        if curl -f http://localhost:8080/ >/dev/null 2>&1; then
            print_message $GREEN "后端服务已就绪"
            break
        fi
        attempt=$((attempt + 1))
        print_message $YELLOW "等待后端服务... ($attempt/30)"
        sleep 10
    done
    
    # 等待前端服务
    print_message $YELLOW "等待前端服务..."
    attempt=0
    while [ $attempt -lt 20 ]; do
        if curl -f http://localhost:80/ >/dev/null 2>&1; then
            print_message $GREEN "前端服务已就绪"
            break
        fi
        attempt=$((attempt + 1))
        print_message $YELLOW "等待前端服务... ($attempt/20)"
        sleep 5
    done
}

# 函数：显示部署结果
show_result() {
    print_message $GREEN "============================================"
    print_message $GREEN "  $PROJECT_NAME 部署完成!"
    print_message $GREEN "============================================"
    echo ""
    print_message $BLUE "服务访问地址:"
    print_message $YELLOW "  前端应用: http://localhost"
    print_message $YELLOW "  后端API:  http://localhost:8080"
    print_message $YELLOW "  数据库:   localhost:3306"
    echo ""
    print_message $BLUE "默认管理员账户:"
    print_message $YELLOW "  用户名: admin"
    print_message $YELLOW "  密码:   password (请登录后立即修改)"
    echo ""
    print_message $BLUE "常用命令:"
    print_message $YELLOW "  查看服务状态: $COMPOSE_CMD ps"
    print_message $YELLOW "  查看日志:     $COMPOSE_CMD logs -f [服务名]"
    print_message $YELLOW "  停止服务:     $COMPOSE_CMD down"
    print_message $YELLOW "  重启服务:     $COMPOSE_CMD restart"
    echo ""
    print_message $GREEN "部署日志已保存到: $LOG_FILE"
}

# 函数：清理函数
cleanup() {
    if [ $? -ne 0 ]; then
        print_message $RED "部署过程中出现错误，正在清理..."
        cd "$PROJECT_DIR"
        $COMPOSE_CMD down 2>/dev/null || true
        print_message $YELLOW "如需重新部署，请运行: $0"
    fi
}

# 主函数
main() {
    # 设置清理trap
    trap cleanup EXIT
    
    print_message $GREEN "============================================"
    print_message $GREEN "  $PROJECT_NAME 一键部署脚本"
    print_message $GREEN "============================================"
    
    # 检查系统要求
    check_requirements
    
    # 设置环境变量
    setup_environment
    
    # 构建镜像
    build_images
    
    # 启动服务
    start_services
    
    # 等待服务就绪
    wait_for_services
    
    # 显示部署结果
    show_result
    
    # 取消清理trap
    trap - EXIT
}

# 脚本参数处理
case "${1:-}" in
    "help"|"-h"|"--help")
        echo "用法: $0 [选项]"
        echo ""
        echo "选项:"
        echo "  help, -h, --help    显示此帮助信息"
        echo "  clean               清理所有容器和镜像"
        echo "  logs                查看服务日志"
        echo "  status              查看服务状态"
        echo "  restart             重启所有服务"
        echo "  stop                停止所有服务"
        echo ""
        exit 0
        ;;
    "clean")
        print_message $YELLOW "清理所有容器和镜像..."
        cd "$PROJECT_DIR"
        $COMPOSE_CMD down --rmi all --volumes --remove-orphans
        print_message $GREEN "清理完成"
        exit 0
        ;;
    "logs")
        cd "$PROJECT_DIR"
        $COMPOSE_CMD logs -f
        exit 0
        ;;
    "status")
        cd "$PROJECT_DIR"
        $COMPOSE_CMD ps
        exit 0
        ;;
    "restart")
        cd "$PROJECT_DIR"
        $COMPOSE_CMD restart
        print_message $GREEN "服务重启完成"
        exit 0
        ;;
    "stop")
        cd "$PROJECT_DIR"
        $COMPOSE_CMD down
        print_message $GREEN "服务已停止"
        exit 0
        ;;
    "")
        # 无参数，执行主函数
        main
        ;;
    *)
        print_message $RED "未知选项: $1"
        print_message $YELLOW "运行 '$0 help' 查看帮助信息"
        exit 1
        ;;
esac