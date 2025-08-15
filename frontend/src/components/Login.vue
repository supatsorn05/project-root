<template>
  <div class="login-container">
    <h2>เข้าสู่ระบบแอดมิน</h2>
    <div class="form-group">
      <input v-model="username" placeholder="Username" class="form-input" @keyup.enter="login"/>
    </div>
    <div class="form-group">
      <input v-model="password" type="password" placeholder="Password" class="form-input" @keyup.enter="login"/>
    </div>
    <button @click="login" class="login-btn" :disabled="loading">
      {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
    </button>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const username = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')
const router = useRouter()

const login = async () => {
  if (!username.value || !password.value) {
    errorMessage.value = 'กรุณากรอก Username และ Password'
    return
  }
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await axios.post('/api/login.php', {
      username: username.value, password: password.value
    }, { withCredentials: true })
    if (res.data.status === 'success') {
      const role = res.data.user.role
      router.push(role === 'admin' ? '/admin' : '/user')
    } else {
      errorMessage.value = res.data.message || 'เข้าสู่ระบบล้มเหลว'
    }
  } catch (err) {
    errorMessage.value = err?.response?.data?.message
      || (err?.message || 'เข้าสู่ระบบล้มเหลว')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; }
h2 { text-align:center; margin-bottom:20px; }
.form-group { margin-bottom:15px; }
.form-input { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; font-size:16px; box-sizing:border-box; }
.login-btn { width:100%; padding:12px; background:#007bff; color:#fff; border:none; border-radius:4px; font-size:16px; cursor:pointer; }
.login-btn:disabled { background:#6c757d; cursor:not-allowed; }
.error-message { margin-top:10px; padding:10px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:4px; text-align:center; }
</style>
