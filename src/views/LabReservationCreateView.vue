<template>
  <div class="reservation-create-page">
    <div class="header">
      <el-button @click="$router.back()" icon="ArrowLeft" type="text">返回</el-button>
      <h2 class="title">预约实验室</h2>
    </div>

    <div class="content">
      <div class="lab-info-card">
        <h3>实验室信息</h3>
        <div class="info-item">
          <span class="label">实验室名称：</span>
          <span class="value">{{ labInfo.name }}</span>
        </div>
        <div class="info-item">
          <span class="label">位置：</span>
          <span class="value">{{ labInfo.location }}</span>
        </div>
        <div class="info-item">
          <span class="label">容量：</span>
          <span class="value">{{ labInfo.capacity }}人</span>
        </div>
      </div>

      <div class="reservation-form">
        <el-form
          ref="formRef"
          :model="form"
          :rules="rules"
          label-width="100px"
        >
          <el-form-item label="预约时间" prop="timeSlot">
            <div class="week-schedule">
              <div class="schedule-header">
                <div class="time-header">时间</div>
                <div v-for="day in sevenDays" :key="day.date" class="day-header">
                  <div class="day-name">{{ day.dayName }}</div>
                  <div class="day-date">{{ day.monthDay }}</div>
                </div>
              </div>
              
              <div class="schedule-body">
                <div v-for="hour in allHours" :key="hour" class="time-row">
                  <div class="time-label">
                    {{ formatHour(hour) }}:00
                  </div>
                  <div v-for="day in sevenDays" :key="`${day.date}-${hour}`" class="time-cell">
                    <div
                      :class="['time-slot', {
                        'selected': isTimeSlotSelected(day.date, hour),
                        'occupied': isTimeSlotOccupied(day.date, hour),
                        'disabled': day.disabled
                      }]"
                      @click="handleTimeSlotClick(day, hour)"
                    >
                      <span v-if="isTimeSlotOccupied(day.date, hour)" class="occupied-text">已预约</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="selectedSlots.length > 0" class="selected-time-info">
              <div class="info-title">已选择的时间段：</div>
              <div class="selected-items">
                <div v-for="slot in selectedSlots" :key="`${slot.date}-${slot.hour}`" class="selected-item">
                  {{ formatDate(slot.date) }} {{ formatHour(slot.hour) }}:00-{{ formatHour(slot.hour + 1) }}:00
                  <el-button @click="removeTimeSlot(slot.date, slot.hour)" type="text" size="small" icon="Close" />
                </div>
              </div>
            </div>
          </el-form-item>

          <el-form-item label="用途说明" prop="purpose">
            <el-input
              v-model="form.purpose"
              type="textarea"
              :rows="4"
              placeholder="请详细说明实验室使用用途"
              maxlength="200"
              show-word-limit
            />
          </el-form-item>

          <el-form-item>
            <el-button type="primary" @click="handleSubmit" :loading="submitting">
              提交预约
            </el-button>
            <el-button @click="$router.back()">取消</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { ArrowLeft, ArrowRight } from '@element-plus/icons-vue'
import { getLabDetail } from '@/api/lab'
import { createReservation, getReservationList } from '@/api/reservation'

const router = useRouter()
const route = useRoute()

const formRef = ref(null)
const submitting = ref(false)
const labInfo = ref({})
const existingReservations = ref([])
const selectedSlots = ref([])

const form = reactive({
  lab_id: route.query.lab_id || '',
  timeSlot: '',
  purpose: ''
})

const rules = {
  timeSlot: [
    { required: true, message: '请选择时间段', trigger: 'change' }
  ],
  purpose: [
    { required: true, message: '请输入用途说明', trigger: 'blur' },
    { min: 5, max: 200, message: '长度在 5 到 200 个字符', trigger: 'blur' }
  ]
}

// 所有可用时间段 (8:00-22:00)
const allHours = Array.from({ length: 14 }, (_, i) => i + 8)

