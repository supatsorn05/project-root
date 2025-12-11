<template>
  <main class="container">
    <header class="page-head">
      <div v-if="termInfo.academic_year !== 'N/A'">
        <h1>โครงการ (ปีการศึกษา: {{ termInfo.academic_year }} เทอม: {{ termInfo.term_name }})</h1>
        <p class="muted">รายชื่อโครงการทั้งหมดสำหรับภาคเรียนนี้</p>
      </div>
      <div v-else>
        <h1>ไม่พบข้อมูลภาคการศึกษา</h1>
      </div>
      <button @click="router.back()" class="btn btn-secondary">กลับ</button>
    </header>

    <section class="card">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>กำลังโหลด...</span>
      </div>
      <div v-if="error" class="error-state">เกิดข้อผิดพลาด: {{ error }}</div>
      <div v-if="!loading && !error">
        <div v-if="projects.length === 0" class="no-projects">
          ไม่พบโครงการสำหรับภาคเรียนนี้
        </div>
        <div v-else class="table-wrapper">
            <table class="table">
              <thead>
                <tr>
                  <th @click="sortBy('project_name')" class="sortable">
                    ชื่อโครงงาน (ไทย)
                    <span v-if="sortKey === 'project_name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                  </th>
                  <th @click="sortBy('project_name_en')" class="sortable">
                    ชื่อโครงงาน (อังกฤษ)
                    <span v-if="sortKey === 'project_name_en'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                  </th>
                  <th @click="sortBy('main_advisor_name')" class="sortable">
                    อาจารย์ที่ปรึกษา
                    <span v-if="sortKey === 'main_advisor_name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                  </th>
                  <th>เอกสารโครงงาน</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="project in sortedProjects" :key="project.project_id">
                  <td>{{ project.project_name }}</td>
                  <td>{{ project.project_name_en }}</td>
                  <td>
                    <div><strong>หลัก:</strong> {{ project.main_advisor_name || '-' }}</div>
                    <div v-if="project.secondary_advisor_name"><strong>รอง:</strong> {{ project.secondary_advisor_name }}</div>
                  </td>
                  <td>
                    <div class="action-buttons">
                        <a v-if="project.document_path" :href="project.document_path" target="_blank" class="btn btn-primary btn-sm">ดูเอกสาร</a>
                        <button @click="openUploadModal(project)" class="btn btn-secondary btn-sm">อัพโหลด</button>
                        <button v-if="project.document_path" @click="deleteDocument(project)" class="btn btn-danger btn-sm">ลบเอกสาร</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
        </div>
      </div>
    </section>

    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="modal-overlay">
      <div class="modal-content">
        <h2>อัพโหลดเอกสารสำหรับ<br><span class="project-name">{{ currentProject.project_name }}</span></h2>
        <div class="form-group">
            <label for="file-upload">เลือกไฟล์ PDF:</label>
            <input id="file-upload" type="file" @change="handleFileChange" accept=".pdf">
        </div>
        <div class="modal-actions">
          <button @click="uploadDocument" class="btn btn-primary" :disabled="!selectedFile || isUploading">{{ isUploading ? 'กำลังอัพโหลด...' : 'อัพโหลด' }}</button>
          <button @click="closeUploadModal" class="btn btn-secondary">ยกเลิก</button>
        </div>
        <p v-if="uploadError" class="error-message">{{ uploadError }}</p>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import http from '../../../services/http';

const route = useRoute();
const router = useRouter();

const projects = ref([]);
const termInfo = ref({ academic_year: '', term_name: '' });
const loading = ref(true);
const error = ref(null);
const sortKey = ref('');
const sortOrder = ref('asc');

const term_id = route.params.term_id;

// Modal State
const showUploadModal = ref(false);
const currentProject = ref(null);
const selectedFile = ref(null);
const uploadError = ref(null);
const isUploading = ref(false);

