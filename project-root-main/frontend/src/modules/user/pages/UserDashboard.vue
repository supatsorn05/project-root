<template>
  <main class="container">
    <header class="page-head">
      <div>
        <h1>ตัวอย่างโครงงานย้อนหลัง</h1>
        <p class="muted">เลือกปีการศึกษาและภาคเรียนที่ต้องการ</p>
      </div>
    </header>

    <section class="card">
      <div v-if="loading" class="loading-state">กำลังโหลด...</div>
      <div v-if="error" class="error-state">เกิดข้อผิดพลาด: {{ error }}</div>
      <div v-if="!loading && !error" class="table-container">
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th class="th-year">ปีการศึกษา</th>
                <th class="tcenter">ภาคต้น</th>
                <th class="tcenter">ภาคปลาย</th>
                <th class="tcenter">ภาคฤดูร้อน</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in academicData" :key="item.year">
                <td class="year">
                  <span class="year-badge">{{ item.year }}</span>
                </td>
                <td v-for="semesterName in ['เทอมต้น', 'เทอมปลาย', 'ภาคฤดูร้อน']" :key="semesterName" class="tcenter">
                  <button 
                    class="pill"
                    @click="selectSemester(item.semesters[semesterName].term_id)"
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
    const response = await http.get('/academic-terms.php');
    const rawAcademicTerms = response.data;

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
        project_count: parseInt(term.project_count),
      };
    });

    academicData.value = Object.values(groupedByYear).sort((a, b) => b.year - a.year);

  } catch (e) {
    error.value = e?.response?.data?.message || e.message || 'Could not connect to the server.';
  } finally {
    loading.value = false;
  }
})

function selectSemester(termId) {
  if (!termId) return;
  router.push({ name: 'user.projects', params: { term_id: termId } })
}
</script>

<style scoped>
:root{
  --bg:#fff; --fg:#111; --muted:#666;
  --line:#e9e9e9; --line-soft:#f2f2f2;
  --card:#fff; --shadow:0 10px 30px rgba(0,0,0,.06);
  --radius:16px; --radius-lg:20px;
  --t:.2s cubic-bezier(.2,.7,.3,1);
}
.container{ padding:0 24px 80px; }
.muted{ color:var(--muted); }
.page-head{ padding:32px 24px; display:flex; justify-content:space-between; align-items:flex-end; gap:20px; flex-wrap:wrap; background:var(--bg); color:var(--fg); }
.page-head h1{ margin:0 0 6px 0; font-size:2.1rem; font-weight:800; letter-spacing:.2px; }
.card{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius-lg); padding:24px; box-shadow:var(--shadow); }
.table-container{ border:1px solid var(--line); border-radius:var(--radius); background:#fff; overflow:hidden; }
.table-wrap{ overflow:auto; }
.table{ width:100%; border-collapse:collapse; }
.table thead th{ background:#f7f7f7; color:#111; padding:14px 16px; text-align:center; font-weight:900; border-bottom:1px solid var(--line); }
.table thead .th-year{ text-align:left; }
.table tbody tr{ transition: background var(--t); border-top:1px solid var(--line-soft); }
.table tbody tr:hover{ background:#fcfcfc; }
.table td{ padding:16px; }
.table .year{ text-align:left; }
.table .tcenter{ text-align:center; }
.year-badge{ font-weight:800; color:#111; }
.pill{ display:inline-flex; align-items:center; justify-content:center; gap:8px; min-width:120px; padding:10px 16px; border-radius:999px; border:1px solid #ffc0cb; background:#fff; color:#d1456b; font-weight:700; cursor:pointer; transition: background var(--t), color var(--t), transform var(--t), box-shadow var(--t); }
.pill:hover{ background:#d1456b; color:#fff; transform:translateY(-1px); box-shadow:0 10px 20px rgba(209,69,107,.2); }
.pill-ico{ font-size:1rem; }
.pill:disabled {
  background: #f0f0f0;
  color: #aaa;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
  border-color: #e0e0e0;
}
.pill:disabled:hover {
    background: #f0f0f0;
    color: #aaa;
}
.loading-state, .error-state { padding: 40px; text-align: center; }
@media (max-width:860px){ .container{ padding:0 16px 60px; } }
@media (max-width:640px){ .table th, .table td{ padding:12px 8px; font-size:.95rem; } }
</style>