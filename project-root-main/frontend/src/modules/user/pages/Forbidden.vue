<template>
  <div class="forbidden-container">
    <h1>403 - Forbidden</h1>
    <p>You don't have permission to access this page.</p>
    <router-link to="/">Go to Home</router-link>
  </div>
</template>

<style scoped>
.forbidden-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
}
</style>