<template>
  <div class="admin-layout">
    <!-- 侧边栏 -->
    <aside class="sidebar" :class="{ 'collapsed': isCollapse }">
      <!-- Logo区域 -->
      <div class="sidebar-header">
        <div class="logo-container">
          <SystemLogo class="logo-icon" />
          <transition name="fade-text">
            <h1 v-show="!isCollapse" class="logo-text">实验室管理系统</h1>
          </transition>
        </div>
      </div>

      <!-- 菜单区域 -->
      <div class="menu-container">
        <el-menu
          :default-active="route.path"
          :collapse="isCollapse"
          :router="true"
          class="sidebar-menu"
          background-color="transparent"
          text-color="#e2e8f0"
          active-text-color="#ffffff"
          :collapse-transition="true"
        >
          <template v-for="menu in userStore.menus" :key="menu.id">
            <!-- 有子菜单 -->
            <el-sub-menu v-if="menu.children && menu.children.length" :index="menu.path" class="menu-item">
              <template #title>
                <div class="menu-icon-wrapper">
                  <div :class="menu.icon" class="menu-icon" />
                </div>
                <span class="menu-title">{{ menu.name }}</span>
              </template>
              <el-menu-item
                v-for="child in menu.children"
                :key="child.id"
                :index="child.path"
                class="submenu-item"
              >
                <div class="submenu-icon-wrapper">
                  <div :class="child.icon" class="submenu-icon" />
                </div>
                <span class="submenu-title">{{ child.name }}</span>
              </el-menu-item>
            </el-sub-menu>
            
            <!-- 无子菜单 -->
            <el-menu-item v-else :index="menu.path" class="menu-item single-menu">
              <div class="menu-icon-wrapper">
                <div :class="menu.icon" class="menu-icon" />
              </div>
              <span class="menu-title">{{ menu.name }}</span>
            </el-menu-item>
          </template>
        </el-menu>
      </div>

      <!-- 折叠按钮 -->
      <div class="collapse-btn" @click="toggleCollapse">
        <div class="collapse-icon" :class="{ 'collapsed': isCollapse }">
          <div class="i-carbon-chevron-left" />
        </div>
      </div>
    </aside>

    <!-- 主内容区 -->
    <main class="main-content" :class="{ 'sidebar-collapsed': isCollapse }">
      <!-- 顶部导航栏 -->
      <header class="top-header">
        <div class="header-left">
          <div class="breadcrumb-container">
            <el-breadcrumb class="breadcrumb">
              <el-breadcrumb-item :to="{ path: '/' }">
                <div class="i-carbon-home breadcrumb-icon" />
                首页
              </el-breadcrumb-item>
              <el-breadcrumb-item>{{ route.meta.title || '仪表盘' }}</el-breadcrumb-item>
            </el-breadcrumb>
          </div>
        </div>
        
        <div class="header-right">
          <!-- 通知图标 -->
          <div class="header-action">
            <el-popover
              placement="bottom-end"
              :width="380"
              trigger="click"
              popper-class="notification-popover"
            >
              <template #reference>
                <el-badge :value="totalNotifications" class="notification-badge" :hidden="totalNotifications === 0">
                  <div class="action-btn">
                    <div class="i-carbon-notification action-icon" />
                  </div>
                </el-badge>
              </template>
              
              <!-- 通知内容 -->
              <div class="notification-container">
                <div class="notification-header">
                  <h3 class="notification-title">待处理事项</h3>
                  <el-button v-if="totalNotifications > 0" link type="primary" @click="markAllAsRead">全部已读</el-button>
                </div>
                
                <el-tabs v-model="activeTab" class="notification-tabs">
                  <!-- 实验室预约 -->
                  <el-tab-pane label="实验室预约" name="reservation">
                    <el-badge :value="pendingReservations.length" class="tab-badge" />
                    <div class="notification-list" v-if="pendingReservations.length > 0">
                      <div
                        v-for="item in pendingReservations"
                        :key="item.id"
                        class="notification-item"
                        @click="handleNotificationClick('reservation', item)"
                      >
                        <div class="notification-icon">
                          <div class="i-carbon-calendar" />
                        </div>
                        <div class="notification-content">
                          <div class="notification-text">
                            <strong>{{ item.user_name }}</strong> 申请预约 <strong>{{ item.lab_name }}</strong>
                          </div>
                          <div class="notification-time">
                            {{ item.start_time }} - {{ item.end_time }}
                          </div>
                          <div class="notification-meta">
                            <span class="notification-date">{{ formatTime(item.create_time) }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="empty-state">
                      <div class="i-carbon-checkmark-outline" />
                      <p>暂无待审批的预约</p>
                    </div>
                  </el-tab-pane>
                  
                  <!-- 试剂申领 -->
                  <el-tab-pane label="试剂申领" name="reagent">
                    <el-badge :value="pendingReagents.length" class="tab-badge" />
                    <div class="notification-list" v-if="pendingReagents.length > 0">
                      <div
                        v-for="item in pendingReagents"
                        :key="item.id"
                        class="notification-item"
                        @click="handleNotificationClick('reagent', item)"
                      >
                        <div class="notification-icon reagent">
                          <div class="i-carbon-chemistry" />
                        </div>
                        <div class="notification-content">
                          <div class="notification-text">
                            <strong>{{ item.operator }}</strong> 申请 {{ item.type === 'out' ? '领用' : '入库' }}
                            <strong>{{ item.reagent_name }}</strong>
                          </div>
                          <div class="notification-time">
                            数量：{{ item.amount }} {{ item.unit }}
                          </div>
                          <div class="notification-meta">
                            <span class="notification-date">{{ formatTime(item.create_time) }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="empty-state">
                      <div class="i-carbon-checkmark-outline" />
                      <p>暂无待审批的申领</p>
                    </div>
                  </el-tab-pane>
                </el-tabs>
                
                <div class="notification-footer" v-if="totalNotifications > 0">
                  <el-button text @click="viewAllNotifications">查看全部</el-button>
                </div>
              </div>
            </el-popover>
          </div>
          
          <!-- 全屏按钮 -->
          <div class="header-action">
            <div class="action-btn" @click="toggleFullscreen">
              <div class="i-carbon-fit-to-screen action-icon" />
            </div>
          </div>
          
          <!-- 用户下拉菜单 -->
          <el-dropdown trigger="click" class="user-dropdown">
            <div class="user-info">
              <el-avatar :size="36" class="user-avatar">
                <div class="i-carbon-user-avatar" />
              </el-avatar>
              <div class="user-details" v-show="!isCollapse">
                <div class="user-name">{{ userStore.userInfo?.name || '管理员' }}</div>
                <div class="user-role">系统管理员</div>
              </div>
              <div class="i-carbon-chevron-down dropdown-arrow" />
            </div>
            <template #dropdown>
              <el-dropdown-menu class="user-dropdown-menu">
                <el-dropdown-item class="dropdown-item" @click="showUserProfile">
                  <div class="i-carbon-user dropdown-item-icon" />
                  个人信息
                </el-dropdown-item>
                <el-dropdown-item class="dropdown-item" @click="showChangePassword">
                  <div class="i-carbon-password dropdown-item-icon" />
                  修改密码
                </el-dropdown-item>
                <el-dropdown-item class="dropdown-item" @click="showSettings">
                  <div class="i-carbon-settings dropdown-item-icon" />
                  系统设置
                </el-dropdown-item>
                <el-dropdown-item divided class="dropdown-item logout-item" @click="handleLogout">
                  <div class="i-carbon-logout dropdown-item-icon" />
                  退出登录
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </header>

      <!-- 页面内容 -->
      <section class="content-area">
        <router-view v-slot="{ Component }">
          <transition name="page-transition" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </section>
    </main>

    <!-- 个人信息对话框 -->
    <el-dialog
      v-model="userProfileVisible"
      title="个人信息"
      width="600px"
      destroy-on-close
      class="custom-dialog"
    >
      <UserProfile />
    </el-dialog>

    <!-- 修改密码对话框 -->
    <el-dialog
      v-model="changePasswordVisible"
      title="修改密码"
      width="500px"
      destroy-on-close
      class="custom-dialog"
    >
      <ChangePassword />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessageBox, ElMessage } from 'element-plus'
import SystemLogo from '@/components/SystemLogo.vue'
import UserProfile from '@/components/UserProfile.vue'
import ChangePassword from '@/components/ChangePassword.vue'
import { useUserStore } from '@/stores/user'
import { getPendingReservations, getPendingReagents } from '@/api/notification'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const isCollapse = ref(false)
const userProfileVisible = ref(false)
const changePasswordVisible = ref(false)

// 通知相关
const activeTab = ref('reservation')
const pendingReservations = ref([])
const pendingReagents = ref([])
const notificationLoading = ref(false)

// 计算总通知数
const totalNotifications = computed(() => {
  return pendingReservations.value.length + pendingReagents.value.length
})

const toggleCollapse = () => {
  isCollapse.value = !isCollapse.value
}

// 加载未审批数据
const loadNotifications = async () => {
  notificationLoading.value = true
  try {
    // 获取未审批的实验室预约
    const reservationRes = await getPendingReservations()
    if (reservationRes.code === 0) {
      pendingReservations.value = reservationRes.data || []
    }
    
    // 获取未审批的试剂申领
    const reagentRes = await getPendingReagents()
    if (reagentRes.code === 0) {
      pendingReagents.value = reagentRes.data || []
    }
  } catch (error) {
    console.error('加载通知失败:', error)
  } finally {
    notificationLoading.value = false
  }
}

// 格式化时间
const formatTime = (timestamp) => {
  if (!timestamp) return ''
  const date = new Date(timestamp * 1000)
  const now = new Date()
  const diff = now - date
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (days === 0) {
    const hours = Math.floor(diff / (1000 * 60 * 60))
    if (hours === 0) {
      const minutes = Math.floor(diff / (1000 * 60))
      return minutes === 0 ? '刚刚' : `${minutes}分钟前`
    }
    return `${hours}小时前`
  } else if (days === 1) {
    return '昨天'
  } else if (days < 7) {
    return `${days}天前`
  } else {
    return date.toLocaleDateString('zh-CN')
  }
}

// 处理通知点击
const handleNotificationClick = (type, item) => {
  if (type === 'reservation') {
    router.push('/lab/reservation')
  } else if (type === 'reagent') {
    router.push('/reagents')
  }
}

// 标记所有为已读
const markAllAsRead = () => {
  ElMessage.success('已标记所有通知为已读')
  loadNotifications()
}

// 查看全部通知
const viewAllNotifications = () => {
  if (activeTab.value === 'reservation') {
    router.push('/lab/reservation')
  } else {
    router.push('/reagents')
  }
}

const showUserProfile = () => {
  userProfileVisible.value = true
}

const showChangePassword = () => {
  changePasswordVisible.value = true
}

const showSettings = () => {
  // 系统设置功能
  console.log('打开系统设置')
}

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}

