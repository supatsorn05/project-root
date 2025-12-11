<template>
  <header class="page-head">
    <div class="header-content">
      <h1>จัดการผู้ใช้งาน</h1>
      <p class="muted">เพิ่ม แก้ไข หรือลบผู้ใช้งานออกจากระบบ</p>
    </div>
  </header>

  <main class="container">
    <!-- Notification -->
    <div v-if="notification.show" :class="`notification is-${notification.type}`">
        <p>{{ notification.message }}</p>
        <button @click="notification.show = false" class="close-btn">&times;</button>
    </div>

    <section class="card">
      <div class="card-title">
        <div class="title-section">
          <h2>ผู้ใช้งานทั้งหมด</h2>
          <span class="subtitle muted">{{ users.length }} ผู้ใช้งาน</span>
        </div>
        <button class="btn btn-primary" @click="showAddUserModal = true">
          <span class="btn-ico">+</span>
          <span>เพิ่มผู้ใช้งาน</span>
        </button>
      </div>

      <div class="table-container">
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th @click="sortBy('full_name')" class="sortable" style="width: 20%;">
                  ชื่อ-นามสกุล
                  <span v-if="sortKey === 'full_name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th @click="sortBy('email')" class="sortable" style="width: 20%;">
                  อีเมล
                  <span v-if="sortKey === 'email'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th @click="sortBy('username')" class="sortable">
                  ชื่อผู้ใช้งาน
                  <span v-if="sortKey === 'username'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th @click="sortBy('role')" class="sortable">
                  บทบาท
                  <span v-if="sortKey === 'role'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th @click="sortBy('student_id')" class="sortable">
                  รหัสนิสิต
                  <span v-if="sortKey === 'student_id'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th @click="sortBy('group_name')" class="sortable">
                  ชื่อกลุ่ม
                  <span v-if="sortKey === 'group_name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th>การดำเนินการ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in sortedUsers" :key="user.id">
                <td>{{ user.full_name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.username }}</td>
                <td>{{ user.role }}</td>
                <td>{{ user.student_id || '-' }}</td>
                <td>{{ user.group_name || '-' }}</td>
                <td>
                  <button class="btn btn-sm btn-secondary" @click="editUser(user)">แก้ไข</button>
                  <button class="btn btn-sm btn-danger" @click="deleteUser(user)">ลบ</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>กำลังโหลดผู้ใช้งาน...</span>
      </div>
      <div v-if="error" class="error-state">
        <p>เกิดข้อผิดพลาด: {{ error }}</p>
      </div>
    </section>

    <AddUserModal v-if="showAddUserModal" @close="showAddUserModal = false" @user-added="handleUserAdded" />
    <EditUserModal v-if="showEditUserModal" :user="selectedUser" @close="showEditUserModal = false" @user-updated="handleUserUpdated" />
  </main>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import http from '../../../services/http';
import { user as authUser } from '../../../composables/useAuth'; // Renamed to avoid conflict
import AddUserModal from '../components/AddUserModal.vue';
import EditUserModal from '../components/EditUserModal.vue';

const router = useRouter();
const users = ref([]);
const loading = ref(false);
const error = ref('');
const showAddUserModal = ref(false);
const showEditUserModal = ref(false);
const selectedUser = ref(null);
const notification = ref({ show: false, message: '', type: '' });
const sortKey = ref('');
const sortOrder = ref('asc');

const sortedUsers = computed(() => {
  if (!sortKey.value) {
    return users.value;
  }
  return [...users.value].sort((a, b) => {
    let aValue = a[sortKey.value] || '';
    let bValue = b[sortKey.value] || '';

    // Natural sort for strings with numbers (like student_id)
    return aValue.localeCompare(bValue, undefined, { numeric: true }) * (sortOrder.value === 'asc' ? 1 : -1);
  });
});

function sortBy(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
}

const showNotification = (message, type) => {
    notification.value = { show: true, message, type };
    setTimeout(() => { notification.value.show = false; }, 3000);
};

