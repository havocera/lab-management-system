# 贡献指南

感谢您对实验室管理系统项目的关注！我们欢迎任何形式的贡献。

## 🚀 如何贡献

### 报告问题

如果您发现了问题，请通过以下方式报告：

1. 在 [GitHub Issues](https://github.com/yourusername/lab-management-system/issues) 中搜索是否已有相似问题
2. 如果没有，创建新的 Issue，请包含：
   - 清晰的问题描述
   - 重现步骤
   - 期望的行为
   - 实际的行为
   - 环境信息（浏览器、操作系统等）

### 功能请求

我们欢迎功能建议！请：

1. 检查是否已有相关的 Issue
2. 创建新的 Feature Request，详细描述：
   - 功能的用途和价值
   - 具体的实现建议
   - 相关的设计图或原型（如有）

### 代码贡献

#### 开发流程

1. **Fork 项目**
   ```bash
   git clone https://github.com/yourusername/lab-management-system.git
   cd lab-management-system
   ```

2. **创建功能分支**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **设置开发环境**
   ```bash
   npm install
   npm run dev
   ```

4. **进行开发**
   - 编写代码
   - 添加测试（如适用）
   - 运行测试确保通过

5. **提交更改**
   ```bash
   git add .
   git commit -m "feat: add your feature description"
   ```

6. **推送分支**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **创建 Pull Request**
   - 提供清晰的标题和描述
   - 关联相关的 Issue
   - 描述更改的内容和原因

#### 代码规范

##### 前端代码规范

- 使用 Vue 3 Composition API
- 组件名使用 PascalCase
- 文件名使用 kebab-case
- 使用 Prettier 格式化代码
- 遵循 ESLint 规则

**组件结构示例：**
```vue
<template>
  <!-- 模板内容 -->
</template>

<script setup>
// 导入
import { ref, reactive } from 'vue'

// 响应式数据
const data = ref('')

// 方法
const handleClick = () => {
  // 处理逻辑
}
</script>

<style scoped>
/* 样式 */
</style>
```

##### 后端代码规范

- 遵循 PSR-12 编码规范
- 使用 Composer 管理依赖
- 控制器方法使用驼峰命名
- 数据库表名使用下划线命名

##### 提交信息规范

使用约定式提交格式：

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**类型说明：**
- `feat`: 新功能
- `fix`: 修复问题
- `docs`: 文档更新
- `style`: 代码格式调整
- `refactor`: 代码重构
- `test`: 测试相关
- `chore`: 构建或辅助工具更改

**示例：**
```
feat(auth): add JWT token validation

- Implement JWT middleware for API routes
- Add token expiration handling
- Update login flow to use JWT

Closes #123
```

## 🧪 测试

在提交 PR 之前，请确保：

```bash
# 运行前端测试
npm run test

# 运行代码格式检查
npm run lint

# 构建项目
npm run build
```

## 📝 文档

如果您的更改影响到：
- API 接口：更新 API 文档
- 新功能：更新 README.md
- 配置：更新相关配置说明

## 🤝 行为准则

请遵循我们的行为准则：

- 保持友好和专业
- 尊重不同的观点和经验
- 接受建设性的批评
- 关注对社区最有利的事情

## 📞 联系方式

如有疑问，可以通过以下方式联系：

- GitHub Issues
- 邮箱：your-email@example.com

再次感谢您的贡献！🎉