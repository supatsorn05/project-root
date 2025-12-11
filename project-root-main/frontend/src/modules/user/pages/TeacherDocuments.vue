<template>
  <main class="container">
    <header class="page-head">
      <div>
        <h1>ตรวจเอกสาร</h1>
        <p class="muted">เอกสารที่นิสิตส่งเพื่อรอการตรวจสอบ</p>
      </div>
    </header>

    <div v-if="loading" class="loading-state">กำลังโหลดเอกสาร...</div>
    <div v-else-if="error" class="error-state">{{ error }}</div>

    <div v-else>
      <!-- Section 1: Submitted Documents -->
      <section class="document-section">
        <h2 class="section-title">เอกสารรอการตรวจ</h2>
        <div v-if="submittedDocuments.length === 0" class="no-data">ไม่มีเอกสารที่ต้องตรวจ</div>
        <div v-else class="document-list">
          <div v-for="doc in submittedDocuments" :key="doc.id" class="document-card">
            <div class="doc-info">
              <div class="doc-title">{{ doc.doc_name }}</div>
              <div class="doc-meta">นักศึกษา: {{ doc.student_name }}</div>
              <div class="doc-meta">ชื่อกลุ่ม: {{ doc.group_name || 'N/A' }}</div>
              <div class="doc-meta">วันที่ส่ง: {{ formatDateTime(doc.uploaded_at) }}</div>
              <a :href="`http://localhost:8000/${doc.file_path}`" target="_blank" class="file-link">ดูไฟล์ของนิสิต</a>
            </div>
            <div class="doc-status">
              <span :class="`status-chip status-${doc.status}`">{{ getStatusText(doc.status) }}</span>
            </div>
            <div class="doc-actions">
              <textarea v-model="comments[doc.id]" placeholder="เพิ่มความคิดเห็น (ถ้ามี)"></textarea>
              <div class="action-buttons">
                <button @click="openUploadModal(doc.id)" class="btn-approve">อนุมัติและแนบไฟล์</button>
                <button @click="submitReview(doc.id, 'rejected')" class="btn-reject">ส่งกลับไปแก้ไข</button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 2: Rejected Documents -->
      <section class="document-section">
        <h2 class="section-title">เอกสารที่ส่งกลับไปแก้ไข</h2>
        <div v-if="rejectedDocuments.length === 0" class="no-data">ไม่มีเอกสารที่ส่งกลับไปแก้ไข</div>
        <div v-else class="document-list">
          <div v-for="doc in rejectedDocuments" :key="doc.id" class="document-card">
            <div class="doc-info">
              <div class="doc-title">{{ doc.doc_name }}</div>
              <div class="doc-meta">นักศึกษา: {{ doc.student_name }}</div>
              <div class="doc-meta">ชื่อกลุ่ม: {{ doc.group_name || 'N/A' }}</div>
              <div class="doc-meta">วันที่ส่ง: {{ formatDateTime(doc.uploaded_at) }}</div>
              <a :href="`http://localhost:8000/${doc.file_path}`" target="_blank" class="file-link">ดูไฟล์ของนิสิต</a>
            </div>
            <div class="doc-status">
              <span :class="`status-chip status-${doc.status}`">{{ getStatusText(doc.status) }}</span>
            </div>
            <div v-if="doc.comment" class="rejection-comment">
              <strong>เหตุผลที่ส่งกลับ:</strong> {{ doc.comment }}
            </div>
          </div>
        </div>
      </section>

      <!-- Section 3: Approved Documents -->
      <section class="document-section">
        <h2 class="section-title">เอกสารที่อนุมัติแล้ว</h2>
        <div v-if="approvedDocuments.length === 0" class="no-data">ไม่มีเอกสารที่อนุมัติแล้ว</div>
        <div v-else class="document-list">
          <div v-for="doc in approvedDocuments" :key="doc.id" class="document-card">
            <div class="doc-info">
              <div class="doc-title">{{ doc.doc_name }}</div>
              <div class="doc-meta">นักศึกษา: {{ doc.student_name }}</div>
              <div class="doc-meta">ชื่อกลุ่ม: {{ doc.group_name || 'N/A' }}</div>
              <div class="doc-meta">วันที่ส่ง: {{ formatDateTime(doc.uploaded_at) }}</div>
              <a :href="`http://localhost:8000/${doc.file_path}`" target="_blank" class="file-link">ดูไฟล์ของนิสิต</a>
              <a v-if="doc.teacher_file_path" :href="`http://localhost:8000/${doc.teacher_file_path}`" target="_blank" class="file-link teacher-file-link">ดูไฟล์ที่อนุมัติแล้ว</a>
            </div>
            <div class="doc-status">
              <span :class="`status-chip status-${doc.status}`">{{ getStatusText(doc.status) }}</span>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="modal-backdrop">
      <div class="modal-content">
        <h3>อนุมัติและอัปโหลดไฟล์ที่ลงนาม</h3>
        <p>กรุณาเลือกไฟล์เอกสารที่ลงนามแล้ว (เช่น .pdf) เพื่อส่งกลับให้นิสิต</p>
        <input type="file" @change="handleFileChange" accept=".pdf,.doc,.docx" class="file-input">
        <div class="modal-actions">
          <button @click="closeUploadModal" class="btn-cancel">ยกเลิก</button>
          <button @click="handleApprovalUpload" class="btn-confirm" :disabled="!selectedFile">ยืนยันและอัปโหลด</button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import http from '../../../services/http';

