#!/bin/bash

# 生产环境部署脚本
# Production Deployment Script

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 项目信息
PROJECT_NAME="Laboratory Management System - Production"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="${PROJECT_DIR}/deploy-prod.log"
COMPOSE_FILE="docker-compose.prod.yml"

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

# 函数：检查生产环境要求
check_production_requirements() {
    print_message $BLUE "检查生产环境要求..."
    
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
    
    # 检查磁盘空间 (至少5GB)
    available_space=$(df . | awk 'NR==2 {print $4}')
    required_space=5242880  # 5GB in KB
    if [ "$available_space" -lt "$required_space" ]; then
        print_message $RED "错误: 磁盘空间不足，至少需要 5GB 可用空间"
        exit 1
    fi
    
    print_message $GREEN "生产环境要求检查完成"
}

# 函数：设置生产环境变量
setup_production_environment() {
    print_message $BLUE "设置生产环境变量..."
    
    if [ ! -f "${PROJECT_DIR}/.env.production" ]; then
        print_message $RED "错误: .env.production 文件不存在"
        print_message $YELLOW "请先创建生产环境配置文件"
        exit 1
    fi
    
    # 复制生产环境配置
    cp "${PROJECT_DIR}/.env.production" "${PROJECT_DIR}/.env"
    
    # 提示用户检查配置
    print_message $YELLOW "请确保已正确配置以下项目:"
    print_message $YELLOW "  - 数据库密码"
    print_message $YELLOW "  - JWT密钥"
    print_message $YELLOW "  - Redis密码"
    print_message $YELLOW "  - 域名配置"
    
    read -p "是否继续部署? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_message $YELLOW "部署已取消"
        exit 0
    fi
    
    print_message $GREEN "生产环境变量设置完成"
}

# 函数：备份现有数据
backup_data() {
    print_message $BLUE "备份现有数据..."
    
    backup_dir="${PROJECT_DIR}/backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$backup_dir"
    
    # 备份数据库
    if docker ps | grep -q labmanage_db_prod; then
        print_message $YELLOW "备份数据库..."
        docker exec labmanage_db_prod mysqldump -u root -p\${DB_ROOT_PASSWORD} \${DB_DATABASE} > "${backup_dir}/database.sql"
    fi
    
    # 备份上传文件
    if [ -d "${PROJECT_DIR}/uploads" ]; then
        print_message $YELLOW "备份上传文件..."
        cp -r "${PROJECT_DIR}/uploads" "${backup_dir}/"
    fi
    
    print_message $GREEN "数据备份完成: $backup_dir"
}

# 函数：拉取最新镜像
pull_latest_images() {
    print_message $BLUE "拉取最新Docker镜像..."
    
    cd "$PROJECT_DIR"
    
    # 登录到GitHub Container Registry
    if [ ! -z "$GITHUB_TOKEN" ]; then
        echo $GITHUB_TOKEN | docker login ghcr.io -u $GITHUB_ACTOR --password-stdin
    fi
    
    # 拉取最新镜像
    $COMPOSE_CMD -f $COMPOSE_FILE pull
    
    print_message $GREEN "镜像拉取完成"
}

# 函数：启动生产服务
start_production_services() {
    print_message $BLUE "启动生产服务..."
    
    cd "$PROJECT_DIR"
    
    # 停止现有服务
    $COMPOSE_CMD -f $COMPOSE_FILE down 2>/dev/null || true
    
    # 启动所有服务
    $COMPOSE_CMD -f $COMPOSE_FILE up -d
    
    print_message $GREEN "生产服务启动完成"
}

