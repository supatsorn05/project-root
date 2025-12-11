<template>
  <div class="modal-backdrop" @click="$emit('close')"></div>
  <div class="modal-container">
    <div class="modal-header">
      <h3>แก้ไขผู้ใช้งาน</h3>
      <button class="close-button" @click="$emit('close')">&times;</button>
    </div>
    <div class="modal-body">
      <form @submit.prevent="updateUser">
        <div class="form-group">
          <label for="edit_full_name">ชื่อ-นามสกุลภาษาไทย:<span class="required">*</span></label>
          <input type="text" id="edit_full_name" v-model="user.full_name" required />
        </div>
        <div class="form-group">
          <label for="edit_email">อีเมล:<span class="required">*</span></label>
          <input type="email" id="edit_email" v-model="user.email" required />
        </div>
        <div class="form-group">
          <label for="edit_username">ชื่อผู้ใช้งานภาษาอังกฤษ:<span class="required">*</span></label>
          <input type="text" id="edit_username" v-model="username" required pattern="[a-zA-Z0-9]+" title="กรุณากรอกชื่อผู้ใช้งานเป็นภาษาอังกฤษและตัวเลขเท่านั้น" />
        </div>
        <div class="form-group">
          <label for="edit_password">รหัสผ่าน (เว้นว่างหากไม่ต้องการเปลี่ยน):</label>
          <input type="password" id="edit_password" v-model="password" />
        </div>
        <div class="form-group">
          <label for="edit_confirm_password">ยืนยันรหัสผ่าน:</label>
          <input type="password" id="edit_confirm_password" v-model="confirmPassword" />
        </div>
        <div class="form-group">
          <label for="edit_role">บทบาท:<span class="required">*</span></label>
          <select id="edit_role" v-model="user.role" required>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
          </select>
        </div>
        <div v-if="user.role === 'student'" class="form-group">
          <label for="edit_student_id">รหัสนิสิต:</label>
          <input type="text" id="edit_student_id" v-model="student_id" :required="user.role === 'student'" pattern="[0-9]{10}" maxlength="10" title="รหัสนิสิตต้องเป็นตัวเลข 10 หลักเท่านั้น" />
        </div>
        <div v-if="user.role === 'student'" class="form-group">
  <label for="group_name">ชื่อกลุ่ม:</label>
  <input
    type="text"
    id="group_name"
    v-model="group_name"
    pattern="[A-Za-z0-9\u0E00-\u0E7F\s\-\_\.\(\)&]+"
    title="ชื่อกลุ่มสามารถใช้ภาษาไทย อังกฤษ ตัวเลข และอักขระ - _ . ( ) & ได้"
  />
</div>
        <button type="submit" class="btn btn-primary" :disabled="loading">{{ loading ? 'กำลังบันทึก...' : 'บันทึก' }}</button>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import http from '../../../services/http'

const props = defineProps({
  user: Object
})

const emit = defineEmits(['close', 'userUpdated'])

const user = ref({ ...props.user }) // Create a local copy of the user prop
const username = ref(props.user.username || '') // Initialize username
const password = ref('') // New ref for password
const confirmPassword = ref('') // New ref for confirm password
const student_id = ref('') // New ref for student_id
const group_name = ref('') // New ref for group_name
const loading = ref(false)
const errorMessage = ref('')

// Watch for changes in the prop and update the local copy
watch(() => props.user, (newUser) => {
  user.value = { ...newUser }
  username.value = newUser.username || '' // Update username on prop change
  password.value = '' // Clear password fields on prop change
  confirmPassword.value = ''
  student_id.value = newUser.student_id || '' // Initialize student_id
  group_name.value = newUser.group_name || '' // Initialize group_name
}, { deep: true, immediate: true })

async function updateUser() {
  errorMessage.value = '' // Clear previous errors

  if (password.value) { // Only validate length if password is provided
    if (password.value.length < 6 || password.value.length > 10) {
      errorMessage.value = 'รหัสผ่านต้องมีความยาว 6-10 ตัวอักษร';
      return;
    }
    if (password.value !== confirmPassword.value) {
      errorMessage.value = 'รหัสผ่านไม่ตรงกัน';
      return;
    }
  }

  if (user.value.role === 'student') {
    if (!student_id.value) {
      errorMessage.value = 'กรุณากรอกรหัสนิสิต';
      return;
    }
    if (!/^[0-9]{10}$/.test(student_id.value)) {
      errorMessage.value = 'รหัสนิสิตต้องเป็นตัวเลข 10 หลักเท่านั้น';
      return;
    }
  }

  loading.value = true
  errorMessage.value = ''
  try {
    const payload = {
      id: user.value.id,
      full_name: user.value.full_name,
      email: user.value.email,
      username: username.value, // Include username
      role: user.value.role
    };

    if (password.value) {
      payload.password = password.value; // Only include password if it's set
    }
    if (user.value.role === 'student') {
      payload.student_id = student_id.value;
      payload.group_name = group_name.value;
    }

    const res = await http.put(`/edit-user.php?id=${user.value.id}`, payload, {
      headers: {
        'Content-Type': 'application/json',
      },
    })
    if (res.data?.status === 'success') {
      emit('userUpdated', user.value)
      emit('close')
    } else {
      errorMessage.value = res.data?.message || 'แก้ไขผู้ใช้งานล้มเหลว'
    }
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
}

.modal-container {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  z-index: 1001;
  width: 90%;
  max-width: 500px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
  margin-bottom: 20px;
}

.modal-header h3 {
  margin: 0;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group input, .form-group select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-primary:disabled {
  background-color: #a0c9ff;
  cursor: not-allowed;
}

.error-message {
  color: red;
  margin-top: 10px;
}
.required {
  color: red;
  margin-left: 4px;
}
</style>