async function fetchUsers() {
  loading.value = true;
  error.value = '';
  try {
    const response = await http.get('/users.php');
    if (response.data.status === 'success') {
      users.value = response.data.users;
    } else {
      error.value = response.data.message;
      showNotification(response.data.message, 'error');
    }
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'ไม่สามารถโหลดผู้ใช้งานได้';
    showNotification(error.value, 'error');
  }
  loading.value = false;
}

async function editUser(user) {
  loading.value = true; // Show loading state while fetching user data
  try {
    const response = await http.get(`/users.php?id=${user.id}`);
    if (response.data.status === 'success' && response.data.user) {
      selectedUser.value = response.data.user;
      showEditUserModal.value = true;
    } else {
      showNotification(response.data.message || 'ไม่พบข้อมูลผู้ใช้งาน', 'error');
    }
  } catch (e) {
    showNotification(e.response?.data?.message || e.message || 'ไม่สามารถโหลดข้อมูลผู้ใช้งานได้', 'error');
  } finally {
    loading.value = false;
  }
}

async function deleteUser(userToDelete) {
  if (!confirm(`คุณแน่ใจหรือไม่ว่าต้องการลบ ${userToDelete.full_name}?`)) {
    return;
  }
  try {
    const response = await http.post('/delete-user.php', { id: userToDelete.id });
    if (response.data.status === 'success') {
      users.value = users.value.filter(u => u.id !== userToDelete.id);
      showNotification('ลบผู้ใช้งานสำเร็จ', 'success');
    } else {
      showNotification(response.data.message, 'error');
    }
  } catch (error) {
    showNotification(error.response?.data?.message || error.message || 'ไม่สามารถลบผู้ใช้งานได้', 'error');
  }
}

function handleUserAdded() {
  showAddUserModal.value = false;
  fetchUsers(); // Refetch all users after adding
  showNotification('เพิ่มผู้ใช้งานสำเร็จ', 'success');
}

function handleUserUpdated() {
  showEditUserModal.value = false;
  fetchUsers(); // Refetch all users after updating
  showNotification('อัปเดตผู้ใช้งานสำเร็จ', 'success');
}

onMounted(() => {
  if (authUser.value) {
    fetchUsers();
  } else {
    const unwatch = watch(authUser, (newValue) => {
      if (newValue) {
        fetchUsers();
        unwatch();
      }
    });
  }
});
</script>

<style scoped>
.container {
  padding: 0 24px 80px;
  max-width: 1200px;
  margin: 0 auto;
}
.muted { color: var(--text-secondary); }

.page-head {
  padding: 32px 0;
  text-align: center;
}
.page-head h1 {
  margin: 0 0 6px 0;
  font-size: 2.1rem;
  font-weight: 700;
  color: var(--text-primary);
}

.card-title {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 24px;
}
.card-title h2 {
  margin: 0 0 4px 0;
  color: var(--text-primary);
  font-weight: 700;
}
.subtitle { font-size: .95rem; color: var(--text-secondary); }

.btn-ico { font-size: 1.2rem; }

.table-container {
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  background: var(--surface-color);
  overflow: hidden;
}
.table-wrap { overflow: auto; }

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 28px;
  color: var(--text-secondary);
}
.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--primary-light);
  border-top: 3px solid var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg) } }

.error-state { padding: 28px; text-align: center; color: var(--danger-color); }

/* Notification Styles */
.notification { padding: 1rem 1.5rem; margin-bottom: 1rem; border-radius: var(--radius); color: #fff; display: flex; justify-content: space-between; align-items: center; }
.notification.is-success { background-color: var(--primary-color); }
.notification.is-error { background-color: var(--danger-color); }
.notification .close-btn { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }

.sortable {
  cursor: pointer;
}

@media (max-width:860px) {
  .container { padding:0 16px 60px; }
}
@media (max-width:640px) {
  .table th, .table td { padding:12px 8px; font-size:.95rem; }
}
</style>
