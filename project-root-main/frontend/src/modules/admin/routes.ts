import Dashboard from './pages/AdminDashboard.vue'
import Users from './pages/AdminUsers.vue'
import Projects from './pages/AdminProjects.vue'
import Documents from './pages/AdminDocuments.vue'
import AdminAcademicTerms from './pages/AdminAcademicTerms.vue'
import AdminProjectsManagement from './pages/AdminProjectsManagement.vue'
import AdminAdvisors from './pages/AdminAdvisors.vue'

export default [
  { path: 'dashboard', name: 'admin.dashboard', component: Dashboard },
  { path: 'profile', name: 'admin.profile', component: () => import('@/modules/user/pages/UserProfile.vue') },
  { path: 'users', name: 'admin.users', component: Users },
  { path: 'projects/:term_id', name: 'admin.projects', component: Projects },
  { path: 'documents', name: 'admin.documents', component: Documents },
  {
    path: 'basic-data',
    name: 'admin.basic-data',
    redirect: { name: 'admin.academic-terms' },
    children: [
      { path: 'academic-terms', name: 'admin.academic-terms', component: AdminAcademicTerms },
      { path: 'projects-management', name: 'admin.projects-management', component: AdminProjectsManagement },
      { path: 'advisors', name: 'admin.advisors', component: AdminAdvisors },
    ],
  },
]
