<template>
  <div class="login-container">
    <!-- 背景装饰 -->
    <div class="background-decoration">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
      <div class="floating-particles">
        <div class="particle" v-for="i in 20" :key="i"></div>
      </div>
    </div>
    
    <!-- 左侧信息展示区 -->
    <div class="info-section">
      <div class="info-content">
        <div class="logo-section">
          <SystemLogo />
          <h1 class="system-title">实验室管理系统</h1>
          <p class="system-subtitle">Laboratory Management System</p>
        </div>
        
        <div class="feature-list">
          <div class="feature-item">
            <div class="feature-icon">
              <div class="i-carbon-chemistry text-2xl"></div>
            </div>
            <div class="feature-text">
              <h3>设备管理</h3>
              <p>智能化设备管理与监控</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">
              <div class="i-carbon-user-multiple text-2xl"></div>
            </div>
            <div class="feature-text">
              <h3>用户权限</h3>
              <p>多角色权限管理系统</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">
              <div class="i-carbon-calendar text-2xl"></div>
            </div>
            <div class="feature-text">
              <h3>预约管理</h3>
              <p>便捷的实验室预约服务</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 右侧登录表单区 -->
    <div class="form-section">
      <div class="form-container">
        <!-- 顶部操作按钮 -->
        <div class="header-actions">
          <el-button
            circle
            size="small"
            class="action-btn"
            @click="toggleDark()"
          >
            <div class="i-carbon-sun dark:i-carbon-moon text-lg"></div>
          </el-button>
          <el-button
            circle
            size="small"
            class="action-btn"
          >
            <div class="i-carbon-language text-lg"></div>
          </el-button>
        </div>
        
        <!-- 登录表单 -->
        <div class="login-form-wrapper">
          <div class="form-header">
            <h2 class="form-title">欢迎回来</h2>
            <p class="form-subtitle">请输入您的账号信息</p>
          </div>
          
          <el-form
            ref="loginFormRef"
            :model="loginForm"
            :rules="rules"
            class="login-form"
            @keyup.enter="handleLogin"
          >
            <el-form-item prop="username">
              <div class="input-wrapper">
                <div class="input-label">用户名</div>
                <el-input
                  v-model="loginForm.username"
                  placeholder="请输入用户名"
                  :prefix-icon="User"
                  size="large"
                  class="custom-input"
                />
              </div>
            </el-form-item>
            
            <el-form-item prop="password">
              <div class="input-wrapper">
                <div class="input-label">密码</div>
                <el-input
                  v-model="loginForm.password"
                  type="password"
                  placeholder="请输入密码"
                  :prefix-icon="Lock"
                  show-password
                  size="large"
                  class="custom-input"
                />
              </div>
            </el-form-item>
            
            <div class="form-options">
              <el-checkbox v-model="rememberMe" class="remember-me">
                记住登录状态
              </el-checkbox>
              <el-link type="primary" :underline="false" class="forgot-password">
                忘记密码？
              </el-link>
            </div>
            
            <el-button
              type="primary"
              size="large"
              class="login-btn"
              :loading="loading"
              @click="handleLogin"
            >
              <span v-if="!loading">立即登录</span>
              <span v-else>登录中...</span>
            </el-button>
          </el-form>
          
          <!-- 其他登录方式 -->
          <div class="other-login">
            <div class="divider">
              <span>其他登录方式</span>
            </div>
            <div class="other-methods">
              <el-button
                class="method-btn"
                @click="handleOtherLogin('验证码登录')"
              >
                <div class="i-carbon-qr-code text-lg"></div>
                <span>验证码登录</span>
              </el-button>
              <el-button
                class="method-btn"
                @click="handleOtherLogin('注册账号')"
              >
                <div class="i-carbon-user-follow text-lg"></div>
                <span>注册账号</span>
              </el-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, nextTick } from 'vue'
import { User, Lock } from '@element-plus/icons-vue'
import { useRouter, useRoute } from 'vue-router'
import { useDark, useToggle } from '@vueuse/core'
import { ElMessage } from 'element-plus'
import WaveBg from '@/components/WaveBg.vue'
import SystemLogo from '@/components/SystemLogo.vue'
import { useUserStore } from '@/stores/user'

const isDark = useDark()
const toggleDark = useToggle(isDark)
const router = useRouter()
const route = useRoute()
const loginFormRef = ref(null)
const loading = ref(false)
const rememberMe = ref(false)

const userStore = useUserStore()

const loginForm = reactive({
  username: '',
  password: ''
})

const rules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '长度在 3 到 20 个字符', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, max: 20, message: '长度在 6 到 20 个字符', trigger: 'blur' }
  ]
}

const handleLogin = async () => {
  if (!loginFormRef.value) return
  
  try {
    await loginFormRef.value.validate()
    loading.value = true
    
    const result = await userStore.loginAction(loginForm)
    if (result.code === 0) {
      if (rememberMe.value) {
        localStorage.setItem('remember_username', loginForm.username)
      } else {
        localStorage.removeItem('remember_username')
      }
      
      ElMessage.success('登录成功')
      
      // 获取重定向地址
      const redirect = route.query.redirect || '/'
      router.replace(redirect)
    } else {
      ElMessage.error(result.msg || '登录失败，请检查用户名和密码')
    }
  } catch (error) {
    console.error('登录失败：', error)
    ElMessage.error('登录失败：' + (error.message || '未知错误'))
  } finally {
    loading.value = false
  }
}

const handleOtherLogin = (type) => {
  ElMessage.info('即将支持' + type)
}

