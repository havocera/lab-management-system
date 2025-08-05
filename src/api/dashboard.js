import request from '@/utils/request'

// 获取仪表盘统计数据
export function getDashboardStatistics() {
  return request({
    url: '/dashboard/statistics',
    method: 'get'
  })
}

// 获取今日实验室使用情况
export function getTodayLabUsage() {
  return request({
    url: '/dashboard/today-lab-usage',
    method: 'get'
  })
}

// 获取明日预约情况
export function getTomorrowReservations() {
  return request({
    url: '/dashboard/tomorrow-reservations',
    method: 'get'
  })
}

// 获取待审批试剂申领
export function getPendingReagents() {
  return request({
    url: '/dashboard/pending-reagents',
    method: 'get'
  })
}

// 审批通过试剂申领
export function approveReagent(id) {
  return request({
    url: '/dashboard/approve-reagent',
    method: 'post',
    data: { id }
  })
}

// 拒绝试剂申领
export function rejectReagent(id, reason) {
  return request({
    url: '/dashboard/reject-reagent',
    method: 'post',
    data: { id, reason }
  })
}

// 获取设备使用趋势
export function getEquipmentTrend(params) {
  return request({
    url: '/dashboard/equipment-trend',
    method: 'get',
    params
  })
}

// 获取实验室分布
export function getLabDistribution(params) {
  return request({
    url: '/dashboard/lab-distribution',
    method: 'get',
    params
  })
}

// 获取最近动态
export function getRecentActivities(params) {
  return request({
    url: '/dashboard/recent-activities',
    method: 'get',
    params
  })
} 