// 计算从今天开始的七天
const sevenDays = computed(() => {
  const days = []
  const dayNames = ['周日', '周一', '周二', '周三', '周四', '周五', '周六']
  
  for (let i = 0; i < 7; i++) {
    const date = new Date()
    date.setDate(date.getDate() + i)
    
    const today = new Date()
    const isToday = date.toDateString() === today.toDateString()
    const isPast = date < today && !isToday
    
    days.push({
      date: date.toISOString().split('T')[0],
      dayName: i === 0 ? '今天' : i === 1 ? '明天' : dayNames[date.getDay()],
      monthDay: `${date.getMonth() + 1}/${date.getDate()}`,
      disabled: isPast
    })
  }
  
  return days
})

// 格式化小时
const formatHour = (hour) => {
  return hour.toString().padStart(2, '0')
}

// 格式化日期
const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return `${date.getMonth() + 1}月${date.getDate()}日`
}

// 处理时间段点击
const handleTimeSlotClick = (day, hour) => {
  // 如果是已禁用的日期或已占用的时间段，则不允许点击
  if (day.disabled || isTimeSlotOccupied(day.date, hour)) {
    return
  }
  
  // 否则正常切换选择状态
  toggleTimeSlot(day.date, hour)
}

// 切换时间段选择
const toggleTimeSlot = (date, hour) => {
  const index = selectedSlots.value.findIndex(slot => slot.date === date && slot.hour === hour)
  
  if (index > -1) {
    selectedSlots.value.splice(index, 1)
  } else {
    selectedSlots.value.push({ date, hour })
  }
  
  // 更新表单值
  form.timeSlot = selectedSlots.value.length > 0 ? 'selected' : ''
}

// 移除时间段
const removeTimeSlot = (date, hour) => {
  const index = selectedSlots.value.findIndex(slot => slot.date === date && slot.hour === hour)
  if (index > -1) {
    selectedSlots.value.splice(index, 1)
  }
  form.timeSlot = selectedSlots.value.length > 0 ? 'selected' : ''
}

// 检查时间段是否被选中
const isTimeSlotSelected = (date, hour) => {
  return selectedSlots.value.some(slot => slot.date === date && slot.hour === hour)
}

// 检查时间段是否被占用
const isTimeSlotOccupied = (date, hour) => {
  return existingReservations.value.some(reservation => {
    // 确保时间格式正确
    const reservationDate = reservation.start_time.split(' ')[0]
    if (reservationDate !== date) return false
    
    const startTime = reservation.start_time.split(' ')[1]
    const endTime = reservation.end_time.split(' ')[1]
    
    const startHour = parseInt(startTime.split(':')[0])
    const endHour = parseInt(endTime.split(':')[0])
    
    // 检查当前小时是否在预约时间范围内
    return hour >= startHour && hour < endHour
  })
}

// 获取实验室详情
const getLabInfo = async () => {
  if (!form.lab_id) {
    ElMessage.error('缺少实验室ID参数')
    router.back()
    return
  }
  
  try {
    const res = await getLabDetail(form.lab_id)
    if (res.code === 0 && res.data) {
      labInfo.value = res.data
    } else {
      throw new Error(res.msg || '获取实验室信息失败')
    }
  } catch (error) {
    console.error('获取实验室信息失败：', error)
    ElMessage.error('获取实验室信息失败')
    router.back()
  }
}

// 加载预约数据
const loadReservations = async () => {
  if (!form.lab_id) return
  
  try {
    const today = new Date()
    const endDate = new Date()
    endDate.setDate(today.getDate() + 6)
    endDate.setHours(23, 59, 59, 999)
    
    const res = await getReservationList({
      lab_id: form.lab_id,
      start_time: today.toISOString().slice(0, 19).replace('T', ' '),
      end_time: endDate.toISOString().slice(0, 19).replace('T', ' ')
    })
    
    if (res.code === 0 && res.data) {
      // 只显示已批准和待审核的预约为占用状态
      existingReservations.value = (res.data.list || []).filter(reservation => 
        reservation.status === 'approved' || reservation.status === 'pending'
      )
      console.log('加载的预约数据:', existingReservations.value)
    }
  } catch (error) {
    console.error('获取预约数据失败：', error)
  }
}

