# 构建阶段
FROM node:18-alpine AS builder

WORKDIR /app

# 复制package文件
COPY package*.json ./

# 安装依赖
RUN npm install --registry https://registry.npmmirror.com

# 复制源代码
COPY . .

# 构建前端项目
RUN npm run build

# 生产阶段 - 使用Nginx镜像
FROM nginx:alpine

# 复制构建的静态文件
COPY --from=builder /app/dist /usr/share/nginx/html

# 复制nginx配置
COPY default.conf /etc/nginx/conf.d/default.conf

# 暴露端口
EXPOSE 80

# 启动nginx
CMD ["nginx", "-g", "daemon off;"]