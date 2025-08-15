<template>
  <div class="reagent-usage-management">
    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <div class="i-carbon-chemistry text-3xl text-blue-600" />
          <div>
            <h1 class="page-title">试剂领用</h1>
            <p class="page-subtitle">管理和申请试剂使用，查看使用记录</p>
          </div>
        </div>
        <el-radio-group v-model="activeTab" size="large" class="tab-switch">
          <el-radio-button label="list">
            <div class="i-carbon-chemistry mr-1" />
            试剂列表
          </el-radio-button>
          <el-radio-button label="records">
            <div class="i-carbon-document mr-1" />
            使用记录
          </el-radio-button>
        </el-radio-group>
      </div>
    </div>

    <!-- 试剂列表 -->
    <div v-if="activeTab === 'list'">
      <!-- 搜索区域 -->
      <el-card class="search-card">
        <template #header>
          <div class="card-header">
            <div class="i-carbon-search mr-2" />
            <span>搜索试剂</span>
          </div>
        </template>
        <el-form :model="searchForm" inline class="search-form">
          <el-form-item>
            <el-input
              v-model="searchForm.name"
              placeholder="请输入试剂名称"
              clearable
              class="search-input"
            >
              <template #prefix>
                <div class="i-carbon-chemistry text-gray-400" />
              </template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-select 
              v-model="searchForm.danger_level" 
              placeholder="请选择危险等级" 
              clearable
              class="search-select"
            >
              <template #prefix>
                <div class="i-carbon-warning text-gray-400" />
              </template>
              <el-option label="低危" value="low" />
              <el-option label="中危" value="medium" />
              <el-option label="高危" value="high" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-select 
              v-model="searchForm.lab_id" 
              placeholder="请选择实验室" 
              clearable
              class="search-select"
            >
              <template #prefix>
                <div class="i-carbon-location text-gray-400" />
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

      <!-- 试剂列表表格 -->
      <el-card class="table-card">
        <el-table 
          :data="filteredReagentList"
          v-loading="loading"
          stripe
          class="reagent-table"
        >
          <el-table-column label="试剂图片" width="120" align="center">
            <template #default="scope">
              <el-image
                :src="scope.row.image"
                class="reagent-image-small"
                fit="cover"
                :preview-src-list="[scope.row.image]"
              >
                <template #error>
                  <div class="image-placeholder-small">
                    <div class="i-carbon-chemistry" />
                  </div>
                </template>
              </el-image>
            </template>
          </el-table-column>
          
          <el-table-column label="试剂信息" min-width="200">
            <template #default="scope">
              <div class="reagent-info-cell">
                <div class="reagent-name-cell">{{ scope.row.name }}</div>
                <div class="reagent-code-cell">编号：{{ scope.row.code }}</div>
                <div class="reagent-spec-cell" v-if="scope.row.specification">
                  规格：{{ scope.row.specification }}
                </div>
              </div>
            </template>
          </el-table-column>
          
          <el-table-column label="所在实验室" width="150">
            <template #default="scope">
              <div class="lab-info-cell">
                <div class="i-carbon-location mr-1" />
                <span class="lab-name-cell">{{ getLabName(scope.row.lab_id) }}</span>
              </div>
            </template>
          </el-table-column>
          
          <el-table-column label="危险等级" width="100" align="center">
            <template #default="scope">
              <el-tag 
                :type="getDangerLevelType(scope.row.danger_level)"
                :class="`danger-${scope.row.danger_level}`"
                size="small"
              >
                {{ getDangerLevelText(scope.row.danger_level) }}
              </el-tag>
            </template>
          </el-table-column>
          
          <el-table-column label="库存信息" width="150">
            <template #default="scope">
              <div class="stock-info-cell">
                <div :class="getStockClass(scope.row.stock, scope.row.min_stock)">
                  {{ scope.row.stock }} {{ scope.row.unit }}
                </div>
                <div class="min-stock-cell">最低：{{ scope.row.min_stock }} {{ scope.row.unit }}</div>
              </div>
            </template>
          </el-table-column>
          
          <el-table-column label="有效期" width="120">
            <template #default="scope">
              <span v-if="scope.row.expiry_date" :class="getExpiryClass(scope.row.expiry_date)">
                {{ scope.row.expiry_date }}
              </span>
              <span v-else class="text-gray-400">无</span>
            </template>
          </el-table-column>
          
          <el-table-column label="保管人" width="100">
            <template #default="scope">
              <span v-if="scope.row.keeper">{{ scope.row.keeper }}</span>
              <span v-else class="text-gray-400">未设置</span>
            </template>
          </el-table-column>
          
          <el-table-column label="存放位置" width="120">
            <template #default="scope">
              <span v-if="scope.row.location">{{ scope.row.location }}</span>
              <span v-else class="text-gray-400">未设置</span>
            </template>
          </el-table-column>
          
          <el-table-column label="操作" width="120" align="center" fixed="right">
            <template #default="scope">
              <el-button 
                type="primary"
                size="small"
                :disabled="scope.row.stock <= 0"
                @click="handleApply(scope.row)"
              >
                <div class="i-carbon-add mr-1" />
                {{ scope.row.stock > 0 ? '申请使用' : '库存不足' }}
              </el-button>
            </template>
          </el-table-column>
        </el-table>
        
        <!-- 空状态 -->
        <div v-if="!loading && filteredReagentList.length === 0" class="empty-state">
          <div class="empty-icon">
            <div class="i-carbon-chemistry text-6xl text-gray-300" />
          </div>
          <div class="empty-text">
            <h3>暂无试剂数据</h3>
            <p v-if="searchForm.name || searchForm.danger_level || searchForm.lab_id">
              尝试调整搜索条件或
              <el-button type="primary" link @click="handleReset">清空筛选</el-button>
            </p>
            <p v-else>系统中还没有可用的试剂</p>
          </div>
        </div>
      </el-card>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="total"
          :page-sizes="[9, 18, 36, 72]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </div>

    <!-- 使用记录 -->
    <div v-if="activeTab === 'records'">
      <!-- 搜索区域 -->
      <el-card class="search-card">
        <template #header>
          <div class="card-header">
            <div class="i-carbon-search mr-2" />
            <span>搜索记录</span>
          </div>
        </template>
        <el-form :model="recordSearchForm" inline class="search-form">
          <el-form-item>
            <el-input
              v-model="recordSearchForm.reagent_name"
              placeholder="请输入试剂名称"
              clearable
              class="search-input"
            >
              <template #prefix>
                <div class="i-carbon-chemistry text-gray-400" />
              </template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-select 
              v-model="recordSearchForm.status" 
              placeholder="请选择状态" 
              clearable
              class="search-select"
            >
              <template #prefix>
                <div class="i-carbon-status text-gray-400" />
              </template>
              <el-option label="待审核" value="pending" />
              <el-option label="已通过" value="approved" />
              <el-option label="已拒绝" value="rejected" />
              <el-option label="已完成" value="completed" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-date-picker
              v-model="recordSearchForm.dateRange"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              class="search-date"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleRecordSearch" class="search-btn">
              <div class="i-carbon-search mr-1" />
              搜索
            </el-button>
            <el-button @click="handleRecordReset" class="reset-btn">
              <div class="i-carbon-reset mr-1" />
              重置
            </el-button>
          </el-form-item>
        </el-form>
      </el-card>

      <!-- 使用记录卡片列表 -->
      <div class="records-grid">
        <!-- 空状态提示 -->
        <div v-if="!recordLoading && recordList.length === 0" class="empty-state">
          <div class="empty-icon">
            <div class="i-carbon-document text-6xl text-gray-300" />
          </div>
          <div class="empty-text">
            <h3>暂无使用记录</h3>
            <p>您还没有试剂使用记录</p>
          </div>
        </div>
        
        <div v-for="record in recordList" :key="record.id" class="record-card">
          <div class="record-header">
            <div class="record-status">
              <el-tag 
                :type="getStatusType(record.status)"
                :class="`status-${record.status}`"
                size="large"
              >
                {{ getStatusText(record.status) }}
              </el-tag>
            </div>
            <div class="record-time">
              <div class="i-carbon-time mr-1" />
              {{ formatTime(record.create_time) }}
            </div>
          </div>
          
          <div class="record-content">
            <h3 class="reagent-name">{{ record.reagent_name }}</h3>
            <div class="reagent-code">编号：{{ record.reagent_code }}</div>
            
            <div class="usage-info">
              <div class="usage-item">
                <div class="i-carbon-cube mr-1" />
                <span class="label">使用数量：</span>
                <span class="usage-amount">{{ record.amount }} {{ record.unit }}</span>
              </div>
              
              <div class="usage-item">
                <div class="i-carbon-user mr-1" />
                <span class="label">操作人：</span>
                <span>{{ record.operator }}</span>
              </div>
            </div>

            <div class="remark-info" v-if="record.remark">
              <div class="remark-label">
                <div class="i-carbon-document mr-1" />
                用途说明
              </div>
              <div class="remark-text">{{ record.remark }}</div>
            </div>
            
            <div class="approve-info" v-if="record.approve_remark">
              <div class="approve-label">
                <div class="i-carbon-checkmark mr-1" />
                审核意见
              </div>
              <div class="approve-text">{{ record.approve_remark }}</div>
              <div class="approver">审核人：{{ record.approver }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="recordPage"
          v-model:page-size="recordPageSize"
          :total="recordTotal"
          :page-sizes="[9, 18, 36, 72]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleRecordSizeChange"
          @current-change="handleRecordCurrentChange"
        />
      </div>
    </div>

    <!-- 申请使用对话框 -->
    <el-dialog
      v-model="dialogVisible"
      title="申请使用试剂"
      width="600px"
    >
      <div class="apply-form">
        <div class="reagent-info-section">
          <h4>试剂信息</h4>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">试剂名称：</span>
              <span>{{ form.reagent_name }}</span>
            </div>
            <div class="info-item">
              <span class="label">所在实验室：</span>
              <span>{{ getLabName(form.lab_id) }}</span>
            </div>
            <div class="info-item">
              <span class="label">当前库存：</span>
              <span class="stock-display">{{ form.current_stock }} {{ form.unit }}</span>
            </div>
            <div class="info-item">
              <span class="label">危险等级：</span>
              <el-tag :type="getDangerLevelType(form.danger_level)" size="small">
                {{ getDangerLevelText(form.danger_level) }}
              </el-tag>
            </div>
          </div>
        </div>

        <el-form
          ref="formRef"
          :model="form"
          :rules="rules"
          label-width="120px"
        >
          <el-form-item label="使用数量" prop="amount">
            <div class="amount-input-group">
              <el-input-number
                v-model="form.amount"
                :min="0"
                :max="form.current_stock"
                :precision="2"
                :step="0.1"
                style="width: 200px"
              />
              <el-select v-model="form.unit" placeholder="单位" class="unit-select">
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
            </div>
          </el-form-item>
          <el-form-item label="用途说明" prop="remark">
            <el-input
              v-model="form.remark"
              type="textarea"
              :rows="4"
              placeholder="请详细说明使用用途和实验目的"
            />
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          提交申请
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { getReagentList, getReagentRecords, applyReagentUsage } from '@/api/reagentUsage'
import { getLabList } from '@/api/lab'
import dayjs from 'dayjs'
import { useUserStore } from '@/stores/user'

// 获取用户状态
const userStore = useUserStore()

// 标签页
const activeTab = ref('list')

// 试剂列表
const loading = ref(false)
const reagentList = ref([])
const currentPage = ref(1)
const pageSize = ref(9)
const total = ref(0)

// 实验室列表
const labList = ref([])
const labOptions = computed(() => labList.value)

// 搜索表单
const searchForm = reactive({
  name: '',
  danger_level: '',
  lab_id: ''
})

// 使用记录
const recordLoading = ref(false)
const recordList = ref([])
const recordPage = ref(1)
const recordPageSize = ref(9)
const recordTotal = ref(0)

// 使用记录搜索表单
const recordSearchForm = reactive({
  reagent_name: '',
  status: '',
  dateRange: []
})

// 申请表单
const dialogVisible = ref(false)
const submitLoading = ref(false)
const formRef = ref(null)
const form = reactive({
  reagent_id: '',
  reagent_name: '',
  lab_id: '',
  danger_level: '',
  current_stock: 0,
  unit: '',
  amount: 0,
  remark: ''
})

// 表单验证规则
const rules = {
  amount: [
    { required: true, message: '请输入使用数量', trigger: 'blur' },
    { type: 'number', min: 0.01, message: '数量必须大于0', trigger: 'blur' }
  ],
  remark: [
    { required: true, message: '请输入用途说明', trigger: 'blur' },
    { max: 500, message: '用途说明不能超过500个字符', trigger: 'blur' }
  ]
}

// 过滤后的试剂列表
const filteredReagentList = computed(() => {
  if (!reagentList.value) return []
  
  return reagentList.value.filter(reagent => {
    // 名称过滤
    if (searchForm.name && !reagent.name.toLowerCase().includes(searchForm.name.toLowerCase())) {
      return false
    }
    
    // 危险等级过滤
    if (searchForm.danger_level && reagent.danger_level !== searchForm.danger_level) {
      return false
    }
    
    // 实验室过滤
    if (searchForm.lab_id && reagent.lab_id !== parseInt(searchForm.lab_id)) {
      return false
    }
    
    return true
  })
})

// 获取实验室列表
const getLabsList = async () => {
  try {
    const res = await getLabList()
    if (res.code === 0 && res.data && Array.isArray(res.data.list)) {
      labList.value = res.data.list
    } else {
      labList.value = []
    }
  } catch (error) {
    console.error('获取实验室列表失败：', error)
    labList.value = []
  }
}

// 获取实验室名称
const getLabName = (labId) => {
  if (!labId || !labList.value) return '未指定'
  const lab = labList.value.find(l => l.id === labId)
  return lab ? lab.name : '未找到实验室'
}

// 获取试剂列表
const getList = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      limit: pageSize.value
    }
    
    const res = await getReagentList(params)
    if (res.code === 0) {
      reagentList.value = res.data.list || []
      total.value = res.data.total || 0
    } else {
      ElMessage.error(res.msg || '获取试剂列表失败')
      reagentList.value = []
      total.value = 0
    }
  } catch (error) {
    console.error('获取试剂列表失败:', error)
    ElMessage.error('获取试剂列表失败')
    reagentList.value = []
    total.value = 0
  } finally {
    loading.value = false
  }
}

