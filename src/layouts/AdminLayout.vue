<template>
  <div class="h-screen flex">
    <!-- 侧边栏 -->
    <div class="w-64 bg-white border-r border-gray-200">
      <div class="h-14 flex items-center gap-2 px-4 border-b border-gray-200">
        <SystemLogo />
        <h1 class="text-lg font-medium text-gray-900">实验室管理系统</h1>
      </div>
      <el-menu
        :default-active="route.path"
        class="!border-none h-[calc(100vh-3.5rem)]"
        :router="true"
        background-color="transparent"
        text-color="#303133"
        active-text-color="#6366f1"
      >
        <template v-for="menu in userStore.menus" :key="menu.id">
          <!-- 有子菜单 -->
          <el-sub-menu v-if="menu.children && menu.children.length" :index="menu.path">
            <template #title>
              <div :class="menu.icon" class="mr-2" />
              <span class="text-gray-700">{{ menu.name }}</span>
            </template>
            <el-menu-item
              v-for="child in menu.children"
              :key="child.id"
              :index="child.path"
            >
              <template #title>
                <div :class="child.icon" class="mr-2" />
                <span class="text-gray-700">{{ child.name }}</span>
              </template>
            </el-menu-item>
          </el-sub-menu>
          <!-- 无子菜单 -->
          <el-menu-item v-else :index="menu.path">
            <template #title>
              <div :class="menu.icon" class="mr-2" />
              <span class="text-gray-700">{{ menu.name }}</span>
            </template>
          </el-menu-item>
        </template>
      </el-menu>
    </div>

    <!-- 主内容区 -->
    <div class="flex-1 flex flex-col overflow-hidden bg-gray-100">
      <!-- 顶部导航栏 -->
      <div class="h-14 flex items-center justify-between px-4 bg-white border-b border-gray-200">
        <div class="flex items-center gap-4">
          <el-button
            circle
            class="!flex !items-center !justify-center bg-white border-gray-200 text-gray-700 hover:bg-primary-50"
            @click="toggleCollapse"
          >
            <div class="i-carbon-menu" />
          </el-button>
          <el-breadcrumb class="text-gray-700">
            <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
            <el-breadcrumb-item>{{ route.meta.title }}</el-breadcrumb-item>
          </el-breadcrumb>
        </div>
        <div class="flex items-center gap-2">
          <el-dropdown trigger="click">
            <el-button circle class="!flex !items-center !justify-center bg-white border-gray-200 text-gray-700 hover:bg-primary-50">
              <div class="i-carbon-user-avatar text-primary-500" />
            </el-button>
            <template #dropdown>
              <el-dropdown-menu class="bg-white border-gray-200">
                <el-dropdown-item class="text-gray-700 hover:bg-primary-50" @click="showUserProfile">个人信息</el-dropdown-item>
                <el-dropdown-item class="text-gray-700 hover:bg-primary-50" @click="showChangePassword">修改密码</el-dropdown-item>
                <el-dropdown-item divided class="text-gray-700 hover:bg-primary-50" @click="handleLogout">退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </div>

      <!-- 页面内容 -->
      <div class="flex-1 overflow-auto p-4">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </div>

    <!-- 个人信息对话框 -->
    <el-dialog
      v-model="userProfileVisible"
      title="个人信息"
      width="600px"
      destroy-on-close
      class="bg-white"
    >
      <UserProfile />
    </el-dialog>

    <!-- 修改密码对话框 -->
    <el-dialog
      v-model="changePasswordVisible"
      title="修改密码"
      width="500px"
      destroy-on-close
      class="bg-white"
    >
      <ChangePassword />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessageBox } from 'element-plus'
import SystemLogo from '@/components/SystemLogo.vue'
import UserProfile from '@/components/UserProfile.vue'
import ChangePassword from '@/components/ChangePassword.vue'
import { useUserStore } from '@/stores/user'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const isCollapse = ref(false)
const userProfileVisible = ref(false)
const changePasswordVisible = ref(false)

const toggleCollapse = () => {
  isCollapse.value = !isCollapse.value
}

const showUserProfile = () => {
  userProfileVisible.value = true
}

const showChangePassword = () => {
  changePasswordVisible.value = true
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
})
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* 菜单样式 */
.el-menu {
  --el-menu-bg-color: transparent;
  --el-menu-text-color: #303133;
  --el-menu-hover-bg-color: #f3f4f6;
  --el-menu-active-color: #6366f1;
}

.el-menu-item.is-active {
  color: #6366f1 !important;
}

.el-menu-item.is-active .el-menu-item__title {
  color: #6366f1 !important;
}

.el-menu-item.is-active .el-menu-item__icon {
  color: #6366f1 !important;
}

.el-sub-menu.is-active > .el-sub-menu__title {
  color: #6366f1 !important;
}

.el-sub-menu.is-active > .el-sub-menu__title .el-sub-menu__icon-arrow {
  color: #6366f1 !important;
}

.el-menu-item:hover {
  background-color: #f3f4f6 !important;
}

.el-sub-menu__title:hover {
  background-color: #f3f4f6 !important;
}

/* 按钮样式 */
.el-button--primary {
  --el-button-bg-color: #6366f1;
  --el-button-border-color: #6366f1;
  --el-button-hover-bg-color: #4f46e5;
  --el-button-hover-border-color: #4f46e5;
  --el-button-active-bg-color: #4338ca;
  --el-button-active-border-color: #4338ca;
}

/* 表格样式 */
.el-table {
  --el-table-bg-color: #ffffff;
  --el-table-tr-bg-color: #ffffff;
  --el-table-header-bg-color: #f9fafb;
  --el-table-border-color: #e5e7eb;
  --el-table-text-color: #374151;
  --el-table-row-hover-bg-color: #f3f4f6;
  --el-table-current-row-bg-color: #eef2ff;
}

.el-table th {
  background-color: #f9fafb !important;
  color: #374151 !important;
}

.el-table td {
  background-color: #ffffff !important;
  color: #374151 !important;
}

.el-table--striped .el-table__body tr.el-table__row--striped td {
  background-color: #f9fafb !important;
}

.el-table--enable-row-hover .el-table__body tr:hover > td {
  background-color: #f3f4f6 !important;
}

.el-table--striped .el-table__body tr.el-table__row--striped:hover > td {
  background-color: #f3f4f6 !important;
}

.el-table--enable-row-hover .el-table__body tr.current-row:hover > td {
  background-color: #eef2ff !important;
}

.el-table--striped .el-table__body tr.current-row:hover > td {
  background-color: #eef2ff !important;
}
</style> 