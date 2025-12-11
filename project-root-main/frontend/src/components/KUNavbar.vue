<template>
  <header class="ku-nav" :class="{ 'is-open': open }">
    <div class="ku-nav__inner">
      <!-- Brand -->
      <div
        class="ku-brand"
        @click="router.push('/')"
        role="button"
        aria-label="กลับหน้าแรก"
      >
        <img class="ku-logo" :src="kuLogo" alt="KU" />
        <div class="ku-brand__text">
          <strong>มหาวิทยาลัยเกษตรศาสตร์</strong>
          <br style="margin-top: 10px;" />
          <small>วิทยาเขตศรีราชา</small>
        </div>
      </div>

      <!-- Links (desktop) -->
      <nav class="ku-links" role="navigation" aria-label="เมนูหลัก">
        <template v-for="link in visibleLinks" :key="link.to || link.label">
          <div v-if="link.children" class="ku-dropdown">
            <button @click="toggleDropdownBasicData" class="ku-link" :aria-expanded="dropdownBasicData">
              <span class="ku-link__icon" aria-hidden="true">
                <svg v-if="link.icon === 'folder'" width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M3 6.5A1.5 1.5 0 0 1 4.5 5h4l2 2H19.5A1.5 1.5 0 0 1 21 8.5V18A2 2 0 0 1 19 20H5a2 2 0 0 1-2-2V6.5Z" stroke="currentColor" stroke-width="1.5" />
                </svg>
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.5" />
                </svg>
              </span>
              <span class="ku-link__label">{{ link.label }}</span>
              <svg class="ku-caret" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            <div v-if="dropdownBasicData" class="ku-dropdown-menu">
              <RouterLink
                v-for="childLink in link.children"
                :key="childLink.to"
                :to="childLink.to"
                class="ku-dropdown-item"
                @click="dropdownBasicData = false"
              >
                {{ childLink.label }}
              </RouterLink>
            </div>
          </div>
          <RouterLink
            v-else
            :to="link.to"
            class="ku-link"
            :class="{ active: route.path === link.to }"
          >
            <span class="ku-link__icon" aria-hidden="true">
              <svg
                v-if="link.icon === 'home'"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
              >
                <path
                  d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linejoin="round"
                />
              </svg>
              <svg
                v-else-if="link.icon === 'folder'"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
              >
                <path
                  d="M3 6.5A1.5 1.5 0 0 1 4.5 5h4l2 2H19.5A1.5 1.5 0 0 1 21 8.5V18A2 2 0 0 1 19 20H5a2 2 0 0 1-2-2V6.5Z"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
              </svg>
              <svg
                v-else-if="link.icon === 'shield'"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
              >
                <path
                  d='M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3Z'
                  stroke='currentColor'
                  stroke-width='1.5'
                />
              </svg>
              <svg
                v-else-if="link.icon === 'users'"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
              >
                <path
                  d='M17 21v-2a4 4 0 0 0-4-4H11a4 4 0 0 0-4 4v2'
                  stroke='currentColor'
                  stroke-width='1.5'
                />
                <circle cx='12' cy='7' r='4' stroke='currentColor' stroke-width='1.5' />
              </svg>
              <svg
                v-else-if="link.icon === 'file'"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
              >
                <path
                  d='M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z'
                  stroke='currentColor'
                  stroke-width='1.5'
                  stroke-linecap='round'
                  stroke-linejoin='round'
                />
                <polyline
                  points='13 2 13 9 20 9'
                  stroke='currentColor'
                  stroke-width='1.5'
                  stroke-linecap='round'
                  stroke-linejoin='round'
                />
              </svg>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
                <circle
                  cx="12"
                  cy="12"
                  r="8"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
              </svg>
            </span>
            <span class="ku-link__label">{{ link.label }}</span>
          </RouterLink>
        </template>
      </nav>

      <!-- Right -->
      <div class="ku-actions">
        

        <div v-if="user" class="ku-user" ref="menuRef">
          <button
            class="ku-user__btn"
            @click="dropdown = !dropdown"
            :aria-expanded="dropdown"
            aria-haspopup="menu"
          >
            <div class="ku-avatar" :aria-label="`ผู้ใช้: ${displayName}`">
              <img v-if="user.profile_image_url" :src="`http://localhost:8000${user.profile_image_url}`" alt="Profile" class="avatar-img">
              <span v-else>{{ initials }}</span>
            </div>
            <div class="ku-user__meta">
              <span class="ku-user__name">{{ displayName }}</span>
              
            </div>
            <svg
              class="ku-caret"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
            >
              <path
                d="M6 9l6 6 6-6"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>

          <!-- Dropdown -->
          <div v-if="dropdown" class="ku-menu" role="menu">
            <!-- ทำหัวข้อชื่อ+อีเมลให้กดเข้า /user ได้ -->
        
              <div v-if="user.role === 'student'" class="ku-menu__head is-link" role="menuitem" @click="router.push({ name: 'user.profile' })">

                <div class="ku-avatar ku-avatar--lg">
                  <img v-if="user.profile_image_url" :src="`http://localhost:8000${user.profile_image_url}`" alt="Profile" class="avatar-img">
                  <span v-else>{{ initials }}</span>
                </div>
                <div>
                  <div class="ku-menu__name">{{ displayName }}</div>
                  <div class="ku-menu__email">{{ user.email }}</div>
                  <div class="ku-menu__role"><strong>Role:</strong> {{ user.role }}</div>
                  <div v-if="user.role === 'student'" class="ku-menu__student-id">
                    <strong>Student ID:</strong> 
                    <span v-if="user.student_id">{{ user.student_id }}</span>
                    <span v-else class="danger">Not Linked</span>
                  </div>
                </div>
              </div>

              <div v-if="user.role === 'teacher'" class="ku-menu__head is-link" role="menuitem" @click="router.push({ name: 'user.profile' })">
                <div class="ku-avatar ku-avatar--lg">
                  <img v-if="user.profile_image_url" :src="`http://localhost:8000${user.profile_image_url}`" alt="Profile" class="avatar-img">
                  <span v-else>{{ initials }}</span>
                </div>
                <div>
                  <div class="ku-menu__name">{{ displayName }}</div>
                  <div class="ku-menu__email">{{ user.email }}</div>
                  <div class="ku-menu__role"><strong>Role:</strong> {{ user.role }}</div>
                </div>
              </div>

              <div v-if="user.role === 'admin'" class="ku-menu__head is-link" role="menuitem" @click="router.push({ name: 'admin.profile' })">
                <div  class="ku-avatar ku-avatar--lg">
                  <img v-if="user.profile_image_url" :src="`http://localhost:8000${user.profile_image_url}`" alt="Profile" class="avatar-img">
                  <span v-else>{{ initials }}</span>
                </div>
                <div>
                  <div class="ku-menu__name">{{ displayName }}</div>
                  <div class="ku-menu__email">{{ user.email }}</div>
                  <div class="ku-menu__role"><strong>Role:</strong> {{ user.role }}</div>
                  <div v-if="user.role === 'student'" class="ku-menu__student-id">
                    <strong>Student ID:</strong> 
                    <span v-if="user.student_id">{{ user.student_id }}</span>
                    <span v-else class="danger">Not Linked</span>
                  </div>
                </div>
              </div>
            <RouterLink  v-if="user.role === 'student' || user.role === 'teacher'" :to="{ name: 'user.profile' }" class="ku-menu__item" role="menuitem">
              ข้อมูลส่วนตัว
            </RouterLink> 
             <RouterLink  v-if="user.role === 'admin'" :to="{ name: 'admin.profile' }" class="ku-menu__item" role="menuitem">
              ข้อมูลส่วนตัว
            </RouterLink>

            <button class="ku-menu__item danger" @click="doLogout">
              ออกจากระบบ
            </button>
          </div>
        </div>

        <div v-show="!user" class="ku-guest">
          <RouterLink class="ku-btn" to="/login">เข้าสู่ระบบ</RouterLink>
        </div>

        <button
          class="ku-burger"
          @click="open = !open"
          aria-label="Toggle menu"
          :aria-expanded="open"
          aria-controls="ku-drawer"
        >
          <span /><span /><span />
        </button>
      </div>
    </div>

    <!-- Drawer (mobile) -->
    <div class="ku-backdrop" v-if="open" @click="open = false" />
    <div id="ku-drawer" class="ku-drawer" v-if="open">
      <nav class="ku-drawer__links" aria-label="เมนูมือถือ">
        <RouterLink
          v-for="link in visibleLinks"
          :key="'m' + link.to"
          :to="link.to"
          class="ku-link"
          :class="{ active: route.path === link.to }"
          @click="open = false"
        >
          <span class="ku-link__label">{{ link.label }}</span>
        </RouterLink>
      </nav>
      <div class="ku-drawer__footer">
        <div class="ku-lang">
          
        </div>
        <button
          v-if="user"
          class="ku-btn danger"
          @click="
            () => {
              open = false;
              doLogout();
            }
          "
        >
          ออกจากระบบ
        </button>
        <RouterLink v-else class="ku-btn" to="/login" @click="open = false">
          เข้าสู่ระบบ
        </RouterLink>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { user, displayName, initials, logout, ensure } from '@/composables/useAuth' // Direct import

