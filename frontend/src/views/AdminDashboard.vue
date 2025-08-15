<template>
  <div>
    <h1>ยินดีต้อนรับ แอดมิน!</h1>
    <p v-if="user">สวัสดี, {{ user.full_name }}</p>
    <p v-else-if="error" style="color:#c00">{{ error }}</p>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
const user = ref(null)
const error = ref('')

onMounted(async () => {
  try {
    const res = await fetch('/api/me.php', { credentials:'include' })
    const data = await res.json()
    if (data.status === 'success') user.value = data.user
    else error.value = data.message || 'Not authenticated'
  } catch (e) {
    error.value = 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้'
  }
})
</script>