// 获取使用记录
const getRecords = async () => {
  recordLoading.value = true
  try {
    const params = {
      page: recordPage.value,
      limit: recordPageSize.value
    }
    
    // 添加搜索参数
    if (recordSearchForm.reagent_name) {
      params.reagent_name = recordSearchForm.reagent_name
    }
    if (recordSearchForm.status) {
      params.status = recordSearchForm.status
    }
    if (recordSearchForm.dateRange && recordSearchForm.dateRange.length === 2) {
      params.start_date = recordSearchForm.dateRange[0]
      params.end_date = recordSearchForm.dateRange[1]
    }
    
    const res = await getReagentRecords(params)
    if (res.code === 0) {
      recordList.value = res.data.list || []
      recordTotal.value = res.data.total || 0
    } else {
      ElMessage.error(res.msg || '获取使用记录失败')
      recordList.value = []
      recordTotal.value = 0
    }
  } catch (error) {
    console.error('获取使用记录失败:', error)
    ElMessage.error('获取使用记录失败')
    recordList.value = []
    recordTotal.value = 0
  } finally {
    recordLoading.value = false
  }
}

// 申请使用
const handleApply = (reagent) => {
  form.reagent_id = reagent.id
  form.reagent_name = reagent.name
  form.lab_id = reagent.lab_id
  form.danger_level = reagent.danger_level
  form.current_stock = reagent.stock
  form.unit = reagent.unit  
  form.amount = 0
  form.remark = ''
  dialogVisible.value = true
}

