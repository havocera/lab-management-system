# 🚀 自动部署配置指南

## 方式一：GitHub Actions 自动部署

### 1. 设置服务器 Secrets

在 GitHub 仓库中添加以下 Secrets（Settings → Secrets and variables → Actions）：

| Secret 名称 | 说明 | 示例 |
|------------|------|------|
| `SERVER_HOST` | 服务器IP地址 | `192.168.1.100` |
| `SERVER_USER` | SSH用户名 | `root` 或 `ubuntu` |
| `SERVER_KEY` | SSH私钥内容 | 完整的私钥文件内容 |
| `SERVER_PORT` | SSH端口（可选） | `22`（默认值） |

### 2. 生成SSH密钥对

在本地生成SSH密钥：
```bash
ssh-keygen -t rsa -b 4096 -C "github-actions"
```

将公钥添加到服务器：
```bash
ssh-copy-id -i ~/.ssh/id_rsa.pub user@your-server-ip
```

将私钥内容添加到GitHub Secrets：
```bash
cat ~/.ssh/id_rsa
```

### 3. 自动部署触发

- 推送到 `main` 分支时自动触发
- 构建Docker镜像并推送到GHCR
- 自动部署到配置的服务器

## 方式二：一键部署脚本

### 在服务器上执行：

```bash
curl -fsSL https://raw.githubusercontent.com/havocera/lab-management-system/main/deploy-quick.sh | bash
```

### 或者手动部署：

```bash
# 拉取镜像
docker pull ghcr.io/havocera/lab-management-system:latest

# 停止旧容器
docker stop lab-management-system || true
docker rm lab-management-system || true

# 启动新容器
docker run -d \
  --name lab-management-system \
  -p 80:80 \
  --restart unless-stopped \
  -v lab_mysql_data:/var/lib/mysql \
  ghcr.io/havocera/lab-management-system:latest
```

## 方式三：本地开发部署

```bash
# 克隆仓库
git clone https://github.com/havocera/lab-management-system.git
cd lab-management-system

# 使用Docker Compose
docker-compose -f docker-compose.all-in-one.yml up --build -d
```

## 访问应用

部署完成后，通过以下地址访问：

- **前端界面**: `http://your-server-ip`
- **后端API**: `http://your-server-ip/api`

## 管理命令

```bash
# 查看容器状态
docker ps

# 查看应用日志
docker logs lab-management-system

# 重启应用
docker restart lab-management-system

# 更新应用
curl -fsSL https://raw.githubusercontent.com/havocera/lab-management-system/main/deploy-quick.sh | bash
```

## 故障排除

### 容器无法启动
```bash
# 查看详细日志
docker logs lab-management-system

# 检查端口占用
netstat -tlnp | grep :80
```

### 数据库连接问题
```bash
# 进入容器检查
docker exec -it lab-management-system bash

# 检查MySQL状态
docker exec -it lab-management-system supervisorctl status
```

### 权限问题
```bash
# 检查文件权限
docker exec -it lab-management-system ls -la /var/www/
```