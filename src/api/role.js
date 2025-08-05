import request from '@/utils/request'

// 获取角色列表
export function getRoleList() {
  return request({
    url: '/role/list',
    method: 'get'
  })
}

// 创建角色
export function createRole(data) {
  return request({
    url: '/role/create',
    method: 'post',
    data
  })
}

// 更新角色
export function updateRole(data) {
  return request({
    url: '/role/update',
    method: 'post',
    data
  })
}

// 更新角色状态
export function updateRoleStatus(data) {
  return request({
    url: '/role/status',
    method: 'post',
    data
  })
}

// 获取角色权限
export function getRolePermissions(roleId) {
  return request({
    url: '/role/permissions',
    method: 'get',
    params: { role_id: roleId }
  })
}

// 更新角色权限
export function updateRolePermissions(data) {
  return request({
    url: '/role/updatePermissions',
    method: 'post',
    data
  })
} 