// 如果之前记住了用户名，自动填充
const rememberedUsername = localStorage.getItem('remember_username')
if (rememberedUsername) {
  loginForm.username = rememberedUsername
  rememberMe.value = true
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* 背景装饰 */
.background-decoration {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 0;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
}

.shape-1 {
  width: 300px;
  height: 300px;
  top: -150px;
  left: -150px;
  animation: float 20s infinite ease-in-out;
}

.shape-2 {
  width: 200px;
  height: 200px;
  top: 50%;
  right: -100px;
  animation: float 25s infinite ease-in-out reverse;
}

.shape-3 {
  width: 150px;
  height: 150px;
  bottom: -75px;
  left: 30%;
  animation: float 15s infinite ease-in-out;
}

.floating-particles {
  position: absolute;
  width: 100%;
  height: 100%;
}

.particle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  animation: particle-float 10s infinite linear;
}

.particle:nth-child(odd) {
  animation-duration: 15s;
  background: rgba(255, 255, 255, 0.2);
}

.particle:nth-child(1) { left: 10%; animation-delay: 0s; }
.particle:nth-child(2) { left: 20%; animation-delay: 1s; }
.particle:nth-child(3) { left: 30%; animation-delay: 2s; }
.particle:nth-child(4) { left: 40%; animation-delay: 3s; }
.particle:nth-child(5) { left: 50%; animation-delay: 4s; }
.particle:nth-child(6) { left: 60%; animation-delay: 5s; }
.particle:nth-child(7) { left: 70%; animation-delay: 6s; }
.particle:nth-child(8) { left: 80%; animation-delay: 7s; }
.particle:nth-child(9) { left: 90%; animation-delay: 8s; }
.particle:nth-child(10) { left: 15%; animation-delay: 9s; }

/* 左侧信息区域 */
.info-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  position: relative;
  z-index: 1;
}

.info-content {
  max-width: 500px;
  text-align: center;
}

.logo-section {
  margin-bottom: 60px;
}

.system-title {
  font-size: 3.5rem;
  font-weight: 700;
  color: white;
  margin: 20px 0 10px 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.system-subtitle {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 300;
  letter-spacing: 2px;
}

.feature-list {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.feature-item:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-5px);
}

.feature-icon {
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.feature-text {
  text-align: left;
}

.feature-text h3 {
  color: white;
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0 0 5px 0;
}

.feature-text p {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.95rem;
  margin: 0;
}

/* 右侧表单区域 */
.form-section {
  width: 480px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 1;
}

.form-container {
  width: 100%;
  max-width: 400px;
  padding: 40px;
}

.header-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-bottom: 40px;
}

.action-btn {
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 1);
  transform: translateY(-2px);
}

.form-header {
  text-align: center;
  margin-bottom: 40px;
}

.form-title {
  font-size: 2.2rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 10px 0;
}

.form-subtitle {
  color: #718096;
  font-size: 1rem;
  margin: 0;
}

.login-form {
  margin-bottom: 30px;
}

.input-wrapper {
  margin-bottom: 20px;
}

.input-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 8px;
}

.custom-input {
  --el-input-height: 50px;
  --el-input-font-size: 16px;
}

.custom-input :deep(.el-input__wrapper) {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.custom-input :deep(.el-input__wrapper:hover) {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.custom-input :deep(.el-input__wrapper.is-focus) {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.remember-me :deep(.el-checkbox__label) {
  color: #4a5568;
  font-size: 0.9rem;
}

.forgot-password {
  font-size: 0.9rem;
  font-weight: 500;
}

.login-btn {
  width: 100%;
  height: 50px;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s ease;
}

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.other-login {
  margin-top: 40px;
}

.divider {
  text-align: center;
  margin-bottom: 20px;
  position: relative;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #e2e8f0;
}

.divider span {
  background: rgba(255, 255, 255, 0.95);
  padding: 0 20px;
  color: #718096;
  font-size: 0.9rem;
  position: relative;
}

.other-methods {
  display: flex;
  gap: 15px;
}

.method-btn {
  flex: 1;
  height: 45px;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  border: 1px solid #e2e8f0;
  background: white;
  transition: all 0.3s ease;
}

.method-btn:hover {
  border-color: #667eea;
  background: #f7fafc;
  transform: translateY(-2px);
}

.method-btn span {
  font-size: 0.8rem;
  color: #4a5568;
}

/* 响应式设计 */
@media (max-width: 1024px) {
  .info-section {
    display: none;
  }
  
  .form-section {
    width: 100%;
  }
}

@media (max-width: 640px) {
  .form-container {
    padding: 20px;
  }
  
  .system-title {
    font-size: 2.5rem;
  }
  
  .other-methods {
    flex-direction: column;
  }
}

/* 动画效果 */
@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(10deg); }
}

@keyframes particle-float {
  0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
  10%, 90% { opacity: 1; }
  100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
}

/* 暗色主题 */
.dark .form-section {
  background: rgba(26, 32, 44, 0.95);
}

.dark .form-title {
  color: #f7fafc;
}

.dark .form-subtitle {
  color: #cbd5e0;
}

.dark .input-label {
  color: #e2e8f0;
}

.dark .custom-input :deep(.el-input__wrapper) {
  background: rgba(45, 55, 72, 0.8);
  border-color: #4a5568;
  color: #f7fafc;
}

.dark .method-btn {
  background: rgba(45, 55, 72, 0.8);
  border-color: #4a5568;
  color: #e2e8f0;
}

.dark .divider span {
  background: rgba(26, 32, 44, 0.95);
  color: #cbd5e0;
}
</style> 