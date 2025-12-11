<template>
  <main class="container">
    <header class="page-head">
      <div>
        <h1>ข้อมูลส่วนตัว</h1>
      </div>
    </header>

    <section class="card" v-if="user">
      <div class="profile-grid">
        <div class="profile-avatar">
          <img v-if="user.profile_image_url" :src="`http://localhost:8000${user.profile_image_url}`" alt="Profile Picture" class="avatar-image">
          <div v-else class="avatar-initials">{{ initials }}</div>
          <button class="upload-btn" @click="triggerFileUpload" :disabled="uploading">
            <span v-if="uploading">Uploading...</span>
            <span v-else>เปลี่ยนรูป</span>
          </button>
          <input type="file" ref="fileInput" @change="onFileSelected" accept="image/png, image/jpeg, image/gif" hidden>
        </div>
        <div class="profile-details">
          <h2>{{ displayName }}</h2>
          <div class="detail-item">
            <span class="label">อีเมล:</span>
            <span>{{ user.email }}</span>
          </div>
          <div class="detail-item">
            <span class="label">ชื่อผู้ใช้:</span>
            <span>{{ user.username }}</span>
          </div>
          <div class="detail-item">
            <span class="label">บทบาท:</span>
            <span class="role-badge">{{ user.role }}</span>
          </div>
          <div class="detail-item" v-if="user.role === 'student' && user.student_id">
            <span class="label">รหัสนิสิต:</span>
            <span>{{ user.student_id }}</span>
          </div>
          <p v-if="uploadError" class="upload-error">{{ uploadError }}</p>
        </div>
      </div>
    </section>
    <div v-else class="loading-state">
      กำลังโหลดข้อมูลผู้ใช้...
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue';
import { user, displayName, initials } from '@/composables/useAuth';
import http from '@/services/http';

const fileInput = ref(null);
const uploading = ref(false);
const uploadError = ref('');

const triggerFileUpload = () => {
  fileInput.value.click();
};

const onFileSelected = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  uploading.value = true;
  uploadError.value = '';

  const formData = new FormData();
  formData.append('profile_picture', file);

  try {
    const response = await http.post('/upload-profile-picture.php', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.status === 'success') {
      // Update user object directly for instant UI update
      user.value.profile_image_url = response.data.url;
    } else {
      throw new Error(response.data.message || 'Upload failed');
    }
  } catch (error) {
    uploadError.value = error.response?.data?.message || error.message || 'An error occurred during upload.';
  } finally {
    uploading.value = false;
  }
};
</script>

<style scoped>
.container {
  padding: 0 24px 80px;
  max-width: 900px;
  margin: 0 auto;
}
.page-head {
  padding: 32px 0;
  text-align: center;
}
.page-head h1 { font-size: 2.1rem; font-weight: 700; }
.muted { color: var(--text-secondary); }
.card { padding: 32px; }
.profile-grid { display: flex; align-items: center; gap: 32px; }

.profile-avatar {
  position: relative;
  text-align: center;
}
.avatar-image, .avatar-initials {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--surface-color);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.avatar-initials {
  background: var(--primary-color);
  color: var(--text-on-primary);
  display: grid;
  place-items: center;
  font-size: 40px;
  font-weight: 700;
}
.upload-btn {
  margin-top: 16px;
  width: 100%;
  padding: 8px 12px;
  border-radius: var(--radius);
  border: 1px solid var(--border-color);
  background: var(--surface-color);
  color: var(--text-primary);
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
}
.upload-btn:hover:not(:disabled) {
  background: var(--background-color);
  border-color: #BDBDBD;
}
.upload-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.profile-details h2 { margin: 0 0 16px; font-size: 2rem; font-weight: 700; }
.detail-item { display: flex; gap: 8px; margin-bottom: 12px; font-size: 1.1rem; }
.detail-item .label { font-weight: 600; color: var(--text-secondary); }
.role-badge { background: var(--primary-extralight); color: var(--primary-dark); padding: 4px 8px; border-radius: var(--radius); font-weight: 600; text-transform: capitalize; }

.loading-state { padding: 40px; text-align: center; color: var(--text-secondary); }
.upload-error { color: var(--danger-color); margin-top: 12px; font-size: 14px; }
</style>
