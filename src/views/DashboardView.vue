<template>
  <div class="space-y-4">
    <!-- 欢迎信息和天气 -->
    <div class="grid grid-cols-3 gap-4">
      <el-card class="col-span-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-medium text-gray-900 dark:text-gray-100">早安，{{ userStore.userInfo?.name || '管理员' }}，今天又是充满活力的一天！</h2>
            <div class="flex items-center mt-2">
              <div class="text-4xl i-carbon-sun mr-2" />
              <div>
                <p class="text-gray-500 dark:text-gray-400">{{ weather.temperature }}℃</p>
                <p class="text-gray-500 dark:text-gray-400">{{ weather.description }}</p>
              </div>
            </div>
          </div>
          <el-button type="primary" @click="refreshData">
            <div class="i-carbon-refresh mr-2" />
            刷新
          </el-button>
        </div>
      </el-card>
      <el-card>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 dark:text-gray-400">今日日期</p>
            <p class="text-2xl font-medium text-gray-900 dark:text-gray-100">{{ currentDate }}</p>
          </div>
          <div class="text-4xl i-carbon-calendar" />
        </div>
      </el-card>
    </div>

    <!-- 数据卡片 -->
    <div class="grid grid-cols-4 gap-4">
      <el-card class="bg-[#e755ba] hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center text-white">
          <div class="text-4xl i-carbon-chart-line-data" />
          <div class="ml-4">
            <div class="text-lg">今日实验室使用</div>
            <div class="text-2xl font-medium mt-1">{{ statistics.todayLabUsage }}</div>
          </div>
        </div>
      </el-card>
      <el-card class="bg-[#8b5cf6] hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center text-white">
          <div class="text-4xl i-carbon-calendar" />
          <div class="ml-4">
            <div class="text-lg">明日预约</div>
            <div class="text-2xl font-medium mt-1">{{ statistics.tomorrowReservations }}</div>
          </div>
        </div>
      </el-card>
      <el-card class="bg-[#0ea5e9] hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center text-white">
          <div class="text-4xl i-carbon-download" />
          <div class="ml-4">
            <div class="text-lg">待审批试剂</div>
            <div class="text-2xl font-medium mt-1">{{ statistics.pendingReagents }}</div>
          </div>
        </div>
      </el-card>
      <el-card class="bg-[#f59e0b] hover:shadow-lg transition-shadow cursor-pointer">
        <div class="flex items-center text-white">
          <div class="text-4xl i-carbon-user-multiple" />
          <div class="ml-4">
            <div class="text-lg">在线用户</div>
            <div class="text-2xl font-medium mt-1">{{ statistics.onlineUsers }}</div>
          </div>
        </div>
      </el-card>
    </div>

    <!-- 今日实验室使用和明日预约 -->
    <div class="grid grid-cols-2 gap-4">
      <el-card>
        <template #header>
          <div class="flex items-center justify-between">
            <span class="text-gray-900 dark:text-gray-100">今日实验室使用</span>
            <el-button type="primary" link>查看详情</el-button>
          </div>
        </template>
        <div class="space-y-4">
          <div v-for="lab in todayLabUsage" :key="lab.id" class="flex items-center justify-between">
            <div class="flex items-center">
              <el-avatar :src="lab.userAvatar" />
              <div class="ml-3">
                <div class="text-gray-900 dark:text-gray-100">{{ lab.name }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ lab.userName }}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-gray-900 dark:text-gray-100">{{ lab.startTime }} - {{ lab.endTime }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">{{ lab.status }}</div>
            </div>
          </div>
        </div>
      </el-card>
      <el-card>
        <template #header>
          <div class="flex items-center justify-between">
            <span class="text-gray-900 dark:text-gray-100">明日预约</span>
            <el-button type="primary" link>查看详情</el-button>
          </div>
        </template>
        <div class="space-y-4">
          <div v-for="lab in tomorrowReservations" :key="lab.id" class="flex items-center justify-between">
            <div class="flex items-center">
              <el-avatar :src="lab.userAvatar" />
              <div class="ml-3">
                <div class="text-gray-900 dark:text-gray-100">{{ lab.name }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ lab.userName }}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-gray-900 dark:text-gray-100">{{ lab.startTime }} - {{ lab.endTime }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">{{ lab.status }}</div>
            </div>
          </div>
        </div>
      </el-card>
    </div>

    <!-- 待审批试剂申领 -->
    <el-card>
      <template #header>
        <div class="flex items-center justify-between">
          <span class="text-gray-900 dark:text-gray-100">待审批试剂申领</span>
          <el-button type="primary" link>查看详情</el-button>
        </div>
      </template>
      <el-table :data="pendingReagents" style="width: 100%">
        <el-table-column prop="userName" label="申请人" width="120">
          <template #default="{ row }">
            <div class="flex items-center">
              <el-avatar :src="row.userAvatar" size="small" />
              <span class="ml-2">{{ row.userName }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="reagentName" label="试剂名称" />
        <el-table-column prop="amount" label="申请数量" width="100" />
        <el-table-column prop="unit" label="单位" width="80" />
        <el-table-column prop="purpose" label="用途" />
        <el-table-column prop="applyTime" label="申请时间" width="180" />
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button type="success" link @click="handleApproveReagent(row)">通过</el-button>
            <el-button type="danger" link @click="handleRejectReagent(row)">拒绝</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useUserStore } from '@/stores/user'
