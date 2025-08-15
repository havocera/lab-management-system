import request from '@/utils/request'

// 获取待审批的实验室预约
export function getPendingReservations() {
  return request({
    url: '/lab/pendingReservations',
    method: 'get'
  })
}

// 获取待审批的试剂申领
export function getPendingReagents() {
  return request({
    url: '/reagent/pendingRecords',
    method: 'get'
  })
}

// 标记通知为已读
export function markAsRead(type, ids) {
  return request({
    url: `/notification/mark-read`,
    method: 'post',
    data: {
      type,
      ids
    }
  })
}

// 获取通知统计
export function getNotificationStats() {
  return request({
    url: '/notification/stats',
    method: 'get'
  })
}