// 提交申请
const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      try {
        submitLoading.value = true
        const res = await applyReagentUsage({
          reagent_id: form.reagent_id,
          amount: form.amount,
          remark: form.remark,
          unit: form.unit,
          operator: userStore.userInfo.username || userStore.userInfo.name
        })
        
        if (res.code === 0) {
          ElMessage.success('申请提交成功')
          dialogVisible.value = false
          resetForm()
          getRecords() // 刷新使用记录
          getList() // 刷新试剂列表以更新库存
        } else {
          ElMessage.error(res.msg || '申请提交失败')
        }
      } catch (error) {
        console.error('提交申请失败:', error)
        ElMessage.error('提交申请失败')
      } finally {
        submitLoading.value = false
      }
    }
  })
}

// 重置表单
const resetForm = () => {
  Object.assign(form, {
    reagent_id: '',
    reagent_name: '',
    lab_id: '',
    danger_level: '',
    current_stock: 0,
    unit: '',
    amount: 0,
    remark: ''
  })
  if (formRef.value) {
    formRef.value.clearValidate()
  }
}

// 分页处理
const handleSizeChange = (val) => {
  pageSize.value = val
  getList()
}

const handleCurrentChange = (val) => {
  currentPage.value = val
  getList()
}

const handleRecordSizeChange = (val) => {
  recordPageSize.value = val
  getRecords()
}

