<template>
    <div class="admin-academic-terms">
        <h1>จัดการภาคการศึกษา</h1>

        <div class="form-section">
            <h2 v-if="!editingTerm">เพิ่มภาคการศึกษาใหม่</h2>
            <h2 v-else>แก้ไขภาคการศึกษา (ID: {{ editingTerm.term_id }})</h2>
            <form @submit.prevent="editingTerm ? updateAcademicTerm() : addAcademicTerm()">
                <div class="form-group">
                    <label for="academic_year">ปีการศึกษา:<span class="required">*</span></label>
                    <input type="text" id="academic_year" v-model="newTerm.academic_year" maxlength="4" pattern="[0-9]{4}" required />
                </div>
                <div class="form-group">
                    <label for="term_name">ชื่อภาคการศึกษา:<span class="required">*</span></label>
                    <select id="term_name" v-model="newTerm.term_name" required>
                        <option value="">เลือกภาคการศึกษา</option>
                        <option v-for="term in termNames" :key="term" :value="term">{{ term }}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">{{ editingTerm ? 'อัปเดตภาคการศึกษา' : 'เพิ่มภาคการศึกษา' }}</button>
                <button type="button" v-if="editingTerm" @click="cancelEdit" class="btn btn-secondary">ยกเลิกการแก้ไข</button>
            </form>
            <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        </div>

        <div class="table-section">
            <h2>รายการภาคการศึกษา</h2>
            <table>
                <thead>
                    <tr>
                        <th>ปีการศึกษา</th>
                        <th>ชื่อภาคการศึกษา</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="term in academicTerms" :key="term.term_id">
                        <td>{{ term.academic_year }}</td>
                        <td>{{ term.term_name }}</td>
                        <td>
                            <button @click="editAcademicTerm(term)" class="btn btn-secondary btn-sm">แก้ไข</button>
                            <button @click="deleteAcademicTerm(term.term_id)" class="btn btn-danger btn-sm">ลบ</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import http from '../../../services/http';

interface AcademicTerm {
    term_id: number;
    academic_year: string;
    term_name: string;
}

const academicTerms = ref<AcademicTerm[]>([]);
const newTerm = ref<Omit<AcademicTerm, 'term_id'>>({
    academic_year: '',
    term_name: ''
});
const editingTerm = ref<AcademicTerm | null>(null);
const errorMessage = ref<string>('');
const termNames = ['เทอมต้น', 'เทอมปลาย', 'ภาคฤดูร้อน'];

// Fetch academic terms from API
const fetchAcademicTerms = async () => {
    try {
        const response = await http.get('/academic-terms.php');
        academicTerms.value = response.data;
    } catch (error) {
        console.error('Error fetching academic terms:', error);
        errorMessage.value = 'ไม่สามารถโหลดข้อมูลภาคการศึกษาได้';
    }
};

// Add new academic term
const addAcademicTerm = async () => {
    errorMessage.value = '';
    if (!newTerm.value.academic_year || !newTerm.value.term_name) {
        errorMessage.value = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        return;
    }
    if (!/^[0-9]{4}$/.test(newTerm.value.academic_year)) {
        errorMessage.value = 'ปีการศึกษาต้องเป็นตัวเลข 4 หลัก';
        return;
    }

    try {
        await http.post('/academic-terms.php', newTerm.value);
        newTerm.value = { academic_year: '', term_name: '' }; // Clear form
        fetchAcademicTerms(); // Re-fetch to ensure sorted order
    } catch (error: any) {
        console.error('Error adding academic term:', error);
        if (error.response && error.response.status === 409) {
            errorMessage.value = 'ภาคการศึกษานี้มีอยู่แล้ว';
        } else {
            errorMessage.value = error.response?.data?.message || 'ไม่สามารถเพิ่มภาคการศึกษาได้';
        }
    }
};

// Update academic term
const updateAcademicTerm = async () => {
    if (!editingTerm.value) return;
    errorMessage.value = '';

    if (!newTerm.value.academic_year || !newTerm.value.term_name) {
        errorMessage.value = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        return;
    }
    if (!/^[0-9]{4}$/.test(newTerm.value.academic_year)) {
        errorMessage.value = 'ปีการศึกษาต้องเป็นตัวเลข 4 หลัก';
        return;
    }

    try {
        await http.put(`/academic-terms.php?id=${editingTerm.value.term_id}`, newTerm.value);
        fetchAcademicTerms(); // Re-fetch to ensure sorted order and updated data
        cancelEdit();
    } catch (error: any) {
        console.error('Error updating academic term:', error);
        if (error.response && error.response.status === 409) {
            errorMessage.value = 'ภาคการศึกษาอื่นที่ใช้ปีและชื่อเทอมนี้มีอยู่แล้ว';
        } else {
            errorMessage.value = error.response?.data?.message || 'ไม่สามารถอัปเดตภาคการศึกษาได้';
        }
    }
};

// Edit academic term
const editAcademicTerm = (term: AcademicTerm) => {
    editingTerm.value = { ...term };
    newTerm.value = { academic_year: term.academic_year, term_name: term.term_name };
    errorMessage.value = ''; // Clear error message when starting to edit
};

// Cancel editing
const cancelEdit = () => {
    editingTerm.value = null;
    newTerm.value = { academic_year: '', term_name: '' };
    errorMessage.value = '';
};

// Delete academic term
const deleteAcademicTerm = async (termId: number) => {
    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบภาคการศึกษานี้?')) {
        return;
    }
    errorMessage.value = '';

    try {
        const usageResponse = await http.get(`/academic-terms.php?check_usage=true&term_id=${termId}`);
        if (usageResponse.data.inUse) {
            alert('ไม่สามารถลบภาคการศึกษานี้ได้ เนื่องจากมีการใช้งานอยู่ในโครงงาน');
            return;
        }

        await http.delete(`/academic-terms.php?id=${termId}`);
        academicTerms.value = academicTerms.value.filter(term => term.term_id !== termId);
        alert('ลบภาคการศึกษาสำเร็จ');
    } catch (error: any) {
        console.error('Error deleting academic term:', error);
        alert(error.response?.data?.message || 'ไม่สามารถลบภาคการศึกษาได้');
    }
};

onMounted(() => {
    fetchAcademicTerms();
});
</script>

<style scoped>
.admin-academic-terms {
    padding: 24px;
    max-width: 1000px;
    margin: 0 auto;
}

h1 {
    text-align: center;
    color: var(--text-primary);
    margin-bottom: 32px;
}

.form-section, .table-section {
    margin-bottom: 32px;
    padding: 24px;
    border-radius: var(--radius-lg);
    background-color: var(--surface-color);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
}

h2 {
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 24px 0;
    font-size: 1.5rem;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 1rem;
}

.form-group input[type="text"],
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-sizing: border-box;
    background-color: var(--surface-color);
    color: var(--text-primary);
    transition: border-color .2s, box-shadow .2s;
    font-size: 1rem;
}

.form-group input[type="text"]:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-extralight);
}

form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
}

.form-group {
    flex-grow: 1;
}

form .btn {
    flex-shrink: 0;
}

.table-section table {
    width: 100%;
    border-collapse: collapse;
}

.table-section th, .table-section td {
    padding: 12px 16px;
    text-align: left;
}

/* Global table styles from main.css will handle most of this */

td .btn {
    margin-right: 8px;
}

.required {
  color: red;
  margin-left: 4px;
}

.error-message {
    color: var(--danger-color);
    margin-top: 16px;
    font-weight: 600;
}
</style>