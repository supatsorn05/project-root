<template>
    <div class="admin-advisors-management">
        <header class="page-head">
            <h1>จัดการอาจารย์ที่ปรึกษา</h1>
        </header>

        <!-- Notification -->
        <div v-if="notification.show" :class="`notification is-${notification.type}`">
            <p>{{ notification.message }}</p>
            <button @click="notification.show = false" class="close-btn">&times;</button>
        </div>

        <!-- Modal for Add/Edit Advisor -->
        <Transition name="modal-fade">
            <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h2 v-if="!editingAdvisor">เพิ่มอาจารย์ที่ปรึกษาใหม่</h2>
                        <h2 v-else>แก้ไขข้อมูลอาจารย์ที่ปรึกษา</h2>
                        <button @click="closeModal" class="modal-close-btn">&times;</button>
                    </div>
                    <form @submit.prevent="editingAdvisor ? updateAdvisor() : addAdvisor()">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="user_id">อาจารย์ (ผู้ใช้งาน):<span class="required">*</span></label>
                                <select id="user_id" v-model="newAdvisor.user_id" required :disabled="!!editingAdvisor">
                                    <option value="">เลือกอาจารย์</option>
                                    <option v-if="editingAdvisor" :value="editingAdvisor.user_id">{{ editingAdvisor.full_name }}</option>
                                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.full_name }} ({{ teacher.email }})
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="department">สาขาวิชา:<span class="required">*</span></label>
                                <select id="department" v-model="newAdvisor.department" required>
                                    <option value="">เลือกสาขาวิชา</option>
                                    <option value="วิทยาการคอมพิวเตอร์">วิทยาการคอมพิวเตอร์</option>
                                    <option value="วิทยาการและเทคโนโลยีดิจิทัล">วิทยาการและเทคโนโลยีดิจิทัล</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" @click="closeModal" class="btn btn-secondary">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">{{ editingAdvisor ? (isSubmitting ? 'กำลังอัปเดต...' : 'อัปเดตข้อมูล') : (isSubmitting ? 'กำลังเพิ่ม...' : 'เพิ่มอาจารย์ที่ปรึกษา') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <div class="table-section">
            <div class="card-title">
                <h2>รายการอาจารย์ที่ปรึกษา</h2>
                <button @click="openAddModal" class="btn btn-primary">เพิ่มอาจารย์ที่ปรึกษาใหม่</button>
            </div>
            <div class="table-wrapper">
                <div v-if="isLoading" class="loading-overlay">
                    <div class="spinner"></div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ชื่ออาจารย์</th>
                            <th>ภาควิชา</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="advisor in advisors" :key="advisor.advisor_id">
                            <td>{{ advisor.full_name }}</td>
                            <td>{{ advisor.department }}</td>
                            <td>
                                <button @click="editAdvisor(advisor)" class="btn btn-secondary btn-sm">แก้ไข</button>
                                <button @click="deleteAdvisor(advisor.advisor_id)" class="btn btn-danger btn-sm">ลบ</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import http from '../../../services/http';

// ... Interfaces ...
interface Advisor { advisor_id: number; user_id: number; department: string; full_name: string; }
interface User { id: number; full_name: string; email: string; role: string; }

// Component State
const advisors = ref<Advisor[]>([]);
const teachers = ref<User[]>([]);
const newAdvisor = ref<Omit<Advisor, 'advisor_id' | 'full_name'>>({ user_id: 0, department: '' });
const editingAdvisor = ref<Advisor | null>(null);
const showModal = ref(false);
const isLoading = ref(true);
const isSubmitting = ref(false);
const notification = ref({ show: false, message: '', type: '' });

// Functions
const showNotification = (message: string, type: 'success' | 'error') => {
    notification.value = { show: true, message, type };
    setTimeout(() => { notification.value.show = false; }, 3000);
};

const openAddModal = () => {
    editingAdvisor.value = null;
    newAdvisor.value = { user_id: 0, department: '' };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const fetchData = async () => {
    isLoading.value = true;
    try {
        const [advisorsResponse, teachersResponse] = await Promise.all([
            http.get('/advisors.php'),
            http.get('/users.php?role=teacher'),
        ]);
        advisors.value = advisorsResponse.data;
        console.log('Advisors data from API:', advisorsResponse.data);
        if (teachersResponse.data?.status === 'success' && Array.isArray(teachersResponse.data.users)) {
            const existingAdvisorUserIds = new Set(advisors.value.map(a => a.user_id));
            teachers.value = teachersResponse.data.users.filter(t => !existingAdvisorUserIds.has(t.id));
        } else {
            teachers.value = [];
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        showNotification('ไม่สามารถดึงข้อมูลเริ่มต้นได้', 'error');
    } finally {
        isLoading.value = false;
    }
};

const addAdvisor = async () => {
    if (!newAdvisor.value.user_id || !newAdvisor.value.department) {
        showNotification('กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
        return;
    }
    isSubmitting.value = true;
    console.log('Submitting new advisor:', newAdvisor.value);
    try {
        const payload = { ...newAdvisor.value }; // Un-proxy the reactive object
        const response = await http.post('/advisors.php', payload);
        console.log('API Success Response:', response);
        
        // Optimistically update UI
        const teacher = teachers.value.find(t => t.id === newAdvisor.value.user_id);
        const newAdvisorData: Advisor = {
            advisor_id: response.data.advisor_id,
            user_id: newAdvisor.value.user_id,
            department: newAdvisor.value.department,
            full_name: teacher ? teacher.full_name : 'Unknown'
        };
        advisors.value.push(newAdvisorData);
        advisors.value.sort((a, b) => a.full_name.localeCompare(b.full_name)); // Keep list sorted
        
        // Remove from teachers list
        teachers.value = teachers.value.filter(t => t.id !== newAdvisor.value.user_id);

        showNotification('เพิ่มอาจารย์ที่ปรึกษาสำเร็จ', 'success');
        closeModal();
    } catch (error: any) {
        console.error('API Error:', error);
        showNotification(error.response?.data?.message || 'ไม่สามารถเพิ่มอาจารย์ที่ปรึกษาได้', 'error');
    } finally {
        isSubmitting.value = false;
    }
};

const editAdvisor = (advisor: Advisor) => {
    editingAdvisor.value = { ...advisor };
    newAdvisor.value = { user_id: advisor.user_id, department: advisor.department };
    showModal.value = true;
};

const updateAdvisor = async () => {
    if (!editingAdvisor.value) return;
    isSubmitting.value = true;
    try {
        await http.put(`/advisors.php?id=${editingAdvisor.value.advisor_id}`, { department: newAdvisor.value.department });
        const index = advisors.value.findIndex(a => a.advisor_id === editingAdvisor.value?.advisor_id);
        if (index !== -1) {
            advisors.value[index].department = newAdvisor.value.department;
        }
        showNotification('อัปเดตข้อมูลสำเร็จ', 'success');
        closeModal();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'ไม่สามารถอัปเดตข้อมูลได้', 'error');
    } finally {
        isSubmitting.value = false;
    }
};

const deleteAdvisor = async (advisorId: number) => {
    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบอาจารย์ที่ปรึกษาท่านนี้?')) return;
    // Optimistic UI update is complex here because we need the full user object to add back to teachers.
    // A full refetch is safer and more reliable for delete.
    isLoading.value = true;
    try {
        await http.delete(`/advisors.php?id=${advisorId}`);
        showNotification('ลบอาจารย์ที่ปรึกษาสำเร็จ', 'success');
        await fetchData(); // Refetch all data to ensure consistency
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'ไม่สามารถลบอาจารย์ที่ปรึกษาได้', 'error');
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>

<style scoped>
/* ... existing themed styles ... */
.admin-advisors-management { padding: 24px; max-width: 1000px; margin: 0 auto; }
.page-head { text-align: center; margin-bottom: 32px; }
.page-head h1 { font-weight: 700; color: var(--text-primary); }
.card-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.table-section { padding: 24px; border-radius: var(--radius-lg); background-color: var(--surface-color); box-shadow: var(--shadow); border: 1px solid var(--border-color); }
.table-section h2 { font-weight: 700; color: var(--text-primary); margin: 0 0 24px 0; }
.table-wrapper { position: relative; }

/* Notification Styles */
.notification { padding: 1rem 1.5rem; margin-bottom: 1rem; border-radius: var(--radius); color: #fff; display: flex; justify-content: space-between; align-items: center; }
.notification.is-success { background-color: var(--primary-color); }
.notification.is-error { background-color: var(--danger-color); }
.notification .close-btn { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }

/* Loading Overlay */
.loading-overlay { position: absolute; inset: 0; background-color: rgba(255,255,255,0.7); display: grid; place-items: center; z-index: 10; }
.spinner { width: 40px; height: 40px; border: 4px solid var(--primary-light); border-top-color: var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* New Modal Styles */
.modal-overlay {
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

.modal-dialog {
    background-color: var(--surface-color);
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}

.modal-close-btn {
    background: none;
    border: none;
    font-size: 1.75rem;
    line-height: 1;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0;
}
.modal-close-btn:hover {
    color: var(--text-primary);
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background-color: var(--background-color);
    border-top: 1px solid var(--border-color);
}

.form-group { margin-bottom: 20px; }
.form-group:last-child { margin-bottom: 0; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-secondary); }
.form-group input[type="text"], .form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-sizing: border-box;
    background-color: var(--surface-color);
    color: var(--text-primary);
    transition: border-color .2s, box-shadow .2s;
}
.form-group input[type="text"]:focus, .form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-extralight);
}

/* Modal Animation */
.modal-fade-enter-active, .modal-fade-leave-active {
    transition: opacity 0.3s ease;
}
.modal-fade-enter-active .modal-dialog, .modal-fade-leave-active .modal-dialog {
    transition: transform 0.3s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
    opacity: 0;
}
.modal-fade-enter-from .modal-dialog, .modal-fade-leave-to .modal-dialog {
    transform: translateY(-20px) scale(0.95);
}

.table td:last-child { white-space: nowrap; }

.required {
  color: red;
  margin-left: 4px;
}
</style>