const handleRecordCurrentChange = (val) => {
  recordPage.value = val
  getRecords()
}

// 搜索功能
const handleSearch = () => {
  currentPage.value = 1
  // 由于使用了计算属性 filteredReagentList，无需重新请求数据
}

const handleReset = () => {
  searchForm.name = ''
  searchForm.danger_level = ''
  searchForm.lab_id = ''
}

// 使用记录搜索
const handleRecordSearch = () => {
  recordPage.value = 1
  getRecords()
}

const handleRecordReset = () => {
  recordSearchForm.reagent_name = ''
  recordSearchForm.status = ''
  recordSearchForm.dateRange = []
  recordPage.value = 1
  getRecords()
}

// 工具函数
const getDangerLevelType = (level) => {
  const types = {
    low: 'success',
    medium: 'warning',
    high: 'danger'
  }
  return types[level] || 'info'
}

const getDangerLevelText = (level) => {
  const texts = {
    low: '低危',
    medium: '中危',
    high: '高危'
  }
  return texts[level] || level
}

const getStatusType = (status) => {
  const types = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
    completed: 'info'
  }
  return types[status] || 'info'
}

const getStatusText = (status) => {
  const texts = {
    pending: '待审核',
    approved: '已通过',
    rejected: '已拒绝',
    completed: '已完成'
  }
  return texts[status] || status
}

