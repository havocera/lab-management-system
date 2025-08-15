@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

:: 实验室管理系统一键部署脚本 (Windows)
:: Laboratory Management System One-Click Deployment Script for Windows

echo.
echo ============================================
echo   实验室管理系统一键部署脚本 (Windows)
echo   Laboratory Management System Deployment
echo ============================================
echo.

:: 颜色设置（Windows支持的ANSI颜色）
set "RED=[91m"
set "GREEN=[92m" 
set "YELLOW=[93m"
set "BLUE=[94m"
set "NC=[0m"

:: 项目信息
set "PROJECT_NAME=Laboratory Management System"
set "PROJECT_DIR=%~dp0"
set "LOG_FILE=%PROJECT_DIR%deploy.log"

:: 函数：打印彩色消息
goto :main

:print_message
    set "color=%~1"
    set "message=%~2"
    set "timestamp=%date% %time%"
    echo %color%[!timestamp!] !message!%NC%
    echo [!timestamp!] !message! >> "%LOG_FILE%"
    goto :eof

:check_command
    where %1 >nul 2>&1
    if errorlevel 1 (
        call :print_message "%RED%" "错误: %1 命令未找到，请先安装 %1"
        pause
        exit /b 1
    )
    goto :eof

:check_requirements
    call :print_message "%BLUE%" "检查系统要求..."
    
    :: 检查 Docker
    call :check_command docker
    if errorlevel 1 exit /b 1
    
    :: 检查 Docker Compose
    docker compose version >nul 2>&1
    if errorlevel 1 (
        docker-compose --version >nul 2>&1
        if errorlevel 1 (
            call :print_message "%RED%" "错误: Docker Compose 未安装"
            pause
            exit /b 1
        ) else (
            set "COMPOSE_CMD=docker-compose"
        )
    ) else (
        set "COMPOSE_CMD=docker compose"
    )
    
    :: 检查 Git (可选)
    where git >nul 2>&1
    if errorlevel 1 (
        call :print_message "%YELLOW%" "警告: Git 未安装，无法显示版本信息"
    ) else (
        call :print_message "%GREEN%" "Git 已安装"
    )
    
    call :print_message "%GREEN%" "系统要求检查完成"
    goto :eof

:setup_environment
    call :print_message "%BLUE%" "设置环境变量..."
    
    if not exist "%PROJECT_DIR%.env" (
        if exist "%PROJECT_DIR%.env.example" (
            copy "%PROJECT_DIR%.env.example" "%PROJECT_DIR%.env" >nul
            call :print_message "%GREEN%" "已从 .env.example 创建 .env 文件"
        ) else (
            call :print_message "%RED%" "错误: .env.example 文件不存在"
            pause
            exit /b 1
        )
    )
    
    :: 生成随机密码和密钥 (Windows方式)
    call :generate_random DB_ROOT_PASSWORD 16
    call :generate_random DB_PASSWORD 16
    call :generate_random JWT_SECRET 32
    
    :: 更新 .env 文件
    powershell -Command "(Get-Content '%PROJECT_DIR%.env') -replace 'DB_ROOT_PASSWORD=.*', 'DB_ROOT_PASSWORD=!DB_ROOT_PASSWORD!' | Set-Content '%PROJECT_DIR%.env'"
    powershell -Command "(Get-Content '%PROJECT_DIR%.env') -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=!DB_PASSWORD!' | Set-Content '%PROJECT_DIR%.env'"
    powershell -Command "(Get-Content '%PROJECT_DIR%.env') -replace 'JWT_SECRET=.*', 'JWT_SECRET=!JWT_SECRET!' | Set-Content '%PROJECT_DIR%.env'"
    
    call :print_message "%GREEN%" "环境变量设置完成"
    goto :eof

