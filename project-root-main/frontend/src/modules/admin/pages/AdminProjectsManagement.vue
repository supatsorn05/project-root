<template>
    <div class="admin-projects-management">
        <header class="page-head">
            <h1>จัดการโครงงาน</h1>
        </header>

        <!-- Modal for Add/Edit Project -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal-content">
                <h2 v-if="!editingProject">เพิ่มโครงงานใหม่</h2>
                <h2 v-else>แก้ไขโครงงาน (ID: {{ editingProject.project_id }})</h2>
                <form @submit.prevent="editingProject ? updateProject() : addProject()">
                    <div class="form-group">
                        <label for="branch">สาขาวิชา:<span class="required">*</span></label>
                        <select id="branch" v-model="selectedBranch" required>
                            <option value="">เลือกสาขาวิชา</option>
                            <option value="วิทยาการคอมพิวเตอร์">วิทยาการคอมพิวเตอร์</option>
                            <option value="วิทยาการและเทคโนโลยีดิจิทัล">วิทยาการและเทคโนโลยีดิจิทัล</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="project_name">ชื่อโครงงาน (ภาษาไทย):<span class="required">*</span></label>
                        <input type="text" id="project_name" v-model="newProject.project_name" required />
                    </div>
                    <div class="form-group">
                        <label for="project_name_en">ชื่อโครงงาน (ภาษาอังกฤษ):<span class="required">*</span></label>
                        <input type="text" id="project_name_en" v-model="newProject.project_name_en" required />
                    </div>
                    <div class="form-group">
                        <label for="main_advisor_id">อาจารย์ที่ปรึกษาหลัก:<span class="required">*</span></label>
                        <select id="main_advisor_id" v-model="newProject.main_advisor_id" required :disabled="!selectedBranch">
                            <option value="0">เลือกอาจารย์ที่ปรึกษาหลัก</option>
                            <option v-for="advisor in filteredAdvisors" :key="advisor.advisor_id" :value="advisor.advisor_id">
                                {{ advisor.full_name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="secondary_advisor_id">อาจารย์ที่ปรึกษารอง (ถ้ามี):</label>
                        <select id="secondary_advisor_id" v-model="newProject.secondary_advisor_id">
                            <option :value="null">ไม่มี</option>
                            <option v-for="advisor in advisors" :key="advisor.advisor_id" :value="advisor.advisor_id">
                                {{ advisor.full_name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="term_id">ภาคการศึกษา:<span class="required">*</span></label>
                        <select id="term_id" v-model="newProject.term_id" required>
                            <option value="0">เลือกภาคการศึกษา</option>
                            <option v-for="term in academicTerms" :key="term.term_id" :value="term.term_id">
                                {{ term.academic_year }} - {{ term.term_name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group" style="display: none;">
                        <label for="abstract">บทคัดย่อ:</label>
                        <textarea id="abstract" v-model="newProject.abstract" rows="5"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ editingProject ? 'อัปเดตโครงงาน' : 'เพิ่มโครงงาน' }}</button>
                    <button type="button" @click="closeModal" class="btn btn-secondary">ยกเลิก</button>
                </form>
            </div>
        </div>

        <div class="table-section">
            <div class="card-title">
                <h2>รายการโครงงาน</h2>
                <button @click="openAddModal" class="btn btn-primary">เพิ่มโครงงานใหม่</button>
            </div>
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
                        <th @click="sortBy('term_id')" class="sortable">
                            ภาคการศึกษา
                            <span v-if="sortKey === 'term_id'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="project in sortedProjects" :key="project.project_id">
                        <td>{{ project.project_name }}</td>
                        <td>{{ project.project_name_en }}</td>
                        <td>
                            <div><strong>หลัก:</strong> {{ project.main_advisor_name }}</div>
                            <div v-if="project.secondary_advisor_id"><strong>รอง:</strong> {{ project.secondary_advisor_name }}</div>
                        </td>
                        <td>{{ getTermName(project.term_id) }}</td>
                        <td>
                            <button @click="editProject(project)" class="btn btn-secondary btn-sm">แก้ไข</button>
                            <button @click="deleteProject(project.project_id)" class="btn btn-danger btn-sm">ลบ</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import http from '../../../services/http';

interface Project {
    project_id: number;
    project_name: string;
    project_name_en: string;
    main_advisor_id: number;
    secondary_advisor_id: number | null;
    term_id: number;
    abstract: string;
    // Properties from JOINs
    main_advisor_name?: string;
    secondary_advisor_name?: string;
}

interface Advisor {
    advisor_id: number;
    full_name: string;
    department: string;
}

interface AcademicTerm {
    term_id: number;
    academic_year: string;
    term_name: string;
}

const projects = ref<Project[]>([]);
const advisors = ref<Advisor[]>([]);
const academicTerms = ref<AcademicTerm[]>([]);
const newProject = ref<Omit<Project, 'project_id' | 'main_advisor_name' | 'secondary_advisor_name'>>({
    project_name: '',
    project_name_en: '',
    main_advisor_id: 0,
    secondary_advisor_id: null,
    term_id: 0,
    abstract: '',
});
const editingProject = ref<Project | null>(null);
const showModal = ref(false);
const selectedBranch = ref('');
const sortKey = ref('');
const sortOrder = ref('asc');

const filteredAdvisors = computed(() => {
    if (!selectedBranch.value) {
        return [];
    }
    return advisors.value.filter(advisor => advisor.department === selectedBranch.value);
});

const sortedProjects = computed(() => {
    if (!sortKey.value) {
        return projects.value;
    }
    return [...projects.value].sort((a, b) => {
        let aValue = a[sortKey.value] || '';
        let bValue = b[sortKey.value] || '';

        if (sortKey.value === 'term_id') {
            aValue = getTermName.value(a.term_id);
            bValue = getTermName.value(b.term_id);
        }

        return aValue.localeCompare(bValue, ['th', 'en'], { numeric: true }) * (sortOrder.value === 'asc' ? 1 : -1);
    });
});

function sortBy(key: string) {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
}

// Functions to control modal visibility
const openAddModal = () => {
    editingProject.value = null;
    newProject.value = { project_name: '', project_name_en: '', main_advisor_id: 0, secondary_advisor_id: null, term_id: 0, abstract: '' };
    selectedBranch.value = '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingProject.value = null;
    newProject.value = { project_name: '', project_name_en: '', main_advisor_id: 0, secondary_advisor_id: null, term_id: 0, abstract: '' };
    selectedBranch.value = '';
};

// Fetch data for dropdowns and projects
const fetchData = async () => {
    try {
        const [projectsResponse, advisorsResponse, academicTermsResponse] = await Promise.all([
            http.get('/projects.php'),
            http.get('/list-advisors.php'),
            http.get('/academic-terms.php'),
        ]);
        projects.value = projectsResponse.data;
        advisors.value = advisorsResponse.data;
        academicTerms.value = academicTermsResponse.data;
    } catch (error) {
        console.error('Error fetching data:', error);
        alert('ไม่สามารถดึงข้อมูลเริ่มต้นได้');
    }
};

// Helper to get advisor name by ID
const getAdvisorName = (id: number | null): string => {
    if (!id) return '-';
    const advisor = advisors.value.find(a => a.advisor_id === id);
    return advisor ? advisor.full_name : 'Unknown';
};

// Helper to get academic term name by ID
const getTermName = computed(() => (id: number) => {
    const term = academicTerms.value.find(t => t.term_id === id);
    return term ? `${term.academic_year} - ${term.term_name}` : 'Unknown';
});

// Add new project
const addProject = async () => {
    if (!newProject.value.project_name || !newProject.value.project_name_en || !newProject.value.main_advisor_id || !newProject.value.term_id) {
        alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
        return;
    }
    if (newProject.value.secondary_advisor_id && newProject.value.main_advisor_id === newProject.value.secondary_advisor_id) {
        alert('อาจารย์ที่ปรึกษาหลักและรองต้องไม่เป็นคนเดียวกัน');
        return;
    }
    try {
        const response = await http.post('/projects.php', newProject.value);
        const newProjectData: Project = {
            ...response.data,
            main_advisor_name: getAdvisorName(response.data.main_advisor_id),
            secondary_advisor_name: getAdvisorName(response.data.secondary_advisor_id),
        };
        projects.value.push(newProjectData);
        alert('เพิ่มโครงงานสำเร็จ');
        closeModal();
    } catch (error: any) { // Explicitly type error as 'any' for easier access to response
        console.error('Error adding project:', error);
        if (error.response && error.response.status === 409) {
            alert(error.response.data.message); // Display specific message for duplicate
        } else {
            alert('ไม่สามารถเพิ่มโครงงานได้');
        }
    }
};

// Edit project
const editProject = (project: Project) => {
    editingProject.value = { ...project };
    newProject.value = {
        project_name: project.project_name,
        project_name_en: project.project_name_en,
        main_advisor_id: project.main_advisor_id,
        secondary_advisor_id: project.secondary_advisor_id,
        term_id: project.term_id,
        abstract: project.abstract,
    };
    showModal.value = true;
};

// Update project
const updateProject = async () => {
    if (!editingProject.value) return;
    if (!newProject.value.project_name || !newProject.value.project_name_en || !newProject.value.main_advisor_id || !newProject.value.term_id) {
        alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
        return;
    }
    if (newProject.value.secondary_advisor_id && newProject.value.main_advisor_id === newProject.value.secondary_advisor_id) {
        alert('อาจารย์ที่ปรึกษาหลักและรองต้องไม่เป็นคนเดียวกัน');
        return;
    }
    try {
        await http.put(`/projects.php?id=${editingProject.value.project_id}`, newProject.value);
        const index = projects.value.findIndex(p => p.project_id === editingProject.value?.project_id);
        if (index !== -1) {
            projects.value[index] = {
                ...projects.value[index], 
                ...newProject.value,
                main_advisor_name: getAdvisorName(newProject.value.main_advisor_id),
                secondary_advisor_name: getAdvisorName(newProject.value.secondary_advisor_id),
            };
        }
        alert('อัปเดตโครงงานสำเร็จ');
        closeModal();
    } catch (error) {
        console.error('Error updating project:', error);
        alert('ไม่สามารถอัปเดตโครงงานได้');
    }
};

// Delete project
const deleteProject = async (projectId: number) => {
    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบโครงงานนี้?')) {
        return;
    }
    try {
        await http.delete(`/projects.php?id=${projectId}`);
        projects.value = projects.value.filter(p => p.project_id !== projectId);
        alert('ลบโครงงานสำเร็จ');
    } catch (error) {
        console.error('Error deleting project:', error);
        alert('ไม่สามารถลบโครงงานได้');
    }
};

onMounted(() => {
    fetchData();
});
</script>

<style scoped>
.admin-projects-management {
    padding: 24px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-head {
    text-align: center;
    margin-bottom: 32px;
}

.page-head h1 {
    font-weight: 700;
    color: var(--text-primary);
}

.card-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.table-section {
    padding: 24px;
    border-radius: var(--radius-lg);
    background-color: var(--surface-color);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
}

.table-section h2 {
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 24px 0;
}

/* Modal Styles */
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

.modal-content {
    background-color: var(--surface-color);
    padding: 30px;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.modal-content h2 {
    margin-top: 0;
    margin-bottom: 24px;
    color: var(--text-primary);
    font-weight: 700;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-secondary);
}

.form-group input[type="text"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 12px; /* Slightly reduced vertical padding */
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-sizing: border-box;
    background-color: var(--surface-color);
    color: var(--text-primary);
    font-size: 0.9rem; /* Reduced font size */
    transition: border-color .2s, box-shadow .2s;
}

.form-group input[type="text"]:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-extralight);
}

.modal-content form .btn {
    margin-right: 8px;
}

.table th.sortable {
    cursor: pointer;
}

.table th:first-child, .table td:first-child {
    text-align: left !important; /* Align project name to left */
    width: 30%; /* Set width for Thai project name */
}

.table th:nth-child(2), .table td:nth-child(2) {
    text-align: left !important; /* Align project name to left */
    width: 30%; /* Set width for English project name */
}

.table th:nth-child(3) { /* Advisor column */
    white-space: nowrap;
}

.table th:nth-child(4) { /* Academic Term column */
    white-space: nowrap;
}

.table th:last-child, .table td:last-child {
    text-align: center !important;
    width: 180px;
}

.table td:last-child {
    white-space: nowrap;
}

.required {
  color: red;
  margin-left: 4px;
}
</style>
