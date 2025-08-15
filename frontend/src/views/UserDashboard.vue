<template>
  <div>
    <h2>Welcome User</h2>
    <p v-if="user">Hello, {{ user.full_name }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const user = ref(null)
onMounted(async () => {
  const res = await fetch('/api/me.php', { credentials:'include' })
  const data = await res.json()
  if (data.status === 'success') user.value = data.user
})
</script>
