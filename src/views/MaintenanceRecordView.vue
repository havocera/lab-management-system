<template>
  <div class="maintenance-management">
    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <div class="i-carbon-tool-kit text-3xl text-orange-600" />
          <div>
            <h1 class="page-title">设备维护记录</h1>
            <p class="page-subtitle">管理和跟踪设备维护历史记录</p>
          </div>
        </div>
        <el-button type="primary" size="large" @click="handleAdd" class="add-btn">
          <div class="i-carbon-add mr-2" />
          新增维护记录
        </el-button>
      </div>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-icon">
          <div class="i-carbon-tool-kit" />
        </div>
        <div class="stat-content">
          <div class="stat-label">维护记录总数</div>
          <div class="stat-value">{{ statistics.total }}</div>
        </div>
      </div>
      <div class="stat-card completed">
        <div class="stat-icon">
          <div class="i-carbon-checkmark-filled" />
        </div>
        <div class="stat-content">
          <div class="stat-label">已完成维护</div>
          <div class="stat-value">{{ statistics.completed }}</div>
        </div>
      </div>
      <div class="stat-card pending">
        <div class="stat-icon">
          <div class="i-carbon-time-filled" />
        </div>
        <div class="stat-content">
          <div class="stat-label">待维护</div>
          <div class="stat-value">{{ statistics.pending }}</div>
        </div>
      </div>
      <div class="stat-card urgent">
        <div class="stat-icon">
          <div class="i-carbon-warning-filled" />
        </div>
        <div class="stat-content">
          <div class="stat-label">紧急维护</div>
          <div class="stat-value">{{ statistics.urgent }}</div>
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
            v-model="searchForm.equipment_name"
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
            <el-option label="待维护" value="pending" />
            <el-option label="维护中" value="in_progress" />
            <el-option label="已完成" value="completed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-select 
            v-model="searchForm.priority" 
            placeholder="请选择优先级" 
            clearable
            class="search-select"
          >
            <template #prefix>
              <div class="i-carbon-warning-hex text-gray-400" />
            </template>
            <el-option label="低" value="low" />
            <el-option label="中" value="medium" />
            <el-option label="高" value="high" />
            <el-option label="紧急" value="urgent" />
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

    <!-- 维护记录列表 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <div class="i-carbon-list mr-2" />
          <span>维护记录列表</span>
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
        :data="maintenanceList"
        stripe
        class="maintenance-table"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column label="设备信息" min-width="200">
          <template #default="{ row }">
            <div class="equipment-item">
              <el-image
                :src="row.equipment_image"
                class="equipment-image"
                fit="cover"
                :preview-src-list="[row.equipment_image]"
              >
                <template #error>
                  <div class="image-placeholder">
                    <div class="i-carbon-tool-box text-gray-400" />
                  </div>
                </template>
              </el-image>
              <div class="equipment-info">
                <div class="equipment-name">{{ row.equipment_name }}</div>
                <div class="equipment-serial">{{ row.equipment_serial }}</div>
                <div class="lab-name">{{ row.lab_name }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="type" label="维护类型" width="120">
          <template #default="{ row }">
            <el-tag :type="getMaintenanceTypeTag(row.type)" size="small">
              {{ getMaintenanceTypeName(row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="priority" label="优先级" width="100">
          <template #default="{ row }">
            <el-tag 
              :type="getPriorityTag(row.priority)" 
              :class="`priority-${row.priority}`"
              size="small"
            >
              {{ getPriorityName(row.priority) }}
            </el-tag>
          </template>
        </el-table-column>
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
        <el-table-column prop="scheduled_date" label="计划日期" width="120" />
        <el-table-column prop="actual_date" label="实际日期" width="120">
          <template #default="{ row }">
            <span v-if="row.actual_date">{{ row.actual_date }}</span>
            <span v-else class="text-gray-400">未完成</span>
          </template>
        </el-table-column>
        <el-table-column prop="technician" label="维护人员" width="120" />
        <el-table-column prop="cost" label="维护费用" width="120" align="right">
          <template #default="{ row }">
            <div class="cost-display" v-if="row.cost">
              ¥{{ row.cost.toLocaleString() }}
            </div>
            <span v-else class="text-gray-400">-</span>
          </template>
        </el-table-column>
        <el-table-column prop="description" label="维护内容" min-width="180">
          <template #default="{ row }">
            <div class="description-text">
              {{ row.description || '暂无描述' }}
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button type="primary" link size="small" @click="handleEdit(row)">
                <div class="i-carbon-edit mr-1" />
                编辑
              </el-button>
              <el-button type="info" link size="small" @click="handleView(row)">
                <div class="i-carbon-view mr-1" />
                详情
              </el-button>
              <el-button 
                v-if="row.status === 'pending' || row.status === 'in_progress'"
                type="success" 
                link 
                size="small" 
                @click="handleComplete(row)"
              >
                <div class="i-carbon-checkmark mr-1" />
                完成
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
      <div class="pagination-wrapper">
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
      :title="dialogType === 'add' ? '新增维护记录' : '编辑维护记录'"
      width="700px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="设备" prop="equipment_id">
          <el-select 
            v-model="form.equipment_id" 
            placeholder="请选择设备"
            filterable
            @change="handleEquipmentChange"
          >
            <el-option
              v-for="equipment in equipmentOptions"
              :key="equipment.id"
              :label="`${equipment.name} (${equipment.serial_number})`"
              :value="equipment.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="维护类型" prop="type">
          <el-select v-model="form.type" placeholder="请选择维护类型">
            <el-option label="定期维护" value="routine" />
            <el-option label="预防性维护" value="preventive" />
            <el-option label="故障维修" value="corrective" />
            <el-option label="紧急维修" value="emergency" />
          </el-select>
        </el-form-item>
        <el-form-item label="优先级" prop="priority">
          <el-select v-model="form.priority" placeholder="请选择优先级">
            <el-option label="低" value="low" />
            <el-option label="中" value="medium" />
            <el-option label="高" value="high" />
            <el-option label="紧急" value="urgent" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择状态">
            <el-option label="待维护" value="pending" />
            <el-option label="维护中" value="in_progress" />
            <el-option label="已完成" value="completed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="计划日期" prop="scheduled_date">
          <el-date-picker
            v-model="form.scheduled_date"
            type="date"
            placeholder="选择计划维护日期"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="实际日期" prop="actual_date">
          <el-date-picker
            v-model="form.actual_date"
            type="date"
            placeholder="选择实际维护日期"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="维护人员" prop="technician">
          <el-input v-model="form.technician" placeholder="请输入维护人员" />
        </el-form-item>
        <el-form-item label="维护费用" prop="cost">
          <el-input-number
            v-model="form.cost"
            :min="0"
            :precision="2"
            :step="100"
            style="width: 100%"
            placeholder="请输入维护费用"
          />
        </el-form-item>
        <el-form-item label="维护内容" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="4"
            placeholder="请输入维护内容描述"
          />
        </el-form-item>
        <el-form-item label="备注" prop="notes">
          <el-input
            v-model="form.notes"
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

    <!-- 查看详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="维护记录详情"
      width="600px"
    >
      <div class="detail-content" v-if="selectedRecord">
        <div class="detail-section">
          <h4>设备信息</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="设备名称">{{ selectedRecord.equipment_name }}</el-descriptions-item>
            <el-descriptions-item label="设备编号">{{ selectedRecord.equipment_serial }}</el-descriptions-item>
            <el-descriptions-item label="所属实验室">{{ selectedRecord.lab_name }}</el-descriptions-item>
          </el-descriptions>
        </div>
        
        <div class="detail-section">
          <h4>维护信息</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="维护类型">
              <el-tag :type="getMaintenanceTypeTag(selectedRecord.type)" size="small">
                {{ getMaintenanceTypeName(selectedRecord.type) }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="优先级">
              <el-tag :type="getPriorityTag(selectedRecord.priority)" size="small">
                {{ getPriorityName(selectedRecord.priority) }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="状态">
              <el-tag :type="getStatusTag(selectedRecord.status)" size="small">
                {{ getStatusName(selectedRecord.status) }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="计划日期">{{ selectedRecord.scheduled_date }}</el-descriptions-item>
            <el-descriptions-item label="实际日期">{{ selectedRecord.actual_date || '未完成' }}</el-descriptions-item>
            <el-descriptions-item label="维护人员">{{ selectedRecord.technician }}</el-descriptions-item>
            <el-descriptions-item label="维护费用">{{ selectedRecord.cost ? `¥${selectedRecord.cost.toLocaleString()}` : '-' }}</el-descriptions-item>
          </el-descriptions>
        </div>
        
        <div class="detail-section" v-if="selectedRecord.description">
          <h4>维护内容</h4>
          <div class="content-text">{{ selectedRecord.description }}</div>
        </div>
        
        <div class="detail-section" v-if="selectedRecord.notes">
          <h4>备注信息</h4>
          <div class="content-text">{{ selectedRecord.notes }}</div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useUserStore } from '@/stores/user'
import { useRoute, useRouter } from 'vue-router'
import { 
  getMaintenanceRecords, 
  addMaintenanceRecord, 
  updateMaintenanceRecord, 
  deleteMaintenanceRecord,
  completeMaintenanceRecord 
} from '@/api/maintenance'
import { getEquipmentList } from '@/api/equipment'
import { getLabList } from '@/api/lab'

const userStore = useUserStore()
const route = useRoute()
const router = useRouter()

// 检查用户权限
const isAdmin = computed(() => userStore.roles.includes('admin'))
const currentUserId = computed(() => userStore.userInfo?.id)

// 搜索表单
const searchForm = reactive({
  equipment_name: '',
  equipment_id: '',
  lab_id: '',
  status: '',
  priority: ''
})

// 实验室和设备选项
const labOptions = ref([])
const equipmentOptions = ref([])

// 表格数据
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const maintenanceList = ref([])

// 统计数据
const statistics = computed(() => {
  const total = maintenanceList.value.length
  const completed = maintenanceList.value.filter(item => item.status === 'completed').length
  const pending = maintenanceList.value.filter(item => item.status === 'pending').length
  const urgent = maintenanceList.value.filter(item => item.priority === 'urgent').length
  
  return {
    total,
    completed,
    pending,
    urgent
  }
})

// 表单数据
const dialogVisible = ref(false)
const dialogType = ref('add')
const submitLoading = ref(false)
const formRef = ref(null)
const form = reactive({
  equipment_id: '',
  type: '',
  priority: '',
  status: 'pending',
  scheduled_date: '',
  actual_date: '',
  technician: '',
  cost: '',
  description: '',
  notes: ''
})

// 详情对话框
const detailDialogVisible = ref(false)
const selectedRecord = ref(null)

// 表单校验规则
const rules = {
  equipment_id: [
    { required: true, message: '请选择设备', trigger: 'change' }
  ],
  type: [
    { required: true, message: '请选择维护类型', trigger: 'change' }
  ],
  priority: [
    { required: true, message: '请选择优先级', trigger: 'change' }
  ],
  status: [
    { required: true, message: '请选择状态', trigger: 'change' }
  ],
  scheduled_date: [
    { required: true, message: '请选择计划日期', trigger: 'change' }
  ],
  technician: [
    { required: true, message: '请输入维护人员', trigger: 'blur' }
  ],
  description: [
    { required: true, message: '请输入维护内容', trigger: 'blur' }
  ]
}

// 获取维护类型标签样式
const getMaintenanceTypeTag = (type) => {
  const map = {
    routine: 'info',
    preventive: 'success',
    corrective: 'warning',
    emergency: 'danger'
  }
  return map[type]
}

const getMaintenanceTypeName = (type) => {
  const map = {
    routine: '定期维护',
    preventive: '预防性维护',
    corrective: '故障维修',
    emergency: '紧急维修'
  }
  return map[type]
}

// 获取优先级标签样式
const getPriorityTag = (priority) => {
  const map = {
    low: 'info',
    medium: 'success',
    high: 'warning',
    urgent: 'danger'
  }
  return map[priority]
}

const getPriorityName = (priority) => {
  const map = {
    low: '低',
    medium: '中',
    high: '高',
    urgent: '紧急'
  }
  return map[priority]
}

// 获取状态标签样式
const getStatusTag = (status) => {
  const map = {
    pending: 'warning',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'danger'
  }
  return map[status]
}

const getStatusName = (status) => {
  const map = {
    pending: '待维护',
    in_progress: '维护中',
    completed: '已完成',
    cancelled: '已取消'
  }
  return map[status]
}

// 获取实验室列表
const fetchLabOptions = async () => {
  try {
    const res = await getLabList({ limit: 100 })
    if (res.code === 0 && res.data) {
      labOptions.value = res.data.list || []
    }
  } catch (error) {
    console.error('获取实验室列表失败：', error)
    ElMessage.error('获取实验室列表失败')
  }
}

// 获取设备列表
const fetchEquipmentOptions = async () => {
  try {
    const res = await getEquipmentList({ limit: 1000 })
    if (res.data) {
      equipmentOptions.value = res.data.list || []
    }
  } catch (error) {
    console.error('获取设备列表失败：', error)
    ElMessage.error('获取设备列表失败')
  }
}

// 搜索和重置
const handleSearch = () => {
  currentPage.value = 1
  fetchData()
}

const handleReset = () => {
  searchForm.equipment_name = ''
  searchForm.equipment_id = ''
  searchForm.lab_id = ''
  searchForm.status = ''
  searchForm.priority = ''
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
      equipment_name: searchForm.equipment_name,
      equipment_id: searchForm.equipment_id,
      lab_id: searchForm.lab_id,
      status: searchForm.status,
      priority: searchForm.priority
    }
    
    // 如果不是管理员，只显示用户管理的实验室的设备维护记录
    if (!isAdmin.value && currentUserId.value) {
      params.manager_id = currentUserId.value
    }
    
    const res = await getMaintenanceRecords(params)
    if (res.data) {
      maintenanceList.value = res.data.list || []
      total.value = res.data.total || 0
    }
  } catch (error) {
    console.error('获取数据失败：', error)
    // 如果API调用失败，使用模拟数据
    const mockData = [
      {
        id: 1,
        equipment_name: '显微镜',
        equipment_serial: 'EQ001',
        equipment_image: '',
        lab_name: '化学实验室A',
        type: 'routine',
        priority: 'medium',
        status: 'pending',
        scheduled_date: '2024-08-20',
        actual_date: '',
        technician: '张三',
        cost: 500,
        description: '定期清洁和校准'
      },
      {
        id: 2,
        equipment_name: '离心机',
        equipment_serial: 'EQ002',
        equipment_image: '',
        lab_name: '生物实验室C',
        type: 'corrective',
        priority: 'high',
        status: 'completed',
        scheduled_date: '2024-08-15',
        actual_date: '2024-08-16',
        technician: '李四',
        cost: 1200,
        description: '更换损坏的转子'
      }
    ]
    
    // 如果有设备ID筛选，过滤模拟数据
    let filteredData = mockData
    if (searchForm.equipment_id) {
      filteredData = mockData.filter(item => item.id === parseInt(searchForm.equipment_id))
    }
    if (searchForm.equipment_name) {
      filteredData = filteredData.filter(item => 
        item.equipment_name.includes(searchForm.equipment_name)
      )
    }
    
    maintenanceList.value = filteredData
    total.value = filteredData.length
  } finally {
    loading.value = false
  }
}

// 设备选择变化
const handleEquipmentChange = (equipmentId) => {
  // 根据选择的设备更新相关信息
  const equipment = equipmentOptions.value.find(item => item.id === equipmentId)
  if (equipment) {
    // 可以在这里设置一些默认值
  }
}

// 新增
const handleAdd = () => {
  dialogType.value = 'add'
  dialogVisible.value = true
  resetForm()
}

// 编辑
const handleEdit = (row) => {
  dialogType.value = 'edit'
  dialogVisible.value = true
  Object.assign(form, row)
}

// 查看详情
const handleView = (row) => {
  selectedRecord.value = row
  detailDialogVisible.value = true
}

// 完成维护
const handleComplete = (row) => {
  ElMessageBox.confirm(
    `确定要将"${row.equipment_name}"的维护记录标记为已完成吗？`,
    '确认操作',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await completeMaintenanceRecord(row.id, {
        actual_date: new Date().toISOString().split('T')[0]
      })
      ElMessage.success('维护记录已标记为完成')
      fetchData()
    } catch (error) {
      console.error('操作失败：', error)
      ElMessage.error('操作失败')
    }
  })
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定要删除"${row.equipment_name}"的维护记录吗？`,
    '警告',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await deleteMaintenanceRecord(row.id)
      ElMessage.success('删除成功')
      fetchData()
    } catch (error) {
      console.error('删除失败：', error)
      ElMessage.error('删除失败')
    }
  })
}

// 重置表单
const resetForm = () => {
  Object.assign(form, {
    equipment_id: '',
    type: '',
    priority: '',
    status: 'pending',
    scheduled_date: '',
    actual_date: '',
    technician: '',
    cost: '',
    description: '',
    notes: ''
  })
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return
  
  try {
    await formRef.value.validate()
    submitLoading.value = true
    
    const submitData = {
      equipment_id: form.equipment_id,
      type: form.type,
      priority: form.priority,
      status: form.status,
      scheduled_date: form.scheduled_date,
      actual_date: form.actual_date,
      technician: form.technician,
      cost: form.cost,
      description: form.description,
      notes: form.notes
    }
    
    if (dialogType.value === 'add') {
      await addMaintenanceRecord(submitData)
    } else {
      await updateMaintenanceRecord(form.id, submitData)
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
  fetchEquipmentOptions()
  
  // 如果URL中有设备参数，自动填充搜索条件
  if (route.query.equipment_id) {
    searchForm.equipment_id = parseInt(route.query.equipment_id)
  }
  if (route.query.equipment_name) {
    searchForm.equipment_name = route.query.equipment_name
  }
  
  fetchData()
})
</script>

<style scoped>
/* 整体布局 */
.maintenance-management {
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

.stat-card.total::before { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.stat-card.completed::before { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card.pending::before { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.stat-card.urgent::before { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }

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

.stat-card.total .stat-icon { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.stat-card.completed .stat-icon { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card.pending .stat-icon { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.stat-card.urgent .stat-icon { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }

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
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
  border: none;
  border-radius: 8px;
}

.reset-btn {
  border-radius: 8px;
}

/* 表格样式 */
.maintenance-table {
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

.equipment-serial {
  font-size: 12px;
  color: #7f8c8d;
  font-family: 'Monaco', 'Consolas', monospace;
  margin-bottom: 2px;
}

.lab-name {
  font-size: 12px;
  color: #3498db;
}

.priority-low {
  background: linear-gradient(135deg, #95a5a6 20%, #7f8c8d 100%);
  border: none;
  color: white;
}

.priority-medium {
  background: linear-gradient(135deg, #27ae60 20%, #2ecc71 100%);
  border: none;
  color: white;
}

.priority-high {
  background: linear-gradient(135deg, #f39c12 20%, #e67e22 100%);
  border: none;
  color: white;
}

.priority-urgent {
  background: linear-gradient(135deg, #e74c3c 20%, #c0392b 100%);
  border: none;
  color: white;
}

.status-pending {
  background: linear-gradient(135deg, #f39c12 20%, #e67e22 100%);
  border: none;
  color: white;
}

.status-in_progress {
  background: linear-gradient(135deg, #3498db 20%, #2980b9 100%);
  border: none;
  color: white;
}

.status-completed {
  background: linear-gradient(135deg, #27ae60 20%, #2ecc71 100%);
  border: none;
  color: white;
}

.status-cancelled {
  background: linear-gradient(135deg, #95a5a6 20%, #7f8c8d 100%);
  border: none;
  color: white;
}

.cost-display {
  font-weight: 600;
  color: #e67e22;
  font-family: 'Monaco', 'Consolas', monospace;
}

.description-text {
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pagination-wrapper {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

/* 详情对话框 */
.detail-content {
  padding: 10px 0;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-section h4 {
  color: #2c3e50;
  margin: 0 0 16px 0;
  font-size: 16px;
  font-weight: 600;
  padding-bottom: 8px;
  border-bottom: 2px solid #ecf0f1;
}

.content-text {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 8px;
  line-height: 1.6;
  color: #2c3e50;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .maintenance-management {
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