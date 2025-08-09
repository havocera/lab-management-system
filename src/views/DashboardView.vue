<template>
  <div class="dashboard-container">
    <!-- 欢迎区域 -->
    <div class="welcome-section">
      <div class="welcome-card">
        <div class="welcome-content">
          <div class="welcome-info">
            <div class="greeting">
              <h1 class="welcome-title">{{ getGreeting() }}，{{ userStore.userInfo?.name || '管理员' }}</h1>
              <p class="welcome-subtitle">欢迎回到实验室管理系统，今天是美好的一天 ✨</p>
            </div>
            <div class="weather-info">
              <div class="weather-icon">
                <div class="i-carbon-sun text-5xl"></div>
              </div>
              <div class="weather-details">
                <div class="temperature">{{ weather.temperature }}°C</div>
                <div class="weather-desc">{{ weather.description }}</div>
              </div>
            </div>
          </div>
          <div class="welcome-actions">
            <el-button type="primary" size="large" class="refresh-btn" @click="refreshData" :loading="refreshing">
              <div class="i-carbon-refresh mr-2"></div>
              {{ refreshing ? '刷新中...' : '刷新数据' }}
            </el-button>
          </div>
        </div>
        <div class="date-card">
          <div class="date-icon">
            <div class="i-carbon-calendar text-3xl"></div>
          </div>
          <div class="date-info">
            <div class="current-date">{{ currentDate }}</div>
            <div class="current-time">{{ currentTime }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-section">
      <div class="stats-grid">
        <div class="stat-card stat-card-1" @click="navigateTo('/labs')">
          <div class="stat-icon">
            <div class="i-carbon-chemistry text-4xl"></div>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ statistics.todayLabUsage || 0 }}</div>
            <div class="stat-label">今日实验室使用</div>
            <div class="stat-trend">
              <div class="i-carbon-trending-up mr-1"></div>
              <span>+8.5%</span>
            </div>
          </div>
          <div class="stat-bg-icon">
            <div class="i-carbon-chemistry"></div>
          </div>
        </div>

        <div class="stat-card stat-card-2" @click="navigateTo('/lab/reservation')">
          <div class="stat-icon">
            <div class="i-carbon-calendar text-4xl"></div>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ statistics.tomorrowReservations || 0 }}</div>
            <div class="stat-label">明日预约</div>
            <div class="stat-trend">
              <div class="i-carbon-trending-up mr-1"></div>
              <span>+12.3%</span>
            </div>
          </div>
          <div class="stat-bg-icon">
            <div class="i-carbon-calendar"></div>
          </div>
        </div>

        <div class="stat-card stat-card-3" @click="navigateTo('/reagents')">
          <div class="stat-icon">
            <div class="i-carbon-flask text-4xl"></div>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ statistics.pendingReagents || 0 }}</div>
            <div class="stat-label">待审批试剂</div>
            <div class="stat-trend">
              <div class="i-carbon-trending-down mr-1"></div>
              <span>-2.1%</span>
            </div>
          </div>
          <div class="stat-bg-icon">
            <div class="i-carbon-flask"></div>
          </div>
        </div>

        <div class="stat-card stat-card-4" @click="navigateTo('/users')">
          <div class="stat-icon">
            <div class="i-carbon-user-multiple text-4xl"></div>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ statistics.onlineUsers || 0 }}</div>
            <div class="stat-label">在线用户</div>
            <div class="stat-trend">
              <div class="i-carbon-trending-up mr-1"></div>
              <span>+5.7%</span>
            </div>
          </div>
          <div class="stat-bg-icon">
            <div class="i-carbon-user-multiple"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- 主要内容区域 -->
    <div class="main-content">
      <div class="content-left">
        <!-- 实验室使用情况图表 -->
        <div class="chart-card">
          <div class="card-header">
            <h3 class="card-title">实验室使用趋势</h3>
            <div class="card-actions">
              <el-select v-model="chartPeriod" size="small" class="period-select">
                <el-option label="今日" value="today"></el-option>
                <el-option label="本周" value="week"></el-option>
                <el-option label="本月" value="month"></el-option>
              </el-select>
            </div>
          </div>
          <div class="chart-container" ref="usageChartRef"></div>
        </div>

        <!-- 最近活动 -->
        <div class="activity-card">
          <div class="card-header">
            <h3 class="card-title">最近活动</h3>
            <el-button type="primary" link>查看全部</el-button>
          </div>
          <div class="activity-list">
            <div class="activity-item" v-for="activity in recentActivities" :key="activity.id">
              <div class="activity-avatar">
                <el-avatar :src="activity.avatar" :size="40">
                  <div class="i-carbon-user"></div>
                </el-avatar>
              </div>
              <div class="activity-content">
                <div class="activity-text">
                  <span class="user-name">{{ activity.user }}</span>
                  {{ activity.action }}
                  <span class="target-name">{{ activity.target }}</span>
                </div>
                <div class="activity-time">{{ activity.time }}</div>
              </div>
              <div class="activity-status">
                <el-tag type="success" size="small">
                  完成
                </el-tag>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="content-right">
        <!-- 今日概览 -->
        <div class="overview-card">
          <div class="card-header">
            <h3 class="card-title">今日概览</h3>
          </div>
          <div class="overview-stats">
            <div class="overview-item">
              <div class="overview-icon success">
                <div class="i-carbon-checkmark"></div>
              </div>
              <div class="overview-info">
                <div class="overview-number">{{ todayOverview.completedTasks || 0 }}</div>
                <div class="overview-label">已完成任务</div>
              </div>
            </div>
            <div class="overview-item">
              <div class="overview-icon warning">
                <div class="i-carbon-time"></div>
              </div>
              <div class="overview-info">
                <div class="overview-number">{{ todayOverview.pendingTasks || 0 }}</div>
                <div class="overview-label">待处理任务</div>
              </div>
            </div>
            <div class="overview-item">
              <div class="overview-icon info">
                <div class="i-carbon-chemistry"></div>
              </div>
              <div class="overview-info">
                <div class="overview-number">{{ todayOverview.activeEquipment || 0 }}</div>
                <div class="overview-label">活跃设备</div>
              </div>
            </div>
            <div class="overview-item">
              <div class="overview-icon danger">
                <div class="i-carbon-warning"></div>
              </div>
              <div class="overview-info">
                <div class="overview-number">{{ todayOverview.alerts || 0 }}</div>
                <div class="overview-label">系统警报</div>
              </div>
            </div>
          </div>
        </div>

        <!-- 快速操作 -->
        <div class="quick-actions-card">
          <div class="card-header">
            <h3 class="card-title">快速操作</h3>
          </div>
          <div class="quick-actions">
            <div class="action-btn" @click="navigateTo('/lab/reservation')">
              <div class="action-icon">
                <div class="i-carbon-add"></div>
              </div>
              <span>新建预约</span>
            </div>
            <div class="action-btn" @click="navigateTo('/equipment')">
              <div class="action-icon">
                <div class="i-carbon-tool-box"></div>
              </div>
              <span>设备管理</span>
            </div>
            <div class="action-btn" @click="navigateTo('/reagents')">
              <div class="action-icon">
                <div class="i-carbon-flask"></div>
              </div>
              <span>试剂申领</span>
            </div>
            <div class="action-btn" @click="navigateTo('/users')">
              <div class="action-icon">
                <div class="i-carbon-user-multiple"></div>
              </div>
              <span>用户管理</span>
            </div>
          </div>
        </div>

        <!-- 系统状态 -->
        <div class="system-status-card">
          <div class="card-header">
            <h3 class="card-title">系统状态</h3>
          </div>
          <div class="status-list">
            <div class="status-item">
              <div class="status-label">CPU 使用率</div>
              <div class="status-bar">
                <el-progress :percentage="systemStatus.cpu || 0" size="small" :show-text="false"></el-progress>
                <span class="status-value">{{ systemStatus.cpu || 0 }}%</span>
              </div>
            </div>
            <div class="status-item">
              <div class="status-label">内存使用率</div>
              <div class="status-bar">
                <el-progress :percentage="systemStatus.memory || 0" size="small" :show-text="false" color="#f56c6c"></el-progress>
                <span class="status-value">{{ systemStatus.memory || 0 }}%</span>
              </div>
            </div>
            <div class="status-item">
              <div class="status-label">磁盘使用率</div>
              <div class="status-bar">
                <el-progress :percentage="systemStatus.disk || 0" size="small" :show-text="false" color="#67c23a"></el-progress>
                <span class="status-value">{{ systemStatus.disk || 0 }}%</span>
              </div>
            </div>
            <div class="status-item">
              <div class="status-label">网络状态</div>
              <div class="status-indicator">
                <el-tag type="success" size="small">正常</el-tag>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { ElMessage, ElLoading } from 'element-plus'
