import request from '@/utils/request'

// 创建预约
export function createReservation(data) {
  return request({
    url: '/reservation/create',
    method: 'post',
    data
  })
}

// 获取预约列表
export function getReservationList(params) {
  return request({
    url: '/reservation/list',
    method: 'get',
    params
  })
}

// 获取我的预约
export function getMyReservations(params) {
  return request({
    url: '/reservation/my',
    method: 'get',
    params
  })
}

// 取消预约
export function cancelReservation(id) {
  return request({
    url: '/reservation/cancel',
    method: 'post',
    data: { id }
  })
}

// 审核预约
export function reviewReservation(data) {
  return request({
    url: '/reservation/review',
    method: 'post',
    data
  })
}

// 获取明日预约统计
export function getTomorrowReservations() {
  return request({
    url: '/reservation/tomorrow-count',
    method: 'get'
  })
}