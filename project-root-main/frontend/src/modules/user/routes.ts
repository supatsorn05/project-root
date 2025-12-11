import type { RouteRecordRaw } from 'vue-router'
import UserDashboard from './pages/UserDashboard.vue'
import ProjectList from './pages/ProjectList.vue'
import UserDocuments from './pages/UserDocuments.vue'
import TeacherDocuments from './pages/TeacherDocuments.vue'
import StudentSubmitDocument from './pages/StudentSubmitDocument.vue'
import MySubmissions from './pages/MySubmissions.vue'


export default [

  { path: 'dashboard', name: 'user.dashboard', component: UserDashboard },
  { path: 'profile', name: 'user.profile', component: () => import('./pages/UserProfile.vue') },
  { path: 'projects/:term_id', name: 'user.projects', component: ProjectList, props: true },
  { path: 'documents', name: 'user.documents', component: UserDocuments },
  { path: 'teacher/documents', name: 'teacher.documents', component: TeacherDocuments, meta: { role: 'teacher' } },
  { path: 'submit-document', name: 'student.submit.document', component: StudentSubmitDocument, meta: { role: 'student' } },
  { path: 'my-submissions', name: 'student.my.submissions', component: MySubmissions, meta: { role: 'student' } },
] as RouteRecordRaw[]