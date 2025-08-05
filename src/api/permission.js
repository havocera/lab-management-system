import request from '@/utils/request'

// 获取权限列表
export function getPermissionList() {
  return request({
    url: '/permission/list',
    method: 'get'
  })
}

// 获取权限树
export function getPermissionTree() {
  return request({
    url: '/permission/tree',
    method: 'get'
  })
}

// 创建权限
export function createPermission(data) {
  return request({
    url: '/permission/create',
    method: 'post',
    data
  })
}

// 更新权限
export function updatePermission(data) {
  return request({
    url: '/permission/update',
    method: 'post',
    data
  })
}

// 更新权限状态
export function updatePermissionStatus(data) {
  return request({
    url: '/permission/status',
    method: 'post',
    data
  })
}

// 删除权限
export function deletePermission(data) {
  return request({
    url: '/permission/delete',
    method: 'post',
    data
  })
} 