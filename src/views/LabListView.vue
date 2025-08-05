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
        <el-form-item label="房间号">
          <el-input
            v-model="searchForm.room_no"
            placeholder="请输入房间号"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="实验室类型"  style="width: 240px">
          <el-select v-model="searchForm.type" placeholder="请选择类型" clearable>
            <el-option label="物理实验室" value="physics" />
            <el-option label="化学实验室" value="chemistry" />
            <el-option label="生物实验室" value="biology" />
            <el-option label="计算机实验室" value="computer" />
          </el-select>
        </el-form-item>
        <el-form-item label="使用状态"  style="width: 240px">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="可用" value="active" />
            <el-option label="不可用" value="inactive" />
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
        <el-table-column prop="room_no" label="房间号" width="120" />
        <el-table-column prop="type" label="类型" width="120">
          <template #default="{ row }">
            <el-tag :type="getTypeTag(row.type)">{{ getTypeName(row.type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="capacity" label="容量" width="100" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">{{ getStatusName(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="manager" label="负责人" width="120" />
        <el-table-column prop="contact" label="联系方式" width="150" />
        <el-table-column prop="create_time" label="创建时间" width="180" />
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
        <el-form-item label="房间号" prop="room_no">
          <el-input v-model="form.room_no" placeholder="请输入房间号" />
        </el-form-item>
        <el-form-item label="实验室类型" prop="type">
          <el-select v-model="form.type" placeholder="请选择类型">
            <el-option label="物理实验室" value="physics" />
            <el-option label="化学实验室" value="chemistry" />
            <el-option label="生物实验室" value="biology" />
            <el-option label="计算机实验室" value="computer" />
          </el-select>
        </el-form-item>
        <el-form-item label="容量" prop="capacity">
          <el-input-number v-model="form.capacity" :min="1" :max="200" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择状态">
            <el-option label="可用" value="active" />
            <el-option label="不可用" value="inactive" />
          </el-select>
        </el-form-item>
        <el-form-item label="负责人" prop="manager">
          <el-input v-model="form.manager" placeholder="请输入负责人姓名" />
        </el-form-item>
        <el-form-item label="联系方式" prop="contact">
          <el-input v-model="form.contact" placeholder="请输入联系方式" />
        </el-form-item>
        <el-form-item label="备注" prop="remark">
          <el-input
            v-model="form.remark"
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
import { getLabList, addLab, updateLab, deleteLab } from '@/api/lab'

// 搜索表单
const searchForm = reactive({
  name: '',
  room_no: '',
  type: '',
  status: ''
})

// 表格数据
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const labList = ref([
  
])

// 表单数据
const dialogVisible = ref(false)
const dialogType = ref('add')
const submitLoading = ref(false)
const formRef = ref(null)
const form = reactive({
  name: '',
  room_no: '',
  type: '',
  capacity: 50,
  status: 'active',
  manager: '',
  contact: '',
  remark: ''
})

// 表单校验规则
const rules = {
  name: [
    { required: true, message: '请输入实验室名称', trigger: 'blur' },
    { min: 3, max: 50, message: '长度在 3 到 50 个字符', trigger: 'blur' }
  ],
  room_no: [
    { required: true, message: '请输入房间号', trigger: 'blur' },
    { pattern: /^[A-Z]\d{3}$/, message: '房间号格式为：大写字母+3位数字，如：A101', trigger: 'blur' }
  ],
  type: [
    { required: true, message: '请选择实验室类型', trigger: 'change' }
  ],
  capacity: [
    { required: true, message: '请输入容量', trigger: 'blur' }
  ],
  status: [
    { required: true, message: '请选择状态', trigger: 'change' }
  ],
  manager: [
    { required: true, message: '请输入负责人姓名', trigger: 'blur' }
  ],
  contact: [
    { required: true, message: '请输入联系方式', trigger: 'blur' },
    { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号码', trigger: 'blur' }
  ]
}

// 类型和状态的标签样式
const getTypeTag = (type) => {
  const map = {
    physics: '',
    chemistry: 'success',
    biology: 'warning',
    computer: 'info'
  }
  return map[type]
}

const getTypeName = (type) => {
  const map = {
    physics: '物理实验室',
    chemistry: '化学实验室',
    biology: '生物实验室',
    computer: '计算机实验室'
  }
  return map[type]
}

const getStatusTag = (status) => {
  const map = {
    active: 'success',
    inactive: 'danger'
  }
  return map[status]
}

const getStatusName = (status) => {
  const map = {
    active: '可用',
    inactive: '不可用'
  }
  return map[status]
}

// 搜索和重置
const handleSearch = () => {
  currentPage.value = 1
  fetchData()
}

const handleReset = () => {
  searchForm.name = ''
  searchForm.room_no = ''
  searchForm.type = ''
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
      room_no: searchForm.room_no,
      type: searchForm.type,
      status: searchForm.status
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
  form.room_no = ''
  form.type = ''
  form.capacity = 50
  form.status = 'active'
  form.manager = ''
  form.contact = ''
  form.remark = ''
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
      room_no: form.room_no,
      type: form.type,
      capacity: form.capacity,
      status: form.status,
      manager: form.manager,
      contact: form.contact,
      description: form.remark
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
</script>

<style scoped>
.el-card {
  --el-card-padding: 16px;
}
</style> 