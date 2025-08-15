import request from '@/utils/request'

// 用户登录
export function login(data) {
  return request({
    url: '/user/login',
    method: 'post',
    data
  })
}

// 用户注册
export function register(data) {
  return request({
    url: '/user/register',
    method: 'post',
    data
  })
}

// 获取用户信息
export function getUserInfo() {
  return request({
    url: '/user/info',
    method: 'get'
  })
}

// 修改密码
export function changePassword(data) {
  return request({
    url: '/user/change-password',
    method: 'post',
    data
  })
}

// 获取用户列表
export function getUserList(params) {
  return request({
    url: '/user/list',
    method: 'get',
    params
  })
}

// 创建用户
export function createUser(data) {
  return request({
    url: '/user/create',
    method: 'post',
    data
  })
}

// 更新用户
export function updateUser(data) {
  return request({
    url: '/user/update',
    method: 'post',
    data
  })
}

// 更新用户状态
export function updateUserStatus(data) {
  return request({
    url: '/user/update-status',
    method: 'post',
    data
  })
}

// 重置用户密码
export function resetUserPassword(data) {
  return request({
    url: '/user/reset-password',
    method: 'post',
    data
  })
}

// 删除用户
export function deleteUser(data) {
  return request({
    url: '/user/delete',
    method: 'post',
    data
  })
}

// 获取登录日志
export function getLoginLogs(params) {
  return request({
    url: '/user/login-logs',
    method: 'get',
    params
  })
}

// 获取用户菜单
export function getUserMenus() {
  return request({
    url: '/user/menus',
    method: 'get'
  })
}

// 更新用户信息
export function updateUserInfo(data) {
  return request({
    url: '/user/update',
    method: 'post',
    data
  })
} 