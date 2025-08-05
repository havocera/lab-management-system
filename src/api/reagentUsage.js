import request from '@/utils/request'

// 获取试剂列表
export function getReagentList(params) {
  return request({
    url: '/reagent',
    method: 'get',
    params
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

// 申请使用试剂
export function applyReagentUsage(data) {
  return request({
    url: '/reagent/in-out',
    method: 'post',
    data: {
      ...data,
      type: 'out' // 出库类型
    }
  })
} 