// โลโก้
const kuLogo = new URL('@/assets/ku-logo.png', import.meta.url).href

const route = useRoute()
const router = useRouter()



// ลิงก์เมนูบนแถบ
const studentNavLinks = [
  { to: '/', label: 'หน้าหลัก', icon: 'home' },
  { to: '/user/documents', label: 'ดาวน์โหลดเอกสาร', icon: 'folder' },
  { to: '/user/submit-document', label: 'ส่งเอกสาร', icon: 'file' },
  { to: '/user/my-submissions', label: 'เอกสารของฉัน', icon: 'file' },
];

const teacherNavLinks = [
  { to: '/', label: 'หน้าหลัก', icon: 'home' },
  { to: '/user/documents', label: 'ดาวน์โหลดเอกสาร', icon: 'folder' },
  { to: '/user/teacher/documents', label: 'ตรวจเอกสาร', icon: 'file' },
];

const adminNavLinks = [
  { to: '/admin/users', label: 'จัดการผู้ใช้', icon: 'users' },
  { to: '/admin/documents', label: 'จัดการเอกสาร', icon: 'folder' },
  { to: '/admin/dashboard', label: 'จัดการเอกสารโครงงาน', icon: 'file' },
  {
    label: 'ข้อมูลพื้นฐาน',
    icon: 'folder',
    children: [
      { to: '/admin/basic-data/academic-terms', label: 'ข้อมูลปีการศึกษา' },
      { to: '/admin/basic-data/projects-management', label: 'ข้อมูลโครงงาน' },
      { to: '/admin/basic-data/advisors', label: 'ข้อมูลอาจารย์ที่ปรึกษา' },
    ]
  },
];

