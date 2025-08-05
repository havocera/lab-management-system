import router from './router'
import { useUserStore } from '@/stores/user'
import { ElMessage } from 'element-plus'

// 白名单路由
const whiteList = ['/login', '/register', '/404', '/403']

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore()
  const hasToken = userStore.token
  
  if (hasToken) {
    if (to.path === '/login') {
      // 已登录且要跳转的页面是登录页
      next({ path: '/' })
    } else {
      // 已登录访问其他页面
      if (userStore.userInfo) {
        // 已有用户信息，直接放行
        next()
      } else {
        try {
          // 获取用户信息
          await userStore.getUserInfo()
          next()
        } catch (error) {
          // 获取用户信息失败，可能是token过期
          userStore.logout()
          ElMessage.error('登录状态已过期，请重新登录')
          next(`/login?redirect=${to.path}`)
        }
      }
    }
  } else {
    // 未登录
    if (whiteList.includes(to.path)) {
      // 在免登录白名单中，直接进入
      next()
    } else {
      // 其他没有访问权限的页面将被重定向到登录页面
      next(`/login?redirect=${to.path}`)
    }
  }
}) 