import { ElMessage, ElMessageBox, ElLoading } from 'element-plus'
import {
  getDashboardStatistics,
  getTodayLabUsage,
  getTomorrowReservations,
  getPendingReagents,
  approveReagent,
  rejectReagent
} from '@/api/dashboard'

const userStore = useUserStore()

// 当前日期
const currentDate = computed(() => {
  return new Date().toLocaleDateString('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    weekday: 'long'
  })
})

// 天气信息
const weather = ref({
  temperature: 25,
  description: '晴朗'
})

// 统计数据
const statistics = ref({
  todayLabUsage: 0,
  tomorrowReservations: 0,
  pendingReagents: 0,
  onlineUsers: 0
})

// 今日实验室使用
const todayLabUsage = ref([])

// 明日预约
const tomorrowReservations = ref([])

// 待审批试剂
const pendingReagents = ref([])

// 刷新数据
const refreshData = async () => {
  try {
    // 显示加载中
    const loadingInstance = ElLoading.service({
      text: '数据加载中...',
      background: 'rgba(0, 0, 0, 0.7)'
    })
    
    try {
      // 单独处理每个请求，不使用Promise.all
      await fetchStatistics()
      await fetchTodayLabUsage()
      await fetchTomorrowReservations()
      await fetchPendingReagents()
      
      ElMessage.success('数据刷新成功')
    } finally {
      // 确保无论成功或失败都关闭加载提示
      loadingInstance.close()
    }
  } catch (error) {
    console.error('刷新数据失败:', error)
    // 不再显示整体刷新失败的消息，而是让每个请求自己处理错误
  }
}

// 获取统计数据
const fetchStatistics = async () => {
  try {
    const res = await getDashboardStatistics()
    console.log('统计数据响应:', res)
    
    // 检查响应是否包含预期的数据结构
    if (res && res.code === 200 && res.data) {
      statistics.value = res.data
      return true
    } else {
      console.error('统计数据响应格式不正确:', res)
      throw new Error(res?.msg || '响应格式不正确')
    }
  } catch (error) {
    console.error('获取统计数据失败:', error)
    ElMessage.error(`获取统计数据失败: ${error.message || '未知错误'}`)
    return false
  }
}

// 获取今日实验室使用
const fetchTodayLabUsage = async () => {
  try {
    const res = await getTodayLabUsage()
    console.log('今日实验室使用响应:', res)
    
    // 检查响应是否包含预期的数据结构
    if (res && res.code === 200 && res.data) {
      todayLabUsage.value = res.data || []
      return true
    } else {
      console.error('今日实验室使用响应格式不正确:', res)
      throw new Error(res?.msg || '响应格式不正确')
    }
  } catch (error) {
    console.error('获取今日实验室使用失败:', error)
    ElMessage.error(`获取今日实验室使用失败: ${error.message || '未知错误'}`)
    return false
  }
}

// 获取明日预约
const fetchTomorrowReservations = async () => {
  try {
    const res = await getTomorrowReservations()
    console.log('明日预约响应:', res)
    
    // 检查响应是否包含预期的数据结构
    if (res && res.code === 200 && res.data) {
      tomorrowReservations.value = res.data || []
      return true
    } else {
      console.error('明日预约响应格式不正确:', res)
      throw new Error(res?.msg || '响应格式不正确')
    }
  } catch (error) {
    console.error('获取明日预约失败:', error)
    ElMessage.error(`获取明日预约失败: ${error.message || '未知错误'}`)
    return false
  }
}

// 获取待审批试剂
const fetchPendingReagents = async () => {
  try {
    const res = await getPendingReagents()
    console.log('待审批试剂响应:', res)
    
    // 检查响应是否包含预期的数据结构
    if (res && res.code === 200 && res.data) {
      pendingReagents.value = res.data || []
      return true
    } else {
      console.error('待审批试剂响应格式不正确:', res)
      throw new Error(res?.msg || '响应格式不正确')
    }
  } catch (error) {
    console.error('获取待审批试剂失败:', error)
    ElMessage.error(`获取待审批试剂失败: ${error.message || '未知错误'}`)
    return false
  }
}

// 审批试剂
const handleApproveReagent = async (row) => {
  try {
    await ElMessageBox.confirm('确定通过该试剂申领申请吗？', '提示', {
      type: 'warning'
    })
    await approveReagent(row.id)
    ElMessage.success('审批通过成功')
    fetchPendingReagents()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('审批通过失败:', error)
      ElMessage.error('审批通过失败')
    }
  }
}

// 拒绝试剂
const handleRejectReagent = async (row) => {
  try {
    await ElMessageBox.confirm('确定拒绝该试剂申领申请吗？', '提示', {
      type: 'warning'
    })
    await rejectReagent(row.id)
    ElMessage.success('拒绝成功')
    fetchPendingReagents()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('拒绝失败:', error)
      ElMessage.error('拒绝失败')
    }
  }
}

onMounted(async () => {
  // await refreshData()
})
</script>

<style scoped>
.el-card {
  @apply border-gray-200 dark:border-gray-700;
}

.el-card__header {
  @apply border-gray-200 dark:border-gray-700;
}

.el-table {
  @apply dark:bg-gray-800;
}

.el-table th {
  @apply dark:bg-gray-700 dark:text-gray-200;
}

.el-table td {
  @apply dark:bg-gray-800 dark:text-gray-200;
}

.el-table--striped .el-table__body tr.el-table__row--striped td {
  @apply dark:bg-gray-700;
}
</style> 