const loading = ref(true);
const error = ref(null);
const documents = ref([]);
const comments = ref({});

// Modal state
const showUploadModal = ref(false);
const selectedDocId = ref(null);
const selectedFile = ref(null);

// Computed properties for filtering documents
const submittedDocuments = computed(() => documents.value.filter(doc => doc.status === 'submitted'));
const rejectedDocuments = computed(() => documents.value.filter(doc => doc.status === 'rejected'));
const approvedDocuments = computed(() => documents.value.filter(doc => doc.status === 'approved'));

async function fetchDocuments() {
  loading.value = true;
  error.value = null;
  try {
    const response = await http.get('/get-teacher-documents.php');
    if (response.data.status === 'success') {
      documents.value = response.data.data;
    } else {
      throw new Error(response.data.message || 'Could not fetch documents.');
    }
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Error loading data';
  } finally {
    loading.value = false;
  }
}

async function submitReview(id, status) { // This is now only for 'rejected'
  const comment = comments.value[id] || '';

  if (status === 'rejected' && !comment.trim()) {
    alert('Please add a comment before rejecting.');
    return;
  }

  const formData = new FormData();
  formData.append('id', id);
  formData.append('status', status);
  formData.append('comment', comment);

  try {
    const response = await http.post('/update-submission-status.php', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.status === 'success') {
      alert('Review submitted successfully.');
      fetchDocuments(); // Refresh the list
    } else {
      throw new Error(response.data.message);
    }
  } catch (e) {
    alert('Error: ' + (e.response?.data?.message || e.message));
  }
}

function openUploadModal(docId) {
  selectedDocId.value = docId;
  showUploadModal.value = true;
}

function closeUploadModal() {
  showUploadModal.value = false;
  selectedDocId.value = null;
  selectedFile.value = null;
}

function handleFileChange(event) {
  selectedFile.value = event.target.files[0] || null;
}

async function handleApprovalUpload() {
  if (!selectedFile.value) {
    alert('Please select a file to upload.');
    return;
  }

  const id = selectedDocId.value;
  const comment = comments.value[id] || '';
  
  const formData = new FormData();
  formData.append('id', id);
  formData.append('status', 'approved');
  formData.append('comment', comment);
  formData.append('teacher_file', selectedFile.value);

  try {
    const response = await http.post('/update-submission-status.php', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.status === 'success') {
      alert('Approval and upload successful.');
      closeUploadModal();
      fetchDocuments(); // Refresh list
    } else {
      throw new Error(response.data.message);
    }
  } catch (e) {
    alert('Upload error: ' + (e.response?.data?.message || e.message));
  }
}


