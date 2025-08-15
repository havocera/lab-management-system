import request from '@/utils/request'

/**
 * 创建使用记录
 */
export function createUsageRecord(data) {
  return request({
    url: '/reservation/usage-record/create',
    method: 'post',
    data
  })
}

/**
 * 获取使用记录
 */
export function getUsageRecord(params) {
  return request({
    url: '/reservation/usage-record/get',
    method: 'get',
    params
  })
}

/**
 * 获取使用记录列表
 */
export function getUsageRecords(params) {
  return request({
    url: '/reservation/usage-record/list',
    method: 'get',
    params
  })
}