import * as echarts from 'echarts'
import { 
  getDashboardStatistics, 
  getTodayLabUsage, 
  getTomorrowReservations, 
  getPendingReagents,
  getTodayOverview,
  getSystemStatus,
  getUsageTrend,
  getRecentActivities
} from '@/api/dashboard'

const router = useRouter()
const userStore = useUserStore()

// 响应式数据
const refreshing = ref(false)
const chartPeriod = ref('today')
const usageChartRef = ref(null)
let usageChart = null
let timeInterval = null

// 当前时间
const currentTime = ref('')
const updateTime = () => {
  currentTime.value = new Date().toLocaleTimeString('zh-CN', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

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

// 初始化默认数据
const statistics = ref({
  todayLabUsage: 0,
  tomorrowReservations: 0,
  pendingReagents: 0,
  onlineUsers: 0
})

// 今日概览数据
const todayOverview = ref({
  completedTasks: 0,
  pendingTasks: 0,
  activeEquipment: 0,
  alerts: 0
})

// 系统状态
const systemStatus = ref({
  cpu: 0,
  memory: 0,
  disk: 0,
  network: 'normal'
})

// 最近活动数据
const recentActivities = ref([])

// 加载仪表盘数据
const loadDashboardData = async () => {
  try {
    // 并行加载所有数据
    const [
      statisticsRes,
      todayOverviewRes,
      systemStatusRes,
      recentActivitiesRes
    ] = await Promise.all([
      getDashboardStatistics(),
      getTodayOverview(),
      getSystemStatus(),
      getRecentActivities()
    ])

    // 更新统计数据
    if (statisticsRes.code === 0) {
      statistics.value = statisticsRes.data
    }

    // 更新今日概览
    if (todayOverviewRes.code === 0) {
      todayOverview.value = todayOverviewRes.data
    }

    // 更新系统状态
    if (systemStatusRes.code === 0) {
      systemStatus.value = systemStatusRes.data
    }

    // 更新最近活动
    if (recentActivitiesRes.code === 0) {
      recentActivities.value = recentActivitiesRes.data
    }

  } catch (error) {
    console.error('加载仪表盘数据失败:', error)
    ElMessage.error('加载数据失败')
  }
}

// 加载图表数据
const loadChartData = async () => {
  try {
    const response = await getUsageTrend({ period: chartPeriod.value })
    if (response.code === 0) {
      updateChartWithData(response.data)
    }
  } catch (error) {
    console.error('加载图表数据失败:', error)
  }
}

// 获取问候语
const getGreeting = () => {
  const hour = new Date().getHours()
  if (hour < 6) return '夜深了'
  if (hour < 9) return '早上好'
  if (hour < 12) return '上午好'
  if (hour < 14) return '中午好'
  if (hour < 18) return '下午好'
  if (hour < 22) return '晚上好'
  return '夜深了'
}

// 导航到指定页面
const navigateTo = (path) => {
  router.push(path)
}

// 刷新数据
const refreshData = async () => {
  refreshing.value = true
  try {
    await loadDashboardData()
    await loadChartData()
    ElMessage.success('数据刷新成功')
  } catch (error) {
    console.error('刷新数据失败:', error)
    ElMessage.error('刷新数据失败')
  } finally {
    refreshing.value = false
  }
}

// 初始化图表
const initChart = () => {
  if (!usageChartRef.value) return
  
  usageChart = echarts.init(usageChartRef.value)
  updateChart()
  
  // 响应式处理
  window.addEventListener('resize', () => {
    usageChart?.resize()
  })
}

// 使用真实数据更新图表
const updateChartWithData = (data) => {
  if (!usageChart || !data || !Array.isArray(data)) return
  
  const times = data.map(item => item.time)
  const labUsageData = data.map(item => item.labUsage)
  const equipmentUsageData = data.map(item => item.equipmentUsage)
  const reagentConsumptionData = data.map(item => item.reagentConsumption)
  
  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross',
        label: {
          backgroundColor: '#6a7985'
        }
      }
    },
    legend: {
      data: ['实验室使用', '设备使用', '试剂消耗']
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: [
      {
        type: 'category',
        boundaryGap: false,
        data: times
      }
    ],
    yAxis: [
      {
        type: 'value'
      }
    ],
    series: [
      {
        name: '实验室使用',
        type: 'line',
        stack: 'Total',
        smooth: true,
        lineStyle: {
          width: 3
        },
        showSymbol: false,
        areaStyle: {
          opacity: 0.8,
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            {
              offset: 0,
              color: 'rgba(128, 255, 165, 1)'
            },
            {
              offset: 1,
              color: 'rgba(1, 191, 236, 1)'
            }
          ])
        },
        emphasis: {
          focus: 'series'
        },
        data: labUsageData
      },
      {
        name: '设备使用',
        type: 'line',
        stack: 'Total',
        smooth: true,
        lineStyle: {
          width: 3
        },
        showSymbol: false,
        areaStyle: {
          opacity: 0.8,
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            {
              offset: 0,
              color: 'rgba(255, 191, 0, 1)'
            },
            {
              offset: 1,
              color: 'rgba(224, 62, 76, 1)'
            }
          ])
        },
        emphasis: {
          focus: 'series'
        },
        data: equipmentUsageData
      },
      {
        name: '试剂消耗',
        type: 'line',
        stack: 'Total',
        smooth: true,
        lineStyle: {
          width: 3
        },
        showSymbol: false,
        areaStyle: {
          opacity: 0.8,
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            {
              offset: 0,
              color: 'rgba(151, 249, 190, 1)'
            },
            {
              offset: 1,
              color: 'rgba(87, 52, 148, 1)'
            }
          ])
        },
        emphasis: {
          focus: 'series'
        },
        data: reagentConsumptionData
      }
    ]
  }
  
  usageChart.setOption(option)
}

