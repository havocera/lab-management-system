import request from '@/utils/request'

// 获取实验室列表
export function getLabList(params) {
  return request({
    url: '/lab',
    method: 'get',
    params
  }).then(res => {
    // 确保返回的数据格式正确
    if (res.code === 0 && Array.isArray(res.data)) {
      // 如果返回的是数组，转换为标准格式
      return {
        code: 0,
        msg: 'success',
        data: {
          list: res.data,
          total: res.data.length
        }
      }
    }
    return res
  })
}

// 获取实验室详情
export function getLabDetail(id) {
  return request({
    url: '/lab/detail',
    method: 'get',
    params: { id }
  })
}

// 新增实验室
export function addLab(data) {
  return request({
    url: '/lab/add',
    method: 'post',
    data
  })
}

// 更新实验室
export function updateLab(id, data) {
  return request({
    url: '/lab/update',
    method: 'post',
    data: {
      id,
      ...data
    }
  })
}

// 删除实验室
export function deleteLab(id) {
  return request({
    url: '/lab/delete',
    method: 'post',
    data: { id }
  })
}

// 获取用户列表（用于负责人选择）
export function getUsers() {
  return request({
    url: '/user/list',
    method: 'get'
  })
}

// 更换实验室负责人
export function changeManager(labId, managerId) {
  return request({
    url: '/lab/changeManager',
    method: 'post',
    data: {
      lab_id: labId,
      manager_id: managerId
    }
  })
} 