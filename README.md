# 🧪 实验室管理系统 (Laboratory Management System)

[![Vue.js](https://img.shields.io/badge/Vue.js-3.5.13-4FC08D.svg?style=flat&logo=vue.js)](https://vuejs.org/)
[![Vite](https://img.shields.io/badge/Vite-6.2.4-646CFF.svg?style=flat&logo=vite)](https://vitejs.dev/)
[![Element Plus](https://img.shields.io/badge/Element%20Plus-2.9.7-409EFF.svg?style=flat&logo=element)](https://element-plus.org/)
[![UnoCSS](https://img.shields.io/badge/UnoCSS-0.66.1-333333.svg?style=flat&logo=unocss)](https://unocss.dev/)
[![ThinkPHP](https://img.shields.io/badge/ThinkPHP-8.0-FF6B35.svg?style=flat&logo=php)](https://thinkphp.cn/)
[![Docker](https://img.shields.io/badge/Docker-20.10+-2496ED.svg?style=flat&logo=docker)](https://docker.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

> 一个基于 Vue 3 + ThinkPHP 8 的现代化实验室管理系统，支持Docker一键部署，提供设备管理、试剂管理、实验室预约等功能。

## 🐳 Docker 部署 (推荐)

### 系统架构

采用微服务化四容器架构：

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   前端 Nginx    │    │   后端 Nginx    │    │   PHP-FPM       │    │   MySQL         │
│   (静态文件)    │◀──▶│   (API路由)     │◀──▶│   (业务逻辑)    │◀──▶│   (数据存储)    │
│   :20080        │    │   内部通信      │    │   内部通信      │    │   :23306        │
└─────────────────┘    └─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 系统要求
- Docker 20.10+
- Docker Compose 2.0+
- 2GB+ 可用内存
- 5GB+ 可用磁盘空间

### 快速部署

#### Windows 用户
```cmd
# 克隆项目
git clone https://github.com/havocera/lab-management-system.git
cd lab-management-system

# 运行部署脚本
docker-deploy.bat
```

#### Linux/macOS 用户
```bash
# 克隆项目
git clone https://github.com/havocera/lab-management-system.git
cd lab-management-system

# 运行部署脚本
chmod +x deploy-docker.sh
./deploy-docker.sh
```

#### 手动部署
```bash
# 配置环境变量（可选）
cp .env.example .env

# 启动服务
docker-compose up --build -d

# 查看运行状态
docker-compose ps
```

### 访问地址
- **前端应用**: http://localhost:20080
- **数据库**: localhost:23306
- **API文档**: 前端访问 `/api/` 路径自动代理到后端

### 默认账户
- **用户名**: admin
- **密码**: 123456

⚠️ **安全提示**: 首次登录后请立即修改默认密码！

### 常用管理命令

```bash
# 查看服务状态
docker-compose ps

# 查看服务日志
docker-compose logs -f nginx      # 前端日志
docker-compose logs -f php-fpm    # PHP日志
docker-compose logs -f mysql      # 数据库日志

# 重启服务
docker-compose restart

# 停止服务
docker-compose down

# 完全清理（删除容器和镜像）
docker-compose down --rmi all
docker system prune -f
```

### 环境变量配置

编辑 `.env` 文件自定义配置：

```env
# 数据库配置
DB_ROOT_PASSWORD=123456
DB_DATABASE=labmanage
DB_USERNAME=labmanage
DB_PASSWORD=123456
DB_PORT=23306

# 服务端口配置
FRONTEND_PORT=20080

# JWT配置
JWT_SECRET=your-secret-key-change-this-in-production

# 应用配置
APP_DEBUG=false
APP_ENV=production
```

### 开发模式

Docker部署支持文件映射，便于本地开发：

- **前端开发**: 修改 `src/` 目录，容器自动同步
- **后端开发**: 修改 `endtp/` 目录，容器自动同步
- **数据库**: 修改 `labmanage.sql`，重建容器自动导入

### 部署故障排除

详细的部署指南和故障排除请参考：[DOCKER.md](DOCKER.md)

## ✨ 功能特性

### 🏠 仪表盘
- 📊 数据统计概览
- 📅 今日实验室使用情况
- 🔔 明日预约提醒
- ⚗️ 待审批试剂申领

### 🧪 实验室管理
- 🏢 实验室信息管理
- 📍 实验室位置和设备配置
- 👥 实验室使用权限控制
- 📈 使用情况统计

### 🔧 设备管理
- 📦 设备信息录入与管理
- 🔍 设备状态监控
- 🛠️ 维护记录管理
- 📋 设备使用日志

### ⚗️ 试剂管理
- 📝 试剂库存管理
- 🎯 试剂申领流程
- 📊 使用记录追踪
- ✅ 审批工作流

### 📅 预约管理
- 🗓️ 实验室在线预约
- ⏰ 时间段管理
- 👨‍🔬 预约审核流程
- 📧 预约提醒通知

### 👥 系统管理
- 🔐 用户权限管理
- 🏷️ 角色管理
- 🛡️ 权限控制
- 📊 操作日志

## 🎨 界面预览

### 登录页面
- 🎨 现代化渐变背景设计
- ✨ 动态粒子效果
- 🌙 深色模式支持
- 📱 响应式布局

### 管理界面
- 🎯 简洁直观的导航
- 📊 数据可视化图表
- 🔄 实时数据更新
- 🎨 Material Design 风格

## 🚀 技术栈

### 前端技术
- **框架**: Vue 3.5.13 + Composition API
- **构建工具**: Vite 6.2.4
- **UI 框架**: Element Plus 2.9.7
- **状态管理**: Pinia 3.0.1
- **路由管理**: Vue Router 4.5.0
- **CSS 框架**: UnoCSS 0.66.1
- **图标**: Iconify (Carbon Design System)
- **工具库**: VueUse 13.0.0
- **图表**: ECharts 5.6.0
- **HTTP 客户端**: Axios 1.8.4

### 后端技术
- **框架**: ThinkPHP 8.0
- **数据库**: MySQL 8.0
- **认证**: JWT Token
- **API**: RESTful API 设计

### 容器化技术
- **容器化**: Docker + Docker Compose
- **Web服务器**: Nginx (前端 + 后端)
- **PHP运行时**: PHP-FPM 8.2
- **数据库**: MySQL 8.0
- **反向代理**: Nginx负载均衡

### 开发工具
- **代码格式化**: Prettier
- **开发调试**: Vue DevTools
- **自动导入**: unplugin-auto-import
- **组件自动导入**: unplugin-vue-components

## 📁 项目结构

```
lab-management-system/
├── src/                      # 前端源码
│   ├── api/                 # API 接口
│   ├── assets/              # 静态资源
│   ├── components/          # 通用组件
│   ├── layouts/             # 布局组件
│   ├── router/              # 路由配置
│   ├── stores/              # 状态管理
│   ├── utils/               # 工具函数
│   └── views/               # 页面组件
├── endtp/                    # 后端源码 (ThinkPHP)
│   ├── app/                 # 应用目录
│   │   ├── controller/      # 控制器
│   │   ├── model/           # 模型
│   │   └── middleware/      # 中间件
│   ├── config/              # 配置文件
│   ├── database/            # 数据库迁移
│   ├── route/               # 路由定义
│   ├── Dockerfile           # PHP-FPM 镜像
│   ├── Dockerfile.nginx     # 后端 Nginx 镜像
│   └── nginx.conf           # 后端 Nginx 配置
├── public/                   # 公共资源
├── dist/                     # 构建输出
├── docker-compose.yml        # Docker 编排文件
├── Dockerfile               # 前端 Nginx 镜像
├── default.conf             # 前端 Nginx 配置
├── .env.example             # 环境变量模板
├── labmanage.sql            # 数据库初始化脚本
├── docker-deploy.bat        # Windows 部署脚本
├── deploy-docker.sh         # Linux/Mac 部署脚本
├── DOCKER.md               # Docker 部署详细文档
└── README.md               # 项目说明
```

## 🛠️ 本地开发

### 环境要求

- **Node.js**: >= 18.0.0
- **npm**: >= 9.0.0
- **PHP**: >= 8.0
- **MySQL**: >= 8.0
- **Composer**: >= 2.0

### 前端开发

```bash
# 克隆项目
git clone https://github.com/havocera/lab-management-system.git
cd lab-management-system

# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产版本
npm run build
```

### 后端开发

```bash
# 进入后端目录
cd endtp

# 安装 PHP 依赖
composer install

# 配置环境变量
cp .env.example .env

# 编辑 .env 文件，配置数据库连接
vim .env

# 启动内置服务器
php think run
```

### 数据库配置

1. 创建 MySQL 数据库：
```sql
CREATE DATABASE labmanage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. 导入初始数据：
```bash
mysql -u root -p labmanage < labmanage.sql
```

3. 配置 `.env` 文件：
```env
[DATABASE]
TYPE = mysql
HOSTNAME = localhost
DATABASE = labmanage
USERNAME = root
PASSWORD = your_password
HOSTPORT = 3306
CHARSET = utf8mb4
```

## 🔧 开发配置

### 代码规范

```bash
# 代码格式化
npm run format

# 类型检查  
npm run type-check

# 预览构建结果
npm run preview
```

### 环境变量

前端环境变量 (`.env.local`):
```env
VITE_API_URL=http://localhost:8080/api
VITE_APP_TITLE=实验室管理系统
```

后端环境变量 (`endtp/.env`):
```env
APP_DEBUG = true
[DATABASE]
TYPE = mysql
HOSTNAME = localhost
DATABASE = labmanage
USERNAME = root
PASSWORD = your_password
```

## 📖 API 文档

### 认证接口

- `POST /api/user/login` - 用户登录
- `POST /api/user/register` - 用户注册  
- `GET /api/user/info` - 获取用户信息
- `POST /api/user/change-password` - 修改密码

### 实验室管理

- `GET /api/lab` - 获取实验室列表
- `POST /api/lab/add` - 添加实验室
- `POST /api/lab/update` - 更新实验室信息
- `POST /api/lab/delete` - 删除实验室

### 设备管理

- `GET /api/equipment` - 获取设备列表
- `POST /api/equipment/add` - 添加设备
- `POST /api/equipment/update` - 更新设备信息
- `POST /api/equipment/delete` - 删除设备

详细 API 文档请参考 [API Documentation](docs/api.md)

## 🤝 贡献指南

我们欢迎所有形式的贡献！请遵循以下步骤：

1. Fork 本项目
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开 Pull Request

### 代码规范

- 使用 Prettier 进行代码格式化
- 遵循 Vue 3 Composition API 规范
- 组件命名使用 PascalCase
- 文件命名使用 kebab-case

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情

## 🙏 致谢

- [Vue.js](https://vuejs.org/) - 渐进式 JavaScript 框架
- [Element Plus](https://element-plus.org/) - Vue 3 组件库
- [UnoCSS](https://unocss.dev/) - 即时原子CSS引擎
- [Carbon Design System](https://carbondesign.com/) - 图标系统
- [ThinkPHP](https://thinkphp.cn/) - PHP 框架

## 📞 联系我们

- 项目地址: [GitHub Repository](https://github.com/havocera/lab-management-system)
- 问题反馈: [GitHub Issues](https://github.com/havocera/lab-management-system/issues)
- 邮箱: ihavoc@163.com

---

⭐ 如果这个项目对你有帮助，请给个 Star！

## ✨ 功能特性

### 🏠 仪表盘
- 📊 数据统计概览
- 📅 今日实验室使用情况
- 🔔 明日预约提醒
- ⚗️ 待审批试剂申领

### 🧪 实验室管理
- 🏢 实验室信息管理
- 📍 实验室位置和设备配置
- 👥 实验室使用权限控制
- 📈 使用情况统计

### 🔧 设备管理
- 📦 设备信息录入与管理
- 🔍 设备状态监控
- 🛠️ 维护记录管理
- 📋 设备使用日志

### ⚗️ 试剂管理
- 📝 试剂库存管理
- 🎯 试剂申领流程
- 📊 使用记录追踪
- ✅ 审批工作流

### 📅 预约管理
- 🗓️ 实验室在线预约
- ⏰ 时间段管理
- 👨‍🔬 预约审核流程
- 📧 预约提醒通知

### 👥 系统管理
- 🔐 用户权限管理
- 🏷️ 角色管理
- 🛡️ 权限控制
- 📊 操作日志

## 🎨 界面预览

### 登录页面
- 🎨 现代化渐变背景设计
- ✨ 动态粒子效果
- 🌙 深色模式支持
- 📱 响应式布局

### 管理界面
- 🎯 简洁直观的导航
- 📊 数据可视化图表
- 🔄 实时数据更新
- 🎨 Material Design 风格

## 🚀 技术栈

### 前端技术
- **框架**: Vue 3.5.13 + Composition API
- **构建工具**: Vite 6.2.4
- **UI 框架**: Element Plus 2.9.7
- **状态管理**: Pinia 3.0.1
- **路由管理**: Vue Router 4.5.0
- **CSS 框架**: UnoCSS 0.66.1
- **图标**: Iconify (Carbon Design System)
- **工具库**: VueUse 13.0.0
- **图表**: ECharts 5.6.0
- **HTTP 客户端**: Axios 1.8.4

### 后端技术
- **框架**: ThinkPHP 8.0
- **数据库**: MySQL 5.7+
- **认证**: JWT Token
- **API**: RESTful API 设计

### 开发工具
- **代码格式化**: Prettier
- **开发调试**: Vue DevTools
- **自动导入**: unplugin-auto-import
- **组件自动导入**: unplugin-vue-components

## 📁 项目结构

```
lab-management-system/
├── src/                    # 前端源码
│   ├── api/               # API 接口
│   ├── assets/            # 静态资源
│   ├── components/        # 通用组件
│   ├── layouts/           # 布局组件
│   ├── router/            # 路由配置
│   ├── stores/            # 状态管理
│   ├── utils/             # 工具函数
│   └── views/             # 页面组件
├── endtp/                 # 后端源码 (ThinkPHP)
│   ├── app/               # 应用目录
│   │   ├── controller/    # 控制器
│   │   ├── model/         # 模型
│   │   └── middleware/    # 中间件
│   ├── config/            # 配置文件
│   ├── database/          # 数据库迁移
│   └── route/             # 路由定义
├── public/                # 公共资源
├── dist/                  # 构建输出
└── docs/                  # 项目文档
```

## 🛠️ 快速开始

### 环境要求

- **Node.js**: >= 18.0.0
- **npm**: >= 9.0.0
- **PHP**: >= 8.0
- **MySQL**: >= 5.7
- **Composer**: >= 2.0

### 前端安装

```bash
# 克隆项目
git clone https://github.com/yourusername/lab-management-system.git
cd lab-management-system

# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产版本
npm run build
```

### 后端安装

```bash
# 进入后端目录
cd endtp

# 安装 PHP 依赖
composer install

# 配置环境变量
cp .env.example .env

# 编辑 .env 文件，配置数据库连接
nano .env

# 运行数据库迁移
php think migrate:run

# 启动服务
php think run
```

### 数据库配置

1. 创建 MySQL 数据库：
```sql
CREATE DATABASE labmanage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. 导入初始数据：
```bash
mysql -u root -p labmanage < labmanage.sql
```

3. 配置 `.env` 文件：
```env
[DATABASE]
TYPE = mysql
HOSTNAME = localhost
DATABASE = labmanage
USERNAME = root
PASSWORD = your_password
HOSTPORT = 3306
CHARSET = utf8mb4
```

## 🔧 开发配置

### 开发环境配置

```bash
# 代码格式化
npm run format

# 类型检查
npm run type-check

# 预览构建结果
npm run preview
```

### 环境变量

前端环境变量 (`.env`):
```env
VITE_API_URL=http://localhost:8000
VITE_APP_TITLE=实验室管理系统
```

后端环境变量 (`endtp/.env`):
```env
APP_DEBUG = true
[DATABASE]
TYPE = mysql
HOSTNAME = localhost
DATABASE = labmanage
USERNAME = root
PASSWORD = your_password
```

## 📖 API 文档

### 认证接口

- `POST /user/login` - 用户登录
- `POST /user/register` - 用户注册  
- `GET /user/info` - 获取用户信息
- `POST /user/change-password` - 修改密码

### 实验室管理

- `GET /lab` - 获取实验室列表
- `POST /lab/add` - 添加实验室
- `POST /lab/update` - 更新实验室信息
- `POST /lab/delete` - 删除实验室

### 设备管理

- `GET /equipment` - 获取设备列表
- `POST /equipment/add` - 添加设备
- `POST /equipment/update` - 更新设备信息
- `POST /equipment/delete` - 删除设备

详细 API 文档请参考 [API Documentation](docs/api.md)

## 🤝 贡献指南

我们欢迎所有形式的贡献！请遵循以下步骤：

1. Fork 本项目
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开 Pull Request

### 代码规范

- 使用 Prettier 进行代码格式化
- 遵循 Vue 3 Composition API 规范
- 组件命名使用 PascalCase
- 文件命名使用 kebab-case

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情

## 🙏 致谢

- [Vue.js](https://vuejs.org/) - 渐进式 JavaScript 框架
- [Element Plus](https://element-plus.org/) - Vue 3 组件库
- [UnoCSS](https://unocss.dev/) - 即时原子CSS引擎
- [Carbon Design System](https://carbondesign.com/) - 图标系统
- [ThinkPHP](https://thinkphp.cn/) - PHP 框架

## 📞 联系我们

- 项目地址: [GitHub Repository](https://github.com/havocera/lab-management-system)
- 问题反馈: [GitHub Issues](https://github.com/havocera/lab-management-system/issues)
- 邮箱: ihavoc@163.com


---

⭐ 如果这个项目对你有帮助，请给个 Star！