// 更新图表数据
const updateChart = () => {
  loadChartData()
}

onMounted(async () => {
  // 初始化时间更新
  updateTime()
  timeInterval = setInterval(updateTime, 1000)
  
  // 等待DOM渲染完成后初始化图表
  await nextTick()
  initChart()
  
  // 初始化加载数据
  await loadDashboardData()
  await loadChartData()
})

// 监听图表时间段变化
watch(chartPeriod, () => {
  loadChartData()
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
  if (usageChart) {
    usageChart.dispose()
  }
  window.removeEventListener('resize', () => {
    usageChart?.resize()
  })
})
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: calc(100vh - 120px);
}

/* 欢迎区域 */
.welcome-section {
  margin-bottom: 24px;
}

.welcome-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 32px;
  color: white;
  box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.welcome-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  transform: rotate(45deg);
}

.welcome-content {
  display: flex;
  align-items: center;
  gap: 40px;
  flex: 1;
  z-index: 1;
}

.welcome-info {
  display: flex;
  align-items: center;
  gap: 32px;
}

.greeting {
  flex: 1;
}

.welcome-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 8px 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.welcome-subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
  font-weight: 300;
}

.weather-info {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 16px;
  backdrop-filter: blur(10px);
}

.weather-icon {
  color: #ffd700;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.temperature {
  font-size: 1.8rem;
  font-weight: 600;
  margin: 0;
}

