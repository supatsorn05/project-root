<template>
  <main class="container">
    <header class="page-head">
      <h1>โครงการ (ปีการศึกษา: {{ termInfo.academic_year }} เทอม: {{ termInfo.term_name }})</h1>
      <p class="muted">รายชื่อโครงการทั้งหมดสำหรับภาคเรียนนี้</p>
      <button @click="router.back()" class="btn btn-secondary">กลับ</button>
    </header>

    <section class="card">
      <div class="card-title">
        <input type="text" v-model="searchTerm" placeholder="ค้นหา..." class="search-input">
      </div>
      <div v-if="loading" class="loading-state">กำลังโหลด...</div>
      <div v-if="error" class="error-state">เกิดข้อผิดพลาด: {{ error }}</div>
      <div v-if="!loading && !error">
        <div v-if="filteredAndSortedProjects.length === 0" class="no-projects">
          ไม่พบโครงการสำหรับภาคเรียนนี้
        </div>
        <div v-else class="table-container">
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th @click="sortBy('project_name')" class="sortable">
                    ชื่อโครงการ
                    <span v-if="sortKey === 'project_name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                  </th>
                  <th @click="sortBy('project_name_en')" class="sortable">
                    ชื่อโครงงาน ( ภาษาอังกฤษ )
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
                <tr v-for="(project, index) in filteredAndSortedProjects" :key="index">
                  <td>{{ project.project_name }}</td>
                  <td>{{ project.project_name_en }}</td>
                  <td>
                    <div>{{ project.main_advisor_name || '-' }}</div>
                    <div v-if="project.secondary_advisor_name" class="text-muted text-sm">
                      (รอง: {{ project.secondary_advisor_name }})
                    </div>
                  </td>
                  <td>
                    <div v-if="project.document_path">
                      <a :href="project.document_path" target="_blank" class="btn btn-primary">ดาวน์โหลด</a>
                    </div>
                    <div v-else>
                      -
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const projects = ref([])
const termInfo = ref({ academic_year: '', term_name: '' })
const loading = ref(true)
const error = ref(null)
const searchTerm = ref('')
const sortKey = ref('project_name')
const sortOrder = ref('asc')

const term_id = route.params.term_id

const filteredAndSortedProjects = computed(() => {
  let filtered = projects.value.filter(p =>
    p.project_name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
    p.project_name_en.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
    (p.main_advisor_name && p.main_advisor_name.toLowerCase().includes(searchTerm.value.toLowerCase()))
  )

  filtered.sort((a, b) => {
    const keyA = a[sortKey.value] || ''
    const keyB = b[sortKey.value] || ''
    
    let result = 0
    if (keyA < keyB) result = -1;
    if (keyA > keyB) result = 1;

    return sortOrder.value === 'asc' ? result : -result
  })

  return filtered
})

function sortBy(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

onMounted(async () => {
  try {
    const response = await axios.get(`/api/projects-by-term.php?term_id=${term_id}`)
    if (response.data.status === 'success') {
      projects.value = response.data.data
      termInfo.value = response.data.term_info
    } else {
      throw new Error(response.data.message || 'Failed to fetch projects.')
    }
  } catch (e) {
    error.value = e?.response?.data?.message || e.message || 'Could not connect to the server.'
  } finally {
    loading.value = false
  }
})
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
.page-head .btn-secondary {
  position: absolute;
  top: 50%;
  right: 0;
  transform: translateY(-50%);
}
.page-head h1 {
  margin: 0 0 6px 0;
  font-size: 2.1rem;
  font-weight: 700;
  letter-spacing: .2px;
  color: var(--text-primary);
}

.card-title {
  padding: 16px;
  border-bottom: 1px solid var(--border-color);
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  background-color: var(--surface-color);
  color: var(--text-primary);
}

.table-container {
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  background: var(--surface-color);
  overflow: hidden;
}

.table-wrap { overflow: auto; }

.table th.sortable {
  cursor: pointer;
  user-select: none;
}

.table th.sortable:hover {
  background-color: var(--surface-hover-color);
}

.loading-state, .error-state, .no-projects {
  padding: 40px;
  text-align: center;
  color: var(--text-secondary);
}

.error-state { color: var(--danger-color); }

/* Making the download button smaller */
.btn-primary {
  padding: 6px 12px;
  font-size: 14px;
}
</style>
