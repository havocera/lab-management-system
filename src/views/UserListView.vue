<template>
  <div class="space-y-4">
    <!-- 页面标题 -->
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-medium">用户管理</h2>
      <el-button type="primary" @click="handleAdd">
        <div class="i-carbon-add mr-1" />
        添加用户
      </el-button>
    </div>

    <!-- 搜索和筛选 -->
    <el-card>
      <el-form :model="searchForm" inline>
        <el-form-item label="用户名">
          <el-input
            v-model="searchForm.username"
            placeholder="请输入用户名"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="姓名">
          <el-input
            v-model="searchForm.name"
            placeholder="请输入姓名"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="角色">
          <el-select v-model="searchForm.role" placeholder="请选择角色" clearable>
            <el-option
              v-for="role in roleOptions"
              :key="role.id"
              :label="role.name"
              :value="role.code"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="正常" value="active" />
            <el-option label="禁用" value="disabled" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <div class="i-carbon-search mr-1" />
            搜索
          </el-button>
          <el-button @click="handleReset">
            <div class="i-carbon-reset mr-1" />
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 用户列表 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="userList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="username" label="用户名" width="150" />
        <el-table-column prop="name" label="姓名" width="120" />
        <el-table-column prop="phone" label="手机号" width="150" />
        <el-table-column prop="email" label="邮箱" min-width="180" />
        <el-table-column prop="role" label="角色" width="100">
          <template #default="{ row }">
            <el-tag :type="getRoleType(row.role)">
              {{ getRoleName(row.role) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 'active' ? 'success' : 'danger'">
              {{ row.status === 'active' ? '正常' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="last_login_time" label="最后登录时间" width="180">
          <template #default="{ row }">
            {{ formatDateTime(row.last_login_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="last_login_ip" label="最后登录IP" width="150" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button
              type="primary"
              link
              @click="handleEdit(row)"
            >
              编辑
            </el-button>
            <el-button
              type="warning"
              link
              @click="handleResetPassword(row)"
            >
              重置密码
            </el-button>
            <el-button
              :type="row.status === 'active' ? 'danger' : 'success'"
              link
              @click="handleToggleStatus(row)"
            >
              {{ row.status === 'active' ? '禁用' : '启用' }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="flex justify-end mt-4">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- 用户表单对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '添加用户' : '编辑用户'"
      width="500px"
      destroy-on-close
    >
      <el-form
        ref="userFormRef"
        :model="userForm"
        :rules="userRules"
        label-width="100px"
      >
        <el-form-item label="用户名" prop="username">
          <el-input
            v-model="userForm.username"
            placeholder="请输入用户名"
            :disabled="dialogType === 'edit'"
          />
        </el-form-item>
        <el-form-item label="姓名" prop="name">
          <el-input
            v-model="userForm.name"
            placeholder="请输入姓名"
          />
        </el-form-item>
        <el-form-item label="手机号" prop="phone">
          <el-input
            v-model="userForm.phone"
            placeholder="请输入手机号"
          />
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input
            v-model="userForm.email"
            placeholder="请输入邮箱"
          />
        </el-form-item>
        <el-form-item label="角色" prop="role">
          <el-select v-model="userForm.role" placeholder="请选择角色">
            <el-option
              v-for="role in roleOptions"
              :key="role.id"
              :label="role.name"
              :value="role.code"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="密码" prop="password" v-if="dialogType === 'add'">
          <el-input
            v-model="userForm.password"
            type="password"
            placeholder="请输入密码"
            show-password
          />
        </el-form-item>
        <el-form-item label="确认密码" prop="confirmPassword" v-if="dialogType === 'add'">
          <el-input
            v-model="userForm.confirmPassword"
            type="password"
            placeholder="请再次输入密码"
            show-password
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="submitForm">
            确认
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getUserList, updateUserStatus, resetUserPassword, createUser, updateUser } from '@/api/user'
import { getRoleList } from '@/api/role'

// 搜索表单
const searchForm = reactive({
  username: '',
  name: '',
  role: '',
  status: ''
})

// 表格数据
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const userList = ref([])

// 对话框相关
const dialogVisible = ref(false)
const dialogType = ref('add') // 'add' 或 'edit'
const userFormRef = ref(null)
const userForm = reactive({
  id: '',
  username: '',
  name: '',
  phone: '',
  email: '',
  role: '',
  password: '',
  confirmPassword: ''
})

// 表单验证规则
const userRules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '长度在 3 到 20 个字符', trigger: 'blur' }
  ],
  name: [
    { required: true, message: '请输入姓名', trigger: 'blur' }
  ],
  phone: [
    { required: true, message: '请输入手机号', trigger: 'blur' },
    { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号码', trigger: 'blur' }
  ],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' }
  ],
  role: [
    { required: true, message: '请选择角色', trigger: 'change' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, max: 20, message: '长度在 6 到 20 个字符', trigger: 'blur' }
  ],
  confirmPassword: [
    { required: true, message: '请再次输入密码', trigger: 'blur' },
    {
      validator: (rule, value, callback) => {
        if (value !== userForm.password) {
          callback(new Error('两次输入的密码不一致'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
}

// 角色选项
const roleOptions = ref([])

// 获取角色列表
const fetchRoleOptions = async () => {
  try {
    const res = await getRoleList()
    if (res.code === 0 && res.data && Array.isArray(res.data.list)) {
      roleOptions.value = res.data.list
    } else {
      roleOptions.value = []
      console.warn('角色数据格式不正确')
    }
  } catch (error) {
    console.error('获取角色列表失败：', error)
    ElMessage.error('获取角色列表失败')
    roleOptions.value = []
  }
}

// 获取角色名称
const getRoleName = (roleCode) => {
  const role = roleOptions.value.find(item => item.code === roleCode)
  return role ? role.name : '未知'
}

// 获取角色类型
const getRoleType = (roleCode) => {
  const types = {
    admin: 'danger',
    teacher: 'warning',
    student: 'info'
  }
  return types[roleCode] || 'info'
}

// 搜索和重置
const handleSearch = () => {
  currentPage.value = 1
  fetchData()
}

const handleReset = () => {
  searchForm.username = ''
  searchForm.name = ''
  searchForm.role = ''
  searchForm.status = ''
  handleSearch()
}

// 分页
const handleSizeChange = (val) => {
  pageSize.value = val
  fetchData()
}

const handleCurrentChange = (val) => {
  currentPage.value = val
  fetchData()
}

// 添加用户
const handleAdd = () => {
  dialogType.value = 'add'
  userForm.id = ''
  userForm.username = ''
  userForm.name = ''
  userForm.phone = ''
  userForm.email = ''
  userForm.role = ''
  userForm.password = ''
  userForm.confirmPassword = ''
  dialogVisible.value = true
}

// 编辑用户
const handleEdit = (row) => {
  dialogType.value = 'edit'
  userForm.id = row.id
  userForm.username = row.username
  userForm.name = row.name
  userForm.phone = row.phone
  userForm.email = row.email
  userForm.role = row.role
  dialogVisible.value = true
}

// 提交表单
const submitForm = async () => {
  if (!userFormRef.value) return
  
  await userFormRef.value.validate(async (valid) => {
    if (valid) {
      try {
        let res
        if (dialogType.value === 'add') {
          // 添加用户
          res = await createUser({
            username: userForm.username,
            password: userForm.password,
            name: userForm.name,
            phone: userForm.phone,
            email: userForm.email,
            role: userForm.role
          })
        } else {
          // 更新用户
          res = await updateUser({
            id: userForm.id,
            name: userForm.name,
            phone: userForm.phone,
            email: userForm.email,
            role: userForm.role
          })
        }
        
        if (res.code === 0) {
          ElMessage.success(dialogType.value === 'add' ? '添加成功' : '更新成功')
          dialogVisible.value = false
          fetchData()
        } else {
          ElMessage.error(res.msg || (dialogType.value === 'add' ? '添加失败' : '更新失败'))
        }
      } catch (error) {
        console.error('操作失败:', error)
        ElMessage.error(dialogType.value === 'add' ? '添加失败' : '更新失败')
      }
    }
  })
}

// 重置密码
const handleResetPassword = (row) => {
  ElMessageBox.confirm(
    `确定要重置用户 "${row.name}" 的密码吗？`,
    '提示',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const res = await resetUserPassword({
        user_id: row.id,
        new_password: '123456' // 默认重置密码
      })
      if (res.code === 0) {
        ElMessage.success('密码重置成功，默认密码为：123456')
      } else {
        ElMessage.error(res.msg || '密码重置失败')
      }
    } catch (error) {
      console.error('密码重置失败:', error)
      ElMessage.error('密码重置失败')
    }
  })
}

// 切换用户状态
const handleToggleStatus = (row) => {
  const action = row.status === 'active' ? '禁用' : '启用'
  ElMessageBox.confirm(
    `确定要${action}用户 "${row.name}" 吗？`,
    '提示',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const res = await updateUserStatus({
        id: row.id,
        status: row.status === 'active' ? 'disabled' : 'active'
      })
      if (res.code === 0) {
        ElMessage.success(`${action}成功`)
        fetchData()
      } else {
        ElMessage.error(res.msg || `${action}失败`)
      }
    } catch (error) {
      console.error(`${action}失败:`, error)
      ElMessage.error(`${action}失败`)
    }
  })
}

// 获取数据
const fetchData = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      limit: pageSize.value,
      ...searchForm
    }
    
    const res = await getUserList(params)
    if (res.code === 0) {
      userList.value = res.data.list
      total.value = res.data.total
    } else {
      ElMessage.error(res.msg || '获取数据失败')
    }
  } catch (error) {
    console.error('获取数据失败：', error)
    ElMessage.error('获取数据失败')
  } finally {
    loading.value = false
  }
}

// 格式化日期时间
const formatDateTime = (timestamp) => {
  if (!timestamp) return '-'
  const date = new Date(timestamp * 1000)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  const seconds = String(date.getSeconds()).padStart(2, '0')
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
}

// 初始化
onMounted(() => {
  fetchRoleOptions()
  fetchData()
})
</script>

<style scoped>
.el-card {
  --el-card-padding: 16px;
}
</style> 