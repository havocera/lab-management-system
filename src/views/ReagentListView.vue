<template>
  <div class="space-y-4">
    <!-- 页面标题和操作按钮 -->
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-medium">试剂管理</h2>
      <el-button type="primary" @click="handleAdd">
        <div class="i-carbon-add mr-1" />
        新增试剂
      </el-button>
    </div>

    <!-- 搜索和筛选 -->
    <el-card>
      <el-form :model="searchForm" inline>
        <el-form-item label="试剂名称">
          <el-input
            v-model="searchForm.name"
            placeholder="请输入试剂名称"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="试剂编号">
          <el-input
            v-model="searchForm.code"
            placeholder="请输入试剂编号"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="所属实验室 " style="width: 240px">
          <el-select v-model="searchForm.labId" placeholder="请选择实验室" clearable>
            <el-option
              v-for="lab in labOptions"
              :key="lab.id"
              :label="lab.name" 
              :value="lab.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="危险等级" style="width: 240px">
          <el-select v-model="searchForm.dangerLevel" placeholder="请选择危险等级" clearable>
            <el-option label="低危" value="low" />
            <el-option label="中危" value="medium" />
            <el-option label="高危" value="high" />
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

    <!-- 试剂列表 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="reagentList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="name" label="试剂名称" min-width="180">
          <template #default="{ row }">
            <div class="flex items-center">
              <el-image
                :src="row.image"
                class="w-8 h-8 mr-2 rounded"
                fit="cover"
                :preview-src-list="[row.image]"
              >
                <template #error>
                  <div class="w-8 h-8 mr-2 rounded bg-gray-200 flex items-center justify-center">
                    <div class="i-carbon-image text-gray-400" />
                  </div>
                </template>
              </el-image>
              {{ row.name }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="code" label="试剂编号" width="120" />
        <el-table-column label="所属实验室" width="200">
          <template #default="{ row }">
            {{ row.lab_name }} ({{ row.lab_room_no }})
          </template>
        </el-table-column>
        <el-table-column prop="specification" label="规格" width="120" />
        <el-table-column prop="stock" label="库存量" width="120">
          <template #default="{ row }">
            <span :class="{ 'text-red-500': row.stock <= row.min_stock }">
              {{ row.stock }} {{ row.unit }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="dangerLevel" label="危险等级" width="100">
          <template #default="{ row }">
            <el-tag :type="getDangerLevelTag(row.danger_level)">
              {{ getDangerLevelName(row.danger_level) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="expiryDate" label="有效期至" width="120">
          <template #default="{ row }">
            <span :class="{ 'text-red-500': isExpiringSoon(row.expiry_date) }">
              {{ row.expiry_date }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="manufacturer" label="生产厂商" width="150" />
        <el-table-column prop="keeper" label="保管人" width="120" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button-group>
              <el-button type="primary" link @click="handleEdit(row)">
                编辑
              </el-button>
              <el-button type="primary" link @click="handleView(row)">
                查看
              </el-button>
              <el-button type="success" link @click="handleInOut(row)">
                出入库
              </el-button>
              <el-button type="warning" link @click="handleRecord(row)">
                使用记录
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
import { ref, reactive, onMounted } from 'vue'
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

const router = useRouter()

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
.el-card {
  --el-card-padding: 16px;
}

.avatar-uploader {
  :deep(.el-upload) {
    @apply border border-dashed border-gray-300 rounded cursor-pointer overflow-hidden;
    width: 96px;
    height: 96px;
  }

  :deep(.el-upload:hover) {
    @apply border-primary;
  }
}

.avatar-uploader-icon {
  @apply flex items-center justify-center text-3xl text-gray-400;
  width: 96px;
  height: 96px;
}
</style> 