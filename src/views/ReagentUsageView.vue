<template>
  <div class="reagent-usage">
    <el-tabs v-model="activeTab">
      <!-- 试剂列表 -->
      <el-tab-pane label="试剂列表" name="list">
        <el-table :data="reagentList" style="width: 100%" v-loading="loading">
          <el-table-column prop="name" label="试剂名称" />
          <el-table-column prop="code" label="试剂编号" />
          <el-table-column prop="specification" label="规格" />
          <el-table-column prop="stock" label="库存" />
          <el-table-column prop="unit" label="单位" />
          <el-table-column prop="danger_level" label="危险等级">
            <template #default="{ row }">
              <el-tag :type="getDangerLevelType(row.danger_level)">
                {{ getDangerLevelText(row.danger_level) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="150">
            <template #default="{ row }">
              <el-button type="primary" size="small" @click="handleApply(row)">
                申请使用
              </el-button>
            </template>
          </el-table-column>
        </el-table>

        <div class="pagination">
          <el-pagination
            v-model:current-page="currentPage"
            v-model:page-size="pageSize"
            :total="total"
            :page-sizes="[10, 20, 50, 100]"
            layout="total, sizes, prev, pager, next"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </el-tab-pane>

      <!-- 使用记录 -->
      <el-tab-pane label="使用记录" name="records">
        <el-table :data="recordList" style="width: 100%" v-loading="recordLoading">
          <el-table-column prop="reagent_name" label="试剂名称" />
          <el-table-column prop="reagent_code" label="试剂编号" />
          <el-table-column prop="amount" label="使用数量" />
          <el-table-column prop="unit" label="单位" />
          <el-table-column prop="operator" label="操作人" />
          <el-table-column prop="create_time" label="使用时间">
            <template #default="{ row }">
              {{ formatTime(row.create_time) }}
            </template>
          </el-table-column>
          <el-table-column prop="status" label="状态">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row.status)">
                {{ getStatusText(row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="remark" label="备注" />
        </el-table>

        <div class="pagination">
          <el-pagination
            v-model:current-page="recordPage"
            v-model:page-size="recordPageSize"
            :total="recordTotal"
            :page-sizes="[10, 20, 50, 100]"
            layout="total, sizes, prev, pager, next"
            @size-change="handleRecordSizeChange"
            @current-change="handleRecordCurrentChange"
          />
        </div>
      </el-tab-pane>
    </el-tabs>

    <!-- 申请使用对话框 -->
    <el-dialog
      v-model="dialogVisible"
      title="申请使用试剂"
      width="500px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="试剂名称">
          <span>{{ form.reagent_name }}</span>
        </el-form-item>
        <el-form-item label="当前库存">
          <span>{{ form.current_stock }} {{ form.unit }}</span>
        </el-form-item>
        <el-form-item label="使用数量" prop="amount">
          <el-input-number
            v-model="form.amount"
            :min="0"
            :max="form.current_stock"
            :precision="2"
            :step="0.1"
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
        <el-form-item label="用途说明" prop="remark">
          <el-input
            v-model="form.remark"
            type="textarea"
            :rows="3"
            placeholder="请说明使用用途"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmit">提交申请</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { getReagentList, getReagentRecords, applyReagentUsage } from '@/api/reagentUsage'
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
const pageSize = ref(10)
const total = ref(0)

// 使用记录
const recordLoading = ref(false)
const recordList = ref([])
const recordPage = ref(1)
const recordPageSize = ref(10)
const recordTotal = ref(0)

// 申请表单
const dialogVisible = ref(false)
const formRef = ref(null)
const form = reactive({
  reagent_id: '',
  reagent_name: '',
  current_stock: 0,
  unit: '',
  amount: 0,
  remark: ''
})

// 表单验证规则
const rules = {
  amount: [
    { required: true, message: '请输入使用数量', trigger: 'blur' },
    { type: 'number', min: 0, message: '数量必须大于0', trigger: 'blur' }
  ],
  remark: [
    { required: true, message: '请输入用途说明', trigger: 'blur' },
    { max: 500, message: '用途说明不能超过500个字符', trigger: 'blur' }
  ]
}

// 获取试剂列表
const getList = async () => {
  loading.value = true
  try {
    const res = await getReagentList({
      page: currentPage.value,
      limit: pageSize.value
    })
    if (res.code === 0) {
      reagentList.value = res.data.list
      total.value = res.data.total
    } else {
      ElMessage.error(res.msg)
    }
  } catch (error) {
    console.error('获取试剂列表失败:', error)
    ElMessage.error('获取试剂列表失败')
  } finally {
    loading.value = false
  }
}

// 获取使用记录
const getRecords = async () => {
  recordLoading.value = true
  try {
    const res = await getReagentRecords({
      page: recordPage.value,
      limit: recordPageSize.value
    })
    if (res.code === 0) {
      recordList.value = res.data.list
      recordTotal.value = res.data.total
    } else {
      ElMessage.error(res.msg)
    }
  } catch (error) {
    console.error('获取使用记录失败:', error)
    ElMessage.error('获取使用记录失败')
  } finally {
    recordLoading.value = false
  }
}

// 申请使用
const handleApply = (row) => {
  form.reagent_id = row.id
  form.reagent_name = row.name
  form.current_stock = row.stock
  form.unit = row.unit  
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
        const res = await applyReagentUsage({
          reagent_id: form.reagent_id,
          amount: form.amount,
          remark: form.remark,
          unit: form.unit,
          operator: userStore.userInfo.username
        })
        
        if (res.code === 0) {
          ElMessage.success('申请提交成功')
          dialogVisible.value = false
          getRecords() // 刷新使用记录
        } else {
          ElMessage.error(res.msg)
        }
      } catch (error) {
        console.error('提交申请失败:', error)
        ElMessage.error('提交申请失败')
      }
    }
  })
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
  return dayjs(timestamp * 1000).format('YYYY-MM-DD HH:mm:ss')
}

// 监听标签页切换
watch(activeTab, (val) => {
  if (val === 'records') {
    getRecords()
  }
})

onMounted(() => {
  getList()
})
</script>

<style scoped>
.reagent-usage {
  padding: 20px;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.ml-2 {
  margin-left: 8px;
}
</style> 