# 函数：健康检查
health_check() {
    print_message $BLUE "执行健康检查..."
    
    # 等待服务启动
    sleep 30
    
    # 检查服务状态
    if ! $COMPOSE_CMD -f $COMPOSE_FILE ps | grep -q "Up"; then
        print_message $RED "部分服务未正常启动"
        $COMPOSE_CMD -f $COMPOSE_FILE logs --tail=50
        exit 1
    fi
    
    # 检查前端访问
    if ! curl -f http://localhost/ >/dev/null 2>&1; then
        print_message $YELLOW "前端服务可能未完全就绪，请稍后检查"
    else
        print_message $GREEN "前端服务运行正常"
    fi
    
    # 检查API访问
    if ! curl -f http://localhost:8080/ >/dev/null 2>&1; then
        print_message $YELLOW "API服务可能未完全就绪，请稍后检查"
    else
        print_message $GREEN "API服务运行正常"
    fi
    
    print_message $GREEN "健康检查完成"
}

# 函数：设置监控和日志
setup_monitoring() {
    print_message $BLUE "设置监控和日志..."
    
    # 创建日志轮转配置
    cat > /tmp/labmanage-logrotate << EOF
${PROJECT_DIR}/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    sharedscripts
    postrotate
        docker compose -f ${PROJECT_DIR}/${COMPOSE_FILE} exec frontend nginx -s reload
    endscript
}
EOF
    
    # 设置定时任务
    if command -v crontab &> /dev/null; then
        crontab -l | grep -v "labmanage-backup" | crontab -
        (crontab -l; echo "0 2 * * * ${PROJECT_DIR}/scripts/backup.sh") | crontab -
    fi
    
    print_message $GREEN "监控和日志设置完成"
}

# 函数：显示生产部署结果
show_production_result() {
    print_message $GREEN "============================================"
    print_message $GREEN "  $PROJECT_NAME 生产部署完成!"
    print_message $GREEN "============================================"
    echo ""
    print_message $BLUE "生产环境访问地址:"
    print_message $YELLOW "  前端应用: http://your-domain.com"
    print_message $YELLOW "  后端API:  http://your-domain.com:8080"
    echo ""
    print_message $BLUE "管理命令:"
    print_message $YELLOW "  查看服务状态: $COMPOSE_CMD -f $COMPOSE_FILE ps"
    print_message $YELLOW "  查看日志:     $COMPOSE_CMD -f $COMPOSE_FILE logs -f"
    print_message $YELLOW "  重启服务:     $COMPOSE_CMD -f $COMPOSE_FILE restart"
    print_message $YELLOW "  停止服务:     $COMPOSE_CMD -f $COMPOSE_FILE down"
    echo ""
    print_message $BLUE "重要提醒:"
    print_message $YELLOW "  1. 请配置防火墙和SSL证书"
    print_message $YELLOW "  2. 定期备份数据库和文件"
    print_message $YELLOW "  3. 监控服务运行状态"
    print_message $YELLOW "  4. 及时更新系统和依赖"
    echo ""
    print_message $GREEN "部署日志已保存到: $LOG_FILE"
}

# 主函数
main() {
    print_message $GREEN "============================================"
    print_message $GREEN "  $PROJECT_NAME 生产环境部署"
    print_message $GREEN "============================================"
    
    # 检查生产环境要求
    check_production_requirements
    
    # 设置生产环境变量
    setup_production_environment
    
    # 备份现有数据
    backup_data
    
    # 拉取最新镜像
    pull_latest_images
    
    # 启动生产服务
    start_production_services
    
    # 健康检查
    health_check
    
    # 设置监控和日志
    setup_monitoring
    
    # 显示部署结果
    show_production_result
}

# 脚本参数处理
case "${1:-}" in
    "backup")
        backup_data
        exit 0
        ;;
    "health")
        health_check
        exit 0
        ;;
    "logs")
        cd "$PROJECT_DIR"
        $COMPOSE_CMD -f $COMPOSE_FILE logs -f
        exit 0
        ;;
    "status")
        cd "$PROJECT_DIR"
        $COMPOSE_CMD -f $COMPOSE_FILE ps
        exit 0
        ;;
    "stop")
        cd "$PROJECT_DIR"
        $COMPOSE_CMD -f $COMPOSE_FILE down
        print_message $GREEN "生产服务已停止"
        exit 0
        ;;
    "")
        main
        ;;
    *)
        echo "用法: $0 [backup|health|logs|status|stop]"
        exit 1
        ;;
esac