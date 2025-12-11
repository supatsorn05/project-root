<template>
  <main class="container">
    <header class="page-head">
      <div>
        <h1>จัดการแม่แบบเอกสาร</h1>
        <p class="muted">อัพโหลดไฟล์สำหรับแบบฟอร์มและแม่แบบรายงาน</p>
      </div>
    </header>

    <div v-if="loading" class="loading-state">กำลังโหลด...</div>
    <div v-if="error" class="error-state">เกิดข้อผิดพลาด: {{ error }}</div>

    <template v-if="docStructure">
      <!-- Hidden file input -->
      <input type="file" ref="fileInput" @change="handleFileUpload" style="display: none" />

      <section class="card">
        <h2 class="section-title">แบบฟอร์ม (CS-01 - CS-04)</h2>
        <div class="management-list">
          <div v-for="form in docStructure.forms" :key="form.id" class="management-row">
            <div class="doc-info">
              <span class="doc-name">{{ form.name }}</span>
              <span v-if="form.available" class="status-chip available">มีไฟล์</span>
              <span v-else class="status-chip missing">ไม่มีไฟล์</span>
            </div>
            <div class="action-buttons">
                <a v-if="form.available" :href="backendBaseUrl + form.path" download target="_blank" class="btn btn-primary btn-sm">ดู PDF</a>
                <button @click="triggerUpload(form.id, 'pdf')" class="btn btn-secondary btn-sm">อัพโหลด PDF</button>
                <button v-if="form.available" @click="deleteDocumentTemplate(form.id, 'pdf', form.name)" class="btn btn-danger btn-sm">ลบ PDF</button>
            </div>
          </div>
        </div>
      </section>

      <section class="card">
        <h2 class="section-title">รูปแบบเล่มรายงาน</h2>
        <div class="management-list">
          <div v-for="template in docStructure.reportTemplates" :key="template.id" class="management-row template-row">
            <div class="doc-info">
              <span class="doc-name">{{ template.name }}</span>
            </div>
            <div class="template-actions">
              <div class="action-group">
                <span class="file-type">PDF</span>
                <span v-if="template.pdf_available" class="status-chip available">มีไฟล์</span>
                <span v-else class="status-chip missing">ไม่มีไฟล์</span>
                <a v-if="template.pdf_available" :href="backendBaseUrl + template.pdfPath" download target="_blank" class="btn btn-primary btn-sm">ดู PDF</a>
                <button @click="triggerUpload(template.id, 'pdf')" class="btn btn-secondary btn-sm">อัพโหลด PDF</button>
                <button v-if="template.pdf_available" @click="deleteDocumentTemplate(template.id, 'pdf', template.name)" class="btn btn-danger btn-sm">ลบ PDF</button>
              </div>
              <div class="action-group">
                <span class="file-type">DOCX</span>
                <span v-if="template.docx_available" class="status-chip available">มีไฟล์</span>
                <span v-else class="status-chip missing">ไม่มีไฟล์</span>
                <a v-if="template.docx_available" :href="backendBaseUrl + template.docxPath" download target="_blank" class="btn btn-primary btn-sm">ดู DOCX</a>
                <button @click="triggerUpload(template.id, 'docx')" class="btn btn-secondary btn-sm">อัพโหลด DOCX</button>
                <button v-if="template.docx_available" @click="deleteDocumentTemplate(template.id, 'docx', template.name)" class="btn btn-danger btn-sm">ลบ DOCX</button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '@/services/http';

const docStructure = ref(null);
const loading = ref(true);
const error = ref(null);
const fileInput = ref(null);

let uploadContext = {};

const backendBaseUrl = 'http://localhost:8000'; // Define backend base URL

async function fetchDocuments() {
  loading.value = true;
  error.value = null;
  try {
    const response = await http.get('/list-document-templates.php');
    if (response.data.status === 'success') {
      docStructure.value = response.data.data;
    } else {
      throw new Error('Failed to load document list.');
    }
  } catch (e) {
    error.value = e?.response?.data?.message || e.message || 'Could not connect to the server.';
  } finally {
    loading.value = false;
  }
}

function triggerUpload(template_id, doc_type) {
  uploadContext = { template_id, doc_type };
  fileInput.value.click();
}

async function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('document', file);
  formData.append('template_id', uploadContext.template_id);
  formData.append('doc_type', uploadContext.doc_type);

  loading.value = true;

  try {
    const response = await http.post('/upload-document-template.php', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    if (response.data.status === 'success') {
      alert('อัพโหลดไฟล์สำเร็จ');
    } else {
      throw new Error(response.data.message);
    }
  } catch (e) {
    alert(e.message || 'เกิดข้อผิดพลาดในการอัพโหลด');
  } finally {
    event.target.value = '';
    await fetchDocuments();
  }
}

async function deleteDocumentTemplate(template_id, doc_type, doc_name) {
  if (!confirm(`คุณแน่ใจหรือไม่ที่จะลบไฟล์ ${doc_name} (${doc_type.toUpperCase()})?`)) return;

  loading.value = true;
  try {
    const response = await http.delete(`/delete-document-template.php?template_id=${template_id}&doc_type=${doc_type}`);
    if (response.data.status === 'success') {
      alert('ลบไฟล์สำเร็จ');
    } else {
      throw new Error(response.data.message);
    }
  } catch (e) {
    alert(e.message || 'เกิดข้อผิดพลาดในการลบไฟล์');
  } finally {
    await fetchDocuments();
  }
}

onMounted(fetchDocuments);
</script>

<style scoped>
.container { padding: 0 24px 80px; }
.muted { color: #666; }
.page-head { padding: 32px 0; text-align: center; }
.page-head h1 { margin: 0 0 6px 0; font-size: 2.1rem; font-weight: 800; }
.card { background: #fff; border: 1px solid #e9e9e9; border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,.06); margin-bottom: 24px; }

.section-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 24px; }

.management-list { display: flex; flex-direction: column; gap: 12px; }
.management-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border: 1px solid #f0f0f0;
  border-radius: 12px;
}
.doc-info { display: flex; align-items: center; gap: 16px; }
.doc-name { font-weight: 600; }
.doc-name-link { font-weight: 600; color: #007bff; text-decoration: none; }
.doc-name-link:hover { text-decoration: underline; }

.status-chip { font-size: 0.8rem; padding: 4px 10px; border-radius: 999px; font-weight: 700; color: white; }
.status-chip.available { background-color: #48bb78; }
.status-chip.missing { background-color: #f56565; }

.upload-btn { font-weight: 600; padding: 8px 16px; border-radius: 8px; border: 1px solid #ccc; background: #f0f0f0; cursor: pointer; }
.upload-btn:hover { background: #e0e0e0; }

.template-row { flex-direction: column; align-items: stretch; gap: 12px; }
.template-actions { display: flex; justify-content: space-between; gap: 16px; }
.action-group { display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #f7f7f7; border-radius: 8px; flex-grow: 1; }
.file-type { font-weight: 700; }
.file-type-link { font-weight: 700; color: #007bff; text-decoration: none; }
.file-type-link:hover { text-decoration: underline; }

.loading-state, .error-state { padding: 40px; text-align: center; }
</style>
