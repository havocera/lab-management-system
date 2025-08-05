import axios from 'axios'
import { ElMessage } from 'element-plus'
import router from '@/router'

// 创建axios实例
const service = axios.create({
  baseURL: process.env.NODE_ENV === 'development' 
    ? 'http://lab.com' 
    : 'http://api.yourdomain.com',
  timeout: 15000, // 请求超时时间
  headers: {
    'Content-Type': 'application/json;charset=utf-8'
  }
})

// 是否正在刷新token
let isRefreshing = false
// 重试队列
let requests = []

// 请求拦截器
service.interceptors.request.use(
  config => {
    const token = localStorage.getItem('Admin-Token')
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  response => {
    const res = response.data
    // 如果返回的状态码不是0，说明接口出错
    if (res.code !== 0) {
      // 登录失效
      if (res.code === 401) {
        handleLogout()
      } else {
        ElMessage.error(res.msg || '请求失败')
      }
      return Promise.reject(res)
    }
    return res
  },
  error => {
    // 处理 HTTP 错误
    if (error.response) {
      switch (error.response.status) {
        case 401:
          handleLogout()
          break
        case 403:
          ElMessage.error('没有权限访问')
          break
        case 404:
          ElMessage.error('请求的资源不存在')
          break
        case 500:
          ElMessage.error('服务器内部错误')
          break
        default:
          ElMessage.error('请求失败')
      }
    } else {
      ElMessage.error('网络连接失败')
    }
    return Promise.reject(error)
  }
)

// 处理登出逻辑
const handleLogout = () => {
  // 如果已经在处理登出，直接返回
  if (isRefreshing) return

  isRefreshing = true

  // 清除本地存储的信息
  localStorage.removeItem('Admin-Token')
  localStorage.removeItem('userInfo')

  // 如果不是登录页面，则跳转到登录页
  if (router.currentRoute.value.path !== '/login') {
    // 记录当前页面路径
    const redirect = router.currentRoute.value.fullPath
    
    // 使用 router.replace 而不是 router.push，避免在历史记录中留下重定向记录
    router.replace({
      path: '/login',
      query: { redirect }
    }).finally(() => {
      // 重置状态
      isRefreshing = false
      requests = []
    })
  } else {
    isRefreshing = false
  }
}

export default service 