:generate_random
    set "var_name=%~1"
    set "length=%~2"
    powershell -Command "$bytes = New-Object byte[] %length%; (New-Object Random).NextBytes($bytes); [Convert]::ToBase64String($bytes) -replace '[+/=]', ''" > temp_random.txt
    set /p random_value=<temp_random.txt
    del temp_random.txt
    set "!var_name!=!random_value!"
    goto :eof

:build_images
    call :print_message "%BLUE%" "构建 Docker 镜像..."
    
    cd /d "%PROJECT_DIR%"
    
    :: 构建前端镜像
    call :print_message "%YELLOW%" "构建前端镜像..."
    docker build -t labmanage-frontend:latest .
    if errorlevel 1 (
        call :print_message "%RED%" "前端镜像构建失败"
        exit /b 1
    )
    
    :: 构建后端镜像
    call :print_message "%YELLOW%" "构建后端镜像..."
    docker build -t labmanage-backend:latest ./endtp/
    if errorlevel 1 (
        call :print_message "%RED%" "后端镜像构建失败"
        exit /b 1
    )
    
    call :print_message "%GREEN%" "Docker 镜像构建完成"
    goto :eof

:start_services
    call :print_message "%BLUE%" "启动服务..."
    
    cd /d "%PROJECT_DIR%"
    
    :: 停止现有服务
    %COMPOSE_CMD% down >nul 2>&1
    
    :: 启动所有服务
    %COMPOSE_CMD% up -d
    if errorlevel 1 (
        call :print_message "%RED%" "服务启动失败"
        exit /b 1
    )
    
    call :print_message "%GREEN%" "服务启动完成"
    goto :eof

:wait_for_services
    call :print_message "%BLUE%" "等待服务就绪..."
    
    :: 等待数据库启动
    call :print_message "%YELLOW%" "等待数据库启动..."
    set /a max_attempts=60
    set /a attempt=0
    
    :db_wait_loop
    if !attempt! geq !max_attempts! (
        call :print_message "%RED%" "数据库启动超时"
        exit /b 1
    )
    
    %COMPOSE_CMD% exec -T database mysqladmin ping -h localhost --silent >nul 2>&1
    if not errorlevel 1 (
        call :print_message "%GREEN%" "数据库已就绪"
        goto :backend_wait
    )
    
    set /a attempt+=1
    call :print_message "%YELLOW%" "等待数据库启动... (!attempt!/!max_attempts!)"
    timeout /t 5 >nul
    goto :db_wait_loop
    
    :backend_wait
    :: 等待后端服务
    call :print_message "%YELLOW%" "等待后端服务..."
    set /a attempt=0
    set /a max_attempts=30
    
    :backend_wait_loop
    if !attempt! geq !max_attempts! goto :frontend_wait
    
    curl -f http://localhost:8080/ >nul 2>&1
    if not errorlevel 1 (
        call :print_message "%GREEN%" "后端服务已就绪"
        goto :frontend_wait
    )
    
    set /a attempt+=1
    call :print_message "%YELLOW%" "等待后端服务... (!attempt!/!max_attempts!)"
    timeout /t 10 >nul
    goto :backend_wait_loop
    
    :frontend_wait
    :: 等待前端服务
    call :print_message "%YELLOW%" "等待前端服务..."
    set /a attempt=0
    set /a max_attempts=20
    
    :frontend_wait_loop
    if !attempt! geq !max_attempts! goto :wait_done
    
    curl -f http://localhost/ >nul 2>&1
    if not errorlevel 1 (
        call :print_message "%GREEN%" "前端服务已就绪"
        goto :wait_done
    )
    
    set /a attempt+=1
    call :print_message "%YELLOW%" "等待前端服务... (!attempt!/!max_attempts!)"
    timeout /t 5 >nul
    goto :frontend_wait_loop
    
    :wait_done
    goto :eof

