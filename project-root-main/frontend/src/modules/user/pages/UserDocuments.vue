<template>
  <main class="container">
    <header class="page-head">
      <div>
        <h1>รวมแบบฟอร์มและเอกสาร</h1>
        <p class="muted">แบบฟอร์มและแม่แบบเอกสารสำหรับโครงงานรายวิชา</p>
      </div>
    </header>

    <div v-if="loading" class="loading-state">กำลังโหลด...</div>
    <div v-if="error" class="error-state">เกิดข้อผิดพลาด: {{ error }}</div>

    <div v-if="docStructure">
      <section class="card">
        <h2 class="section-title">แบบฟอร์ม (CS-01 - CS-04)</h2>
        <p class="section-subtitle">ฟอร์มเอกสารสำคัญสำหรับยื่นเสนอโครงงาน</p>
        <div class="file-list">
          <a v-for="form in docStructure.forms" :key="form.id" :href="backendBaseUrl + form.path" class="file-item" :class="{ disabled: !form.available }" download>
            <div class="file-icon-wrapper">
              <svg class="file-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#e53e3e">
                <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a.375.375 0 0 1-.375-.375V6.75A3.75 3.75 0 0 0 9 3H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                <path d="M12.971 1.816A5.23 5.23 0 0 1 15.75 1.5h.75a.75.75 0 0 1 .75.75v.75a.75.75 0 0 1-.75.75h-.75a.75.75 0 0 0-.53.22l-1.07 1.07-1.5-1.5 1.07-1.07a.75.75 0 0 0-.22-.53Z" />
              </svg>
            </div>
            <div class="file-name">{{ form.name }}</div>
            <div class="download-icon-wrapper">
              <svg v-if="form.available" class="download-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v11.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06l3.22 3.22V3a.75.75 0 0 1 .75-.75Zm-9 13.5a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
              </svg>
              <span v-else class="not-available-chip">ไม่มีไฟล์</span>
            </div>
          </a>
        </div>
      </section>

      <section class="card">
        <h2 class="section-title">รูปแบบเล่มรายงาน</h2>
        <p class="section-subtitle">แม่แบบสำหรับจัดทำรูปเล่มรายงานสมบูรณ์</p>
        <div class="template-list">
          <div v-for="template in docStructure.reportTemplates" :key="template.id" class="template-item">
            <span class="template-name">{{ template.name }}</span>
            <div class="template-links">
              <a :href="backendBaseUrl + template.pdfPath" class="file-badge" :class="{ disabled: !template.pdf_available }" download>
                <svg class="file-icon-badge" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#e53e3e">
                  <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a.375.375 0 0 1-.375-.375V6.75A3.75 3.75 0 0 0 9 3H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" /><path d="M12.971 1.816A5.23 5.23 0 0 1 15.75 1.5h.75a.75.75 0 0 1 .75.75v.75a.75.75 0 0 1-.75.75h-.75a.75.75 0 0 0-.53.22l-1.07 1.07-1.5-1.5 1.07-1.07a.75.75 0 0 0-.22-.53Z" />
                </svg>
                <span>PDF</span>
              </a>
              <a :href="backendBaseUrl + template.docxPath" class="file-badge" :class="{ disabled: !template.docx_available }" download>
                <svg class="file-icon-badge" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#4A90E2">
                  <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a.375.375 0 0 1-.375-.375V6.75A3.75 3.75 0 0 0 9 3H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" /><path d="M12.971 1.816A5.23 5.23 0 0 1 15.75 1.5h.75a.75.75 0 0 1 .75.75v.75a.75.75 0 0 1-.75.75h-.75a.75.75 0 0 0-.53.22l-1.07 1.07-1.5-1.5 1.07-1.07a.75.75 0 0 0-.22-.53Z" />
                </svg>
                <span>DOCX</span>
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const docStructure = ref(null);
const loading = ref(true);
const error = ref(null);

const backendBaseUrl = 'http://localhost:8000';

onMounted(async () => {
  try {
    const response = await axios.get('/api/list-document-templates.php');
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
});
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px 80px;
}

