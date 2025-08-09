<template>
  <div class="space-y-4">
    <!-- 页面标题和操作按钮 -->
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-medium">实验室管理</h2>
      <el-button type="primary" @click="handleAdd">
        <div class="i-carbon-add mr-1" />
        新增实验室
      </el-button>
    </div>

    <!-- 搜索和筛选 -->
    <el-card>
      <el-form :model="searchForm" inline>
        <el-form-item label="实验室名称">
          <el-input
            v-model="searchForm.name"
            placeholder="请输入实验室名称"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="位置">
          <el-input
            v-model="searchForm.location"
            placeholder="请输入位置"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="状态"  style="width: 240px">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="空闲" :value="0" />
            <el-option label="使用中" :value="1" />
            <el-option label="维护中" :value="2" />
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

    <!-- 实验室列表 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="labList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="name" label="实验室名称" min-width="180">
          <template #default="{ row }">
            <div class="flex items-center">
              <el-avatar :size="32" class="mr-2">
                {{ row.name.charAt(0) }}
              </el-avatar>
              {{ row.name }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="location" label="位置" width="150" />
        <el-table-column prop="capacity" label="容量" width="100" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">{{ getStatusName(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="manager_name" label="负责人" width="120">
          <template #default="{ row }">
            <span v-if="row.manager_name">{{ row.manager_name }}</span>
            <el-tag v-else type="info">未设置</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="180">
          <template #default="{ row }">
            {{ row.create_time }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button-group>
              <el-button type="primary" link @click="handleEdit(row)">
                编辑
              </el-button>
              <el-button type="primary" link @click="handleView(row)">
                查看
              </el-button>
              <el-button type="danger" link @click="handleDelete(row)">
                删除
              </el-button>
            </el-button-group>
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

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '新增实验室' : '编辑实验室'"
      width="600px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="实验室名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入实验室名称" />
        </el-form-item>
        <el-form-item label="位置" prop="location">
          <el-input v-model="form.location" placeholder="请输入位置" />
        </el-form-item>
        <el-form-item label="容量" prop="capacity">
          <el-input-number v-model="form.capacity" :min="1" :max="200" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择状态">
            <el-option label="空闲" value="0" />
            <el-option label="使用中" value="1" />
            <el-option label="维护中" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="负责人" prop="manager_id">
          <el-select 
            v-model="form.manager_id" 
            placeholder="请选择负责人" 
            filterable
            clearable
            @focus="loadUsers"
          >
            <el-option
              v-for="user in userList"
              :key="user.id"
              :label="`${user.name} (${user.email})`"
              :value="user.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="备注" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入备注信息"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getLabList, addLab, updateLab, deleteLab, getUsers } from '@/api/lab'

// 搜索表单
const searchForm = reactive({
  name: '',
  location: '',
  status: ''
})

// 表格数据
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const labList = ref([])
const userList = ref([])

// 表单数据
const dialogVisible = ref(false)
const dialogType = ref('add')
const submitLoading = ref(false)
const formRef = ref(null)
const form = reactive({
  name: '',
  location: '',
  capacity: 30,
  status: 0,
  manager_id: '',
  description: ''
})

// 表单校验规则
const rules = {
  name: [
    { required: true, message: '请输入实验室名称', trigger: 'blur' },
    { min: 3, max: 50, message: '长度在 3 到 50 个字符', trigger: 'blur' }
  ],
  location: [
    { required: true, message: '请输入位置', trigger: 'blur' },
    { max: 200, message: '位置不能超过200个字符', trigger: 'blur' }
  ],
  manager_id: [
    { required: false, message: '请选择负责人', trigger: 'change' }
  ]
}

// 获取状态标签样式
const getStatusTag = (status) => {
  const map = {
    0: 'success',  // 空闲
    1: 'warning',  // 使用中
    2: 'danger'    // 维护中
  }
  return map[status]
}

const getStatusName = (status) => {
  const map = {
    0: '空闲',
    1: '使用中',
    2: '维护中'
  }
  return map[status]
}

// 获取用户列表
const loadUsers = async () => {
  if (userList.value.length > 0) return
  
  try {
    console.log('开始加载用户列表...')
    const res = await getUsers()
    console.log('用户列表返回结果:', res)
    if (res.code === 0) {
      userList.value = res.data || []
      console.log('用户列表加载成功:', userList.value)
    } else {
      console.error('获取用户列表失败:', res.msg)
      ElMessage.error('获取用户列表失败: ' + res.msg)
    }
  } catch (error) {
    console.error('获取用户列表失败：', error)
    ElMessage.error('获取用户列表失败')
  }
}

// 搜索和重置
const handleSearch = () => {
  currentPage.value = 1
  fetchData()
}

const handleReset = () => {
  searchForm.name = ''
  searchForm.location = ''
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

// 获取数据
const fetchData = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      limit: pageSize.value,
      name: searchForm.name,
      location: searchForm.location
    }
    
    const res = await getLabList(params)
    if (res.data) {
      labList.value = res.data.list
      total.value = res.data.total
    }
  } catch (error) {
    console.error('获取数据失败：', error)
    ElMessage.error('获取数据失败')
  } finally {
    loading.value = false
  }
}

// 新增
const handleAdd = () => {
  dialogType.value = 'add'
  dialogVisible.value = true
  form.name = ''
  form.location = ''
  form.capacity = 30
  form.status = 0
  form.manager_id = ''
  form.description = ''
}

// 编辑
const handleEdit = (row) => {
  dialogType.value = 'edit'
  dialogVisible.value = true
  Object.assign(form, row)
}

// 查看
const handleView = (row) => {
  ElMessage('查看详情：' + row.name)
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定要删除实验室"${row.name}"吗？`,
    '警告',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await deleteLab(row.id)
      ElMessage.success('删除成功')
      fetchData()
    } catch (error) {
      console.error('删除失败：', error)
      ElMessage.error('删除失败')
    }
  })
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return
  
  try {
    await formRef.value.validate()
    submitLoading.value = true
    
    const submitData = {
      name: form.name,
      location: form.location,
      capacity: form.capacity,
      status: form.status,
      manager_id: form.manager_id || null,
      description: form.description
    }
    
    if (dialogType.value === 'add') {
      await addLab(submitData)
    } else {
      await updateLab(form.id, submitData)
    }
    
    ElMessage.success(dialogType.value === 'add' ? '新增成功' : '编辑成功')
    dialogVisible.value = false
    fetchData()
  } catch (error) {
    console.error('提交失败：', error)
    ElMessage.error('提交失败：' + (error.message || '未知错误'))
  } finally {
    submitLoading.value = false
  }
}

// 初始化
fetchData()
loadUsers()
</script>

<style scoped>
.el-card {
  --el-card-padding: 16px;
}
</style> 