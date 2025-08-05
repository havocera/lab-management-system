import request from '@/utils/request'

// 获取试剂列表
export function getReagentList(params) {
  return request({
    url: '/reagent',
    method: 'get',
    params
  })
}

// 获取试剂详情
export function getReagentDetail(id) {
  return request({
    url: '/reagent/detail',
    method: 'get',
    params: { id }
  })
}

// 新增试剂
export function addReagent(data) {
  return request({
    url: '/reagent/add',
    method: 'post',
    data
  })
}

// 更新试剂
export function updateReagent(id, data) {
  return request({
    url: '/reagent/update',
    method: 'post',
    data: {
      id,
      ...data
    }
  })
}

// 删除试剂
export function deleteReagent(id) {
  return request({
    url: '/reagent/delete',
    method: 'post',
    data: { id }
  })
}

// 试剂出入库
export function reagentInOut(data) {
  return request({
    url: '/reagent/in-out',
    method: 'post',
    data
  })
}

// 获取试剂使用记录
export function getReagentRecords(params) {
  return request({
    url: '/reagent/records',
    method: 'get',
    params
  })
}

// 上传试剂图片
export function uploadReagentImage(data) {
  return request({
    url: '/upload/reagent',
    method: 'post',
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    data
  })
}

/**
 * 审核试剂出库申请
 * @param {Object} data 审核数据
 * @returns {Promise}
 */
export function approveReagentOutbound(data) {
  return request({
    url: '/reagent/approve',
    method: 'post',
    data
  })
} 