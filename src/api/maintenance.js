import request from '@/utils/request'

// 获取维护记录列表
export function getMaintenanceRecords(params) {
  return request({
    url: '/maintenance/records',
    method: 'get',
    params
  })
}

// 获取维护记录详情
export function getMaintenanceRecordDetail(id) {
  return request({
    url: '/maintenance/records/detail',
    method: 'get',
    params: { id }
  })
}

// 新增维护记录
export function addMaintenanceRecord(data) {
  return request({
    url: '/maintenance/records/add',
    method: 'post',
    data
  })
}

// 更新维护记录
export function updateMaintenanceRecord(id, data) {
  return request({
    url: '/maintenance/records/update',
    method: 'post',
    data: {
      id,
      ...data
    }
  })
}

// 删除维护记录
export function deleteMaintenanceRecord(id) {
  return request({
    url: '/maintenance/records/delete',
    method: 'post',
    data: { id }
  })
}

// 完成维护
export function completeMaintenanceRecord(id, data) {
  return request({
    url: '/maintenance/records/complete',
    method: 'post',
    data: {
      id,
      ...data
    }
  })
}

// 获取维护统计数据
export function getMaintenanceStats(params) {
  return request({
    url: '/maintenance/stats',
    method: 'get',
    params
  })
}