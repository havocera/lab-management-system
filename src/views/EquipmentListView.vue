<template>
  <div class="equipment-management">
    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <div class="i-carbon-tool-box text-3xl text-blue-600" />
          <div>
            <h1 class="page-title">实验设备管理</h1>
            <p class="page-subtitle">管理和维护实验室设备信息</p>
          </div>
        </div>
        <el-button type="primary" size="large" @click="handleAdd" class="add-btn">
          <div class="i-carbon-add mr-2" />
          新增设备
        </el-button>
      </div>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-icon">
          <div class="i-carbon-tool-box" />
        </div>
        <div class="stat-content">
          <div class="stat-label">设备总数</div>
          <div class="stat-value">{{ statistics.total }}</div>
        </div>
      </div>
      <div class="stat-card normal">
        <div class="stat-icon">
          <div class="i-carbon-checkmark-filled" />
        </div>
        <div class="stat-content">
          <div class="stat-label">正常设备</div>
          <div class="stat-value">{{ statistics.normal }}</div>
        </div>
      </div>
      <div class="stat-card repairing">
        <div class="stat-icon">
          <div class="i-carbon-warning-filled" />
        </div>
        <div class="stat-content">
          <div class="stat-label">维修中</div>
          <div class="stat-value">{{ statistics.repairing }}</div>
        </div>
      </div>
      <div class="stat-card scrapped">
        <div class="stat-icon">
          <div class="i-carbon-trash-can" />
        </div>
        <div class="stat-content">
          <div class="stat-label">报废设备</div>
          <div class="stat-value">{{ statistics.scrapped }}</div>
        </div>
      </div>
    </div>

    <!-- 搜索区域 -->
    <el-card class="search-card">
      <template #header>
        <div class="card-header">
          <div class="i-carbon-search mr-2" />
          <span>搜索筛选</span>
        </div>
      </template>
      <el-form :model="searchForm" inline class="search-form">
        <el-form-item>
          <el-input
            v-model="searchForm.name"
            placeholder="请输入设备名称"
            clearable
            @keyup.enter="handleSearch"
            class="search-input"
          >
            <template #prefix>
              <div class="i-carbon-tool-box text-gray-400" />
            </template>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-input
            v-model="searchForm.serial_number"
            placeholder="请输入设备编号"
            clearable
            @keyup.enter="handleSearch"
            class="search-input"
          >
            <template #prefix>
              <div class="i-carbon-hashtag text-gray-400" />
            </template>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-select 
            v-model="searchForm.lab_id" 
            placeholder="请选择实验室" 
            clearable
            class="search-select"
          >
            <template #prefix>
              <div class="i-carbon-chemistry text-gray-400" />
            </template>
            <el-option
              v-for="lab in labOptions"
              :key="lab.id"
              :label="lab.name"
              :value="lab.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-select 
            v-model="searchForm.status" 
            placeholder="请选择状态" 
            clearable
            class="search-select"
          >
            <template #prefix>
              <div class="i-carbon-status text-gray-400" />
            </template>
            <el-option label="正常" value="normal" />
            <el-option label="维修中" value="repairing" />
            <el-option label="报废" value="scrapped" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch" class="search-btn">
            <div class="i-carbon-search mr-1" />
            搜索
          </el-button>
          <el-button @click="handleReset" class="reset-btn">
            <div class="i-carbon-reset mr-1" />
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 设备列表 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <div class="i-carbon-list mr-2" />
          <span>设备列表</span>
          <div class="table-actions">
            <el-button text size="small" @click="fetchData">
              <div class="i-carbon-renew mr-1" />
              刷新
            </el-button>
          </div>
        </div>
      </template>
      <el-table
        v-loading="loading"
        :data="equipmentList"
        stripe
        class="equipment-table"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="name" label="设备名称" min-width="200">
          <template #default="{ row }">
            <div class="equipment-item">
              <el-image
                :src="row.image"
                class="equipment-image"
                fit="cover"
                :preview-src-list="[row.image]"
              >
                <template #error>
                  <div class="image-placeholder">
                    <div class="i-carbon-image text-gray-400" />
                  </div>
                </template>
              </el-image>
              <div class="equipment-info">
                <div class="equipment-name">{{ row.name }}</div>
                <div class="equipment-model">{{ row.model }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="serial_number" label="设备编号" width="140">
          <template #default="{ row }">
            <el-tag type="info" size="small" class="serial-tag">
              {{ row.serial_number }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="所属实验室" width="180">
          <template #default="{ row }">
            <div class="lab-info">
              <div class="i-carbon-chemistry text-blue-500 mr-1" />
              <span>{{ row.lab_name }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="设备价值" width="120" align="right">
          <template #default="{ row }">
            <div class="price-display">
              ¥{{ row.price.toLocaleString() }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="purchase_date" label="购入日期" width="120" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag 
              :type="getStatusTag(row.status)" 
              :class="`status-${row.status}`"
              size="small"
            >
              {{ getStatusName(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="manufacturer" label="生产厂商" width="150" />
        <el-table-column prop="maintainer" label="维护人员" width="120" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button type="primary" link size="small" @click="handleEdit(row)">
                <div class="i-carbon-edit mr-1" />
                编辑
              </el-button>
              <el-button type="info" link size="small" @click="handleView(row)">
                <div class="i-carbon-view mr-1" />
                查看
              </el-button>
              <el-button type="warning" link size="small" @click="handleMaintenance(row)">
                <div class="i-carbon-tool-kit mr-1" />
                维护
              </el-button>
              <el-button type="danger" link size="small" @click="handleDelete(row)">
                <div class="i-carbon-trash-can mr-1" />
                删除
              </el-button>
            </div>
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
      :title="dialogType === 'add' ? '新增设备' : '编辑设备'"
      width="700px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="设备名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入设备名称" />
        </el-form-item>
        <el-form-item label="设备型号" prop="model">
          <el-input v-model="form.model" placeholder="请输入设备型号" />
        </el-form-item>
        <el-form-item label="设备编号" prop="serial_number">
          <el-input v-model="form.serial_number" placeholder="请输入设备编号" />
        </el-form-item>
        <el-form-item label="所属实验室" prop="lab_id">
          <el-select v-model="form.lab_id" placeholder="请选择实验室">
            <el-option
              v-for="lab in labOptions"
              :key="lab.id"
              :label="lab.name"
              :value="lab.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="设备图片" prop="image">
          <el-upload
            class="avatar-uploader"
            action="/api/upload"
            :show-file-list="false"
            :on-success="handleUploadSuccess"
            :before-upload="beforeUpload"
          >
            <img v-if="form.image" :src="form.image" class="w-24 h-24 rounded" />
            <el-icon v-else class="avatar-uploader-icon">
              <div class="i-carbon-add-filled" />
            </el-icon>
          </el-upload>
        </el-form-item>
        <el-form-item label="设备价值" prop="price">
          <el-input-number
            v-model="form.price"
            :min="0"
            :precision="2"
            :step="1000"
            style="width: 180px"
          />
        </el-form-item>
        <el-form-item label="购入日期" prop="purchase_date">
          <el-date-picker
            v-model="form.purchase_date"
            type="date"
            placeholder="选择日期"
            style="width: 180px"
          />
        </el-form-item>
        <el-form-item label="设备状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择状态">
            <el-option label="正常" value="normal" />
            <el-option label="维修中" value="repairing" />
            <el-option label="报废" value="scrapped" />
          </el-select>
        </el-form-item>
        <el-form-item label="生产厂商" prop="manufacturer">
          <el-input v-model="form.manufacturer" placeholder="请输入生产厂商" />
        </el-form-item>
        <el-form-item label="维护人员" prop="maintainer">
          <el-input v-model="form.maintainer" placeholder="请输入维护人员" />
        </el-form-item>
        <el-form-item label="设备说明" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入设备说明"
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
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getEquipmentList, addEquipment, updateEquipment, deleteEquipment } from '@/api/equipment'
import { getLabList } from '@/api/lab'
import { useUserStore } from '@/stores/user'
import { useRouter } from 'vue-router'

const userStore = useUserStore()
const router = useRouter()

// 检查用户权限
const isAdmin = computed(() => userStore.roles.includes('admin'))
const currentUserId = computed(() => userStore.userInfo?.id)

// 搜索表单
const searchForm = reactive({
  name: '',
  serial_number: '',
  lab_id: '',
  status: ''
})

// 实验室选项
const labOptions = ref([])

// 获取实验室列表
const fetchLabOptions = async () => {
  try {
    const res = await getLabList({ limit: 100 })
    if (res.data) {
      labOptions.value = res.data.list
    }
  } catch (error) {
    console.error('获取实验室列表失败：', error)
    ElMessage.error('获取实验室列表失败')
  }
}

// 表格数据
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const equipmentList = ref([])

// 统计数据
const statistics = computed(() => {
  const total = equipmentList.value.length
  const normal = equipmentList.value.filter(item => item.status === 'normal').length
  const repairing = equipmentList.value.filter(item => item.status === 'repairing').length
  const scrapped = equipmentList.value.filter(item => item.status === 'scrapped').length
  
  return {
    total,
    normal,
    repairing,
    scrapped
  }
})

// 表单数据
const dialogVisible = ref(false)
const dialogType = ref('add')
const submitLoading = ref(false)
const formRef = ref(null)
const form = reactive({
  name: '',
  model: '',
  serial_number: '',
  lab_id: '',
  image: '',
  price: 0,
  purchase_date: '',
  status: 'normal',
  manufacturer: '',
  maintainer: '',
  description: ''
})

// 表单校验规则
const rules = {
  name: [
    { required: true, message: '请输入设备名称', trigger: 'blur' },
    { min: 1, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
  ],
  model: [
    { required: true, message: '请输入设备型号', trigger: 'blur' },
    { max: 50, message: '长度不能超过 50 个字符', trigger: 'blur' }
  ],
  serial_number: [
    { required: true, message: '请输入设备编号', trigger: 'blur' }
  ],
  lab_id: [
    { required: true, message: '请选择所属实验室', trigger: 'change' },
    { type: 'number', message: '实验室ID必须为数字', trigger: 'change' }
  ],
  price: [
    { required: true, message: '请输入设备价值', trigger: 'blur' }
  ],
  purchase_date: [
    { required: true, message: '请选择购入日期', trigger: 'change' }
  ],
  status: [
    { required: true, message: '请选择设备状态', trigger: 'change' }
  ],
  manufacturer: [
    { required: true, message: '请输入生产厂商', trigger: 'blur' }
  ],
  maintainer: [
    { required: true, message: '请输入维护人员', trigger: 'blur' }
  ]
}

// 状态标签
const getStatusTag = (status) => {
  const map = {
    normal: 'success',
    repairing: 'warning',
    scrapped: 'danger'
  }
  return map[status]
}

const getStatusName = (status) => {
  const map = {
    normal: '正常',
    repairing: '维修中',
    scrapped: '报废'
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
  searchForm.serial_number = ''
  searchForm.lab_id = ''
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
      serial_number: searchForm.serial_number,
      lab_id: searchForm.lab_id,
      status: searchForm.status
    }
    
    // 如果不是管理员，只显示用户管理的实验室的设备
    if (!isAdmin.value && currentUserId.value) {
      params.manager_id = currentUserId.value
    }
    
    const res = await getEquipmentList(params)
    if (res.data) {
      equipmentList.value = res.data.list
      total.value = res.data.total
    }
  } catch (error) {
    console.error('获取数据失败：', error)
    ElMessage.error('获取数据失败')
  } finally {
    loading.value = false
  }
}

// 图片上传
const handleUploadSuccess = (res) => {
  form.image = res.url
}

const beforeUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    ElMessage.error('只能上传图片文件！')
    return false
  }
  if (!isLt2M) {
    ElMessage.error('图片大小不能超过 2MB！')
    return false
  }
  return true
}

// 新增
const handleAdd = () => {
  dialogType.value = 'add'
  dialogVisible.value = true
  form.name = ''
  form.model = ''
  form.serial_number = ''
  form.lab_id = ''
  form.image = ''
  form.price = 0
  form.purchase_date = ''
  form.status = 'normal'
  form.manufacturer = ''
  form.maintainer = ''
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

// 维护记录
const handleMaintenance = (row) => {
  router.push({
    path: '/maintenance-records',
    query: { equipment_id: row.id, equipment_name: row.name }
  })
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定要删除设备"${row.name}"吗？`,
    '警告',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await deleteEquipment(row.id)
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
      model: form.model,
      serial_number: form.serial_number,
      lab_id: form.lab_id,
      image: form.image,
      price: form.price,
      purchase_date: form.purchase_date,
      status: form.status,
      manufacturer: form.manufacturer,
      maintainer: form.maintainer,
      description: form.description
    }
    
    if (dialogType.value === 'add') {
      await addEquipment(submitData)
    } else {
      await updateEquipment(form.id, submitData)
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
onMounted(() => {
  fetchLabOptions()
  fetchData()
})
</script>

<style scoped>
/* 整体布局 */
.equipment-management {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 24px;
}

/* 页面头部 */
.page-header {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.title-section {
  display: flex;
  align-items: center;
  gap: 16px;
}

.page-title {
  font-size: 28px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}

.page-subtitle {
  color: #7f8c8d;
  margin: 4px 0 0 0;
  font-size: 14px;
}

.add-btn {
  height: 44px;
  padding: 0 24px;
  border-radius: 8px;
  font-weight: 500;
}

/* 统计卡片 */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
}

.stat-card.total::before { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card.normal::before { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-card.repairing::before { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-card.scrapped::before { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); }

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-card.total .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card.normal .stat-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-card.repairing .stat-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-card.scrapped .stat-icon { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); }

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #2c3e50;
}

/* 卡片样式 */
.search-card, .table-card {
  border-radius: 12px;
  margin-bottom: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  border: none;
}

.card-header {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #2c3e50;
}

.table-actions {
  margin-left: auto;
}

/* 搜索表单 */
.search-form {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
}

.search-input, .search-select {
  width: 200px;
}

.search-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 8px;
}

.reset-btn {
  border-radius: 8px;
}

/* 表格样式 */
.equipment-table {
  :deep(.el-table__header) {
    background: #f8f9fa;
  }
  
  :deep(.el-table__row:hover) {
    background: #f8f9ff;
  }
}

.equipment-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.equipment-image {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  flex-shrink: 0;
}

.image-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px dashed #d9d9d9;
}

.equipment-info {
  flex: 1;
}

.equipment-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 2px;
}

.equipment-model {
  font-size: 12px;
  color: #7f8c8d;
}

.serial-tag {
  background: #f0f2f5;
  border: none;
  font-family: 'Monaco', 'Consolas', monospace;
  letter-spacing: 0.5px;
}

.lab-info {
  display: flex;
  align-items: center;
}

.price-display {
  font-weight: 600;
  color: #e67e22;
  font-family: 'Monaco', 'Consolas', monospace;
}

.status-normal {
  background: linear-gradient(135deg, #4facfe 20%, #00f2fe 100%);
  border: none;
  color: white;
}

.status-repairing {
  background: linear-gradient(135deg, #f093fb 20%, #f5576c 100%);
  border: none;
  color: white;
}

.status-scrapped {
  background: linear-gradient(135deg, #ffecd2 20%, #fcb69f 100%);
  border: none;
  color: #d63031;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

/* 上传组件 */
.avatar-uploader {
  :deep(.el-upload) {
    border: 2px dashed #d9d9d9;
    border-radius: 8px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
    width: 96px;
    height: 96px;
  }

  :deep(.el-upload:hover) {
    border-color: #409eff;
    background: #fafbff;
  }
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 96px;
  height: 96px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .equipment-management {
    padding: 16px;
  }
  
  .page-header {
    padding: 16px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .search-form {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input, .search-select {
    width: 100%;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 4px;
  }
}
</style> 