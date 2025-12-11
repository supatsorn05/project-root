import { createApp } from 'vue'
import App from './App.vue'
import router from './router'   // ระบุไฟล์ให้ชัด
import './assets/main.css'

createApp(App).use(router).mount('#app')
