import request from '@/utils/request'

// 获取设备列表
export function getEquipmentList(params) {
  return request({
    url: '/equipment',
    method: 'get',
    params
  })
}

// 获取设备详情
export function getEquipmentDetail(id) {
  return request({
    url: '/equipment/detail',
    method: 'get',
    params: { id }
  })
}

// 新增设备
export function addEquipment(data) {
  return request({
    url: '/equipment/add',
    method: 'post',
    data
  })
}

// 更新设备
export function updateEquipment(id, data) {
  return request({
    url: '/equipment/update',
    method: 'post',
    data: {
      id,
      ...data
    }
  })
}

// 删除设备
export function deleteEquipment(id) {
  return request({
    url: '/equipment/delete',
    method: 'post',
    data: { id }
  })
} 