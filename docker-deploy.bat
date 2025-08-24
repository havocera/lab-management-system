@echo off
REM 实验室管理系统 Docker 部署脚本 (Windows)

echo === 实验室管理系统 Docker 部署 ===

REM 检查是否存在环境变量文件
if not exist .env (
    echo 复制环境变量配置文件...
    copy .env.example .env
    echo 请编辑 .env 文件设置您的数据库密码和其他配置
)

REM 停止并删除现有容器
echo 停止现有容器...
docker-compose down

REM 构建并启动服务
echo 构建并启动服务...
docker-compose up --build -d

REM 等待服务启动
echo 等待服务启动...
timeout /t 30 /nobreak

REM 显示服务状态
echo === 服务状态 ===
docker-compose ps

echo.
echo === 部署完成 ===
echo 访问地址: http://localhost:20080
echo.
echo 服务组成:
echo - Nginx: 前端+后端统一入口
echo - PHP-FPM: 后端API服务
echo - MySQL: 数据库服务
echo.
echo 常用命令:
echo 查看日志: docker-compose logs -f [service_name]
echo 停止服务: docker-compose down
echo 重启服务: docker-compose restart

pause