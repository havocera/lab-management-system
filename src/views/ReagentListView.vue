<template>
  <div class="reagent-management">
    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <div class="i-carbon-chemistry text-3xl text-purple-600" />
          <div>
            <h1 class="page-title">试剂管理</h1>
            <p class="page-subtitle">管理和维护实验室化学试剂库存</p>
          </div>
        </div>
        <el-button type="primary" size="large" @click="handleAdd" class="add-btn">
          <div class="i-carbon-add mr-2" />
          新增试剂
        </el-button>
      </div>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-icon">
          <div class="i-carbon-chemistry" />
        </div>
        <div class="stat-content">
          <div class="stat-label">试剂总数</div>
          <div class="stat-value">{{ statistics.total }}</div>
        </div>
      </div>
      <div class="stat-card normal">
        <div class="stat-icon">
          <div class="i-carbon-checkmark-filled" />
        </div>
        <div class="stat-content">
          <div class="stat-label">库存充足</div>
          <div class="stat-value">{{ statistics.sufficient }}</div>
        </div>
      </div>
      <div class="stat-card low">
        <div class="stat-icon">
          <div class="i-carbon-warning-filled" />
        </div>
        <div class="stat-content">
          <div class="stat-label">库存不足</div>
          <div class="stat-value">{{ statistics.insufficient }}</div>
        </div>
      </div>
      <div class="stat-card danger">
        <div class="stat-icon">
          <div class="i-carbon-warning-hex-filled" />
        </div>
        <div class="stat-content">
          <div class="stat-label">危险试剂</div>
          <div class="stat-value">{{ statistics.dangerous }}</div>
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
            placeholder="请输入试剂名称"
            clearable
            @keyup.enter="handleSearch"
            class="search-input"
          >
            <template #prefix>
              <div class="i-carbon-chemistry text-gray-400" />
            </template>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-input
            v-model="searchForm.code"
            placeholder="请输入试剂编号"
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
            v-model="searchForm.labId" 
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
            v-model="searchForm.dangerLevel" 
            placeholder="请选择危险等级" 
            clearable
            class="search-select"
          >
            <template #prefix>
              <div class="i-carbon-warning-hex text-gray-400" />
            </template>
            <el-option label="低危" value="low" />
            <el-option label="中危" value="medium" />
            <el-option label="高危" value="high" />
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

    <!-- 试剂列表 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <div class="i-carbon-list mr-2" />
          <span>试剂列表</span>
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
        :data="reagentList"
        stripe
        class="reagent-table"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="name" label="试剂名称" min-width="200">
          <template #default="{ row }">
            <div class="reagent-item">
              <el-image
                :src="row.image"
                class="reagent-image"
                fit="cover"
                :preview-src-list="[row.image]"
              >
                <template #error>
                  <div class="image-placeholder">
                    <div class="i-carbon-chemistry text-gray-400" />
                  </div>
                </template>
              </el-image>
              <div class="reagent-info">
                <div class="reagent-name">{{ row.name }}</div>
                <div class="reagent-code">{{ row.code }}</div>
              </div>
            </div>
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
        <el-table-column prop="specification" label="规格" width="120">
          <template #default="{ row }">
            <el-tag type="info" size="small" class="spec-tag">
              {{ row.specification }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="stock" label="库存量" width="120" align="right">
          <template #default="{ row }">
            <div class="stock-display" :class="{ 'stock-low': row.stock <= (row.min_stock || 10) }">
              <div class="stock-amount">{{ row.stock }} {{ row.unit }}</div>
              <div class="stock-status" v-if="row.stock <= (row.min_stock || 10)">
                <div class="i-carbon-warning text-red-500" />
                库存不足
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="dangerLevel" label="危险等级" width="100">
          <template #default="{ row }">
            <el-tag 
              :type="getDangerLevelTag(row.danger_level)" 
              :class="`danger-${row.danger_level}`"
              size="small"
            >
              {{ getDangerLevelName(row.danger_level) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="expiryDate" label="有效期至" width="120">
          <template #default="{ row }">
            <div class="expiry-display" :class="{ 'expiry-warning': isExpiringSoon(row.expiry_date) }">
              <div v-if="isExpiringSoon(row.expiry_date)" class="i-carbon-warning text-orange-500 mr-1" />
              {{ row.expiry_date }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="manufacturer" label="生产厂商" width="150" />
        <el-table-column prop="keeper" label="保管人" width="120" />
        <el-table-column label="操作" width="300" fixed="right">
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
              <el-button type="success" link size="small" @click="handleInOut(row)">
                <div class="i-carbon-document-add mr-1" />
                出入库
              </el-button>
              <el-button type="warning" link size="small" @click="handleRecord(row)">
                <div class="i-carbon-document mr-1" />
                记录
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
      :title="dialogType === 'add' ? '新增试剂' : '编辑试剂'"
      width="700px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="试剂名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入试剂名称" />
        </el-form-item>
        <el-form-item label="试剂编号" prop="code">
          <el-input v-model="form.code" placeholder="请输入试剂编号" />
        </el-form-item>
        <el-form-item label="所属实验室" prop="labId">
          <el-select v-model="form.labId" placeholder="请选择实验室">
            <el-option
              v-for="lab in labOptions"
              :key="lab.id"
              :label="`${lab.name} (${lab.room_no})`"
              :value="lab.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="试剂图片" prop="image">
          <el-upload
            class="avatar-uploader"
            :action="`/upload/reagent`"
            :headers="{
              Authorization: 'Bearer ' + (localStorage?.getItem('token') || '')
            }"
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
        <el-form-item label="规格" prop="specification">
          <el-input v-model="form.specification" placeholder="请输入规格" />
        </el-form-item>
        <el-form-item label="库存量" prop="stock">
          <el-input-number
            v-model="form.stock"
            :min="0"
            :precision="2"
            :step="1"
            style="width: 180px"
          />
          <el-select v-model="form.unit" placeholder="单位" class="ml-2" style="width: 100px">
            <el-option-group label="体积">
              <el-option label="毫升" value="ml" />
              <el-option label="升" value="L" />
            </el-option-group>
            <el-option-group label="质量">
              <el-option label="毫克" value="mg" />
              <el-option label="克" value="g" />
              <el-option label="千克" value="kg" />
            </el-option-group>
            <el-option-group label="计数">
              <el-option label="个" value="个" />
              <el-option label="瓶" value="瓶" />
              <el-option label="盒" value="盒" />
              <el-option label="支" value="支" />
            </el-option-group>
          </el-select>
        </el-form-item>
        <el-form-item label="最低库存" prop="minStock">
          <el-input-number
            v-model="form.minStock"
            :min="0"
            :precision="2"
            :step="1"
            style="width: 180px"
          />
        </el-form-item>
        <el-form-item label="危险等级" prop="dangerLevel">
          <el-select v-model="form.dangerLevel" placeholder="请选择危险等级">
            <el-option label="低危" value="low" />
            <el-option label="中危" value="medium" />
            <el-option label="高危" value="high" />
          </el-select>
        </el-form-item>
        <el-form-item label="有效期至" prop="expiryDate">
          <el-date-picker
            v-model="form.expiryDate"
            type="date"
            placeholder="选择日期"
            style="width: 180px"
          />
        </el-form-item>
        <el-form-item label="生产厂商" prop="manufacturer">
          <el-input v-model="form.manufacturer" placeholder="请输入生产厂商" />
        </el-form-item>
        <el-form-item label="保管人" prop="keeper">
          <el-input v-model="form.keeper" placeholder="请输入保管人" />
        </el-form-item>
        <el-form-item label="存放位置" prop="location">
          <el-input v-model="form.location" placeholder="请输入存放位置" />
        </el-form-item>
        <el-form-item label="安全说明" prop="safetyInfo">
          <el-input
            v-model="form.safetyInfo"
            type="textarea"
            :rows="3"
            placeholder="请输入安全使用说明"
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

    <!-- 出入库对话框 -->
    <el-dialog
      v-model="inOutDialogVisible"
      title="试剂出入库"
      width="500px"
    >
      <el-form
        ref="inOutFormRef"
        :model="inOutForm"
        :rules="inOutRules"
        label-width="100px"
      >
        <el-form-item label="操作类型" prop="type">
          <el-radio-group v-model="inOutForm.type">
            <el-radio label="in">入库</el-radio>
            <el-radio label="out">出库</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="数量" prop="amount">
          <el-input-number
            v-model="inOutForm.amount"
            :min="0"
            :precision="2"
            :step="1"
            style="width: 180px"
          />
          <el-select v-model="inOutForm.unit" placeholder="单位" class="ml-2" style="width: 100px">
            <el-option-group label="体积">
              <el-option label="毫升" value="ml" />
              <el-option label="升" value="L" />
            </el-option-group>
            <el-option-group label="质量">
              <el-option label="毫克" value="mg" />
              <el-option label="克" value="g" />
              <el-option label="千克" value="kg" />
            </el-option-group>
            <el-option-group label="计数">
              <el-option label="个" value="个" />
              <el-option label="瓶" value="瓶" />
              <el-option label="盒" value="盒" />
              <el-option label="支" value="支" />
            </el-option-group>
          </el-select>
        </el-form-item>
        <el-form-item label="操作人" prop="operator">
          <el-input v-model="inOutForm.operator" placeholder="请输入操作人" />
        </el-form-item>
        <el-form-item label="备注" prop="remark">
          <el-input
            v-model="inOutForm.remark"
            type="textarea"
            :rows="3"
            placeholder="请输入备注信息"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="inOutDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="inOutLoading" @click="handleInOutSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getReagentList,
  addReagent,
  updateReagent,
  deleteReagent,
  reagentInOut,
  uploadReagentImage
} from '@/api/reagent'
import { getLabList } from '@/api/lab'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'

const router = useRouter()
const userStore = useUserStore()

// 检查用户权限
const isAdmin = computed(() => userStore.roles.includes('admin'))
const currentUserId = computed(() => userStore.userInfo?.id)

// 搜索表单
const searchForm = reactive({
  name: '',
  code: '',
  labId: '',
  dangerLevel: ''
})

// 实验室选项
const labOptions = ref([])

// 获取实验室列表
const fetchLabOptions = async () => {
  try {
    const res = await getLabList({ limit: 100 })
    if (res.code === 0) {
      // 添加调试信息
      console.log('实验室列表数据:', res.data.list)
      labOptions.value = res.data.list
    } else {
      ElMessage.error(res.msg || '获取实验室列表失败')
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
const reagentList = ref([])

// 统计数据
const statistics = computed(() => {
  const total = reagentList.value.length
  const sufficient = reagentList.value.filter(item => item.stock > (item.min_stock || 10)).length
  const insufficient = reagentList.value.filter(item => item.stock <= (item.min_stock || 10)).length
  const dangerous = reagentList.value.filter(item => item.danger_level === 'high').length
  
  return {
    total,
    sufficient,
    insufficient,
    dangerous
  }
})

// 表单数据
const dialogVisible = ref(false)
const dialogType = ref('add')
const submitLoading = ref(false)
const formRef = ref(null)
const form = reactive({
  id: '',
  name: '',
  code: '',
  labId: '',
  image: '',
  specification: '',
  stock: 0,
  minStock: 0,
  unit: 'ml',
  dangerLevel: 'low',
  expiryDate: '',
  manufacturer: '',
  keeper: '',
  location: '',
  safetyInfo: ''
})

// 出入库数据
const inOutDialogVisible = ref(false)
const inOutLoading = ref(false)
const inOutFormRef = ref(null)
const currentReagent = ref({})
const inOutForm = reactive({
  type: 'in',
  amount: 0,
  unit: '',
  operator: '',
  remark: ''
})

// 表单校验规则
const rules = {
  name: [
    { required: true, message: '请输入试剂名称', trigger: 'blur' },
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入试剂编号', trigger: 'blur' },
    { pattern: /^RG\d{7}$/, message: '编号格式为：RG + 7位数字，如：RG2024001', trigger: 'blur' }
  ],
  labId: [
    { required: true, message: '请选择所属实验室', trigger: 'change' }
  ],
  specification: [
    { required: true, message: '请输入规格', trigger: 'blur' }
  ],
  stock: [
    { required: true, message: '请输入库存量', trigger: 'blur' }
  ],
  minStock: [
    { required: true, message: '请输入最低库存', trigger: 'blur' }
  ],
  unit: [
    { required: true, message: '请选择单位', trigger: 'change' }
  ],
  dangerLevel: [
    { required: true, message: '请选择危险等级', trigger: 'change' }
  ],
  expiryDate: [
    { required: true, message: '请选择有效期', trigger: 'change' }
  ],
  manufacturer: [
    { required: true, message: '请输入生产厂商', trigger: 'blur' }
  ],
  keeper: [
    { required: true, message: '请输入保管人', trigger: 'blur' }
  ],
  location: [
    { required: true, message: '请输入存放位置', trigger: 'blur' }
  ]
}

// 出入库表单校验规则
const inOutRules = {
  type: [
    { required: true, message: '请选择操作类型', trigger: 'change' }
  ],
  amount: [
    { required: true, message: '请输入数量', trigger: 'blur' }
  ],
  unit: [
    { required: true, message: '请选择单位', trigger: 'change' }
  ],
  operator: [
    { required: true, message: '请输入操作人', trigger: 'blur' }
  ]
}

// 危险等级标签
const getDangerLevelTag = (level) => {
  const map = {
    low: 'info',
    medium: 'warning',
    high: 'danger'
  }
  return map[level]
}

const getDangerLevelName = (level) => {
  const map = {
    low: '低危',
    medium: '中危',
    high: '高危'
  }
  return map[level]
}

// 检查是否即将过期（30天内）
const isExpiringSoon = (date) => {
  const expiryDate = new Date(date)
  const now = new Date()
  const diffDays = Math.ceil((expiryDate - now) / (1000 * 60 * 60 * 24))
  return diffDays <= 30 && diffDays > 0
}

// 搜索和重置
const handleSearch = () => {
  currentPage.value = 1
  fetchData()
}

const handleReset = () => {
  searchForm.name = ''
  searchForm.code = ''
  searchForm.labId = ''
  searchForm.dangerLevel = ''
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
      code: searchForm.code,
      lab_id: searchForm.labId,
      danger_level: searchForm.dangerLevel
    }
    
    // 如果不是管理员，只显示用户管理的实验室的试剂
    if (!isAdmin.value && currentUserId.value) {
      params.manager_id = currentUserId.value
    }
    
    const res = await getReagentList(params)
    if (res.code === 0) {
      // 添加调试信息
      console.log('试剂列表数据:', res.data.list)
      
      // 确保每个试剂项都有实验室信息
      reagentList.value = res.data.list.map(item => {
        // 如果没有实验室信息，设置默认值
        if (!item.lab_name) {
          console.warn(`试剂 ${item.name} (ID: ${item.id}) 缺少实验室信息`)
          item.lab_name = '未知实验室'
        }
        if (!item.lab_room_no) {
          item.lab_room_no = '未知房间'
        }
        return item
      })
      
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

// 图片上传
const handleUploadSuccess = (res) => {
  if (res.code === 0) {
    form.image = res.data.url
    ElMessage.success('上传成功')
  } else {
    ElMessage.error(res.msg || '上传失败')
  }
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
  form.id = ''
  form.name = ''
  form.code = ''
  form.labId = ''
  form.image = ''
  form.specification = ''
  form.stock = 0
  form.minStock = 0
  form.unit = 'ml'
  form.dangerLevel = 'low'
  form.expiryDate = ''
  form.manufacturer = ''
  form.keeper = ''
  form.location = ''
  form.safetyInfo = ''
}

// 编辑
const handleEdit = (row) => {
  dialogType.value = 'edit'
  dialogVisible.value = true
  form.id = row.id
  form.name = row.name
  form.code = row.code
  form.labId = row.lab_id
  form.image = row.image
  form.specification = row.specification
  form.stock = row.stock
  form.minStock = row.min_stock
  form.unit = row.unit
  form.dangerLevel = row.danger_level
  form.expiryDate = row.expiry_date
  form.manufacturer = row.manufacturer
  form.keeper = row.keeper
  form.location = row.location
  form.safetyInfo = row.safety_info
}

// 查看
const handleView = (row) => {
  ElMessage('查看详情：' + row.name)
}

// 出入库
const handleInOut = (row) => {
  currentReagent.value = row
  inOutDialogVisible.value = true
  inOutForm.type = 'in'
  inOutForm.amount = 0
  inOutForm.unit = row.unit // 默认使用试剂的单位
  inOutForm.operator = ''
  inOutForm.remark = ''
}

// 使用记录
const handleRecord = (row) => {
  router.push({
    name: 'reagent_records',
    query: {
      reagent_id: row.id,
      name: row.name,
      code: row.code
    }
  })
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定要删除试剂"${row.name}"吗？`,
    '警告',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const res = await deleteReagent(row.id)
      if (res.code === 0) {
        ElMessage.success('删除成功')
        fetchData()
      } else {
        ElMessage.error(res.msg || '删除失败')
      }
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
    
    // 格式化日期为 YYYY-MM-DD 格式
    const formatDate = (date) => {
      if (!date) return ''
      const d = new Date(date)
      const year = d.getFullYear()
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const day = String(d.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    }
    
    const data = {
      name: form.name,
      code: form.code,
      lab_id: form.labId,
      image: form.image,
      specification: form.specification,
      stock: form.stock,
      min_stock: form.minStock,
      unit: form.unit,
      danger_level: form.dangerLevel,
      expiry_date: formatDate(form.expiryDate),
      manufacturer: form.manufacturer,
      keeper: form.keeper,
      location: form.location,
      safety_info: form.safetyInfo
    }
    
    let res
    if (dialogType.value === 'add') {
      res = await addReagent(data)
    } else {
      // 确保 ID 是数字类型
      const id = parseInt(form.id)
      if (isNaN(id)) {
        throw new Error('无效的试剂 ID')
      }
      res = await updateReagent(id, data)
    }
    
    if (res.code === 0) {
      ElMessage.success(dialogType.value === 'add' ? '新增成功' : '编辑成功')
      dialogVisible.value = false
      fetchData()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    console.error('提交失败：', error)
    ElMessage.error('提交失败：' + (error.message || '未知错误'))
  } finally {
    submitLoading.value = false
  }
}

// 提交出入库
const handleInOutSubmit = async () => {
  if (!inOutFormRef.value) return

  try {
    await inOutFormRef.value.validate()
    inOutLoading.value = true

    const data = {
      reagent_id: currentReagent.value.id,
      type: inOutForm.type,
      amount: inOutForm.amount,
      unit: inOutForm.unit,
      operator: inOutForm.operator,
      remark: inOutForm.remark
    }
    
    const res = await reagentInOut(data)
    
    if (res.code === 0) {
      ElMessage.success(inOutForm.type === 'in' ? '入库成功' : '出库成功')
      inOutDialogVisible.value = false
      fetchData()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    console.error('操作失败：', error)
    ElMessage.error('操作失败：' + (error.message || '未知错误'))
  } finally {
    inOutLoading.value = false
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
.reagent-management {
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

.stat-card.total::before { background: linear-gradient(135deg, #8e44ad 0%, #3498db 100%); }
.stat-card.normal::before { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card.low::before { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.stat-card.danger::before { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }

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

.stat-card.total .stat-icon { background: linear-gradient(135deg, #8e44ad 0%, #3498db 100%); }
.stat-card.normal .stat-icon { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card.low .stat-icon { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.stat-card.danger .stat-icon { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }

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
  background: linear-gradient(135deg, #8e44ad 0%, #3498db 100%);
  border: none;
  border-radius: 8px;
}

.reset-btn {
  border-radius: 8px;
}

/* 表格样式 */
.reagent-table {
  :deep(.el-table__header) {
    background: #f8f9fa;
  }
  
  :deep(.el-table__row:hover) {
    background: #f8f9ff;
  }
}

.reagent-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.reagent-image {
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

.reagent-info {
  flex: 1;
}

.reagent-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 2px;
}

.reagent-code {
  font-size: 12px;
  color: #7f8c8d;
  font-family: 'Monaco', 'Consolas', monospace;
}

.spec-tag {
  background: #f0f2f5;
  border: none;
  font-size: 12px;
}

.lab-info {
  display: flex;
  align-items: center;
}

.stock-display {
  text-align: right;
}

.stock-amount {
  font-weight: 600;
  color: #27ae60;
  font-family: 'Monaco', 'Consolas', monospace;
}

.stock-display.stock-low .stock-amount {
  color: #e74c3c;
}

.stock-status {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  font-size: 12px;
  color: #e74c3c;
  margin-top: 2px;
  gap: 4px;
}

.danger-low {
  background: linear-gradient(135deg, #27ae60 20%, #2ecc71 100%);
  border: none;
  color: white;
}

.danger-medium {
  background: linear-gradient(135deg, #f39c12 20%, #e67e22 100%);
  border: none;
  color: white;
}

.danger-high {
  background: linear-gradient(135deg, #e74c3c 20%, #c0392b 100%);
  border: none;
  color: white;
}

.expiry-display {
  display: flex;
  align-items: center;
  font-family: 'Monaco', 'Consolas', monospace;
}

.expiry-display.expiry-warning {
  color: #e67e22;
  font-weight: 600;
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
  .reagent-management {
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