.muted {
  color: var(--text-secondary);
}

.page-head {
  padding: 32px 0;
  text-align: center;
  border-bottom: 1px solid var(--border-color); /* Added border for separation */
  margin-bottom: 32px;
}

.page-head h1 {
  margin: 0 0 6px 0;
  font-size: 2.1rem;
  font-weight: 700;
  color: var(--text-primary);
}

.card {
  background: rgba(var(--surface-color-rgb), 0.7);
  border: 1px solid rgba(var(--border-color-rgb), 0.2);
  border-radius: 16px;
  padding: 24px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
  margin-bottom: 32px;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text-primary);
}

.section-subtitle {
  color: var(--text-secondary);
  margin-bottom: 24px;
  min-height: 20px; /* Adjusted min-height for better spacing */
}

.file-list, .template-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.file-item, .template-item {
  display: flex;
  align-items: center;
  padding: 16px; /* Increased padding for better touch targets */
  border-radius: 12px;
  text-decoration: none;
  color: var(--text-primary);
  background-color: rgba(var(--background-color-rgb), 0.6); /* Slightly more opaque */
  border: 1px solid transparent;
  transition: all 0.2s ease-in-out;
}

.file-item:not(.disabled):hover, .template-item:not(.disabled):hover {
  transform: translateY(-3px); /* More pronounced lift effect */
  border-color: rgba(var(--primary-color-rgb), 0.4); /* Highlight border on hover */
  background-color: rgba(var(--surface-hover-color-rgb), 0.8); /* More opaque on hover */
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1); /* Subtle shadow on hover */
}

.file-item.disabled {
  opacity: 0.5;
  pointer-events: none;
}

.file-icon-wrapper {
  flex-shrink: 0;
  width: 48px; /* Slightly larger icon wrapper */
  height: 48px;
  border-radius: 10px; /* Slightly more rounded */
  margin-right: 20px; /* Increased margin */
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(var(--primary-color-rgb), 0.1); /* Use primary color for icon background */
}

.file-icon {
  width: 28px; /* Slightly larger icon */
  height: 28px;
}

.file-name, .template-name {
  font-weight: 600;
  flex-grow: 1;
  font-size: 1.1rem; /* Slightly larger font for names */
}

.download-icon-wrapper {
  margin-left: 20px; /* Increased margin */
}

.download-icon {
  width: 28px; /* Slightly larger icon */
  height: 28px;
  color: var(--text-secondary);
  transition: color 0.2s ease;
}

.file-item:not(.disabled):hover .download-icon {
  color: var(--primary-color); /* Highlight download icon on hover */
}

.not-available-chip {
  font-size: 12px;
  font-weight: 500;
  color: var(--text-secondary);
  background-color: rgba(var(--text-secondary-rgb), 0.1);
  padding: 4px 8px;
  border-radius: 6px;
}

.template-item {
  justify-content: space-between;
}

.template-links {
  display: flex;
  gap: 10px;
}

.file-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px; /* Increased padding */
  border-radius: 8px;
  font-size: 0.9rem; /* Slightly larger font */
  font-weight: 600;
  text-decoration: none;
  color: var(--text-primary);
  background-color: rgba(var(--border-color-rgb), 0.1);
  border: 1px solid transparent;
  transition: all 0.2s ease-in-out;
}

.file-badge .file-icon-badge {
  width: 20px; /* Slightly larger icon */
  height: 20px;
}

.file-badge:not(.disabled):hover {
  transform: scale(1.03); /* Subtle scale on hover */
  background-color: rgba(var(--border-color-rgb), 0.2);
  border-color: rgba(var(--primary-color-rgb), 0.3); /* Highlight border on hover */
}

.file-badge.disabled {
  opacity: 0.5;
  pointer-events: none;
}

.loading-state,
.error-state {
  padding: 60px 20px;
  text-align: center;
  color: var(--text-secondary);
  font-size: 1.1rem;
}

.error-state {
  color: var(--danger-color);
}
</style>
