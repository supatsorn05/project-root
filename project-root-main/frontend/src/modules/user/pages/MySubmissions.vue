<template>
  <main class="container">
    <header class="page-head">
      <div>
        <h1>เอกสารของฉัน</h1>
        <p class="muted">ประวัติการส่งเอกสารและสถานะการตรวจสอบ</p>
      </div>
    </header>

    <section class="card">
      <div v-if="loading" class="loading-state">กำลังโหลดประวัติ...</div>
      <div v-else-if="error" class="error-state">{{ error }}</div>
      
      <div v-else-if="submissions.length === 0" class="no-data-state">
        ยังไม่มีประวัติการส่งเอกสาร
      </div>

      <div v-else class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>ชื่อเอกสาร</th>
              <th>ส่งให้ (อาจารย์)</th>
              <th>วันที่ส่ง</th>
              <th>สถานะ</th>
              <th>ความคิดเห็น</th>
              <th>ไฟล์แนบ</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sub in submissions" :key="sub.student_document_id">
              <td>{{ sub.doc_name }}</td>
              <td>{{ sub.teacher_name }}</td>
              <td>{{ formatDateTime(sub.uploaded_at) }}</td>
              <td>
                <span :class="`status-chip status-${sub.status}`">
                  {{ getStatusText(sub.status) }}
                </span>
              </td>
              <td>{{ sub.comment || '-' }}</td>
              <td>
                <a :href="`http://localhost:8000/${sub.file_path}`" target="_blank" rel="noopener noreferrer" class="file-link">ดูไฟล์</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '../../../services/http';
import { user } from '@/composables/useAuth';

const submissions = ref([]);
const loading = ref(true);
const error = ref(null);

async function fetchSubmissions() {
  if (!user.value || user.value.role !== 'student') {
    error.value = 'Access Denied: This page is for students only.';
    loading.value = false;
    return;
  }

  loading.value = true;
  error.value = null;
  try {
    const response = await http.get('/get-my-submissions.php');
    if (response.data.status === 'success') {
      submissions.value = response.data.data;
    } else {
      throw new Error(response.data.message || 'Could not fetch submission history.');
    }
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
    console.error(e);
  } finally {
    loading.value = false;
  }
}

function getStatusText(status) {
  const statusMap = {
    submitted: 'ส่งแล้ว',
    approved: 'อนุมัติแล้ว',
    rejected: 'ต้องแก้ไข',
  };
  return statusMap[status] || status;
}

function formatDateTime(dateTimeString) {
  if (!dateTimeString) return '-';
  const options = {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  };
  return new Date(dateTimeString).toLocaleDateString('th-TH', options);
}

onMounted(fetchSubmissions);
</script>

<style scoped>
.container { padding: 0 24px 80px; }
.muted { color: #666; }
.page-head { padding: 32px 0; }
.page-head h1 { margin: 0 0 6px 0; font-size: 2.1rem; font-weight: 800; }
.card { background: #fff; border: 1px solid #e9e9e9; border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,.06); }

.loading-state, .error-state, .no-data-state { padding: 40px; text-align: center; font-size: 1.2rem; color: #666; }

.table-container { border: 1px solid #eee; border-radius: 12px; overflow: hidden; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 16px; text-align: left; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.table th { background-color: #f9f9f9; font-weight: 700; }

.status-chip {
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 700;
  color: #fff;
  white-space: nowrap;
}
.status-submitted { background-color: #f0ad4e; }
.status-approved { background-color: #5cb85c; }
.status-rejected { background-color: #d9534f; }

.file-link {
  color: #007bff;
  text-decoration: none;
  font-weight: 600;
}
.file-link:hover { text-decoration: underline; }
</style>