const formatTime = (timestamp) => {
  if (!timestamp) return ''
  // 如果是时间戳，转换为毫秒
  if (typeof timestamp === 'number' && timestamp < 10000000000) {
    return dayjs(timestamp * 1000).format('YYYY-MM-DD HH:mm:ss')
  }
  // 否则直接格式化
  return dayjs(timestamp).format('YYYY-MM-DD HH:mm:ss')
}

// 库存状态样式
const getStockClass = (current, min) => {
  if (current <= 0) return 'text-red-500 font-bold'
  if (current <= min) return 'text-orange-500 font-semibold'
  return 'text-green-600'
}

// 有效期状态样式
const getExpiryClass = (expiryDate) => {
  if (!expiryDate) return ''
  const today = new Date()
  const expiry = new Date(expiryDate)
  const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'text-red-500 font-bold' // 已过期
  if (diffDays <= 30) return 'text-orange-500 font-semibold' // 30天内过期
  if (diffDays <= 90) return 'text-yellow-600' // 90天内过期
  return 'text-green-600' // 正常
}

// 监听标签页切换
watch(activeTab, (val) => {
  if (val === 'records') {
    getRecords()
  }
})

onMounted(() => {
  getLabsList()
  getList()
})
</script>

<style scoped>
/* 整体布局样式 */
.reagent-usage-management {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 24px;
}

