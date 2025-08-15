import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/components/Login.vue'
import AdminDashboard from '@/views/AdminDashboard.vue'
import UserDashboard from '@/views/UserDashboard.vue'

const routes = [
  { path: '/', name: 'Login', component: Login },
  { path: '/admin', name: 'Admin', component: AdminDashboard },
  { path: '/user', name: 'User', component: UserDashboard }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