// 提交预约
const handleSubmit = async () => {
  if (!formRef.value) return
  
  try {
    await formRef.value.validate()
    
    if (selectedSlots.value.length === 0) {
      ElMessage.error('请选择至少一个时间段')
      return
    }
    
    // 检查选中的时间段是否有已被占用的
    const hasOccupiedSlot = selectedSlots.value.some(slot => isTimeSlotOccupied(slot.date, slot.hour))
    if (hasOccupiedSlot) {
      ElMessage.error('选中的时间段中包含已被占用的时段，请重新选择')
      return
    }
    
    submitting.value = true
    
    // 按日期分组预约时间段
    const dateGroups = {}
    selectedSlots.value.forEach(slot => {
      if (!dateGroups[slot.date]) {
        dateGroups[slot.date] = []
      }
      dateGroups[slot.date].push(slot.hour)
    })
    
    // 为每个日期创建预约记录
    const reservationPromises = []
    
    for (const [date, hours] of Object.entries(dateGroups)) {
      const sortedHours = hours.sort((a, b) => a - b)
      
      // 检查是否为连续时间段，如果不连续则分别创建预约
      let currentStart = sortedHours[0]
      let currentEnd = sortedHours[0]
      
      for (let i = 1; i <= sortedHours.length; i++) {
        if (i === sortedHours.length || sortedHours[i] !== currentEnd + 1) {
          // 创建一个预约记录
          const startTime = `${date} ${formatHour(currentStart)}:00:00`
          const endTime = `${date} ${formatHour(currentEnd + 1)}:00:00`
          
          const reservationData = {
            lab_id: form.lab_id,
            start_time: startTime,
            end_time: endTime,
            purpose: form.purpose
          }
          
          reservationPromises.push(createReservation(reservationData))
          
          if (i < sortedHours.length) {
            currentStart = sortedHours[i]
            currentEnd = sortedHours[i]
          }
        } else {
          currentEnd = sortedHours[i]
        }
      }
    }
    
    // 并发提交所有预约
    const results = await Promise.all(reservationPromises)
    
    // 检查所有预约是否成功
    const allSuccess = results.every(res => res.code === 0)
    
    if (allSuccess) {
      ElMessage.success('预约提交成功，请等待审核')
      router.push('/lab/reservation')
    } else {
      const failedCount = results.filter(res => res.code !== 0).length
      ElMessage.error(`${failedCount} 个预约提交失败，请重试`)
    }
  } catch (error) {
    if (typeof error === 'string') return // 表单验证失败
    console.error('提交预约失败：', error)
    ElMessage.error('提交预约失败：' + (error.message || '未知错误'))
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  getLabInfo()
  loadReservations()
})
</script>

<style scoped>
.reservation-create-page {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #ebeef5;
}

.title {
  margin: 0 0 0 15px;
  font-size: 24px;
  font-weight: 600;
  color: #303133;
}

.content {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 30px;
}

.lab-info-card {
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 20px;
  height: fit-content;
}

