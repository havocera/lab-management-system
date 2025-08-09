<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-medium">实验室预约</h2>
      <el-radio-group v-model="activeTab" size="large">
        <el-radio-button label="labs">实验室列表</el-radio-button>
        <el-radio-button label="my">我的预约</el-radio-button>
        <el-radio-button v-if="isTeacher" label="all">预约管理</el-radio-button>
      </el-radio-group>
    </div>

    <!-- 实验室列表 -->
    <div v-if="activeTab === 'labs'">
      <el-table
        v-loading="loading"
        :data="labList"
        border
      >
        <el-table-column prop="name" label="实验室名称" min-width="150" />
        <el-table-column prop="location" label="位置" min-width="150" />
        <el-table-column prop="capacity" label="容量" width="100" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === '0' ? 'success' : 'danger'">
              {{ row.status === '0' ? '可用' : '不可用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" @click="handleReserve(row)">
              预约使用
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- 我的预约 -->
    <div v-if="activeTab === 'my'">
      <el-table
        v-loading="loading"
        :data="myReservations"
        border
      >
        <el-table-column prop="lab_name" label="实验室" min-width="150" />
        <el-table-column label="使用时间" min-width="300">
          <template #default="{ row }">
            {{ formatDateTime(row.start_time) }} 至 {{ formatDateTime(row.end_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="purpose" label="用途" min-width="200" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button
              v-if="row.status === 'pending' || row.status === 'approved'"
              type="danger"
              @click="handleCancel(row)"
            >
              取消
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="mt-4 flex justify-center">
        <el-pagination
          v-model:current-page="myPage"
          v-model:page-size="myLimit"
          :total="myTotal"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next"
          @size-change="getMyReservations"
          @current-change="getMyReservations"
        />
      </div>
    </div>

    <!-- 预约管理（管理员） -->
    <div v-if="activeTab === 'all'">
      <el-form :inline="true" class="mb-4">
        <el-form-item label="实验室">
          <el-select v-model="searchForm.lab_id" placeholder="选择实验室" style="width: 200px;" clearable>
            <el-option
              v-for="lab in labList"
              :key="lab.id"
              :label="lab.name"
              :value="lab.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" style="width: 200px;" placeholder="选择状态" clearable>
            <el-option label="待审核" value="pending" />
            <el-option label="已批准" value="approved" />
            <el-option label="已拒绝" value="rejected" />
            <el-option label="已完成" value="completed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="时间范围">
          <el-date-picker
            v-model="searchForm.timeRange"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="resetSearch">重置</el-button>
        </el-form-item>
      </el-form>

      <el-table
        v-loading="loading"
        :data="allReservations"
        border
      >
        <el-table-column prop="lab_name" label="实验室" min-width="150" />
        <el-table-column prop="user_name" label="预约人" min-width="120" />
        <el-table-column label="使用时间" min-width="300">
          <template #default="{ row }">
            {{ formatDateTime(row.start_time) }} 至 {{ formatDateTime(row.end_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="purpose" label="用途" min-width="200" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button
              v-if="row.status === 'pending'"
              type="success"
              @click="handleReview(row, 'approved')"
            >
              批准
            </el-button>
            <el-button
              v-if="row.status === 'pending'"
              type="danger"
              @click="handleReview(row, 'rejected')"
            >
              拒绝
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="mt-4 flex justify-center">
        <el-pagination
          v-model:current-page="allPage"
          v-model:page-size="allLimit"
          :total="allTotal"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next"
          @size-change="getAllReservations"
          @current-change="getAllReservations"
        />
      </div>
    </div>

    <!-- 预约对话框 -->
    <el-dialog
      v-model="dialogVisible"
      title="预约实验室"
      width="500px"
      destroy-on-close
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="实验室" prop="lab_id">
          <el-input v-model="form.lab_name" disabled />
        </el-form-item>
        <el-form-item label="使用时间" prop="start_time">
          <el-date-picker
            v-model="form.start_time"
            type="datetime"
            placeholder="选择开始时间"
            :disabled-date="disabledDate"
          />
        </el-form-item>
        <el-form-item label="结束时间" prop="end_time">
          <el-date-picker
            v-model="form.end_time"
            type="datetime"
            placeholder="选择结束时间"
            :disabled-date="disabledDate"
          />
        </el-form-item>
        <el-form-item label="用途说明" prop="purpose">
          <el-input
            v-model="form.purpose"
            type="textarea"
            :rows="3"
            placeholder="请输入使用用途"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getLabList } from '@/api/lab'
import { createReservation, getReservationList, getMyReservations, cancelReservation, reviewReservation } from '@/api/reservation'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const isAdmin = computed(() => userStore.roles.includes('admin'))
const isTeacher = computed(() => userStore.roles.includes('teacher'))

const loading = ref(false)
const labList = ref([])
const dialogVisible = ref(false)
const formRef = ref(null)
const currentLab = ref(null)
const activeTab = ref('labs')

// 我的预约
const myReservations = ref([])
const myPage = ref(1)
const myLimit = ref(10)
const myTotal = ref(0)

// 所有预约（管理员）
const allReservations = ref([])
const allPage = ref(1)
const allLimit = ref(10)
const allTotal = ref(0)
const searchForm = reactive({
  lab_id: '',
  status: '',
  timeRange: []
})

const form = reactive({
  lab_id: '',
  lab_name: '',
  start_time: '',
  end_time: '',
  purpose: ''
})

const rules = {
  start_time: [
    { required: true, message: '请选择开始时间', trigger: 'change' }
  ],
  end_time: [
    { required: true, message: '请选择结束时间', trigger: 'change' }
  ],
  purpose: [
    { required: true, message: '请输入用途说明', trigger: 'blur' },
    { min: 2, max: 200, message: '长度在 2 到 200 个字符', trigger: 'blur' }
  ]
}

// 获取实验室列表
const getList = async () => {
  try {
    loading.value = true
    const res = await getLabList()
    if (res.code === 0 && res.data && Array.isArray(res.data.list)) {
      labList.value = res.data.list.filter(lab => lab.status === '0')
    } else {
      labList.value = []
      console.warn('实验室数据格式不正确')
    }
  } catch (error) {
    console.error('获取实验室列表失败：', error)
    ElMessage.error('获取实验室列表失败')
    labList.value = []
  } finally {
    loading.value = false
  }
}

// 获取我的预约
const getMyReservationsList = async () => {
  try {
    loading.value = true
    const res = await getMyReservations({
      page: myPage.value,
      limit: myLimit.value
    })
    if (res.code === 0 && res.data) {
      myReservations.value = res.data.list || []
      myTotal.value = res.data.total || 0
    } else {
      myReservations.value = []
      myTotal.value = 0
    }
  } catch (error) {
    console.error('获取我的预约失败：', error)
    ElMessage.error('获取我的预约失败')
    myReservations.value = []
    myTotal.value = 0
  } finally {
    loading.value = false
  }
}

// 获取所有预约（管理员）
const getAllReservations = async () => {
  try {
    loading.value = true
    const params = {
      page: allPage.value,
      limit: allLimit.value,
      lab_id: searchForm.lab_id,
      status: searchForm.status
    }
    if (searchForm.timeRange && searchForm.timeRange.length === 2) {
      params.start_time = searchForm.timeRange[0]
      params.end_time = searchForm.timeRange[1]
    }
    const res = await getReservationList(params)
    if (res.code === 0 && res.data) {
      allReservations.value = res.data.list || []
      allTotal.value = res.data.total || 0
    } else {
      allReservations.value = []
      allTotal.value = 0
    }
  } catch (error) {
    console.error('获取预约列表失败：', error)
    ElMessage.error('获取预约列表失败')
    allReservations.value = []
    allTotal.value = 0
  } finally {
    loading.value = false
  }
}

// 禁用过去的日期
const disabledDate = (time) => {
  return time.getTime() < Date.now() - 8.64e7 // 禁用今天之前的日期
}

// 打开预约对话框
const handleReserve = (row) => {
  currentLab.value = row
  dialogVisible.value = true
  form.lab_id = row.id
  form.lab_name = row.name
}

// 提交预约
const handleSubmit = async () => {
  if (!formRef.value) return
  
  try {
    await formRef.value.validate()
    
    // 验证时间
    if (form.end_time <= form.start_time) {
      ElMessage.error('结束时间必须大于开始时间')
      return
    }
    
    const res = await createReservation(form)
    
    if (res.code === 0) {
      ElMessage.success('预约成功')
      dialogVisible.value = false
      getList()
      getMyReservationsList()
    } else {
      ElMessage.error(res.msg || '预约失败')
    }
  } catch (error) {
    console.error('预约失败：', error)
    ElMessage.error('预约失败：' + (error.message || '未知错误'))
  }
}

// 取消预约
const handleCancel = async (row) => {
  try {
    await ElMessageBox.confirm('确定要取消该预约吗？', '提示', {
      type: 'warning'
    })
    const res = await cancelReservation(row.id)
    if (res.code === 0) {
      ElMessage.success('取消成功')
      getMyReservationsList()
      if (isAdmin.value) {
        getAllReservations()
      }
    } else {
      ElMessage.error(res.msg || '取消失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('取消预约失败：', error)
      ElMessage.error('取消预约失败：' + (error.message || '未知错误'))
    }
  }
}

// 审核预约
const handleReview = async (row, status) => {
  try {
    const action = status === 'approved' ? '批准' : '拒绝'
    await ElMessageBox.confirm(`确定要${action}该预约吗？`, '提示', {
      type: 'warning'
    })
    const res = await reviewReservation({
      id: row.id,
      status
    })
    if (res.code === 0) {
      ElMessage.success(`${action}成功`)
      getAllReservations()
    } else {
      ElMessage.error(res.msg || `${action}失败`)
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('审核预约失败：', error)
      ElMessage.error('审核预约失败：' + (error.message || '未知错误'))
    }
  }
}

// 搜索
const handleSearch = () => {
  allPage.value = 1
  getAllReservations()
}

// 重置搜索
const resetSearch = () => {
  searchForm.lab_id = ''
  searchForm.status = ''
  searchForm.timeRange = []
  handleSearch()
}

// 格式化日期时间
const formatDateTime = (datetime) => {
  return datetime ? new Date(datetime).toLocaleString() : ''
}

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
    completed: 'info',
    cancelled: 'info'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    pending: '待审核',
    approved: '已批准',
    rejected: '已拒绝',
    completed: '已完成',
    cancelled: '已取消'
  }
  return texts[status] || status
}

// 监听标签页切换
watch(activeTab, (newVal) => {
  if (newVal === 'my') {
    getMyReservationsList()
  } else if (newVal === 'all' && isAdmin.value) {
    getAllReservations()
  }
})

onMounted(() => {
  getList()
  if (activeTab.value === 'my') {
    getMyReservationsList()
  } else if (activeTab.value === 'all' && isAdmin.value) {
    getAllReservations()
  }
})
</script> 