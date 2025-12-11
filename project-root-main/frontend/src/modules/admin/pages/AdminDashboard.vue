<template>
  <header class="page-head">
    <div class="header-content">
      <h1>จัดการเอกสารตัวอย่างโครงงานย้อนหลัง</h1>
      <p class="muted"> </p>
    </div>

    <div class="header-stats">
      <div class="stat-card">
        <div class="stat-value">{{ academicData.length }}</div>
        <div class="stat-label">ปีการศึกษา</div>
      </div>
      <div class="stat-card stat-card--soft">
        <div class="stat-value">{{ academicData.length * 3 }}</div>
        <div class="stat-label">เทอมทั้งหมด</div>
      </div>
    </div>
  </header>

  <main class="container">
    <section class="card">
      <div class="card-title">
        <div class="title-section">
          <h2>รายการโครงงาน</h2>
          <span class="subtitle muted">แสดงโครงงานทั้งหมด</span>
        </div>
        
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>กำลังโหลดข้อมูล...</span>
      </div>
      <div v-else-if="error" class="error-state">เกิดข้อผิดพลาด: {{ error }}</div>
      <div v-else class="table-container">
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th class="th-year">ปีการศึกษา</th>
                <th>เทอมต้น</th>
                <th>เทอมปลาย</th>
                <th>ภาคฤดูร้อน</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in academicData" :key="item.year">
                <td class="year"><span class="year-badge">{{ item.year }}</span></td>
                <td v-for="semesterName in ['เทอมต้น', 'เทอมปลาย', 'ภาคฤดูร้อน']" :key="semesterName" class="tcenter">
                  <button 
                    class="pill"
                    @click="goToTerm(item.semesters[semesterName].term_id)"
                    :disabled="!item.semesters[semesterName] || item.semesters[semesterName].project_count === 0"
                  >
                    <span v-if="item.semesters[semesterName] && item.semesters[semesterName].project_count > 0">
                      <span class="pill-ico">📄</span>
                      <span>{{ item.semesters[semesterName].project_count }} โครงการ</span>
                    </span>
                    <span v-else>-</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '../../../services/http'

const router = useRouter()
const academicData = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const response = await http.get('/academic-terms.php'); // Corrected endpoint
    const rawAcademicTerms = response.data; // Direct array of academic terms

    const groupedByYear = {};
    rawAcademicTerms.forEach(term => {
      if (!groupedByYear[term.academic_year]) {
        groupedByYear[term.academic_year] = {
          year: term.academic_year,
          semesters: {
            'เทอมต้น': null,
            'เทอมปลาย': null,
            'ภาคฤดูร้อน': null,
          },
        };
      }
      groupedByYear[term.academic_year].semesters[term.term_name] = {
        term_id: term.term_id,
        project_count: parseInt(term.project_count), // Use project_count from backend
      };
    });

    // Convert groupedByYear object to an array and sort by year (descending)
    academicData.value = Object.values(groupedByYear).sort((a, b) => b.year - a.year);

  } catch (e) {
    error.value = e?.response?.data?.message || e.message || 'Could not connect to the server.';
  } finally {
    loading.value = false;
  }
})

function goToTerm(termId) {
  if (!termId) return;
  router.push({ name: 'admin.projects', params: { term_id: termId } })
}


</script>


<style scoped>
.container {
  padding: 0 24px 80px;
  max-width: 1200px;
  margin: 0 auto;
}
.muted { color: var(--text-secondary); }

.page-head{
  padding:32px 24px;
  position: relative;
  text-align: center;
}
.page-head h1{ margin:0 0 6px 0; font-size:2.1rem; font-weight:700; letter-spacing:.2px; color: var(--text-primary); }

.header-stats{
  position: absolute;
  top: 50%;
  right: 24px;
  transform: translateY(-50%);
  display:flex;
  gap:16px;
}
.stat-card{
  border:1px solid var(--border-color); 
  border-radius:12px; 
  padding:12px 16px; 
  text-align:center; 
  background:var(--surface-color);
  box-shadow: var(--shadow);
}
.stat-card--soft{ background: var(--background-color); }
.stat-value{ color:var(--primary-dark); font-size:1.5rem; font-weight:800; line-height:1; }
.stat-label{ color:var(--text-secondary); font-size:.85rem; }

.card-title{ display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:16px; }
.card-title h2{ margin:0 0 4px 0; font-weight: 700; }
.subtitle{ font-size:.95rem; }

.pill{
  display:inline-flex; align-items:center; justify-content:center; gap:8px; min-width:120px;
  padding:10px 16px; border-radius:999px; border:1px solid var(--primary-light); background:var(--primary-extralight); color:var(--primary-dark); font-weight:700; cursor:pointer;
  transition: all .2s;
}
.pill:hover:not(:disabled){ background:var(--primary-light); color:var(--text-primary); transform:translateY(-1px); box-shadow:0 10px 20px rgba(0,0,0,.12); }
.pill:disabled {
  background: var(--background-color);
  color: var(--text-disabled);
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
  border-color: var(--border-color);
}
.pill-ico{ font-size:1rem; }

.table-container{ border:1px solid var(--border-color); border-radius:var(--radius); background:var(--surface-color); overflow:hidden; }
.table-wrap{ overflow:auto; }

.table .year{ text-align:left; }
.table .tcenter{ text-align:center; }
.year-badge{ font-weight:800; color:var(--text-primary); }

.loading-state{ display:flex; flex-direction:column; align-items:center; gap:12px; padding:28px; color:var(--text-secondary); }
.spinner{ width:28px; height:28px; border:3px solid var(--primary-light); border-top:3px solid var(--primary-color); border-radius:50%; animation:spin 1s linear infinite; }
@keyframes spin{ to{ transform:rotate(360deg) } }

@media (max-width:860px){
  .container{ padding:0 16px 60px; }
  .header-stats { display: none; } /* Hide stats on smaller screens */
}
@media (max-width:640px){
  .table th, .table td{ padding:12px 8px; font-size:.95rem; }
}
</style>