:show_result
    call :print_message "%GREEN%" "============================================"
    call :print_message "%GREEN%" "  %PROJECT_NAME% 部署完成!"
    call :print_message "%GREEN%" "============================================"
    echo.
    call :print_message "%BLUE%" "服务访问地址:"
    call :print_message "%YELLOW%" "  前端应用: http://localhost"
    call :print_message "%YELLOW%" "  后端API:  http://localhost:8080"
    call :print_message "%YELLOW%" "  数据库:   localhost:3306"
    echo.
    call :print_message "%BLUE%" "默认管理员账户:"
    call :print_message "%YELLOW%" "  用户名: admin"
    call :print_message "%YELLOW%" "  密码:   password (请登录后立即修改)"
    echo.
    call :print_message "%BLUE%" "常用命令:"
    call :print_message "%YELLOW%" "  查看服务状态: %COMPOSE_CMD% ps"
    call :print_message "%YELLOW%" "  查看日志:     %COMPOSE_CMD% logs -f [服务名]"
    call :print_message "%YELLOW%" "  停止服务:     %COMPOSE_CMD% down"
    call :print_message "%YELLOW%" "  重启服务:     %COMPOSE_CMD% restart"
    echo.
    call :print_message "%GREEN%" "部署日志已保存到: %LOG_FILE%"
    goto :eof

:main
    :: 脚本参数处理
    if "%~1"=="help" goto :show_help
    if "%~1"=="-h" goto :show_help
    if "%~1"=="--help" goto :show_help
    if "%~1"=="clean" goto :clean
    if "%~1"=="logs" goto :logs
    if "%~1"=="status" goto :status
    if "%~1"=="restart" goto :restart
    if "%~1"=="stop" goto :stop
    if "%~1"=="" goto :deploy
    
    call :print_message "%RED%" "未知选项: %~1"
    call :print_message "%YELLOW%" "运行 '%~nx0 help' 查看帮助信息"
    pause
    exit /b 1

:show_help
    echo 用法: %~nx0 [选项]
    echo.
    echo 选项:
    echo   help, -h, --help    显示此帮助信息
    echo   clean               清理所有容器和镜像
    echo   logs                查看服务日志
    echo   status              查看服务状态
    echo   restart             重启所有服务
    echo   stop                停止所有服务
    echo.
    pause
    exit /b 0

:clean
    call :print_message "%YELLOW%" "清理所有容器和镜像..."
    cd /d "%PROJECT_DIR%"
    %COMPOSE_CMD% down --rmi all --volumes --remove-orphans
    call :print_message "%GREEN%" "清理完成"
    pause
    exit /b 0

:logs
    cd /d "%PROJECT_DIR%"
    %COMPOSE_CMD% logs -f
    exit /b 0

:status
    cd /d "%PROJECT_DIR%"
    %COMPOSE_CMD% ps
    pause
    exit /b 0

:restart
    cd /d "%PROJECT_DIR%"
    %COMPOSE_CMD% restart
    call :print_message "%GREEN%" "服务重启完成"
    pause
    exit /b 0

:stop
    cd /d "%PROJECT_DIR%"
    %COMPOSE_CMD% down
    call :print_message "%GREEN%" "服务已停止"
    pause
    exit /b 0

:deploy
    call :print_message "%GREEN%" "============================================"
    call :print_message "%GREEN%" "  %PROJECT_NAME% 一键部署脚本"
    call :print_message "%GREEN%" "============================================"
    
    :: 检查系统要求
    call :check_requirements
    if errorlevel 1 exit /b 1
    
    :: 设置环境变量
    call :setup_environment
    if errorlevel 1 exit /b 1
    
    :: 构建镜像
    call :build_images
    if errorlevel 1 exit /b 1
    
    :: 启动服务
    call :start_services
    if errorlevel 1 exit /b 1
    
    :: 等待服务就绪
    call :wait_for_services
    
    :: 显示部署结果
    call :show_result
    
    echo.
    echo 部署完成！按任意键退出...
    pause >nul
    exit /b 0