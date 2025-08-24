# 🧪 实验室管理系统 - 后端API

[![ThinkPHP](https://img.shields.io/badge/ThinkPHP-8.0-FF6B35.svg?style=flat&logo=php)](https://thinkphp.cn/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg?style=flat&logo=php)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1.svg?style=flat&logo=mysql)](https://mysql.com/)
[![JWT](https://img.shields.io/badge/JWT-Auth-000000.svg?style=flat&logo=json-web-tokens)](https://jwt.io/)

> 基于 ThinkPHP 8.0 构建的实验室管理系统后端API服务，提供完整的RESTful API接口，支持JWT认证、权限控制等功能。

## 🚀 技术栈

- **框架**: ThinkPHP 8.0
- **PHP版本**: >= 8.2
- **数据库**: MySQL 8.0+
- **认证**: JWT Token
- **架构**: RESTful API
- **容器化**: Docker + PHP-FPM

## ✨ 功能特性

### 🔐 认证与授权
- JWT Token 认证机制
- 用户登录/注册/密码修改
- 角色权限管理
- 中间件权限验证

### 🧪 业务功能
- **用户管理**: 用户信息、角色分配
- **实验室管理**: 实验室信息、状态管理
- **设备管理**: 设备登记、状态监控、维护记录
- **试剂管理**: 库存管理、申领审批、使用记录
- **预约管理**: 实验室预约、时间段管理、审核流程
- **权限管理**: 角色权限、操作日志

### 📊 系统功能
- 数据统计分析
- 操作日志记录
- 异常处理机制
- API版本控制

## 📁 项目结构

```
endtp/
├── app/                          # 应用目录
│   ├── controller/               # 控制器层
│   │   ├── Dashboard.php         # 仪表盘控制器
│   │   ├── Equipment.php         # 设备管理控制器
│   │   ├── Lab.php              # 实验室管理控制器
│   │   ├── Reagent.php          # 试剂管理控制器
│   │   ├── Reservation.php      # 预约管理控制器
│   │   ├── User.php             # 用户管理控制器
│   │   ├── Role.php             # 角色管理控制器
│   │   └── Permission.php       # 权限管理控制器
│   ├── model/                   # 模型层
│   │   ├── Lab.php              # 实验室模型
│   │   ├── Reagent.php          # 试剂模型
│   │   └── ReagentRecord.php    # 试剂记录模型
│   ├── middleware/              # 中间件
│   │   ├── AdminAuth.php        # 管理员认证中间件
│   │   ├── JwtAuth.php          # JWT认证中间件
│   │   └── SystemLog.php        # 系统日志中间件
│   ├── common/                  # 公共文件
│   │   └── exception/           # 自定义异常
│   │       └── JsonException.php
│   ├── AppService.php           # 应用服务
│   ├── BaseController.php       # 基础控制器
│   ├── ExceptionHandle.php      # 异常处理
│   ├── Request.php              # 请求验证
│   └── common.php               # 公共函数
├── config/                      # 配置文件
│   ├── app.php                  # 应用配置
│   ├── database.php             # 数据库配置
│   ├── jwt.php                  # JWT配置
│   ├── log.php                  # 日志配置
│   └── middleware.php           # 中间件配置
├── database/                    # 数据库相关
│   └── migrations/              # 数据库迁移文件
├── public/                      # Web入口目录
│   ├── index.php               # 入口文件
│   └── .htaccess               # Apache重写规则
├── route/                       # 路由定义
│   └── app.php                  # API路由配置
├── runtime/                     # 运行时目录
│   └── log/                     # 日志文件
├── deploy/                      # 部署配置
│   ├── nginx-backend.conf       # Nginx配置
│   ├── php.ini                  # PHP配置
│   └── supervisord.conf         # Supervisor配置
├── Dockerfile                   # PHP-FPM镜像
├── Dockerfile.nginx             # 后端Nginx镜像
├── nginx.conf                   # Nginx配置文件
├── composer.json                # Composer依赖
└── think                        # 命令行工具
```

## 🛠️ 本地开发

### 环境要求

- PHP >= 8.2
- MySQL >= 8.0
- Composer >= 2.0
- 扩展要求：`pdo_mysql`, `mysqli`, `curl`, `json`, `mbstring`

### 安装步骤

```bash
# 1. 进入后端目录
cd endtp

# 2. 安装PHP依赖
composer install

# 3. 配置环境变量
cp .env.example .env

# 4. 编辑配置文件
vim .env
```

### 环境配置

编辑 `.env` 文件：

```ini
# 应用配置
APP_DEBUG = true
DEFAULT_TIMEZONE = Asia/Shanghai

# 数据库配置
[DATABASE]
TYPE = mysql
HOSTNAME = localhost
DATABASE = labmanage
USERNAME = root
PASSWORD = your_password
HOSTPORT = 3306
CHARSET = utf8mb4
PREFIX = 
DEBUG = true

# JWT配置
JWT_SECRET = your-jwt-secret-key
JWT_EXPIRE = 7200

# 日志配置
[LOG]
DRIVER = file
PATH = ../runtime/log/
```

### 数据库初始化

```bash
# 1. 创建数据库
mysql -u root -p
CREATE DATABASE labmanage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 2. 导入数据库结构（在项目根目录执行）
cd ../
mysql -u root -p labmanage < labmanage.sql
```

### 启动服务

```bash
# 启动ThinkPHP内置服务器
php think run

# 或指定端口
php think run -p 8080

# 访问地址
# http://localhost:8000
```

## 🐳 Docker 部署

### 构建镜像

```bash
# 构建PHP-FPM镜像
docker build -t labmanage-php-fpm .

# 构建后端Nginx镜像
docker build -f Dockerfile.nginx -t labmanage-php-nginx .
```

### 容器运行

详细的Docker部署方案请参考项目根目录的 [docker-compose.yml](../docker-compose.yml) 和 [DOCKER.md](../DOCKER.md)

## 📖 API 接口文档

### 基础信息

- **Base URL**: `http://localhost:8000` (开发环境)
- **请求格式**: `application/json`
- **响应格式**: `application/json`
- **认证方式**: `Bearer Token (JWT)`

### 认证接口

#### 用户登录
```http
POST /user/login
Content-Type: application/json

{
    "username": "admin",
    "password": "password"
}
```

**响应示例**:
```json
{
    "code": 200,
    "message": "登录成功",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "username": "admin",
            "nickname": "管理员",
            "role": "admin"
        }
    }
}
```

#### 获取用户信息
```http
GET /user/info
Authorization: Bearer {token}
```

#### 修改密码
```http
POST /user/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
    "old_password": "old_password",
    "new_password": "new_password"
}
```

### 实验室管理

#### 获取实验室列表
```http
GET /lab?page=1&limit=10
Authorization: Bearer {token}
```

#### 添加实验室
```http
POST /lab/add
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "物理实验室A",
    "room_no": "A301",
    "type": "physics",
    "capacity": 50,
    "manager": "张老师",
    "contact": "13800138000",
    "description": "用于物理基础实验"
}
```

### 设备管理

#### 获取设备列表
```http
GET /equipment?lab_id=1&status=normal
Authorization: Bearer {token}
```

#### 添加设备
```http
POST /equipment/add
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "显微镜",
    "model": "BX53",
    "serial_number": "BX53001",
    "lab_id": 1,
    "purchase_date": "2024-01-15",
    "price": 50000.00,
    "description": "生物显微镜"
}
```

### 试剂管理

#### 获取试剂列表
```http
GET /reagent?category=化学试剂
Authorization: Bearer {token}
```

#### 试剂申领
```http
POST /reagent/apply
Authorization: Bearer {token}
Content-Type: application/json

{
    "reagent_id": 1,
    "quantity": 100,
    "purpose": "生物实验使用",
    "expected_date": "2024-02-01"
}
```

### 预约管理

#### 获取预约列表
```http
GET /reservation?status=pending&user_id=1
Authorization: Bearer {token}
```

#### 创建预约
```http
POST /reservation/create
Authorization: Bearer {token}
Content-Type: application/json

{
    "lab_id": 1,
    "start_time": "2024-02-01 09:00:00",
    "end_time": "2024-02-01 17:00:00",
    "purpose": "物理实验课程"
}
```

#### 审核预约
```http
POST /reservation/approve
Authorization: Bearer {token}
Content-Type: application/json

{
    "id": 1,
    "status": "approved",
    "remark": "审核通过"
}
```

### 统计数据

#### 获取仪表盘数据
```http
GET /dashboard/stats
Authorization: Bearer {token}
```

**响应示例**:
```json
{
    "code": 200,
    "data": {
        "lab_count": 10,
        "equipment_count": 150,
        "reagent_count": 80,
        "reservation_count": 25,
        "today_reservations": [...],
        "equipment_status": {...},
        "recent_activities": [...]
    }
}
```

## 🔧 开发工具

### 命令行工具

```bash
# 查看所有可用命令
php think

# 生成控制器
php think make:controller User

# 生成模型
php think make:model User

# 生成中间件
php think make:middleware Auth

# 清除缓存
php think clear

# 查看路由列表
php think route:list
```

### 调试工具

```bash
# 启用调试模式
APP_DEBUG = true

# 查看日志
tail -f runtime/log/$(date +%Y%m%d).log

# SQL调试
DATABASE.DEBUG = true
```

## 🚦 代码规范

### PSR规范
- 遵循 PSR-2 代码风格规范
- 遵循 PSR-4 自动加载规范
- 使用 PSR-3 日志接口

### 命名约定
- 控制器：大驼峰命名，如 `UserController`
- 模型：大驼峰命名，如 `UserModel`
- 方法：小驼峰命名，如 `getUserInfo`
- 数据库表：下划线命名，如 `user_info`

### API设计规范
- 使用标准HTTP状态码
- RESTful API设计原则
- 统一的响应格式
- 完善的错误处理

## 🧪 测试

### 单元测试

```bash
# 运行测试
php think test

# 生成测试报告
php think test --coverage
```

### API测试

推荐使用 Postman 或 Apifox 进行API测试，项目提供了完整的接口文档。

## 🔐 安全注意事项

1. **JWT密钥**: 生产环境必须修改 `JWT_SECRET`
2. **数据库密码**: 使用强密码并定期更换
3. **调试模式**: 生产环境必须关闭 `APP_DEBUG`
4. **文件权限**: 合理设置文件和目录权限
5. **HTTPS**: 生产环境建议启用HTTPS
6. **输入验证**: 严格验证所有用户输入
7. **SQL注入**: 使用参数化查询防止SQL注入

## 📝 更新日志

### v1.2.0 (2024-08-24)
- 重构Docker部署架构
- 优化Nginx配置和伪静态规则
- 改进JWT认证机制
- 完善API文档

### v1.1.0 (2024-04-05)
- 添加试剂管理模块
- 优化预约审核流程
- 增加操作日志记录
- 修复已知安全漏洞

### v1.0.0 (2024-04-01)
- 首次发布
- 实现基础功能模块
- 完成Docker部署方案

## 🤝 贡献指南

1. Fork 项目
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开 Pull Request

## 📄 许可证

本项目采用 MIT 许可证，详见 [LICENSE.txt](LICENSE.txt) 文件。

## 📞 技术支持

- 项目地址: [GitHub Repository](https://github.com/havocera/lab-management-system)
- 问题反馈: [GitHub Issues](https://github.com/havocera/lab-management-system/issues)
- 邮箱: ihavoc@163.com

---

基于 ThinkPHP 8.0 构建 | 专业实验室管理解决方案
