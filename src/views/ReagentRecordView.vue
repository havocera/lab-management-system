<template>
  <div class="space-y-4">
    <!-- 页面标题 -->
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-medium">试剂使用记录</h2>
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
        <el-form-item label="操作类型">
          <el-select v-model="searchForm.type" placeholder="请选择操作类型" clearable>
            <el-option label="入库" value="in" />
            <el-option label="出库" value="out" />
          </el-select>
        </el-form-item>
        <el-form-item label="审核状态">
          <el-select v-model="searchForm.status" placeholder="请选择审核状态" clearable>
            <el-option label="待审核" value="pending" />
            <el-option label="已通过" value="approved" />
            <el-option label="已拒绝" value="rejected" />
          </el-select>
        </el-form-item>
        <el-form-item label="操作时间">
          <el-date-picker
            v-model="searchForm.dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
          />
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

    <!-- 记录列表 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="recordList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="reagent_name" label="试剂名称" min-width="180">
          <template #default="{ row }">
            <div class="flex items-center">
              <el-image
                :src="row.reagent_image"
                class="w-8 h-8 mr-2 rounded"
                fit="cover"
                :preview-src-list="[row.reagent_image]"
              >
                <template #error>
                  <div class="w-8 h-8 mr-2 rounded bg-gray-200 flex items-center justify-center">
                    <div class="i-carbon-image text-gray-400" />
                  </div>
                </template>
              </el-image>
              {{ row.reagent_name }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="reagent_code" label="试剂编号" width="120" />
        <el-table-column prop="type" label="操作类型" width="100">
          <template #default="{ row }">
            <el-tag :type="row.type === 'in' ? 'success' : 'warning'">
              {{ row.type === 'in' ? '入库' : '出库' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="数量" width="120">
          <template #default="{ row }">
            {{ row.amount }} {{ row.unit }}
          </template>
        </el-table-column>
        <el-table-column prop="operator" label="操作人" width="120" />
        <el-table-column label="审核状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ row.status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="approver" label="审核人" width="120">
          <template #default="{ row }">
            {{ row.approver || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="approve_remark" label="审核备注" width="200">
          <template #default="{ row }">
            {{ row.approve_remark || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="操作时间" width="180" />
        <el-table-column prop="remark" label="备注" min-width="200" />
        <el-table-column label="操作" width="120" fixed="right">
          <template #default="{ row }">
            <el-button
              v-if="row.type === 'out' && row.status === 'pending'"
              type="primary"
              link
              @click="handleApprove(row)"
            >
              审核
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

    <!-- 审核对话框 -->
    <el-dialog
      v-model="approveDialogVisible"
      title="审核出库申请"
      width="500px"
      destroy-on-close
    >
      <el-form
        ref="approveFormRef"
        :model="approveForm"
        :rules="approveRules"
        label-width="100px"
      >
        <el-form-item label="试剂名称">
          <span>{{ currentRecord?.reagent_name }}</span>
        </el-form-item>
        <el-form-item label="出库数量">
          <span>{{ currentRecord?.amount }} {{ currentRecord?.unit }}</span>
        </el-form-item>
        <el-form-item label="申请人">
          <span>{{ currentRecord?.operator }}</span>
        </el-form-item>
        <el-form-item label="申请备注">
          <span>{{ currentRecord?.remark || '-' }}</span>
        </el-form-item>
        <el-form-item label="审核结果" prop="status">
          <el-radio-group v-model="approveForm.status">
            <el-radio label="approved">通过</el-radio>
            <el-radio label="rejected">拒绝</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="审核备注" prop="approve_remark">
          <el-input
            v-model="approveForm.approve_remark"
            type="textarea"
            :rows="3"
            placeholder="请输入审核备注"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="approveDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="submitApprove">
            确认
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getReagentRecords, approveReagentOutbound } from '@/api/reagent'
import { useRoute, useRouter } from 'vue-router'

// 搜索表单
const searchForm = reactive({
  name: '',
  code: '',
  type: '',
  status: '',
  dateRange: []
})

// 表格数据
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const recordList = ref([])

// 审核相关
const approveDialogVisible = ref(false)
const currentRecord = ref(null)
const approveFormRef = ref(null)
const approveForm = ref({
  id: '',
  status: 'approved',
  approve_remark: ''
})

const approveRules = {
  status: [{ required: true, message: '请选择审核结果', trigger: 'change' }],
  approve_remark: [{ required: true, message: '请输入审核备注', trigger: 'blur' }]
}
const route = useRoute()
// 搜索和重置
const handleSearch = () => {
  currentPage.value = 1
  fetchData()
}

const handleReset = () => {
  searchForm.name = ''
  searchForm.code = ''
  searchForm.type = ''
  searchForm.status = ''
  searchForm.dateRange = []
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

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger'
  }
  return types[status] || 'info'
}

// 打开审核对话框
const handleApprove = (row) => {
  currentRecord.value = row
  approveForm.value = {
    id: row.id,
    status: 'approved',
    approve_remark: ''
  }
  approveDialogVisible.value = true
}

// 提交审核
const submitApprove = async () => {
  if (!approveFormRef.value) return
  await approveFormRef.value.validate(async (valid) => {
    if (valid) {
      try {
        const res = await approveReagentOutbound(approveForm.value)
        if (res.code === 0) {
          ElMessage.success('审核成功')
          approveDialogVisible.value = false
          fetchData()
        } else {
          ElMessage.error(res.msg || '审核失败')
        }
      } catch (error) {
        console.error('审核失败:', error)
        ElMessage.error('审核失败')
      }
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
      reagent_name: searchForm.name,
      reagent_code: searchForm.code,
      type: searchForm.type,
      status: searchForm.status,
      start_time: searchForm.dateRange?.[0] || '',
      end_time: searchForm.dateRange?.[1] || ''
    }

  
    if (route.query.reagent_id) {
      params.reagent_id = route.query.reagent_id
      // 自动填充试剂名称和编号
      if (!searchForm.name && route.query.name) {
        searchForm.name = route.query.name
      }
      if (!searchForm.code && route.query.code) {
        searchForm.code = route.query.code
      }
    }
    
    const res = await getReagentRecords(params)
    if (res.code === 0) {
      recordList.value = res.data.list.map(item => ({
        ...item,
        // 格式化日期
        create_time: item.create_time
      }))
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
  if (!timestamp) return ''
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
  fetchData()
})
</script>

<style scoped>
.el-card {
  --el-card-padding: 16px;
}
</style> 