const visibleLinks = computed(() => {
  if (!user.value) return [] // Guest (login page)
  if (user.value.role === 'admin') return adminNavLinks
  if (user.value.role === 'teacher') return teacherNavLinks
  if (user.value.role === 'student') return studentNavLinks
  return [] // Default for any other role
})

const open = ref(false)
const dropdown = ref(false)
const dropdownBasicData = ref(false)
const lang = ref(localStorage.getItem('ku_lang') || 'TH')
const setLang = (code) => {
  lang.value = code
  localStorage.setItem('ku_lang', code)
}

async function doLogout () {
  await logout()
  dropdown.value = false
  open.value = false
  router.replace({ name: 'login' })
}

const toggleDropdownBasicData = () => {
  dropdownBasicData.value = !dropdownBasicData.value;
};

// dropdown handlers
const menuRef = ref(null)
const onDocClick = (e) => {
  if (dropdown.value && menuRef.value && !menuRef.value.contains(e.target)) {
    dropdown.value = false
  }
}
const onEsc = (e) => { if (e.key === 'Escape') dropdown.value = false }

onMounted(() => {
  ensure()
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onEsc)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onEsc)
})
watch(() => route.fullPath, () => { open.value = false; dropdown.value = false })
</script>

<style scoped>
/* Nav */
.ku-nav {
  position: sticky;
  top: 0;
  inset-inline: 0;
  background: var(--primary-light);
  color: var(--text-primary);
  border-bottom: 1px solid rgba(0,0,0,0.1);
  z-index: 50;
  isolation: isolate;
  box-shadow: var(--shadow);
}
.ku-nav__inner {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 16px;
  padding: 12px clamp(12px, 4vw, 28px);
}

/* Brand */
.ku-brand { display: inline-flex; align-items: center; gap: 12px; cursor: pointer; }
.ku-logo { height: 40px; }
.ku-brand__text strong { color: var(--text-primary); }
.ku-brand__text small { color: var(--text-secondary); font-size: 12px; }

