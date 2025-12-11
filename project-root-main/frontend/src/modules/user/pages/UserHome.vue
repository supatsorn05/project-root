<template>
  <header class="page-head">
    <div class="header-content">
      <h1>หน้าหลัก</h1>
      <p class="muted">สรุปย้อนหลัง • กดเลือก "เทอมต้น/เทอมปลาย/ภาคฤดูร้อน" ได้ทุกปี</p>
    </div>

    <div class="header-stats">
      <div class="stat-card">
        <div class="stat-value">{{ years.length }}</div>
        <div class="stat-label">ปีการศึกษา</div>
      </div>
      <div class="stat-card stat-card--soft">
        <div class="stat-value">{{ years.length * 3 }}</div>
        <div class="stat-label">เทอมทั้งหมด</div>
      </div>
    </div>
  </header>

  <main class="container">
    <section class="card">
      <div class="card-title">
        <div class="title-section">
          <h2>รายการย้อนหลัง</h2>
          <span class="subtitle muted">เลือกปีและเทอมที่ต้องการดูข้อมูล</span>
        </div>
        
      </div>

      <div class="table-container">
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
              <tr v-for="(y, i) in years" :key="y">
                <td class="year"><span class="year-badge">{{ y }}</span></td>
                <td class="tcenter">
                  <button class="pill" @click="goToTerm(y,'เทอมต้น')">
                    <span class="pill-ico">📚</span>เทอมต้น
                  </button>
                </td>
                <td class="tcenter">
                  <button class="pill" @click="goToTerm(y,'เทอมปลาย')">
                    <span class="pill-ico">📖</span>เทอมปลาย
                  </button>
                </td>
                <td class="tcenter">
                  <button class="pill" @click="goToTerm(y,'ภาคฤดูร้อน')">
                    <span class="pill-ico">☀️</span>ภาคฤดูร้อน
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>กำลังโหลดข้อมูล...</span>
      </div>
    </section>

    
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ✅ ปี พ.ศ. 5 ปีล่าสุด (ถ้า template มี {{ years.length }})
const years = (() => {
  const buddhist = new Date().getFullYear() + 543
  return [0,1,2,3,4].map(i => buddhist - i)
})()

const users = ref([])
const loading = ref(false)
const error = ref('')

async function load(){
  loading.value = true; error.value = ''
  try{
    const res = await fetch('/api/users.php', { credentials: 'include' })
    if (res.status === 401) return router.replace({ name:'login', query:{ next:'/admin' } })
    if (res.status === 403) { error.value = 'ไม่มีสิทธิ์เข้าถึงหน้านี้'; return router.replace({ name:'user' }) }
    const data = await res.json()
    if (data?.status === 'success') users.value = data.users || []
    else error.value = data?.message || 'โหลดข้อมูลล้มเหลว'
  }catch(e){ error.value = e?.message || 'โหลดข้อมูลล้มเหลว' }
  finally{ loading.value = false }
}
onMounted(load)
</script>


<style scoped>
:root{
  --bg:#fff; --fg:#111; --muted:#666;
  --line:#e9e9e9; --line-soft:#f2f2f2;
  --card:#fff; --shadow:0 10px 30px rgba(0,0,0,.06);
  --radius:16px; --radius-lg:20px;
  --t:.2s cubic-bezier(.2,.7,.3,1);
}

/* Base */
.container{ padding:0 24px 80px; }
.muted{ color:var(--muted); }

/* Header */
.page-head{
  padding:32px 24px; display:flex; justify-content:space-between; align-items:flex-end; gap:20px; flex-wrap:wrap;
  background:var(--bg); color:var(--fg);
}
.page-head h1{ margin:0 0 6px 0; font-size:2.1rem; font-weight:800; letter-spacing:.2px; }
.header-stats{ display:flex; gap:16px; }
.stat-card{
  border:1px solid var(--line); border-radius:12px; padding:12px 16px; text-align:center; background:var(--card);
  box-shadow: var(--shadow);
}
.stat-card--soft{ background: #fafafa; }
.stat-value{ color:var(--fg); font-size:1.5rem; font-weight:800; line-height:1; }
.stat-label{ color:var(--muted); font-size:.85rem; }

/* Card */
.card{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius-lg); padding:24px; box-shadow:var(--shadow); }
.card-title{ display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:16px; }
.card-title h2{ margin:0 0 4px 0; color:var(--fg); }
.subtitle{ font-size:.95rem; }

/* Buttons */
.btn{ display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:12px; cursor:pointer; transition:transform var(--t), box-shadow var(--t), background var(--t), color var(--t); }
.btn-ico{ font-size:1rem; }
.btn-ghost{ background:#fff; color:var(--fg); border:1px solid var(--line); }
.btn-ghost:hover{ transform:translateY(-1px); box-shadow:0 8px 18px rgba(0,0,0,.08); }

.pill{
  display:inline-flex; align-items:center; justify-content:center; gap:8px; min-width:120px;
  padding:10px 16px; border-radius:999px; border:1px solid #111; background:#fff; color:#111; font-weight:700; cursor:pointer;
  transition: background var(--t), color var(--t), transform var(--t), box-shadow var(--t);
}
.pill:hover{ background:#111; color:#fff; transform:translateY(-1px); box-shadow:0 10px 20px rgba(0,0,0,.12); }
.pill-ico{ font-size:1rem; }

/* Table */
.table-container{ border:1px solid var(--line); border-radius:var(--radius); background:#fff; overflow:hidden; }
.table-wrap{ overflow:auto; }
.table{ width:100%; border-collapse:collapse; }
.table thead th{
  background:#f7f7f7; color:#111; padding:14px 16px; text-align:center; font-weight:900; border-bottom:1px solid var(--line);
}
.table thead .th-year{ text-align:left; }
.table tbody tr{ transition: background var(--t); border-top:1px solid var(--line-soft); }
.table tbody tr:hover{ background:#fcfcfc; }
.table td{ padding:16px; }
.table .year{ text-align:left; }
.table .tcenter{ text-align:center; }
.year-badge{ font-weight:800; color:#111; }

/* Loading */
.loading-state{ display:flex; flex-direction:column; align-items:center; gap:12px; padding:28px; color:var(--muted); }
.spinner{ width:28px; height:28px; border:3px solid var(--line); border-top:3px solid #111; border-radius:50%; animation:spin 1s linear infinite; }
@keyframes spin{ to{ transform:rotate(360deg) } }

/* Quick actions */
.quick-actions{ background:#fff; border:1px solid var(--line); border-radius:20px; padding:24px; box-shadow:var(--shadow); margin-top:24px; }
.quick-actions h3{ margin:0 0 16px 0; font-size:1.25rem; color:#111; }
.actions-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px; }
.action-card{
  display:flex; align-items:center; gap:16px; padding:18px; border:1px solid var(--line); border-radius:16px; background:#fff; color:#111; text-align:left; cursor:pointer;
  transition: transform var(--t), box-shadow var(--t), border-color var(--t), background var(--t);
}
.action-card:hover{ transform:translateY(-2px); box-shadow:0 14px 28px rgba(0,0,0,.08); border-color:#111; }
.action-ico{
  font-size:1.6rem; width:56px; height:56px; display:grid; place-items:center; background:#111; color:#fff; border-radius:14px;
}
.action-text strong{ display:block; margin-bottom:4px; }

/* Utils */
.tcenter{text-align:center}

/* Responsive */
@media (max-width:860px){
  .container{ padding:0 16px 60px; }
}
@media (max-width:640px){
  .table th, .table td{ padding:12px 8px; font-size:.95rem; }
}
</style>