const handleLogout = () => {
  ElMessageBox.confirm('确定要退出登录吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    userStore.logout()
    router.push('/login')
  })
}

onMounted(async () => {
  // 获取用户菜单
  try {
    await userStore.getUserMenusAction()
    // 如果菜单为空，设置默认菜单
    if (userStore.menus.length === 0) {
      userStore.setDefaultMenus()
    }
  } catch (error) {
    console.error('获取菜单失败，使用默认菜单:', error)
    userStore.setDefaultMenus()
  }
  
  // 加载通知数据
  loadNotifications()
  
  // 定时刷新通知（每30秒）
  setInterval(() => {
    loadNotifications()
  }, 30000)
})
</script>

<style scoped>
/* =========================== 全局布局 =========================== */
.admin-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* =========================== 侧边栏样式 =========================== */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
  z-index: 1000;
}

.sidebar.collapsed {
  width: 70px;
}

/* 侧边栏头部 */
.sidebar-header {
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  width: 40px;
  height: 40px;
  color: #60a5fa;
  filter: drop-shadow(0 2px 8px rgba(96, 165, 250, 0.3));
}

.logo-text {
  font-size: 1.3rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: 0.5px;
}

/* 菜单容器 */
.menu-container {
  flex: 1;
  padding: 20px 0;
  overflow-y: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.menu-container::-webkit-scrollbar {
  display: none;
}

/* 菜单项样式 */
.sidebar-menu {
  border: none !important;
  background: transparent !important;
}

.menu-item, .submenu-item {
  margin: 4px 16px;
  border-radius: 12px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.menu-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(96, 165, 250, 0.2), rgba(139, 92, 246, 0.2));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 12px;
}

