import { defineStore } from 'pinia'
import { ref } from 'vue'
import { login, getUserInfo, getUserMenus } from '@/api/user'
import { getToken, setToken, removeToken } from '@/utils/auth'

export const useUserStore = defineStore('user', () => {
  // 状态
  const token = ref(getToken() || '')
  const userInfo = ref(null)
  const roles = ref([])
  const permissions = ref([])
  const menus = ref([])

  // 登录
  async function loginAction(userInfo) {
    try {
      const { username, password } = userInfo
      const res = await login({ username: username.trim(), password })
      if (res.code === 0) {
        console.log(res.data)
        const { token: newToken, user, roles: userRoles, permissions: userPermissions } = res.data
        token.value = newToken
        userInfo.value = user
        roles.value = userRoles
        permissions.value = userPermissions
        setToken(newToken)
        return res
      }
      return res
    } catch (error) {
      console.error('登录失败:', error)
      return { code: 1, msg: '登录失败' }
    }
  }

  // 获取用户信息
  async function getUserInfoAction() {
    try {
      const res = await getUserInfo()
      if (res.code === 0) {
        const { user, roles: userRoles, permissions: userPermissions } = res.data
        userInfo.value = user
        roles.value = userRoles
        permissions.value = userPermissions
        return res
      }
      return res
    } catch (error) {
      console.error('获取用户信息失败:', error)
      return { code: 1, msg: '获取用户信息失败' }
    }
  }

  // 获取用户菜单
  async function getUserMenusAction() {
    try {
      const res = await getUserMenus()
      if (res.code === 0) {
        menus.value = res.data
        return res
      }
      return res
    } catch (error) {
      console.error('获取用户菜单失败:', error)
      // 如果API获取失败，使用默认菜单
      setDefaultMenus()
      return { code: 0, msg: '使用默认菜单' }
    }
  }

  // 设置默认菜单
  function setDefaultMenus() {
    menus.value = [
      {
        id: 1,
        name: '仪表盘',
        path: '/dashboard',
        icon: 'i-carbon-dashboard',
        children: []
      },
      {
        id: 2,
        name: '实验室管理',
        path: '/labs',
        icon: 'i-carbon-chemistry',
        children: []
      },
      {
        id: 3,
        name: '设备管理',
        path: '/equipment',
        icon: 'i-carbon-tool-box',
        children: []
      },
      {
        id: 4,
        name: '试剂管理',
        path: '/reagents',
        icon: 'i-carbon-move',
        children: [
          {
            id: 41,
            name: '试剂列表',
            path: '/reagents',
            icon: 'i-carbon-move'
          },
          {
            id: 42,
            name: '使用记录',
            path: '/reagent/records',
            icon: 'i-carbon-document'
          },
          {
            id: 43,
            name: '试剂领用',
            path: '/reagent-usage',
            icon: 'i-carbon-box'
          }
        ]
      },
      {
        id: 5,
        name: '预约管理',
        path: '/lab/reservation',
        icon: 'i-carbon-calendar',
        children: []
      },
      {
        id: 6,
        name: '系统管理',
        path: '/system',
        icon: 'i-carbon-settings',
        children: [
          {
            id: 61,
            name: '用户管理',
            path: '/users',
            icon: 'i-carbon-user-multiple'
          },
          {
            id: 62,
            name: '角色管理',
            path: '/roles',
            icon: 'i-carbon-user-role'
          },
          {
            id: 63,
            name: '权限管理',
            path: '/permissions',
            icon: 'i-carbon-security'
          }
        ]
      }
    ]
  }

  // 退出登录
  function logout() {
    token.value = ''
    userInfo.value = null
    roles.value = []
    permissions.value = []
    menus.value = []
    removeToken()
  }

  return {
    // 状态
    token,
    userInfo,
    roles,
    permissions,
    menus,
    // 方法
    loginAction,
    getUserInfoAction,
    getUserMenusAction,
    setDefaultMenus,
    logout
  }
}) 