/* 页面头部样式 */
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

.tab-switch {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* 搜索卡片样式 */
.search-card {
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

.search-form {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
}

.search-input, .search-select {
  width: 200px;
}

.search-date {
  width: 280px;
}

.search-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 8px;
  font-weight: 500;
}

.reset-btn {
  border-radius: 8px;
}

/* 试剂表格卡片 */
.table-card {
  border-radius: 12px;
  margin-bottom: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  border: none;
  overflow: hidden;
}

/* 试剂表格样式 */
.reagent-table {
  border-radius: 8px;
  overflow: hidden;
}

.reagent-table .el-table__header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.reagent-table .el-table__header th {
  background: transparent;
  color: white;
  font-weight: 600;
  border: none;
}

.reagent-table .el-table__body tr:hover {
  background: linear-gradient(135deg, #f8f9ff 0%, #f3f0ff 100%);
}

/* 表格内容样式 */
.reagent-image-small {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  object-fit: cover;
}

.image-placeholder-small {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border-radius: 8px;
  border: 2px dashed #dee2e6;
  color: #ced4da;
  font-size: 24px;
}

.reagent-info-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.reagent-name-cell {
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
  line-height: 1.3;
}

.reagent-code-cell {
  font-size: 12px;
  color: #6c757d;
  font-family: 'Monaco', 'Consolas', monospace;
}

.reagent-spec-cell {
  font-size: 13px;
  color: #495057;
}

.lab-info-cell {
  display: flex;
  align-items: center;
  gap: 6px;
}

.lab-name-cell {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 600;
}

.stock-info-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.min-stock-cell {
  font-size: 12px;
  color: #868e96;
}

/* 通用颜色样式类 */
.text-red-500 {
  color: #ef4444;
}

.text-orange-500 {
  color: #f97316;
}

.text-yellow-600 {
  color: #ca8a04;
}

.text-green-600 {
  color: #16a34a;
}

.text-gray-400 {
  color: #9ca3af;
}

.font-bold {
  font-weight: 700;
}

.font-semibold {
  font-weight: 600;
}

/* 使用记录卡片网格布局 */
.records-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

/* 使用记录卡片样式 */
.record-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  border-left: 4px solid #f59e0b;
}

.record-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.record-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.record-status .el-tag {
  font-weight: 600;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 12px;
}

.record-time {
  display: flex;
  align-items: center;
  font-size: 12px;
  color: #7f8c8d;
  font-family: 'Monaco', 'Consolas', monospace;
  background: #f8f9fa;
  padding: 6px 12px;
  border-radius: 6px;
}

.record-content {
  margin-bottom: 20px;
}

.reagent-code {
  font-size: 13px;
  color: #6c757d;
  margin-bottom: 16px;
  font-family: 'Monaco', 'Consolas', monospace;
}

.usage-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.usage-item {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #495057;
  padding: 8px 0;
}

.usage-item .label {
  font-weight: 500;
  color: #6c757d;
  margin-right: 8px;
  min-width: 80px;
}

.usage-amount {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 600;
}

.remark-info, .approve-info {
  margin-top: 16px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 12px;
}

.remark-label, .approve-label {
  display: flex;
  align-items: center;
  font-size: 13px;
  color: #495057;
  margin-bottom: 8px;
  font-weight: 600;
}

.remark-text, .approve-text {
  color: #2c3e50;
  line-height: 1.5;
  font-size: 14px;
  margin-bottom: 8px;
}

.approver {
  font-size: 12px;
  color: #6c757d;
  text-align: right;
}

/* 分页样式 */
.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 24px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

/* 申请对话框样式 */
.apply-form {
  padding: 10px 0;
}

.reagent-info-section {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 24px;
}

.reagent-info-section h4 {
  margin: 0 0 16px 0;
  color: #2c3e50;
  font-size: 16px;
  font-weight: 600;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.info-grid .info-item {
  background: white;
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.info-grid .info-item .label {
  display: block;
  font-weight: 500;
  color: #6c757d;
  margin-bottom: 4px;
  font-size: 12px;
}

.stock-display {
  color: #16a34a;
  font-weight: 600;
}

.amount-input-group {
  display: flex;
  gap: 12px;
  align-items: center;
}

.unit-select {
  width: 120px;
}

/* 危险等级样式 */
.danger-low {
  background: linear-gradient(135deg, #10b981 20%, #059669 100%);
  border: none;
  color: white;
}

.danger-medium {
  background: linear-gradient(135deg, #f59e0b 20%, #d97706 100%);
  border: none;
  color: white;
}

.danger-high {
  background: linear-gradient(135deg, #ef4444 20%, #dc2626 100%);
  border: none;
  color: white;
}

/* 状态样式 */
.status-pending {
  background: linear-gradient(135deg, #f59e0b 20%, #d97706 100%);
  border: none;
  color: white;
}

.status-approved {
  background: linear-gradient(135deg, #10b981 20%, #059669 100%);
  border: none;
  color: white;
}

.status-rejected {
  background: linear-gradient(135deg, #ef4444 20%, #dc2626 100%);
  border: none;
  color: white;
}

.status-completed {
  background: linear-gradient(135deg, #6366f1 20%, #4f46e5 100%);
  border: none;
  color: white;
}

/* 空状态样式 */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: #fafafa;
  border-radius: 8px;
  margin: 20px;
}

.empty-icon {
  margin-bottom: 24px;
  opacity: 0.6;
}

.empty-text {
  text-align: center;
}

.empty-text h3 {
  color: #2c3e50;
  margin: 0 0 16px 0;
  font-size: 18px;
  font-weight: 600;
}

.empty-text p {
  color: #7f8c8d;
  margin: 0;
  line-height: 1.6;
  font-size: 14px;
}

/* 响应式设计 */
@media (max-width: 1024px) {
  .search-form {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input, .search-select, .search-date {
    width: 100%;
  }
  
  .reagent-table {
    font-size: 14px;
  }
  
  .reagent-table .el-table__cell {
    padding: 8px 4px;
  }
}

@media (max-width: 768px) {
  .reagent-usage-management {
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
  
  .search-form {
    gap: 12px;
  }
  
  .pagination-wrapper {
    padding: 16px;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .amount-input-group {
    flex-direction: column;
    align-items: stretch;
  }
  
  .unit-select {
    width: 100%;
  }
  
  /* 表格移动端优化 */
  .reagent-table {
    font-size: 12px;
  }
  
  .reagent-image-small {
    width: 40px;
    height: 40px;
  }
  
  .image-placeholder-small {
    width: 40px;
    height: 40px;
    font-size: 16px;
  }
  
  .reagent-name-cell {
    font-size: 14px;
  }
  
  .reagent-code-cell {
    font-size: 10px;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 24px;
  }
  
  .reagent-info-section {
    padding: 16px;
  }
  
  /* 表格小屏幕优化 */
  .reagent-table .el-table__cell {
    padding: 6px 2px;
  }
  
  .reagent-table .el-button {
    padding: 4px 8px;
    font-size: 12px;
  }
}
</style> 