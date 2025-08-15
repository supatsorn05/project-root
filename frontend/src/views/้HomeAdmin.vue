<template>
  <div class="layout"
       style="background:radial-gradient(1200px 500px at 20% -10%, #CFFFE233 0%, transparent 60%), #000000; color:#F6F6F6; min-height:100vh">

    <!-- Top Navbar -->
    <nav class="navbar"
         style="position:sticky; top:0; z-index:40; display:flex; align-items:center; justify-content:space-between; padding:16px 24px; background:rgba(0,0,0,.92); border-bottom:1px solid #1f1f1f; backdrop-filter:blur(12px);">
      <div class="brand" @click="goHome" style="display:flex; align-items:center; gap:12px; cursor:pointer;">
        <div class="logo"
             style="width:40px; height:40px; border-radius:12px; display:grid; place-items:center; font-weight:800; color:#000000; background:linear-gradient(90deg,#CFFFE2,#A2D5C6); box-shadow:0 10px 30px rgba(0,0,0,.25);">UP</div>
        <div class="brand-text" style="line-height:1.2;">
          <strong style="color:#F6F6F6; font-size:1.1rem;">University Portal</strong>
          <span style="color:#A2D5C6; font-size:.85rem;">Admin</span>
        </div>
      </div>

      <div class="nav-actions" style="display:flex; align-items:center; gap:16px;">
        <input class="search" v-model="q" placeholder="ค้นหา..."
               style="width:240px; padding:10px 14px; border-radius:12px; border:1px solid #2a2a2a; background:#111111; color:#F6F6F6; outline:none;">

        <button class="icon-btn" title="แจ้งเตือน"
                style="position:relative; padding:10px 12px; border-radius:12px; border:1px solid #2a2a2a; background:#111111; color:#F6F6F6; cursor:pointer;">
          <span class="notification-icon" style="font-size:1.1rem;">🔔</span>
          <span class="notification-badge"
                style="position:absolute; top:-4px; right:-4px; background:#CFFFE2; color:#000000; font-size:.7rem; padding:2px 6px; border-radius:10px; font-weight:700;">3</span>
        </button>

        <div class="avatar-wrap" ref="menuRef" style="position:relative;">
          <button class="avatar" @click="toggleMenu" :aria-expanded="openMenu"
                  style="width:40px; height:40px; border-radius:50%; border:2px solid #2a2a2a; background:linear-gradient(90deg,#CFFFE2,#A2D5C6); color:#000000; font-weight:800; cursor:pointer;">
            {{ initials }}
          </button>

          <transition name="fade">
            <div v-if="openMenu" class="dropdown"
                 style="position:absolute; right:0; top:50px; width:280px; background:#0f0f0f; color:#F6F6F6; border:1px solid #1f1f1f; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.35);">
              <div class="dropdown-head"
                   style="display:flex; gap:12px; padding:16px; align-items:center; border-bottom:1px solid #1f1f1f; background:linear-gradient(135deg,#CFFFE21A,#A2D5C61A);">
                <div class="avatar small"
                     style="width:36px; height:36px; border-radius:50%; display:grid; place-items:center; background:linear-gradient(90deg,#CFFFE2,#A2D5C6); color:#000000; font-weight:800;">{{ initials }}</div>
                <div>
                  <div class="name" style="font-weight:700; color:#F6F6F6;">{{ me.full_name }}</div>
                  <div class="muted" style="color:#A2D5C6;">@{{ me.username }}</div>
                </div>
              </div>
              <ul class="menu" style="list-style:none; margin:0; padding:8px;">
                <li><RouterLink to="/admin" class="menu-item"
                    style="display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:12px; color:#F6F6F6; text-decoration:none;">
                    <span class="menu-icon" style="width:20px; text-align:center;">🏠</span>แดชบอร์ด</RouterLink></li>
                <li><a @click.prevent="goUsers" href="#" class="menu-item"
                       style="display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:12px; color:#F6F6F6; text-decoration:none;">
                       <span class="menu-icon" style="width:20px; text-align:center;">👥</span>จัดการผู้ใช้</a></li>
                <li><a @click.prevent="goDocs" href="#" class="menu-item"
                       style="display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:12px; color:#F6F6F6; text-decoration:none;">
                       <span class="menu-icon" style="width:20px; text-align:center;">📄</span>จัดการเอกสาร</a></li>
                <li><a @click.prevent="goSettings" href="#" class="menu-item"
                       style="display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:12px; color:#F6F6F6; text-decoration:none;">
                       <span class="menu-icon" style="width:20px; text-align:center;">⚙️</span>ตั้งค่าระบบ</a></li>
                <li class="sep" style="height:1px; background:#1f1f1f; margin:8px 4px;"></li>
                <li><a @click.prevent="logout" class="danger menu-item"
                       style="display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:12px; color:#ffbcbc; text-decoration:none;">
                       <span class="menu-icon" style="width:20px; text-align:center;">🚪</span>ออกจากระบบ</a></li>
              </ul>
            </div>
          </transition>
        </div>
      </div>
    </nav>

    <!-- Page header -->
    <header class="page-head" style="padding:32px 24px; display:flex; justify-content:space-between; align-items:flex-end; gap:20px; flex-wrap:wrap;">
      <div class="header-content">
        <h1 style="color:#CFFFE2; font-size:2.2rem; margin:0 0 8px 0; font-weight:700;">หน้าหลัก</h1>
        <p class="muted" style="color:#A2D5C6;">สรุปย้อนหลัง • กดเลือก "เทอมต้น/เทอมปลาย/ภาคฤดูร้อน" ได้ทุกปี</p>
      </div>

      <div class="header-stats" style="display:flex; gap:16px;">
        <div class="stat-card"
             style="background:linear-gradient(135deg,#CFFFE21A,#A2D5C61A); border:1px solid #1f1f1f; border-radius:12px; padding:12px 16px; text-align:center;">
          <div class="stat-value" style="color:#CFFFE2; font-size:1.5rem; font-weight:800; line-height:1;">{{ years.length }}</div>
          <div class="stat-label" style="color:#A2D5C6; font-size:.85rem;">ปีการศึกษา</div>
        </div>
        <div class="stat-card"
             style="background:linear-gradient(135deg,#CFFFE21A,#A2D5C61A); border:1px solid #1f1f1f; border-radius:12px; padding:12px 16px; text-align:center;">
          <div class="stat-value" style="color:#CFFFE2; font-size:1.5rem; font-weight:800; line-height:1;">{{ years.length * 3 }}</div>
          <div class="stat-label" style="color:#A2D5C6; font-size:.85rem;">เทอมทั้งหมด</div>
        </div>
      </div>
    </header>

    <main class="container" style="padding:0 24px 80px;">
      <section class="card"
               style="background:#0f0f0f; border:1px solid #1f1f1f; border-radius:20px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,.25);">
        <div class="card-title" style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:24px;">
          <div class="title-section">
            <h2 style="margin:0 0 4px 0; color:#F6F6F6;">รายการย้อนหลัง</h2>
            <span class="subtitle" style="color:#A2D5C6;">เลือกปีและเทอมที่ต้องการดูข้อมูล</span>
          </div>
          <div class="right">
            <button class="btn ghost" @click="reload"
                    style="display:flex; align-items:center; gap:8px; padding:10px 16px; border-radius:12px; border:1px solid #2a2a2a; background:transparent; color:#F6F6F6; cursor:pointer;">
              <span class="btn-icon">🔄</span>
              รีเฟรช
            </button>
          </div>
        </div>

        <div class="table-container" style="border:1px solid #1f1f1f; border-radius:16px; background:#0f0f0f; overflow:hidden;">
          <div class="table-wrap" style="overflow:auto;">
            <table class="table" style="width:100%; border-collapse:collapse;">
              <thead>
                <tr>
                  <th class="year-header"
                      style="background:linear-gradient(90deg,#CFFFE2,#A2D5C6); color:#000000; padding:16px; text-align:left; font-weight:900;">ปีการศึกษา</th>
                  <th style="background:linear-gradient(90deg,#CFFFE2,#A2D5C6); color:#000000; padding:16px; text-align:center; font-weight:900;">เทอมต้น</th>
                  <th style="background:linear-gradient(90deg,#CFFFE2,#A2D5C6); color:#000000; padding:16px; text-align:center; font-weight:900;">เทอมปลาย</th>
                  <th style="background:linear-gradient(90deg,#CFFFE2,#A2D5C6); color:#000000; padding:16px; text-align:center; font-weight:900;">ภาคฤดูร้อน</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(y, index) in years" :key="y"
                    style="transition:all .2s; border-top:1px solid #1f1f1f; background:transparent;">
                  <td class="year" style="padding:18px 16px; text-align:left;">
                    <div class="year-badge" style="font-weight:800; color:#F6F6F6;">{{ y }}</div>
                  </td>
                  <td style="padding:18px 16px; text-align:center;">
                    <button class="pill" @click="goToTerm(y,'เทอมต้น')"
                            style="display:inline-flex; align-items:center; gap:8px; justify-content:center; min-width:120px; padding:10px 16px; border-radius:20px; border:1px solid #A2D5C6; background:#CFFFE2; color:#000000; font-weight:700; cursor:pointer;">
                      <span class="pill-icon">📚</span>เทอมต้น
                    </button>
                  </td>
                  <td style="padding:18px 16px; text-align:center;">
                    <button class="pill" @click="goToTerm(y,'เทอมปลาย')"
                            style="display:inline-flex; align-items:center; gap:8px; justify-content:center; min-width:120px; padding:10px 16px; border-radius:20px; border:1px solid #A2D5C6; background:#CFFFE2; color:#000000; font-weight:700; cursor:pointer;">
                      <span class="pill-icon">📖</span>เทอมปลาย
                    </button>
                  </td>
                  <td style="padding:18px 16px; text-align:center;">
                    <button class="pill" @click="goToTerm(y,'ภาคฤดูร้อน')"
                            style="display:inline-flex; align-items:center; gap:8px; justify-content:center; min-width:120px; padding:10px 16px; border-radius:20px; border:1px solid #A2D5C6; background:#CFFFE2; color:#000000; font-weight:700; cursor:pointer;">
                      <span class="pill-icon">☀️</span>ภาคฤดูร้อน
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state" style="display:flex; flex-direction:column; align-items:center; gap:16px; padding:40px; color:#A2D5C6;">
          <div class="spinner"
               style="width:32px; height:32px; border:3px solid #1f1f1f; border-top:3px solid #A2D5C6; border-radius:50%; animation:spin 1s linear infinite;"></div>
          <span>กำลังโหลดข้อมูล...</span>
        </div>
      </section>

      <!-- Quick Actions -->
      <section class="quick-actions"
               style="background:#0f0f0f; border:1px solid #1f1f1f; border-radius:20px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,.15);">
        <h3 style="margin:0 0 20px 0; font-size:1.4rem; color:#CFFFE2;">เมนูด่วน</h3>
        <div class="actions-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
          <button class="action-card" @click="goUsers"
                  style="display:flex; align-items:center; gap:16px; padding:20px; border:1px solid #1f1f1f; border-radius:16px; background:#111111; color:#F6F6F6; text-align:left; cursor:pointer;">
            <div class="action-icon"
                 style="font-size:2rem; width:60px; height:60px; display:grid; place-items:center; background:#A2D5C6; border:1px solid #1f1f1f; border-radius:16px; color:#000000;">👥</div>
            <div class="action-text">
              <strong style="color:#F6F6F6;">จัดการผู้ใช้</strong>
              <span style="color:#A2D5C6;">เพิ่ม แก้ไข ลบข้อมูลผู้ใช้</span>
            </div>
          </button>

          <button class="action-card" @click="goDocs"
                  style="display:flex; align-items:center; gap:16px; padding:20px; border:1px solid #1f1f1f; border-radius:16px; background:#111111; color:#F6F6F6; text-align:left; cursor:pointer;">
            <div class="action-icon"
                 style="font-size:2rem; width:60px; height:60px; display:grid; place-items:center; background:#A2D5C6; border:1px solid #1f1f1f; border-radius:16px; color:#000000;">📄</div>
            <div class="action-text">
              <strong style="color:#F6F6F6;">จัดการเอกสาร</strong>
              <span style="color:#A2D5C6;">อัปโหลด ดาวน์โหลด เอกสาร</span>
            </div>
          </button>

          <button class="action-card" @click="goSettings"
                  style="display:flex; align-items:center; gap:16px; padding:20px; border:1px solid #1f1f1f; border-radius:16px; background:#111111; color:#F6F6F6; text-align:left; cursor:pointer;">
            <div class="action-icon"
                 style="font-size:2rem; width:60px; height:60px; display:grid; place-items:center; background:#A2D5C6; border:1px solid #1f1f1f; border-radius:16px; color:#000000;">⚙️</div>
            <div class="action-text">
              <strong style="color:#F6F6F6;">ตั้งค่าระบบ</strong>
              <span style="color:#A2D5C6;">กำหนดค่าต่างๆ ของระบบ</span>
            </div>
          </button>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const q = ref('')
