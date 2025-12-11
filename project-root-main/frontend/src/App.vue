<template>
  <NavBar />
  <router-view/>
</template>

<script>
import { defineComponent } from 'vue'
import NavBar from './components/KUNavbar.vue'

export default defineComponent({
  components: { NavBar }
})
</script>