.menu-item:hover::before,
.menu-item.is-active::before {
  opacity: 1;
}

.menu-icon-wrapper, .submenu-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  position: relative;
  z-index: 1;
}

.menu-icon, .submenu-icon {
  font-size: 18px;
  transition: all 0.3s ease;
}

.menu-title, .submenu-title {
  font-weight: 500;
  letter-spacing: 0.3px;
  position: relative;
  z-index: 1;
}

.single-menu {
  height: 48px;
  line-height: 48px;
}

.submenu-item {
  height: 40px;
  line-height: 40px;
  margin: 2px 8px;
  padding-left: 48px !important;
}

/* 折叠按钮 */
.collapse-btn {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.collapse-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(-50%) scale(1.1);
}

.collapse-icon {
  color: #e2e8f0;
  font-size: 16px;
  transition: transform 0.3s ease;
}

.collapse-icon.collapsed {
  transform: rotate(180deg);
}

/* =========================== 主内容区样式 =========================== */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin-left: 0;
  overflow: hidden;
}

.main-content.sidebar-collapsed {
  margin-left: 0;
}

/* 顶部导航栏 */
.top-header {
  height: 70px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
  z-index: 100;
  position: relative;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 24px;
}

.breadcrumb-container {
  display: flex;
  align-items: center;
}

