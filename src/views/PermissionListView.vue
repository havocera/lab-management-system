<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-medium">权限菜单管理</h2>
      <el-button type="primary" @click="handleAdd">
        <div class="i-carbon-add mr-2" />
        新增权限
      </el-button>
    </div>

    <!-- 权限列表 -->
    <el-table
      v-loading="loading"
      :data="permissionList"
      border
    >
      <el-table-column prop="name" label="名称" min-width="200" />
      <el-table-column prop="code" label="标识" min-width="150" />
      <el-table-column prop="type" label="类型" width="100">
        <template #default="{ row }">
          <el-tag :type="row.type === 'menu' ? 'success' : 'info'">
            {{ row.type === 'menu' ? '菜单' : '按钮' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="path" label="路径" min-width="150" />
      <el-table-column prop="component" label="组件" min-width="150" />
      <el-table-column prop="icon" label="图标" width="100">
        <template #default="{ row }">
          <div v-if="row.icon" :class="row.icon" class="text-lg" />
        </template>
      </el-table-column>
      <el-table-column prop="sort" label="排序" width="80" />
      <el-table-column prop="status" label="状态" width="100">
        <template #default="{ row }">
          <el-tag :type="row.status === 'active' ? 'success' : 'danger'">
            {{ row.status === 'active' ? '启用' : '禁用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="200" fixed="right">
        <template #default="{ row }">
          <el-button-group>
            <el-button type="primary" link @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button
              :type="row.status === 'active' ? 'danger' : 'success'"
              link
              @click="handleStatusChange(row)"
            >
              {{ row.status === 'active' ? '禁用' : '启用' }}
            </el-button>
          </el-button-group>
        </template>
      </el-table-column>
    </el-table>

    <!-- 权限表单对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '新增权限' : '编辑权限'"
      width="500px"
      destroy-on-close
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入权限名称" />
        </el-form-item>
        <el-form-item label="标识" prop="code">
          <el-input v-model="form.code" placeholder="请输入权限标识" />
        </el-form-item>
        <el-form-item label="类型" prop="type">
          <el-radio-group v-model="form.type">
            <el-radio label="menu">菜单</el-radio>
            <el-radio label="button">按钮</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item
          v-if="form.type === 'menu'"
          label="路径"
          prop="path"
        >
          <el-input v-model="form.path" placeholder="请输入路由路径" />
        </el-form-item>
        <el-form-item
          v-if="form.type === 'menu'"
          label="组件"
          prop="component"
        >
          <el-input v-model="form.component" placeholder="请输入组件路径" />
        </el-form-item>
        <el-form-item
          v-if="form.type === 'menu'"
          label="图标"
          prop="icon"
        >
          <el-input v-model="form.icon" placeholder="请输入图标类名" />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="form.sort" :min="0" :max="999" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="form.status">
            <el-radio label="active">启用</el-radio>
            <el-radio label="inactive">禁用</el-radio>
          </el-radio-group>
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
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getPermissionList, createPermission, updatePermission, updatePermissionStatus } from '@/api/permission'

const loading = ref(false)
const permissionList = ref([])
const dialogVisible = ref(false)
const dialogType = ref('add')
const formRef = ref(null)

const form = reactive({
  name: '',
  code: '',
  type: 'menu',
  path: '',
  component: '',
  icon: '',
  sort: 0,
  status: 'active'
})

const rules = {
  name: [
    { required: true, message: '请输入权限名称', trigger: 'blur' },
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入权限标识', trigger: 'blur' },
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
  ],
  type: [
    { required: true, message: '请选择权限类型', trigger: 'change' }
  ],
  path: [
    { required: true, message: '请输入路由路径', trigger: 'blur' }
  ],
  component: [
    { required: true, message: '请输入组件路径', trigger: 'blur' }
  ],
  sort: [
    { required: true, message: '请输入排序号', trigger: 'blur' }
  ],
  status: [
    { required: true, message: '请选择状态', trigger: 'change' }
  ]
}

// 获取权限列表
const getList = async () => {
  try {
    loading.value = true
    const res = await getPermissionList()
    if (res.code === 0 && res.data && Array.isArray(res.data.list)) {
      permissionList.value = res.data.list
    } else {
      permissionList.value = []
      console.warn('权限数据格式不正确')
    }
  } catch (error) {
    console.error('获取权限列表失败：', error)
    ElMessage.error('获取权限列表失败')
    permissionList.value = []
  } finally {
    loading.value = false
  }
}

// 新增权限
const handleAdd = () => {
  dialogType.value = 'add'
  dialogVisible.value = true
  // 重置表单
  Object.assign(form, {
    name: '',
    code: '',
    type: 'menu',
    path: '',
    component: '',
    icon: '',
    sort: 0,
    status: 'active'
  })
}

// 编辑权限
const handleEdit = (row) => {
  dialogType.value = 'edit'
  dialogVisible.value = true
  // 填充表单
  Object.assign(form, row)
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return
  
  try {
    await formRef.value.validate()
    const api = dialogType.value === 'add' ? createPermission : updatePermission
    const res = await api(form)
    
    if (res.code === 0) {
      ElMessage.success(dialogType.value === 'add' ? '新增成功' : '编辑成功')
      dialogVisible.value = false
      getList()
    } else {
      ElMessage.error(res.msg || (dialogType.value === 'add' ? '新增失败' : '编辑失败'))
    }
  } catch (error) {
    console.error('提交失败：', error)
    ElMessage.error('提交失败：' + (error.message || '未知错误'))
  }
}

// 修改状态
const handleStatusChange = async (row) => {
  try {
    const res = await updatePermissionStatus({
      id: row.id,
      status: row.status === 'active' ? 'inactive' : 'active'
    })
    
    if (res.code === 0) {
      ElMessage.success('状态修改成功')
      getList()
    } else {
      ElMessage.error(res.msg || '状态修改失败')
    }
  } catch (error) {
    console.error('状态修改失败：', error)
    ElMessage.error('状态修改失败：' + (error.message || '未知错误'))
  }
}

onMounted(() => {
  getList()
})
</script> 