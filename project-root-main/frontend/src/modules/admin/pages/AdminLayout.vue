<!-- frontend/src/views/admin/AdminLayout.vue -->
<template>
  <main class="admin-layout">
    <aside class="admin-aside">
      <h2>ผู้ดูแลระบบ</h2>
      <nav class="admin-nav">
        <RouterLink :to="{ name:'admin.dashboard' }">แดชบอร์ด</RouterLink>
        <RouterLink :to="{ name:'admin.users' }">จัดการผู้ใช้</RouterLink>
        <RouterLink :to="{ name:'admin.documents' }">จัดการเอกสาร</RouterLink>
        <details class="admin-nav-group" open>
          <summary>ข้อมูลพื้นฐาน</summary>
          <div class="admin-nav-submenu">
            <RouterLink :to="{ name:'admin.academic-terms' }">ข้อมูลปีการศึกษา</RouterLink>
            <RouterLink :to="{ name:'admin.projects-management' }">ข้อมูลโครงงาน</RouterLink>
            <RouterLink :to="{ name:'admin.advisors' }">ข้อมูลอาจารย์ที่ปรึกษา</RouterLink>
          </div>
        </details>
        <RouterLink :to="{ name:'admin.settings' }">ตั้งค่าระบบ</RouterLink>
      </nav>
    </aside>

    <section class="admin-content">
      <router-view />
    </section>
  </main>
</template>

<script setup></script>

<style scoped>
.admin-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  min-height: calc(100vh - 65px); /* Adjust for navbar height */
  background: var(--background-color);
  color: var(--text-primary);
}
.admin-aside {
  background: var(--surface-color);
  border-right: 1px solid var(--border-color);
  padding: 20px;
}
.admin-aside h2 {
  margin: 0 0 12px 0;
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
}
.admin-nav { display: grid; gap: 8px; }
.admin-nav a {
  padding: 10px 12px;
  border-radius: var(--radius);
  text-decoration: none;
  color: var(--text-secondary);
  border: 1px solid transparent;
  font-weight: 600;
  transition: background .15s, color .15s;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.admin-nav a:hover {
  background: var(--primary-extralight);
  color: var(--primary-dark);
}
.admin-nav a.router-link-active {
  background: var(--primary-color);
  color: var(--text-on-primary);
}
.admin-content { padding: 24px; }

.admin-nav-group summary {
  padding: 10px 12px;
  border-radius: var(--radius);
  cursor: pointer;
  list-style: none;
  font-weight: 600;
  color: var(--text-secondary);
  transition: background .15s, color .15s;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.admin-nav-group summary::-webkit-details-marker { display: none; }

.admin-nav-group summary:hover {
  background: var(--primary-extralight);
  color: var(--primary-dark);
}

.admin-nav-group summary::after {
  content: '';
  width: 16px;
  height: 16px;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: center;
  transition: transform 0.2s ease-in-out;
}

.admin-nav-group[open] > summary::after { transform: rotate(180deg); }

.admin-nav-submenu {
  display: grid;
  gap: 0;
  padding-left: 20px;
  margin-top: 8px;
}

.admin-nav-submenu a {
    border-top: 1px solid var(--border-color);
    padding-top: 12px;
    padding-bottom: 12px;
    border-radius: 0;
}

.admin-nav-submenu a:hover {
    padding-left: 12px; /* Add padding on hover to keep text alignment */
}

@media (max-width:860px) {
  .admin-layout { grid-template-columns: 1fr; }
  .admin-aside { display: none; }
  .admin-content { padding: 16px; }
}
</style>