const openMenu = ref(false)
const menuRef = ref(null)
const loading = ref(false)

const me = (() => {
  try {
    return JSON.parse(localStorage.getItem('me')) || {
      id: 1, username: 'admin', full_name: 'Administrator', role: 'admin'
    }
  } catch { return { id: 1, username: 'admin', full_name: 'Administrator', role: 'admin' } }
})()

const initials = computed(() =>
  me.full_name?.split(' ').map(s => s[0]).join('').slice(0,2).toUpperCase() || 'AD'
)

// ปี พ.ศ. ย้อนหลัง 5 ปีอัตโนมัติ
const years = (() => {
  const buddhist = new Date().getFullYear() + 543
  return [0,1,2,3,4].map(i => buddhist - i)
})()

function toggleMenu(){ openMenu.value = !openMenu.value }
function goHome(){ router.push('/admin') }
function goUsers(){ alert('ไปหน้า: จัดการผู้ใช้') }
function goDocs(){ alert('ไปหน้า: จัดการเอกสาร') }
function goSettings(){ alert('ไปหน้า: ตั้งค่าระบบ') }
function logout(){ localStorage.removeItem('me'); router.push('/') }

function reload(){ loading.value = true; setTimeout(() => { loading.value = false }, 1000) }
function goToTerm(year, term){ alert(`ไปหน้า: ปี ${year} • ${term}`) }

function onDocClick(e){
  if (!menuRef.value) return
  if (!menuRef.value.contains(e.target)) openMenu.value = false
}
onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
</script>

<style scoped>
/* โครงสร้าง/รีสปอนส์ซีฟ (สีจัดใน inline แล้ว) */
.fade-enter-active,.fade-leave-active{ transition: all .2s ease; }
.fade-enter-from,.fade-leave-to{ opacity:0; transform: translateY(-8px) scale(.95); }

@keyframes spin{ 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }

@media (max-width:860px){
  .search{ display:none; }
  .container{ padding:0 16px 60px !important; }
}
@media (max-width:640px){
  .navbar{ padding:12px 16px !important; }
  .table th, .table td{ padding:12px 8px !important; font-size:.95rem !important; }
}
</style>
