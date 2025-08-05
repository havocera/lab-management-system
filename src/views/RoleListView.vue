<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-medium">角色管理</h2>
      <el-button type="primary" @click="handleAdd">
        <div class="i-carbon-add mr-2" />
        新增角色
      </el-button>
    </div>

    <!-- 角色列表 -->
    <el-table
      v-loading="loading"
      :data="roleList"
      border
    >
      <el-table-column prop="name" label="角色名称" min-width="150" />
      <el-table-column prop="code" label="角色标识" min-width="150" />
      <el-table-column prop="description" label="描述" min-width="200" />
      <el-table-column prop="status" label="状态" width="100">
        <template #default="{ row }">
          <el-tag :type="row.status === 'active' ? 'success' : 'danger'">
            {{ row.status === 'active' ? '启用' : '禁用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="250" fixed="right">
        <template #default="{ row }">
          <el-button-group>
            <el-button type="primary" link @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="primary" link @click="handlePermission(row)">
              权限设置
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

    <!-- 角色表单对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '新增角色' : '编辑角色'"
      width="500px"
      destroy-on-close
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="角色名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入角色名称" />
        </el-form-item>
        <el-form-item label="角色标识" prop="code">
          <el-input v-model="form.code" placeholder="请输入角色标识" />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入角色描述"
          />
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

    <!-- 权限设置对话框 -->
    <el-dialog
      v-model="permissionDialogVisible"
      title="权限设置"
      width="600px"
      destroy-on-close
    >
      <el-tree
        ref="permissionTreeRef"
        :data="permissionTree"
        :props="{ label: 'name' }"
        show-checkbox
        node-key="id"
        default-expand-all
        :default-checked-keys="checkedPermissions"
      />
      <template #footer>
        <el-button @click="permissionDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handlePermissionSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getRoleList, createRole, updateRole, updateRoleStatus, getRolePermissions, updateRolePermissions } from '@/api/role'
import { getPermissionList } from '@/api/permission'

const loading = ref(false)
const roleList = ref([])
const dialogVisible = ref(false)
const dialogType = ref('add')
const formRef = ref(null)
const permissionDialogVisible = ref(false)
const permissionTreeRef = ref(null)
const permissionTree = ref([])
const checkedPermissions = ref([])
const currentRole = ref(null)

const form = reactive({
  name: '',
  code: '',
  description: '',
  status: 'active'
})

const rules = {
  name: [
    { required: true, message: '请输入角色名称', trigger: 'blur' },
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入角色标识', trigger: 'blur' },
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
  ],
  status: [
    { required: true, message: '请选择状态', trigger: 'change' }
  ]
}

// 获取角色列表
const getList = async () => {
  try {
    loading.value = true
    const res = await getRoleList()
    if (res.code === 0 && res.data && Array.isArray(res.data.list)) {
      roleList.value = res.data.list
    } else {
      roleList.value = []
      console.warn('角色数据格式不正确')
    }
  } catch (error) {
    console.error('获取角色列表失败：', error)
    ElMessage.error('获取角色列表失败')
    roleList.value = []
  } finally {
    loading.value = false
  }
}

// 获取权限树
const getPermissionTree = async () => {
  try {
    const res = await getPermissionList()
    if (res.code === 0 && res.data && Array.isArray(res.data.list)) {
      permissionTree.value = res.data.list
    } else {
      permissionTree.value = []
      console.warn('权限数据格式不正确')
    }
  } catch (error) {
    console.error('获取权限树失败：', error)
    ElMessage.error('获取权限树失败')
    permissionTree.value = []
  }
}

// 新增角色
const handleAdd = () => {
  dialogType.value = 'add'
  dialogVisible.value = true
  // 重置表单
  Object.assign(form, {
    name: '',
    code: '',
    description: '',
    status: 'active'
  })
}

// 编辑角色
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
    const api = dialogType.value === 'add' ? createRole : updateRole
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
    const res = await updateRoleStatus({
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

// 打开权限设置
const handlePermission = async (row) => {
  currentRole.value = row
  permissionDialogVisible.value = true
  
  try {
    // 获取角色权限
    const res = await getRolePermissions(row.id)
    if (res.code === 0 && Array.isArray(res.data)) {
      checkedPermissions.value = res.data.map(item => item.id)
    } else {
      checkedPermissions.value = []
      console.warn('角色权限数据格式不正确')
    }
  } catch (error) {
    console.error('获取角色权限失败：', error)
    ElMessage.error('获取角色权限失败')
    checkedPermissions.value = []
  }
}

// 提交权限设置
const handlePermissionSubmit = async () => {
  if (!permissionTreeRef.value || !currentRole.value) return
  
  try {
    const checkedKeys = permissionTreeRef.value.getCheckedKeys()
    const res = await updateRolePermissions({
      role_id: currentRole.value.id,
      permission_ids: checkedKeys
    })
    
    if (res.code === 0) {
      ElMessage.success('权限设置成功')
      permissionDialogVisible.value = false
    } else {
      ElMessage.error(res.msg || '权限设置失败')
    }
  } catch (error) {
    console.error('权限设置失败：', error)
    ElMessage.error('权限设置失败：' + (error.message || '未知错误'))
  }
}

onMounted(() => {
  getList()
  getPermissionTree()
})
</script> 