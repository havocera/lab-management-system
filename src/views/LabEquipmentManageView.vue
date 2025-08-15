<template>
  <div class="space-y-4">
    <!-- 页面标题和操作按钮 -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <el-button type="info" @click="goBack" plain>
          <div class="i-carbon-arrow-left mr-1" />
          返回实验室列表
        </el-button>
        <div>
          <h2 class="text-2xl font-medium">{{ labInfo.name }} - 设备管理</h2>
          <p class="text-gray-500 text-sm">位置：{{ labInfo.location }} | 负责人：{{ labInfo.manager_name || '未设置' }}</p>
        </div>
      </div>
      <el-button type="primary" @click="handleAdd">
        <div class="i-carbon-add mr-1" />
        新增设备
      </el-button>
    </div>

    <!-- 搜索和筛选 -->
    <el-card>
      <el-form :model="searchForm" inline>
        <el-form-item label="设备名称">
          <el-input
            v-model="searchForm.name"
            placeholder="请输入设备名称"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="设备编号">
          <el-input
            v-model="searchForm.serial_number"
            placeholder="请输入设备编号"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="设备状态" style="width: 240px">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="正常" value="normal" />
            <el-option label="维修中" value="repairing" />
            <el-option label="报废" value="scrapped" />
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

    <!-- 设备统计 -->
    <el-row :gutter="16">
      <el-col :span="6">
        <el-card>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">设备总数</p>
              <p class="text-2xl font-bold text-blue-600">{{ statistics.total }}</p>
            </div>
            <div class="i-carbon-tool-box text-3xl text-blue-600" />
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">正常设备</p>
              <p class="text-2xl font-bold text-green-600">{{ statistics.normal }}</p>
            </div>
            <div class="i-carbon-checkmark-filled text-3xl text-green-600" />
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">维修中</p>
              <p class="text-2xl font-bold text-yellow-600">{{ statistics.repairing }}</p>
            </div>
            <div class="i-carbon-warning-filled text-3xl text-yellow-600" />
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">报废设备</p>
              <p class="text-2xl font-bold text-red-600">{{ statistics.scrapped }}</p>
            </div>
            <div class="i-carbon-trash-can text-3xl text-red-600" />
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 设备列表 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="equipmentList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="name" label="设备名称" min-width="180">
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
        <el-table-column prop="model" label="型号" width="120" />
        <el-table-column prop="serial_number" label="设备编号" width="120" />
        <el-table-column prop="price" label="设备价值" width="120">
          <template #default="{ row }">
            ¥{{ row.price.toLocaleString() }}
          </template>
        </el-table-column>
        <el-table-column prop="purchase_date" label="购入日期" width="120" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">{{ getStatusName(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="manufacturer" label="生产厂商" width="150" />
        <el-table-column prop="maintainer" label="维护人员" width="120" />
        <el-table-column label="操作" width="250" fixed="right">
          <template #default="{ row }">
            <el-button-group>
              <el-button type="primary" link @click="handleEdit(row)">
                编辑
              </el-button>
              <el-button type="primary" link @click="handleView(row)">
                查看
              </el-button>
              <el-button type="warning" link @click="handleMaintenance(row)">
                维护记录
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
import { useRoute, useRouter } from 'vue-router'
import { getEquipmentList, addEquipment, updateEquipment, deleteEquipment } from '@/api/equipment'
import { getLabDetail } from '@/api/lab'

const route = useRoute()
const router = useRouter()

// 实验室ID和信息
const labId = computed(() => parseInt(route.params.id))
const labInfo = ref({})

// 搜索表单
const searchForm = reactive({
  name: '',
  serial_number: '',
  status: ''
})

// 设备统计
const statistics = ref({
  total: 0,
  normal: 0,
  repairing: 0,
  scrapped: 0
})

// 表格数据
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const equipmentList = ref([])

// 表单数据
const dialogVisible = ref(false)
const dialogType = ref('add')
const submitLoading = ref(false)
const formRef = ref(null)
const form = reactive({
  name: '',
  model: '',
  serial_number: '',
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
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
  ],
  model: [
    { required: true, message: '请输入设备型号', trigger: 'blur' },
    { max: 50, message: '长度不能超过 50 个字符', trigger: 'blur' }
  ],
  serial_number: [
    { required: true, message: '请输入设备编号', trigger: 'blur' },
    { pattern: /^\d{10}$/, message: '编号格式为：10位数字，如：2024001001', trigger: 'blur' }
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

// 返回实验室列表
const goBack = () => {
  router.push('/labs')
}

// 获取实验室信息
const fetchLabInfo = async () => {
  try {
    const res = await getLabDetail(labId.value)
    if (res.data) {
      labInfo.value = res.data
    }
  } catch (error) {
    console.error('获取实验室信息失败：', error)
    ElMessage.error('获取实验室信息失败')
  }
}

// 更新统计信息
const updateStatistics = (list) => {
  statistics.value.total = list.length
  statistics.value.normal = list.filter(item => item.status === 'normal').length
  statistics.value.repairing = list.filter(item => item.status === 'repairing').length
  statistics.value.scrapped = list.filter(item => item.status === 'scrapped').length
}

// 搜索和重置
const handleSearch = () => {
  currentPage.value = 1
  fetchData()
}

const handleReset = () => {
  searchForm.name = ''
  searchForm.serial_number = ''
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
      lab_id: labId.value,
      name: searchForm.name,
      serial_number: searchForm.serial_number,
      status: searchForm.status
    }
    
    const res = await getEquipmentList(params)
    if (res.data) {
      equipmentList.value = res.data.list
      total.value = res.data.total
      updateStatistics(res.data.list)
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
  ElMessage('查看维护记录：' + row.name)
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
      lab_id: labId.value, 
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
  fetchLabInfo()
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