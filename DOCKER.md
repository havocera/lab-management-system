# 实验室管理系统 Docker 部署指南

## 系统架构

本项目采用简化的三服务架构：

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Nginx         │    │   PHP-FPM       │    │   MySQL         │
│                 │    │                 │    │                 │
│ ┌─────────────┐ │    │ ┌─────────────┐ │    │ ┌─────────────┐ │
│ │   前端      │ │    │ │   后端API   │ │    │ │   数据库    │ │
│ │   静态文件  │◀┼────┤ │   PHP应用   │◀┼────┤ │   MySQL     │ │
│ └─────────────┘ │    │ └─────────────┘ │    │ └─────────────┘ │
└─────────────────┘    └─────────────────┘    └─────────────────┘
      :80                    :9000                    :3306
```

## 快速部署

### 1. 准备环境

确保您的系统已安装：
- Docker
- Docker Compose

### 2. 配置环境变量

```bash
# 复制环境变量模板
cp .env.example .env

# 编辑环境变量（可选）
vim .env
```

### 3. 启动服务

**Windows 用户：**
```cmd
docker-deploy.bat
```

**Linux/Mac 用户：**
```bash
chmod +x deploy-docker.sh
./deploy-docker.sh
```

**手动启动：**
```bash
docker-compose up --build -d
```

### 4. 访问系统

启动成功后，访问：http://localhost:20080

## 服务说明

### Nginx 服务
- **端口**：20080
- **功能**：
  - 提供前端静态文件服务
  - 代理 `/api/*` 请求到 PHP-FPM
  - 支持前端路由

### PHP-FPM 服务
- **端口**：9000 (内部)
- **功能**：
  - 处理后端 API 请求
  - ThinkPHP 框架
  - JWT 认证

### MySQL 服务
- **端口**：23306
- **功能**：
  - 数据持久化存储
  - 自动初始化数据库
  - 使用根目录 `labmanage.sql` 文件

## 文件映射

### 前端文件映射
- `./src` → `/app/src` (开发时实时编辑)
- `./public` → `/app/public`
- 配置文件实时映射

### 后端文件映射
- `./endtp` → `/var/www/html` (开发时实时编辑)

### 数据库文件映射
- `./labmanage.sql` → 初始化脚本

## 常用命令

```bash
# 查看服务状态
docker-compose ps

# 查看服务日志
docker-compose logs -f nginx
docker-compose logs -f php-fpm  
docker-compose logs -f mysql

# 重启服务
docker-compose restart

# 停止服务
docker-compose down

# 重新构建并启动
docker-compose up --build -d

# 清理所有容器和镜像
docker-compose down --rmi all
docker system prune -f
```

## 开发模式

由于配置了文件映射，您可以：

1. **前端开发**：修改 `src/` 目录下的文件，重新构建前端容器即可
2. **后端开发**：修改 `endtp/` 目录下的文件，重启 PHP-FPM 容器即可
3. **数据库**：修改 `labmanage.sql` 文件，重新创建数据库容器即可

## 生产部署注意事项

1. 修改 `.env` 文件中的默认密码
2. 设置强密码用于数据库和JWT密钥
3. 确保防火墙配置正确
4. 定期备份数据库数据

## 故障排除

### 服务启动失败
```bash
# 查看详细日志
docker-compose logs

# 检查端口占用
netstat -tlnp | grep :20080
netstat -tlnp | grep :23306
```

### 数据库连接失败
```bash
# 检查数据库服务状态
docker-compose logs mysql

# 检查数据库连接
docker-compose exec mysql mysql -u root -p
```

### 权限问题
```bash
# 重置文件权限
sudo chown -R $USER:$USER .
chmod -R 755 .
```