# GitHub Pages 部署指南

## 概览

本项目支持多种部署方式：

1. **GitHub Pages**: 仅前端静态部署 (适合演示)
2. **GitHub Actions + 云服务器**: 完整系统部署 (推荐生产环境)
3. **本地Docker部署**: 开发和测试环境

## 1. GitHub Pages 部署 (仅前端)

### 步骤 1: 启用GitHub Pages

1. 在GitHub仓库页面，点击 `Settings`
2. 滚动到 `Pages` 部分
3. 在 `Source` 下选择 `GitHub Actions`

### 步骤 2: 配置环境变量

在GitHub仓库的 `Settings > Secrets and variables > Actions` 中添加：

```
API_URL: https://your-backend-api-domain.com/api
```

### 步骤 3: 推送代码

推送代码到 `main` 分支，GitHub Actions会自动构建并部署到Pages。

访问地址: `https://yourusername.github.io/lab-management-system`

> **注意**: GitHub Pages只能部署前端，需要单独部署后端API服务。

## 2. 云服务器完整部署

### 步骤 1: 准备云服务器

- **系统要求**: Ubuntu 20.04+ 或 CentOS 7+
- **配置要求**: 2GB+ 内存，20GB+ 硬盘，Docker支持
- **网络要求**: 公网IP，开放80、443、8080端口

### 步骤 2: 配置GitHub Secrets

在GitHub仓库的 `Settings > Secrets and variables > Actions` 中添加：

```bash
# 服务器连接信息
HOST: your-server-ip
USERNAME: root
SSH_PRIVATE_KEY: |
  -----BEGIN OPENSSH PRIVATE KEY-----
  your-private-key-content
  -----END OPENSSH PRIVATE KEY-----
PORT: 22

# GitHub Token (自动生成，用于拉取镜像)
GITHUB_TOKEN: ghp_xxxxxxxxxxxx

# API配置
API_URL: https://your-domain.com/api
```

### 步骤 3: 服务器初始化

在服务器上运行：

```bash
# 安装Docker和Docker Compose
curl -fsSL https://get.docker.com | bash
sudo usermod -aG docker $USER

# 克隆项目
git clone https://github.com/yourusername/lab-management-system.git
cd lab-management-system

# 配置生产环境
cp .env.production .env
# 编辑 .env 文件，设置安全的密码和密钥

# 首次部署
chmod +x deploy-prod.sh
./deploy-prod.sh
```

### 步骤 4: 域名和SSL配置

1. **配置域名**: 将域名A记录指向服务器IP
2. **申请SSL证书**: 使用Let's Encrypt或其他CA
3. **配置Nginx**: 更新nginx配置文件

```bash
# 使用certbot申请SSL证书
sudo apt install certbot
sudo certbot certonly --standalone -d your-domain.com
```

## 3. 自动化部署流程

### GitHub Actions工作流程

```mermaid
graph LR
    A[推送代码] --> B[构建前端]
    A --> C[构建Docker镜像]
    B --> D[部署到GitHub Pages]
    C --> E[推送到容器注册表]
    E --> F[部署到生产服务器]
```

### 部署触发条件

- **自动触发**: 推送到 `main` 分支
- **手动触发**: 在Actions页面手动运行
- **定时触发**: 每天凌晨自动检查更新

## 4. 环境配置说明

### 开发环境 (.env.development)
```env
NODE_ENV=development
VITE_API_URL=http://localhost:8080/api
VITE_APP_TITLE=实验室管理系统 - 开发版
```

### 生产环境 (.env.production)
```env
NODE_ENV=production
VITE_API_URL=https://your-domain.com/api
VITE_APP_TITLE=实验室管理系统

# 数据库配置
DB_ROOT_PASSWORD=secure_password_here
DB_PASSWORD=another_secure_password
JWT_SECRET=very_long_random_string_here

# 其他安全配置...
```

## 5. 监控和维护

### 健康检查

```bash
# 检查服务状态
./deploy-prod.sh status

# 查看日志
./deploy-prod.sh logs

# 执行健康检查
./deploy-prod.sh health
```

### 备份策略

```bash
# 手动备份
./deploy-prod.sh backup

# 自动备份 (crontab)
0 2 * * * /opt/lab-management-system/deploy-prod.sh backup
```

### 更新部署

```bash
# 拉取最新代码和镜像
git pull origin main
./deploy-prod.sh
```

## 6. 故障排除

### 常见问题

1. **容器无法启动**
   - 检查端口占用: `netstat -tlnp | grep :80`
   - 查看容器日志: `docker logs container_name`

2. **数据库连接失败**
   - 检查数据库容器状态
   - 验证环境变量配置

3. **SSL证书问题**
   - 检查证书有效期: `openssl x509 -in cert.pem -text -noout`
   - 更新证书配置

### 日志位置

- **应用日志**: `./logs/`
- **Docker日志**: `docker logs <container_name>`
- **系统日志**: `/var/log/`
- **部署日志**: `./deploy-prod.log`

## 7. 安全建议

1. **定期更新密码和密钥**
2. **启用防火墙，仅开放必要端口**
3. **定期备份数据**
4. **监控系统资源使用情况**
5. **及时更新系统和Docker镜像**
6. **使用HTTPS和强SSL证书**

## 支持

如有问题，请提交GitHub Issue或联系管理员。