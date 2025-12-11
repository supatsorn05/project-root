<template>
  <main class="container">
    <header class="page-head">
      <div>
        <h1>ส่งเอกสาร (สำหรับผู้ยังไม่มีโครงงาน)</h1>
        <p class="muted">เลือกประเภทเอกสารและอาจารย์ที่ต้องการส่งเอกสารให้ตรวจสอบ</p>
      </div>
    </header>

    <section class="card">
      <div v-if="loading" class="loading-state">กำลังโหลดข้อมูล...</div>
      <div v-else-if="error" class="error-state">{{ error }}</div>
      
      <form v-else @submit.prevent="handleUpload">
        <div class="form-grid">
          <!-- Document Type Selection -->
          <div class="form-group">
            <label for="doc-type">1. เลือกประเภทเอกสาร</label>
            <select id="doc-type" v-model="selectedDocType" required>
              <option disabled value="">กรุณาเลือกประเภทเอกสาร</option>
              <option v-for="doc in docTypes" :key="doc.doc_type_id" :value="doc.doc_type_id">
                {{ doc.doc_code }} - {{ doc.doc_name }}
              </option>
            </select>
          </div>

          <!-- Teacher Selection -->
          <div class="form-group">
            <label for="teacher">2. เลือกอาจารย์ผู้รับ</label>
            <select id="teacher" v-model="selectedTeacher" required>
              <option disabled value="">กรุณาเลือกอาจารย์</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.full_name }}
              </option>
            </select>
          </div>

          <!-- File Input -->
          <div class="form-group">
            <label for="file-upload">3. เลือกไฟล์ (PDF เท่านั้น)</label>
            <input type="file" id="file-upload" @change="handleFileChange" accept=".pdf" required />
          </div>
        </div>

        <div class="form-actions">
            <button type="submit" :disabled="isUploading">
                <span v-if="isUploading">กำลังอัปโหลด...</span>
                <span v-else>ส่งเอกสาร</span>
            </button>
        </div>
      </form>

      <!-- Upload Result -->
      <div v-if="uploadResult" class="upload-result">
        <h4>{{ uploadResult.message }}</h4>
        <p>
          คุณสามารถดูไฟล์ที่อัปโหลดได้ที่นี่: 
          <a :href="`http://localhost:8000/${uploadResult.filePath}`" target="_blank" rel="noopener noreferrer">ดูเอกสาร</a>
        </p>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '../../../services/http';

const docTypes = ref([]);
const teachers = ref([]);
const selectedDocType = ref('');
const selectedTeacher = ref('');
const selectedFile = ref(null);
const uploadResult = ref(null); // To store { message, filePath }

const loading = ref(true);
const error = ref(null);
const isUploading = ref(false);

async function fetchData() {
  loading.value = true;
  error.value = null;
  try {
    const [docTypesResponse, teachersResponse] = await Promise.all([
      http.get('/list-submission-types.php'),
      http.get('/list-teachers.php')
    ]);
    
    docTypes.value = docTypesResponse.data;
    teachers.value = teachersResponse.data;

  } catch (e) {
    error.value = 'ไม่สามารถโหลดข้อมูลเริ่มต้นได้ (ประเภทเอกสาร, รายชื่ออาจารย์)';
    console.error(e);
  } finally {
    loading.value = false;
  }
}

function handleFileChange(event) {
  const file = event.target.files[0];
  if (file && file.type === 'application/pdf') {
    selectedFile.value = file;
  } else {
    alert('กรุณาเลือกไฟล์ PDF เท่านั้น');
    event.target.value = null; // Clear the input
    selectedFile.value = null;
  }
}

async function handleUpload() {
  if (!selectedDocType.value || !selectedTeacher.value || !selectedFile.value) {
    alert('กรุณากรอกข้อมูลให้ครบถ้วน: ประเภทเอกสาร, อาจารย์, และไฟล์');
    return;
  }

  isUploading.value = true;
  uploadResult.value = null; // Clear previous result

  const formData = new FormData();
  formData.append('doc_type_id', selectedDocType.value);
  formData.append('teacher_user_id', selectedTeacher.value);
  formData.append('file', selectedFile.value);

  try {
    const response = await http.post('/upload-student-document.php', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.status === 'success') {
      // alert('ส่งเอกสารสำเร็จ!');
      uploadResult.value = { 
        message: 'ส่งเอกสารสำเร็จ!', 
        filePath: response.data.file_path 
      };
      // Reset form
      selectedDocType.value = '';
      selectedTeacher.value = '';
      selectedFile.value = null;
      document.getElementById('file-upload').value = null;
    } else {
      throw new Error(response.data.message || 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ');
    }
  } catch (e) {
    alert(`เกิดข้อผิดพลาดในการอัปโหลด: ${e.message}`);
    console.error(e);
  } finally {
    isUploading.value = false;
  }
}

onMounted(fetchData);
</script>

<style scoped>
.container { padding: 0 24px 80px; }
.muted { color: #666; }
.page-head { padding: 32px 0; }
.page-head h1 { margin: 0 0 6px 0; font-size: 2.1rem; font-weight: 800; }
.card { background: #fff; border: 1px solid #e9e9e9; border-radius: 20px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,.06); }

.loading-state, .error-state { padding: 40px; text-align: center; font-size: 1.2rem; color: #666; }

.form-grid {
  display: grid;
  gap: 24px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #333;
}

.form-group select,
.form-group input[type="file"] {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 1rem;
  background-color: #f9f9f9;
}

.form-group input[type="file"]::file-selector-button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    cursor: pointer;
    margin-right: 10px;
}

.form-actions {
  margin-top: 32px;
  text-align: right;
}

.form-actions button {
  padding: 12px 30px;
  font-size: 1.1rem;
  font-weight: 700;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.form-actions button:hover {
  background-color: #218838;
}

.form-actions button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.upload-result {
  margin-top: 32px;
  padding: 20px;
  border: 1px solid #d4edda;
  background-color: #d4edda;
  color: #155724;
  border-radius: 8px;
}

.upload-result h4 {
  margin-top: 0;
  font-weight: 700;
}

.upload-result a {
  color: #155724;
  font-weight: 600;
  text-decoration: underline;
}
</style>