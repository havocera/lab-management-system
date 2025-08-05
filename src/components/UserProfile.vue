<template>
  <div class="p-4">
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="100px"
      class="max-w-2xl mx-auto"
    >
      <el-form-item label="用户名" prop="username">
        <el-input v-model="form.username" disabled />
      </el-form-item>
      <el-form-item label="姓名" prop="name">
        <el-input v-model="form.name" />
      </el-form-item>
      <el-form-item label="手机号" prop="phone">
        <el-input v-model="form.phone" />
      </el-form-item>
      <el-form-item label="邮箱" prop="email">
        <el-input v-model="form.email" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="handleSubmit">保存修改</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getUserInfo, updateUserInfo } from '@/api/user'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const formRef = ref(null)

const form = ref({
  username: '',
  name: '',
  phone: '',
  email: ''
})

const rules = {
  name: [
    { required: true, message: '请输入姓名', trigger: 'blur' },
    { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
  ],
  phone: [
    { required: true, message: '请输入手机号', trigger: 'blur' },
    { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur' }
  ],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' }
  ]
}

const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      try {
        const res = await updateUserInfo(form.value)
        if (res.code === 0) {
          ElMessage.success('修改成功')
          // 更新用户信息
          await userStore.getUserInfoAction()
        } else {
          ElMessage.error(res.msg || '修改失败')
        }
      } catch (error) {
        ElMessage.error('修改失败')
      }
    }
  })
}

onMounted(async () => {
  try {
    const res = await getUserInfo()
    if (res.code === 0) {
      const { user } = res.data
      form.value = {
        username: user.username,
        name: user.name,
        phone: user.phone,
        email: user.email
      }
    }
  } catch (error) {
    ElMessage.error('获取用户信息失败')
  }
})
</script> 