.lab-info-card h3 {
  margin: 0 0 15px 0;
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.info-item {
  display: flex;
  margin-bottom: 12px;
}

.info-item .label {
  font-weight: 500;
  color: #606266;
  min-width: 100px;
}

.info-item .value {
  color: #303133;
}

.reservation-form {
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 20px;
}

.week-schedule {
  border: 1px solid #ebeef5;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
}

.schedule-header {
  display: grid;
  grid-template-columns: 100px repeat(7, 1fr);
  background: #f5f7fa;
  border-bottom: 2px solid #ebeef5;
}

.time-header {
  padding: 15px 10px;
  font-weight: 600;
  text-align: center;
  border-right: 1px solid #ebeef5;
  background: #e6f0ff;
}

.day-header {
  padding: 15px 10px;
  text-align: center;
  border-right: 1px solid #ebeef5;
  font-weight: 500;
}

.day-header:last-child {
  border-right: none;
}

.day-name {
  font-size: 14px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 4px;
}

.day-date {
  font-size: 12px;
  color: #606266;
}

.schedule-body {
  max-height: 400px;
  overflow-y: auto;
}

.time-row {
  display: grid;
  grid-template-columns: 100px repeat(7, 1fr);
  border-bottom: 1px solid #f5f7fa;
}

.time-row:last-child {
  border-bottom: none;
}

.time-label {
  padding: 10px;
  font-weight: 500;
  color: #606266;
  background: #fafbfc;
  border-right: 1px solid #ebeef5;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.time-cell {
  padding: 4px;
  border-right: 1px solid #f5f7fa;
}

.time-cell:last-child {
  border-right: none;
}

.time-slot {
  width: 100%;
  height: 40px;
  border: 2px solid #f0f0f0;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.time-slot:hover:not(.occupied):not(.disabled) {
  border-color: #409eff;
  background: #ecf5ff;
}

.time-slot.selected {
  background: #409eff;
  border-color: #409eff;
  color: #fff;
}

.time-slot.occupied {
  background: #f5f5f5;
  border-color: #e4e7ed;
  cursor: not-allowed;
  position: relative;
}

.time-slot.occupied::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 2px,
    #e4e7ed 2px,
    #e4e7ed 4px
  );
  border-radius: 4px;
  pointer-events: none;
}

.time-slot.disabled {
  background: #f9f9f9;
  border-color: #e4e7ed;
  cursor: not-allowed;
  opacity: 0.6;
}

.occupied-text {
  font-size: 10px;
  color: #909399;
  font-weight: 500;
  z-index: 1;
  position: relative;
  background: rgba(245, 245, 245, 0.9);
  padding: 1px 3px;
  border-radius: 2px;
}

.selected-time-info {
  margin-top: 20px;
  padding: 15px;
  background: #f0f9ff;
  border: 1px solid #b3d8ff;
  border-radius: 8px;
}

.info-title {
  font-weight: 600;
  color: #409eff;
  margin-bottom: 10px;
}

.selected-items {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.selected-item {
  display: flex;
  align-items: center;
  padding: 6px 12px;
  background: #409eff;
  color: #fff;
  border-radius: 16px;
  font-size: 12px;
  gap: 8px;
}

.selected-item .el-button {
  color: #fff;
  font-size: 12px;
  padding: 2px;
}

.selected-item .el-button:hover {
  color: #f56c6c;
}

@media (max-width: 768px) {
  .content {
    grid-template-columns: 1fr;
  }
  
  .schedule-header,
  .time-row {
    grid-template-columns: 80px repeat(7, minmax(50px, 1fr));
  }
  
  .time-header,
  .day-header {
    padding: 10px 5px;
    font-size: 12px;
  }
  
  .time-label {
    padding: 8px 5px;
    font-size: 11px;
  }
  
  .time-cell {
    padding: 2px;
  }
  
  .time-slot {
    height: 35px;
    font-size: 10px;
  }
  
  .selected-items {
    flex-direction: column;
    gap: 5px;
  }
  
  .selected-item {
    font-size: 11px;
    padding: 4px 8px;
  }
}

@media (max-width: 480px) {
  .schedule-header,
  .time-row {
    grid-template-columns: 60px repeat(7, minmax(40px, 1fr));
  }
  
  .time-header,
  .day-header {
    padding: 8px 3px;
    font-size: 10px;
  }
  
  .day-name {
    font-size: 10px;
    margin-bottom: 2px;
  }
  
  .day-date {
    font-size: 9px;
  }
  
  .time-label {
    padding: 6px 3px;
    font-size: 9px;
  }
  
  .time-slot {
    height: 30px;
    font-size: 8px;
  }
  
  .occupied-text {
    font-size: 8px;
  }
}
</style>