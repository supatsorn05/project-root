<template>
  <nav style="padding:12px;border-bottom:1px solid #eee;display:flex;gap:12px;">
    <router-link to="/">Login</router-link>
    <router-link to="/admin">Admin</router-link>
    <router-link to="/user">User</router-link>
    <button @click="logout" style="margin-left:auto;">Logout</button>
  </nav>
</template>

<script setup>
const logout = async () => {
  await fetch('/api/logout.php', { method:'POST', credentials:'include' })
  location.href = '/'
}
</script>
