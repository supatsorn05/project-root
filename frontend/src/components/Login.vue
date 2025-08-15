<template>
  <div class="login-container">
    <h2>เข้าสู่ระบบแอดมิน</h2>

    <div class="form-group">
      <input
        v-model.trim="identifier"
        placeholder="Email หรือ Username"
        class="form-input"
        autocomplete="username"
        @keyup.enter="login"
      />
    </div>

    <div class="form-group">
      <input
        v-model="password"
        :type="showPass ? 'text' : 'password'"
        placeholder="Password"
        class="form-input"
        autocomplete="current-password"
        @keyup.enter="login"
      />
      <label class="showpass">
        <input type="checkbox" v-model="showPass" /> แสดงรหัส
      </label>
    </div>

    <button @click="login" class="login-btn" :disabled="loading">
      {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
    </button>

    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const identifier = ref('')   // email หรือ username
const password   = ref('')
const showPass   = ref(false)
const loading    = ref(false)
const errorMessage = ref('')
const router = useRouter()

const login = async () => {
  if (!identifier.value || !password.value) {
    errorMessage.value = 'กรุณากรอก Email/Username และ Password'
    return
  }
  loading.value = true
  errorMessage.value = ''

  try {
    const isEmail = identifier.value.includes('@')
    const payload = isEmail
      ? { email: identifier.value,  password: password.value }
      : { username: identifier.value, password: password.value }

    const res = await axios.post('/api/login.php', payload, {
      withCredentials: true,
      headers: { 'Content-Type': 'application/json' }
    })

    if (res.data?.status === 'success') {
      const role = res.data.user?.role || 'student'
      router.push(role === 'admin' ? '/admin' : '/user')
    } else {
      errorMessage.value = res.data?.message || 'เข้าสู่ระบบล้มเหลว'
    }
  } catch (err) {
    errorMessage.value =
      err?.response?.data?.message ||
      err?.message ||
      'เข้าสู่ระบบล้มเหลว'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container { max-width: 420px; margin: 60px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background: #fff; }
h2 { text-align:center; margin-bottom:20px; }
.form-group { margin-bottom:15px; position: relative; }
.form-input { width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:8px; font-size:16px; box-sizing:border-box; }
.showpass { display:block; margin-top:6px; font-size:12px; color:#666; }
.login-btn { width:100%; padding:12px; background:#0d6efd; color:#fff; border:none; border-radius:8px; font-size:16px; cursor:pointer; }
.login-btn:disabled { background:#6c757d; cursor:not-allowed; }
.error-message { margin-top:12px; padding:10px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:8px; text-align:center; }
</style>
