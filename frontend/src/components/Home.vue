<template>
  <div>
    <h2>Welcome to the Home Page</h2>
    <p v-if="user">Hello, {{ user.full_name }}</p>
  </div>
</template>

<script>
export default {
  data() { return { user: null } },
  async created() {
    const res = await this.$axios.get('/me.php')
    this.user = res.data.user
  }
}
</script>
