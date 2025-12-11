import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import userRoutes from '../modules/user/routes'
import adminRoutes from '../modules/admin/routes'
import UserLayout from '../layouts/UserLayout.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import { user, ensure } from '@/composables/useAuth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login
  },
  {
    path: '/user',
    name: 'user',
    component: UserLayout,
    children: userRoutes,
    meta: { requiresAuth: true, roles: ['student', 'teacher', 'instructor'] }
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminLayout,
    children: adminRoutes,
    meta: { requiresAuth: true, roles: ['admin'] }
  },
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/forbidden',
    name: 'forbidden',
    component: () => import('../modules/user/pages/Forbidden.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  // Only try to load user data if navigating to a protected route
  if (requiresAuth && localStorage.getItem('token') && !user.value) {
    await ensure();
  }

  const userRole = user.value?.role;
  console.log(`Navigating to ${to.fullPath} from ${from.fullPath}. User role: ${userRole}`);

  // If trying to access login page while authenticated, redirect to the correct dashboard
  if (to.name === 'login' && userRole) {
    console.log('User is authenticated and trying to access login page. Redirecting...');
    if (userRole === 'admin') {
      return next({ path: '/admin/dashboard' });
    } else {
      return next({ path: '/user/dashboard' });
    }
  }

  // If the route requires authentication
  if (requiresAuth) {
    console.log('Route requires auth.');
    // If user is not authenticated (no role), redirect to login
    if (!userRole) {
      console.log('User not authenticated. Redirecting to login.');
      return next({ name: 'login', query: { redirect: to.fullPath } });
    }

    // Check for role-based access
    const requiredRoles = (to.meta.roles as string[]) || [];
    console.log(`Required roles: ${requiredRoles}`);
    if (requiredRoles.length > 0 && !requiredRoles.includes(userRole)) {
      console.log('User does not have required role. Redirecting to forbidden.');
      return next({ name: 'forbidden' });
    }
  }
  
  console.log('Allowing navigation.');
  next();
});

export default router