/* Links */
.ku-links { display: none; gap: 8px; justify-self: end; }
@media (min-width: 900px) { .ku-links { display: inline-flex; } }
.ku-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: var(--radius);
  color: var(--text-secondary);
  background: transparent;
  border: 1px solid transparent;
  font-weight: 600;
  text-decoration: none;
  transition: .15s;
}
.ku-link:hover {
  background: rgba(0,0,0,0.05);
  color: var(--text-primary);
}
.ku-link.active {
  background: var(--primary-color);
  color: var(--text-on-primary);
}

/* Right area */
.ku-actions { display: inline-flex; align-items: center; gap: 10px; }

/* User */
.ku-user { position: relative; }
.ku-user__btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: transparent;
  border: 1px solid var(--border-color);
  border-radius: 999px;
  padding: 6px 10px;
  cursor: pointer;
  transition: background .15s;
}
.ku-user__btn:hover { background: rgba(0,0,0,0.05); }
.ku-user__meta { display: none; }
@media (min-width: 1024px) { .ku-user__meta { display: inline-flex; flex-direction: column; line-height: 1.2; text-align: left;} }
.ku-user__name { font-weight: 600; color: var(--text-primary); }
.ku-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--primary-color);
  color: var(--text-on-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 12px;
  overflow: hidden;
}
.ku-avatar--lg {
  width: 40px;
  height: 40px;
  font-size: 16px;
}
.ku-avatar .avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.ku-caret { color: var(--text-secondary); transition: transform .2s; }
.ku-user__btn[aria-expanded="true"] .ku-caret,
.ku-link[aria-expanded="true"] .ku-caret { transform: rotate(180deg); }

/* Dropdown */
.ku-menu {
  position: absolute;
  right: 0;
  margin-top: 8px;
  min-width: 240px;
  background: var(--surface-color);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  z-index: 1000;
  overflow: hidden;
}
.ku-menu__head {
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 10px;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid var(--border-color);
}
.ku-menu__head.is-link { text-decoration: none; color: inherit; cursor: pointer; }
.ku-menu__head.is-link:hover { background: var(--background-color); }
.ku-menu__name { color: var(--text-primary); font-weight: 600; }
.ku-menu__email { color: var(--text-secondary); font-size: 14px; }
.ku-menu__role { font-size: 12px; margin-top: 4px; color: var(--text-secondary); }
.ku-menu__student-id { font-size: 12px; color: var(--text-secondary); }
.ku-menu__item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 10px 12px;
  background: transparent;
  border: 0;
  color: var(--text-primary);
  text-decoration: none;
  cursor: pointer;
  font-family: var(--font-family);
  font-size: 15px;
}
.ku-menu__item + .ku-menu__item { border-top: 1px solid var(--border-color); }
.ku-menu__item:hover { background: var(--background-color); }
.ku-menu__item.danger { color: var(--danger-color); font-weight: 600; }
.ku-menu__item.danger:hover { background: var(--danger-light); }

/* Burger / Drawer */
.ku-burger {
  display: inline-grid;
  gap: 4px;
  width: 38px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: transparent;
  place-content: center;
  cursor: pointer;
}
.ku-burger span { width: 18px; height: 2px; background: var(--text-primary); }
@media (min-width: 900px) { .ku-burger { display: none; } }
.ku-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 5200; }
.ku-drawer {
  position: fixed;
  inset: 65px 0 0 0; /* height of navbar */
  background: var(--surface-color);
  color: var(--text-primary);
  display: grid;
  grid-template-rows: 1fr auto;
  padding: 16px;
  gap: 16px;
  z-index: 5300;
  border-top: 1px solid var(--border-color);
}
.ku-drawer .ku-link { color: var(--text-primary); border-radius: 12px; padding: 12px; }
.ku-drawer .ku-link.active { background: var(--primary-extralight); color: var(--primary-dark); }
.ku-drawer__footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  border-top: 1px dashed var(--border-color);
  padding-top: 12px;
}

.ku-dropdown {
  position: relative;
}

.ku-dropdown-menu {
  position: absolute;
  background: var(--surface-color);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 8px 0;
  min-width: 180px;
  z-index: 100;
  top: 100%;
  left: 0;
  margin-top: 8px;
  box-shadow: var(--shadow);
}

.ku-dropdown-item {
  display: block;
  padding: 8px 12px;
  color: var(--text-primary);
  text-decoration: none;
}

.ku-dropdown-item:hover {
  background: var(--background-color);
}

.danger { color: var(--danger-color) !important; }
</style>