.breadcrumb {
  font-weight: 500;
}

.breadcrumb-icon {
  margin-right: 6px;
  font-size: 16px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-action {
  position: relative;
}

.action-btn {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.action-btn:hover {
  background: rgba(96, 165, 250, 0.1);
  border-color: rgba(96, 165, 250, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(96, 165, 250, 0.2);
}

.action-icon {
  font-size: 18px;
  color: #64748b;
  transition: color 0.3s ease;
}

.action-btn:hover .action-icon {
  color: #60a5fa;
}

.notification-badge :deep(.el-badge__content) {
  background: linear-gradient(135deg, #f59e0b, #f97316);
  border: 2px solid white;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

/* 用户下拉菜单 */
.user-dropdown {
  cursor: pointer;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 12px;
  border-radius: 12px;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.user-info:hover {
  background: rgba(96, 165, 250, 0.1);
  border-color: rgba(96, 165, 250, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(96, 165, 250, 0.2);
}

.user-avatar {
  background: linear-gradient(135deg, #60a5fa, #8b5cf6);
}

.user-details {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.2;
}

.user-role {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.2;
}

.dropdown-arrow {
  font-size: 12px;
  color: #9ca3af;
  transition: transform 0.3s ease;
}

.user-dropdown.is-opened .dropdown-arrow {
  transform: rotate(180deg);
}

/* 用户下拉菜单样式 */
.user-dropdown-menu {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  padding: 8px;
  min-width: 180px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.dropdown-item:hover {
  background: rgba(96, 165, 250, 0.1);
  color: #60a5fa;
}

.dropdown-item-icon {
  font-size: 16px;
}

.logout-item:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

/* 内容区域 */
.content-area {
  flex: 1;
  padding: 24px;
  overflow: auto;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
}

/* =========================== 动画效果 =========================== */
.fade-text-enter-active,
.fade-text-leave-active {
  transition: all 0.3s ease;
}

.fade-text-enter-from {
  opacity: 0;
  transform: translateX(-10px);
}

.fade-text-leave-to {
  opacity: 0;
  transform: translateX(10px);
}

.page-transition-enter-active,
.page-transition-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-transition-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.page-transition-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* =========================== 自定义对话框 =========================== */
.custom-dialog :deep(.el-dialog) {
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.custom-dialog :deep(.el-dialog__header) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 16px 16px 0 0;
  padding: 20px 24px;
}

.custom-dialog :deep(.el-dialog__title) {
  color: white;
  font-weight: 600;
}

.custom-dialog :deep(.el-dialog__close) {
  color: white;
}

/* =========================== 响应式设计 =========================== */
@media (max-width: 768px) {
  .sidebar {
    width: 280px;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    transform: translateX(-100%);
  }
  
  .sidebar.show {
    transform: translateX(0);
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .top-header {
    padding: 0 16px;
  }
  
  .content-area {
    padding: 16px;
  }
  
  .user-details {
    display: none;
  }
}

/* =========================== Element Plus 覆盖样式 =========================== */
.sidebar-menu :deep(.el-menu-item),
.sidebar-menu :deep(.el-sub-menu__title) {
  border-radius: 12px !important;
  margin: 4px 16px !important;
  position: relative;
  overflow: hidden;
}

.sidebar-menu :deep(.el-menu-item.is-active) {
  background: linear-gradient(135deg, rgba(96, 165, 250, 0.3), rgba(139, 92, 246, 0.3)) !important;
  color: #ffffff !important;
  box-shadow: 0 4px 15px rgba(96, 165, 250, 0.4);
}

.sidebar-menu :deep(.el-menu-item:hover),
.sidebar-menu :deep(.el-sub-menu__title:hover) {
  background: rgba(255, 255, 255, 0.1) !important;
  color: #ffffff !important;
}

.sidebar-menu :deep(.el-sub-menu .el-menu-item) {
  background: transparent !important;
  margin: 2px 8px !important;
  padding-left: 48px !important;
}

.sidebar-menu :deep(.el-sub-menu .el-menu-item.is-active) {
  background: linear-gradient(135deg, rgba(96, 165, 250, 0.2), rgba(139, 92, 246, 0.2)) !important;
}

/* 滚动条样式 */
.content-area::-webkit-scrollbar,
.menu-container::-webkit-scrollbar {
  width: 6px;
}

.content-area::-webkit-scrollbar-track,
.menu-container::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.content-area::-webkit-scrollbar-thumb,
.menu-container::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
}

.content-area::-webkit-scrollbar-thumb:hover,
.menu-container::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

/* =========================== 通知样式 =========================== */
.notification-container {
  padding: 0;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
}

.notification-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.notification-tabs {
  border-bottom: none;
}

.notification-tabs :deep(.el-tabs__header) {
  margin: 0;
  padding: 0 20px;
}

.notification-tabs :deep(.el-tabs__item) {
  height: 40px;
  line-height: 40px;
}

.tab-badge {
  position: absolute;
  top: 8px;
  right: 10px;
}

.notification-list {
  max-height: 300px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  padding: 12px 20px;
  cursor: pointer;
  transition: background-color 0.2s;
  border-bottom: 1px solid #f3f4f6;
}

.notification-item:hover {
  background-color: #f9fafb;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
  margin-right: 12px;
}

.notification-icon.reagent {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-text {
  font-size: 14px;
  color: #374151;
  line-height: 1.5;
  margin-bottom: 4px;
}

.notification-text strong {
  color: #1f2937;
}

.notification-time {
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 4px;
}

.notification-meta {
  display: flex;
  align-items: center;
  gap: 8px;
}

.notification-date {
  font-size: 12px;
  color: #9ca3af;
}

.empty-state {
  padding: 40px 20px;
  text-align: center;
  color: #9ca3af;
}

.empty-state > div {
  font-size: 48px;
  margin-bottom: 12px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.notification-footer {
  padding: 12px 20px;
  border-top: 1px solid #e5e7eb;
  text-align: center;
}

/* 通知弹出框样式 */
:deep(.notification-popover) {
  padding: 0 !important;
}

:deep(.notification-popover .el-popover__title) {
  display: none;
}
</style> 