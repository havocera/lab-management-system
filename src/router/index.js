import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/user'

// 白名单路由
const whiteList = ['/login']

// 静态路由
export const constantRoutes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: {
      title: '登录'
    }
  },
  {
    path: '/',
    component: () => import('../layouts/AdminLayout.vue'),
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('../views/DashboardView.vue'),
        meta: {
          requiresAuth: true,
          title: '仪表盘',
          icon: 'i-carbon-dashboard'
        }
      },
      {
        path: 'labs',
        name: 'labs',
        component: () => import('../views/LabListView.vue'),
        meta: {
          requiresAuth: true,
          title: '实验室管理',
          icon: 'i-carbon-chemistry'
        }
      },
      {
        path: 'equipment',
        name: 'equipment',
        component: () => import('../views/EquipmentListView.vue'),
        meta: {
          requiresAuth: true,
          title: '设备管理',
          icon: 'i-carbon-tool-box'
        }
      },
      {
        path: 'reagents',
        name: 'reagents',
        component: () => import('../views/ReagentListView.vue'),
        meta: {
          requiresAuth: true,
          title: '试剂管理',
          icon: 'i-carbon-move'
        }
      },
      {
        path: 'reagent/records',
        name: 'reagent_records',
        component: () => import('../views/ReagentRecordView.vue'),
        meta: {
          requiresAuth: true,
          title: '试剂使用记录',
          icon: 'i-carbon-document'
        }
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('../views/UserListView.vue'),
        meta: {
          requiresAuth: true,
          title: '用户管理',
          icon: 'i-carbon-user-multiple'
        }
      },
      {
        path: 'roles',
        name: 'roles',
        component: () => import('../views/RoleListView.vue'),
        meta: {
          requiresAuth: true,
          title: '角色管理',
          icon: 'i-carbon-user-role'
        }
      },
      {
        path: 'permissions',
        name: 'permissions',
        component: () => import('../views/PermissionListView.vue'),
        meta: {
          requiresAuth: true,
          title: '权限管理',
          icon: 'i-carbon-security'
        }
      },
      {
        path: 'lab/reservation',
        name: 'LabReservation',
        component: () => import('@/views/LabReservationView.vue'),
        meta: {
          requiresAuth: true,
          title: '实验室预约',
          icon: 'i-carbon-calendar'
        }
      },
      {
        path: '/reagent-usage',
        name: 'ReagentUsage',
        component: () => import('@/views/ReagentUsageView.vue'),
        meta: {
          title: '试剂领用',
          icon: 'i-carbon-box'
        }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes: constantRoutes
})

// 路由守卫
router.beforeEach(async (to, from, next) => {
  // 设置页面标题
  document.title = to.meta.title ? `${to.meta.title} - 实验室管理系统` : '实验室管理系统'
  
  // 白名单路由直接放行
  if (whiteList.includes(to.path)) {
    next()
    return
  }
  
  const userStore = useUserStore()
  const token = userStore.token
  
  if (token) {
    if (to.path === '/login') {
      // 已登录，重定向到首页
      next({ path: '/' })
    } else {
      // 已登录，检查是否有用户信息
      if (userStore.userInfo) {
        next()
      } else {
        try {
          // 获取用户信息
          const res = await userStore.getUserInfoAction()
          if (res.code === 0) {
            next()
          } else {
            // 获取用户信息失败，清除token并跳转到登录页
            userStore.logout()
            next(`/login?redirect=${to.path}`)
          }
        } catch (error) {
          // 获取用户信息失败，清除token并跳转到登录页
          userStore.logout()
          next(`/login?redirect=${to.path}`)
        }
      }
    }
  } else {
    // 未登录，跳转到登录页
    next(`/login?redirect=${to.path}`)
  }
})

export default router