function getStatusText(status) {
  const statusMap = { submitted: 'Pending', approved: 'Approved', rejected: 'Needs revision' };
  return statusMap[status] || status;
}

function formatDateTime(dateTimeString) {
  if (!dateTimeString) return '-';
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateTimeString).toLocaleDateString('th-TH', options);
}

onMounted(fetchDocuments);
</script>

<style scoped>
.container { padding: 0 24px 80px; }
.muted { color: #666; }
.page-head { padding: 32px 0; }
.page-head h1 { margin: 0 0 6px 0; font-size: 2.1rem; font-weight: 800; }

.loading-state, .error-state, .no-data { padding: 40px; text-align: center; font-size: 1.2rem; color: #666; }

.document-section { margin-bottom: 48px; }
.section-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 24px; border-bottom: 2px solid #eee; padding-bottom: 12px; }

.document-list { display: grid; gap: 24px; }
.document-card {
  background: #fff; 
  border: 1px solid #e9e9e9; 
  border-radius: 16px; 
  box-shadow: 0 8px 25px rgba(0,0,0,.05);
  display: grid;
  grid-template-columns: 1fr auto;
  grid-template-rows: auto auto;
  grid-template-areas:
    "info status"
    "actions actions";
  padding: 20px;
  gap: 16px 20px;
}

.doc-info { grid-area: info; }
.doc-status { grid-area: status; text-align: right; }
.doc-actions { grid-area: actions; border-top: 1px solid #f0f0f0; padding-top: 16px; }

.doc-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 8px; }
.doc-meta { color: #555; margin-bottom: 4px; font-size: 0.9rem; }
.file-link { color: #007bff; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.file-link:hover { text-decoration: underline; }

.teacher-file-link {
  color: #28a745; /* Green to signify approved */
  margin-left: 16px; /* Add some space */
}

.status-chip { padding: 5px 12px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; color: #fff; white-space: nowrap; }
.status-submitted { background-color: #f0ad4e; }
.status-approved { background-color: #5cb85c; }
.status-rejected { background-color: #d9534f; }

.doc-actions textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  margin-bottom: 12px;
  font-size: 0.95rem;
  min-height: 60px;
}

.action-buttons { display: flex; justify-content: flex-end; gap: 12px; }
.action-buttons button { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-approve { background-color: #28a745; color: white; }
.btn-approve:hover { background-color: #218838; }
.btn-reject { background-color: #dc3545; color: white; }
.btn-reject:hover { background-color: #c82333; }

.rejection-comment {
  grid-area: actions; 
  border-top: 1px solid #f0f0f0; 
  padding-top: 16px;
  background-color: #fff8f8;
  padding: 12px;
  border-radius: 8px;
  color: #d9534f;
  font-size: 0.9rem;
}

.rejection-comment strong { color: #b94a48; }

/* Modal Styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  width: 90%;
  max-width: 500px;
}

.modal-content h3 {
  margin-top: 0;
  margin-bottom: 12px;
  font-size: 1.4rem;
}

.modal-content p {
  margin-bottom: 20px;
  color: #666;
}

.file-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  margin-bottom: 24px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.modal-actions button {
  padding: 10px 20px;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  cursor: pointer;
}

.btn-cancel {
  background-color: #f0f0f0;
  color: #333;
}
.btn-cancel:hover {
  background-color: #e0e0e0;
}

.btn-confirm {
  background-color: #007bff;
  color: white;
}
.btn-confirm:hover {
  background-color: #0056b3;
}
.btn-confirm:disabled {
  background-color: #a0c8f0;
  cursor: not-allowed;
}
</style>