const sortedProjects = computed(() => {
  if (!sortKey.value) {
    return projects.value;
  }
  return [...projects.value].sort((a, b) => {
    let aValue = a[sortKey.value] || '';
    let bValue = b[sortKey.value] || '';
    return aValue.localeCompare(bValue, ['th', 'en']) * (sortOrder.value === 'asc' ? 1 : -1);
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

async function fetchProjects() {
  loading.value = true;
  error.value = null;
  try {
    const response = await http.get(`/projects-by-term.php?term_id=${term_id}`);
    if (response.data.status === 'success') {
      projects.value = response.data.data;
      termInfo.value = response.data.term_info;
    } else {
      throw new Error(response.data.message || 'Failed to fetch projects.');
    }
  } catch (e) {
    error.value = e?.response?.data?.message || e.message || 'Could not connect to the server.';
  } finally {
    loading.value = false;
  }
}

function openUploadModal(project) {
  currentProject.value = project;
  showUploadModal.value = true;
  selectedFile.value = null;
  uploadError.value = null;
}

function closeUploadModal() {
  showUploadModal.value = false;
}

function handleFileChange(event) {
  selectedFile.value = event.target.files[0];
}

async function uploadDocument() {
  if (!selectedFile.value || !currentProject.value) return;

  isUploading.value = true;
  uploadError.value = null;

  const formData = new FormData();
  formData.append('file', selectedFile.value);
  formData.append('project_id', currentProject.value.project_id);
  // This assumes a fixed doc_type_id for the final project report.
  // In a real app, this might need to be more dynamic.
  formData.append('doc_type_id', 1);

  try {
    const response = await http.post('/upload-document.php', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    if (response.data.status === 'success') {
      alert('อัพโหลดเอกสารสำเร็จ!');
      await fetchProjects(); // Refresh the list
      closeUploadModal();
    } else {
      throw new Error(response.data.message || 'อัพโหลดเอกสารล้มเหลว');
    }
  } catch (e) {
    uploadError.value = e?.response?.data?.message || e.message || 'เกิดข้อผิดพลาดในการอัพโหลดเอกสาร';
  } finally {
    isUploading.value = false;
  }
}

async function deleteDocument(project) {
  if (!confirm(`คุณแน่ใจหรือไม่ที่จะลบเอกสารของโครงงาน "${project.project_name}"?`)) return;

  try {
    // This assumes doc_type_id=1 for the main project document.
    const response = await http.delete(`/delete-document.php?project_id=${project.project_id}&doc_type_id=1`);
    if (response.data.status === 'success') {
      alert('เอกสารถูกลบเรียบร้อยแล้ว');
      await fetchProjects(); // Refresh the list
    } else {
      throw new Error(response.data.message || 'ลบเอกสารล้มเหลว');
    }
  } catch (e) {
    alert(e?.response?.data?.message || e.message || 'เกิดข้อผิดพลาดในการลบเอกสาร');
  }
}

onMounted(fetchProjects);
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
  position: relative;
  text-align: center;
}
.page-head h1 { margin: 0 0 6px 0; font-size: 2.1rem; font-weight: 700; }
.page-head .btn-secondary {
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
}

.table-wrapper { 
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    background: var(--surface-color);
    overflow: hidden;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.loading-state, .error-state, .no-projects { padding: 40px; text-align: center; color: var(--text-secondary); }
.error-state { color: var(--danger-color); }

/* Modal Styles */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: var(--surface-color); padding: 30px; border-radius: var(--radius-lg); width: 90%; max-width: 500px; display: flex; flex-direction: column; gap: 20px; }
.modal-content h2 { margin-top: 0; font-size: 1.5rem; color: var(--text-primary); font-weight: 700; line-height: 1.4; }
.modal-content .project-name { color: var(--primary-dark); }

.form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
.modal-content input[type="file"] { border: 1px solid var(--border-color); padding: 10px; border-radius: var(--radius); background: var(--background-color); width: 100%; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 1rem; }

.error-message { color: var(--danger-color); font-size: 0.9rem; margin-top: 1rem; text-align: center; }

.sortable {
  cursor: pointer;
}
</style>