.weather-desc {
  font-size: 0.95rem;
  opacity: 0.9;
  margin: 0;
}

.welcome-actions {
  z-index: 1;
}

.refresh-btn {
  padding: 12px 24px;
  font-size: 1rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.refresh-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.date-card {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 16px;
  padding: 24px;
  backdrop-filter: blur(10px);
  text-align: center;
  min-width: 200px;
  z-index: 1;
}

.date-icon {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 12px;
}

.current-date {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 4px;
}

.current-time {
  font-size: 1.5rem;
  font-weight: 700;
  font-family: 'Courier New', monospace;
}

/* 统计卡片 */
.stats-section {
  margin-bottom: 24px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.stat-card-1 {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-card-2 {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.stat-card-3 {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.stat-card-4 {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
}

.stat-icon {
  margin-bottom: 16px;
}

.stat-content {
  position: relative;
  z-index: 2;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 8px;
  line-height: 1;
}

.stat-label {
  font-size: 1rem;
  opacity: 0.9;
  margin-bottom: 12px;
}

.stat-trend {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
  opacity: 0.8;
}

.stat-bg-icon {
  position: absolute;
  top: -20px;
  right: -20px;
  font-size: 8rem;
  opacity: 0.1;
  z-index: 1;
}

/* 主要内容区域 */
.main-content {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 24px;
}

.content-left,
.content-right {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* 通用卡片样式 */
.chart-card,
.activity-card,
.overview-card,
.quick-actions-card,
.system-status-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #f0f0f0;
}

.card-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.card-actions {
  display: flex;
  gap: 12px;
}

.period-select {
  width: 100px;
}

/* 图表容器 */
.chart-container {
  height: 350px;
  padding: 20px;
}

/* 活动列表 */
.activity-list {
  padding: 20px 24px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 0;
  border-bottom: 1px solid #f5f5f5;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-avatar {
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-text {
  color: #4a5568;
  margin-bottom: 4px;
}

.user-name {
  font-weight: 600;
  color: #2d3748;
}

.target-name {
  font-weight: 500;
  color: #667eea;
}

.activity-time {
  font-size: 0.85rem;
  color: #a0aec0;
}

.activity-status {
  flex-shrink: 0;
}

/* 概览统计 */
.overview-stats {
  padding: 20px 24px;
}

.overview-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 0;
  border-bottom: 1px solid #f5f5f5;
}

.overview-item:last-child {
  border-bottom: none;
}

.overview-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
}

.overview-icon.success {
  background: linear-gradient(135deg, #48bb78, #38a169);
}

.overview-icon.warning {
  background: linear-gradient(135deg, #ed8936, #dd6b20);
}

.overview-icon.info {
  background: linear-gradient(135deg, #4299e1, #3182ce);
}

.overview-icon.danger {
  background: linear-gradient(135deg, #f56565, #e53e3e);
}

.overview-number {
  font-size: 1.8rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 4px;
}

.overview-label {
  font-size: 0.9rem;
  color: #718096;
}

/* 快速操作 */
.quick-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  padding: 20px 24px;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 24px 16px;
  background: #f7fafc;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.action-btn:hover {
  background: #667eea;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.action-icon {
  width: 48px;
  height: 48px;
  background: white;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.action-btn:hover .action-icon {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

/* 系统状态 */
.status-list {
  padding: 20px 24px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #f5f5f5;
}

.status-item:last-child {
  border-bottom: none;
}

.status-label {
  font-weight: 500;
  color: #4a5568;
}

.status-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  margin-left: 20px;
}

.status-bar :deep(.el-progress) {
  flex: 1;
}

.status-value {
  font-weight: 600;
  color: #2d3748;
  min-width: 40px;
  text-align: right;
}

.status-indicator {
  display: flex;
  align-items: center;
}

/* 响应式设计 */
@media (max-width: 1200px) {
  .main-content {
    grid-template-columns: 1fr;
  }
  
  .content-right {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    display: grid;
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 16px;
  }
  
  .welcome-card {
    flex-direction: column;
    text-align: center;
    gap: 24px;
  }
  
  .welcome-content {
    flex-direction: column;
    gap: 24px;
  }
  
  .welcome-info {
    flex-direction: column;
    gap: 24px;
    text-align: center;
  }
  
  .welcome-title {
    font-size: 2rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .quick-actions {
    grid-template-columns: 1fr;
  }
  
  .chart-container {
    height: 300px;
  }
}

/* 暗色主题支持 */
.dark .dashboard-container {
  background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
}

.dark .welcome-card,
.dark .chart-card,
.dark .activity-card,
.dark .overview-card,
.dark .quick-actions-card,
.dark .system-status-card {
  background: #2d3748;
  color: #e2e8f0;
}

.dark .card-title {
  color: #e2e8f0;
}

.dark .activity-text {
  color: #cbd5e0;
}

.dark .user-name {
  color: #e2e8f0;
}

.dark .overview-number {
  color: #e2e8f0;
}

.dark .overview-label {
  color: #a0aec0;
}

.dark .status-label {
  color: #cbd5e0;
}

.dark .status-value {
  color: #e2e8f0;
}

.dark .action-btn {
  background: #4a5568;
  color: #e2e8f0;
}

.dark .action-btn:hover {
  background: #667eea;
}
</style> 