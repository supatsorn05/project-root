<template>

  <div class="ku-login">
    <!-- Animated Background -->
    <div class="particles">
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
    </div>

    <main class="ku-main">
      <form
        class="ku-card"
        @submit.prevent="login"
        novalidate
        aria-labelledby="login-title"
      >
        <div class="ku-title">
          <div class="ku-title__icon" aria-hidden="true">
            <svg
              width="28"
              height="28"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"
                stroke="currentColor"
                stroke-width="1.5"
              />
              <path
                d="M20 21v-2a5 5 0 0 0-5-5H9a5 5 0 0 0-5 5v2"
                stroke="currentColor"
                stroke-width="1.5"
              />
            </svg>
          </div>
          <h2 id="login-title">เข้าสู่ระบบ</h2>
        </div>

        <label class="ku-field" for="identifier">
          <span class="ku-label">อีเมลหรือชื่อผู้ใช้</span>
          <div class="ku-inputwrap" :class="{ 'is-open': showSuggest }">
            <span class="ku-ico" aria-hidden="true">
              <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M4 6h16v12H4z"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
                <path
                  d="M4 7l8 6 8-6"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
              </svg>
            </span>

            <input
              id="identifier"
              ref="idField"
              v-model.trim="identifier"
              placeholder="name@ku.th หรือ username"
              inputmode="email"
              autocapitalize="off"
              spellcheck="false"
              autocomplete="off"
              @focus="openSuggest"
              @blur="closeSuggest"
              @input="onIdInput"
              @keydown.down.prevent="move(1)"
              @keydown.up.prevent="move(-1)"
              @keydown.enter.prevent="chooseActive"
            />

            <!-- Suggest ที่สไตล์เข้ากับการ์ด -->
            <ul
              v-if="showSuggest && filteredIds.length"
              class="ku-suggest"
              role="listbox"
            >
              <li
                v-for="(opt, i) in filteredIds"
                :key="opt"
                :class="{ active: i === activeIndex }"
                role="option"
                @mousedown.prevent="select(opt)"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path
                    d="M4 6h16v12H4z"
                    stroke="currentColor"
                    stroke-width="1.5"
                  />
                  <path
                    d="M4 7l8 6 8-6"
                    stroke="currentColor"
                    stroke-width="1.5"
                  />
                </svg>
                <span>{{ opt }}</span>
              </li>
            </ul>
          </div>
        </label>

        <label class="ku-field" for="password">
          <span class="ku-label">รหัสผ่าน</span>
          <div class="ku-inputwrap">
            <span class="ku-ico" aria-hidden="true">
              <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <rect
                  x="3"
                  y="11"
                  width="18"
                  height="9"
                  rx="2"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
                <path
                  d="M8 11V8a4 4 0 1 1 8 0v3"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
              </svg>
            </span>
            <input
              id="password"
              :type="showPass ? 'text' : 'password'"
              v-model="password"
              placeholder="รหัสผ่าน"
              autocomplete="current-password"
              @keyup.enter="login"
              :aria-invalid="!!errorMessage"
            />
            <button
              class="ku-pass__toggle"
              type="button"
              @click="showPass = !showPass"
              :aria-pressed="showPass"
            >
              <svg
                v-if="!showPass"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
                <circle
                  cx="12"
                  cy="12"
                  r="3"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
              </svg>
              <svg
                v-else
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.5" />
                <path
                  d="M10.6 10.6a2 2 0 1 0 2.83 2.83"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
                <path
                  d="M9.9 4.4A10.4 10.4 0 0 1 12 4.03C18 4.03 22 12 22 12a18.7 18.7 0 0 1-4.14 4.93M6.2 7.2C3.84 9.18 2 12 2 12s4 7 10 7c1.29 0 2.5-.23 3.6-.65"
                  stroke="currentColor"
                  stroke-width="1.5"
                />
              </svg>
              <span class="sr-only">{{
                showPass ? "ซ่อนรหัสผ่าน" : "แสดงรหัสผ่าน"
              }}</span>
            </button>
          </div>
        </label>

        <div class="ku-row" style="margin-top: 30px; margin-bottom: 30px">
          <div class="form-check">
            <input
              class="form-check-input"
              type="checkbox"
              id="remember"
              v-model="remember"
            />
            <label class="form-check-label" for="remember">จดจำฉันไว้</label>
          </div>

          <RouterLink to="/forgot" class="ku-textlink">ลืมรหัสผ่าน?</RouterLink>
        </div>

        <button
          class="ku-btn ku-btn--primary"
          type="submit"
          :disabled="loading"
          :aria-busy="loading"
        >
          <span v-if="loading" class="ku-spin" aria-hidden="true"></span>
          {{ loading ? "กำลังเข้าสู่ระบบ…" : "เข้าสู่ระบบ" }}
        </button>

        <p v-if="errorMessage" class="ku-error" role="alert">
          {{ errorMessage }}
        </p>

        <p class="ku-note">
          สำหรับผู้ใช้งานใหม่ กรุณาติดต่อผู้ดูแลระบบเพื่อเปิดสิทธิ์เข้าใช้งาน
        </p>
      </form>
    </main>

    <footer class="ku-footer">
      <small
        >&copy; {{ new Date().getFullYear() }} Kasetsart University, Sriracha
        Campus</small
      >
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import http from '@/services/http'
import { useRouter } from 'vue-router'
import { refresh, setToken, user } from '@/composables/useAuth' // Direct import

const router = useRouter()

const identifier = ref('')
const password   = ref('')
const remember   = ref(false)
const showPass   = ref(false)
const loading    = ref(false)
const errorMessage = ref('')

const idField = ref(null)
const showSuggest = ref(false)
const activeIndex = ref(-1)
const recentIds = ref([])

const filteredIds = computed(() =>
  recentIds.value
    .filter(v => !identifier.value || v.toLowerCase().includes(identifier.value.toLowerCase()))
    .slice(0,5)
)

const openSuggest = () => { if (recentIds.value.length) showSuggest.value = true }
const closeSuggest = () => { setTimeout(() => (showSuggest.value = false), 120) }
const onIdInput = () => { showSuggest.value = true; activeIndex.value = -1 }
const select = (v) => { identifier.value = v; showSuggest.value = false; nextTick(() => idField.value?.focus()) }
const move = (d) => { if (!filteredIds.value.length) return; activeIndex.value = (activeIndex.value + d + filteredIds.value.length) % filteredIds.value.length }
const chooseActive = () => { if (activeIndex.value >= 0) select(filteredIds.value[activeIndex.value]); else login() }

const saveRecent = (id) => {
  const arr = Array.from(new Set([id, ...recentIds.value])).slice(0, 6)
  recentIds.value = arr
  localStorage.setItem('ku_ids', JSON.stringify(arr))
}

onMounted(() => {
  const last = localStorage.getItem('ku_last_id')
  if (last && !identifier.value) identifier.value = last
  try { recentIds.value = JSON.parse(localStorage.getItem('ku_ids') || '[]') } catch {}
})

const login = async () => {
  if (!identifier.value || !password.value) {
    errorMessage.value = 'กรุณากรอกอีเมล/ชื่อผู้ใช้ และรหัสผ่าน'
    return
  }
  loading.value = true
  errorMessage.value = ''
  try {
    const isEmail = identifier.value.includes('@')
    const payload = isEmail
      ? { email: identifier.value, password: password.value }
      : { username: identifier.value, password: password.value }

    const res = await http.post('/login.php', payload, {
      headers: { 'Content-Type': 'application/json' }
    })

    console.log('Login response:', res.data)

    if (res.data?.status === 'success') {
      // Explicitly remove old token before setting new one
      localStorage.removeItem('token'); 
      localStorage.setItem('token', res.data.token)
      setToken(res.data.token) // Explicitly set the token in useAuth.js
      
      if (remember.value) localStorage.setItem('ku_last_id', identifier.value)
      saveRecent(identifier.value)

      // Let the router guard handle the user state refresh.
      // Just redirect based on the role returned from the login response.
      const loggedInUser = res.data.user;
      if (loggedInUser?.role === 'admin') {
        console.log('Redirecting to /admin/dashboard')
        router.replace('/admin/dashboard')
      } else {
        console.log('Redirecting to /user/dashboard')
        router.replace('/user/dashboard')
      }
    } else {
      errorMessage.value = res.data?.message || 'เข้าสู่ระบบล้มเหลว'
    }
  } catch (e) {
    errorMessage.value = e?.response?.data?.message || e?.message || 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
  } finally {
    loading.value = false
  }
}
</script>



<style scoped>
.ku-login {
  min-height: 100svh;
  color: var(--text-primary);
  background-color: var(--background-color);
  position: relative;
  overflow: clip;
  font-synthesis-weight: none;
}

/* ---------- layout ---------- */
.ku-main {
  min-height: 100svh;
  display: grid;
  place-items: center;
  padding: 24px;
}
.ku-card {
  width: 100%;
  max-width: 420px;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  padding: 28px;
  transition: transform 0.2s, box-shadow 0.2s;
  will-change: transform;
}
.ku-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 60px rgba(0, 0, 0, 0.1), 0 3px 12px rgba(0, 0, 0, 0.06);
}

/* ---------- title ---------- */
.ku-title {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
}
.ku-title__icon {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  background: linear-gradient(var(--surface-color), var(--background-color));
  color: var(--primary-color);
}
.ku-title h2 {
  font-size: 22px;
  letter-spacing: 0.2px;
  font-weight: 700;
}

/* ---------- field ---------- */
.ku-field { display: block; margin: 14px 0; }
.ku-label {
  display: inline-block;
  font-size: 12px;
  letter-spacing: 0.4px;
  color: var(--text-secondary);
  margin-bottom: 8px;
  user-select: none;
}
.ku-inputwrap { position: relative; display: grid; align-items: center; }
.ku-ico {
  position: absolute;
  inset-inline-start: 12px;
  pointer-events: none;
  color: var(--text-secondary);
}

/* ---------- password toggle ---------- */
.ku-pass__toggle {
  position: absolute;
  inset-inline-end: 6px;
  top: 50%;
  transform: translateY(-50%);
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 10px;
  background: transparent;
  color: var(--text-secondary);
  display: grid;
  place-items: center;
  transition: background 0.2s, transform 0.2s, color 0.2s;
}
.ku-pass__toggle:hover {
  background: var(--primary-extralight);
  color: var(--primary-dark);
}
.ku-pass__toggle:active { transform: translateY(-50%) scale(0.98); }

/* ---------- row / checkbox / link ---------- */
.ku-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin: 10px 0 2px;
}
.ku-textlink {
  color: var(--primary-dark);
  text-decoration: underline;
  text-decoration-thickness: 1px;
  text-underline-offset: 3px;
  font-weight: 600;
}
.ku-textlink:hover { text-decoration-thickness: 2px; }

/* ---------- button ---------- */
.ku-btn {
  width: 100%;
  border-radius: 16px;
  padding: 12px 16px;
  font-weight: 700;
  letter-spacing: 0.2px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}
.ku-btn--primary {
  background: var(--primary-color);
  color: var(--text-on-primary);
  border: 1px solid var(--primary-color);
  box-shadow: 0 8px 20px rgba(76, 175, 80, 0.25);
}
.ku-btn--primary:hover {
  background: var(--primary-dark);
  border-color: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 10px 28px rgba(76, 175, 80, 0.3);
}
.ku-btn--primary:active { transform: translateY(0); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18); }
.ku-btn[disabled] { opacity: 0.7; cursor: not-allowed; }

/* spinner */
.ku-spin {
  width: 16px;
  height: 16px;
  border-radius: 999px;
  border: 2px solid var(--text-on-primary);
  border-right-color: transparent;
  display: inline-block;
  animation: spin 700ms linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ---------- feedback ---------- */
.ku-error {
  margin-top: 12px;
  padding: 12px 14px;
  border-radius: 14px;
  text-align: center;
  background: var(--danger-light);
  color: var(--text-primary);
  border: 1px solid var(--danger-color);
  font-size: 14px;
  line-height: 1.45;
}
.ku-note { margin-top: 12px; color: var(--text-secondary); font-size: 13px; }

/* ---------- footer ---------- */
.ku-footer {
  position: fixed;
  inset-inline: 0;
  bottom: 10px;
  display: grid;
  place-items: center;
  color: var(--text-secondary);
  pointer-events: none;
}

/* ---------- a11y helpers ---------- */
.sr-only { position: absolute !important; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

/* ---------- responsive ---------- */
@media (max-width: 480px) {
  .ku-card { padding: 22px; border-radius: 16px; }
  input { padding: 12px 42px 12px 38px; }
}

/* ---------- OVERRIDES: frame always + card-fit suggest ---------- */
.ku-card { overflow: hidden; position: relative; }
.ku-inputwrap {
  position: relative;
  display: grid;
  align-items: center;
  border: 1px solid var(--border-color);
  border-radius: 14px;
  background: var(--surface-color);
  padding-inline: 40px 44px;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.ku-inputwrap:hover { border-color: #BDBDBD; }
.ku-inputwrap:focus-within {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px var(--primary-extralight);
}

.ku-inputwrap > input {
  border: 0 !important;
  background: transparent !important;
  padding: 12px 0 !important;
  width: 100%;
  outline: none;
  font-size: 15px;
  color: var(--text-primary);
}
input::placeholder { color: var(--text-disabled); }

.ku-suggest {
  position: absolute;
  left: -1px;
  right: -1px;
  top: calc(100% + 6px);
  background: var(--surface-color);
  border: 1px solid var(--border-color);
  border-radius: 14px;
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.14), 0 2px 8px rgba(0, 0, 0, 0.06);
  z-index: 3;
  max-height: 220px;
  overflow: auto;
}
.ku-suggest li {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  cursor: pointer;
  user-select: none;
  font-size: 14px;
  color: var(--text-primary);
  line-height: 1.3;
}
.ku-suggest li + li { border-top: 1px solid var(--background-color); }
.ku-suggest li:hover,
.ku-suggest li.active {
  background: var(--primary-extralight);
  color: var(--primary-dark);
}

.ku-pass__toggle { inset-inline-end: 6px; }

:deep(input):-webkit-autofill {
  -webkit-text-fill-color: var(--text-primary);
  transition: background-color 9999s ease-in-out 0s;
  box-shadow: 0 0 0 1000px var(--surface-color) inset !important;
}
.ku-inputwrap > input { border: 0 !important; border-radius: 0 !important; background: transparent !important; box-shadow: none !important; outline: none !important; padding: 12px 0 !important; }
.ku-inputwrap > input:focus,
.ku-inputwrap > input:focus-visible { border: 0 !important; box-shadow: none !important; outline: none !important; }

/* --- Bootstrap-like checkbox (no dependency) --- */
.form-check-input{
  -webkit-appearance: none;
  appearance: none;
  display: inline-block;
  box-sizing: border-box;
  width: 1rem;
  height: 1rem;
  min-width: 1rem;
  padding: 0 !important;
  margin: 0;
  border: 1px solid var(--text-disabled);
  border-radius: .25rem !important;
  background-color: var(--surface-color);
  background-repeat: no-repeat;
  background-position: center;
  background-size: 60% 60%;
  box-shadow: none !important;
  vertical-align: middle;
  cursor: pointer;
  transition: border-color .15s, box-shadow .15s, background-color .15s;
}

.form-check-input:focus{
  outline: 0;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 .2rem var(--primary-extralight) !important;
}

.form-check-input:checked{
  border-color: var(--primary-color);
  background-color: var(--primary-color);
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3E%3C/svg%3E");
}

.form-check{ display: inline-flex; align-items: center; gap: .5rem; }
.form-check-label{ user-select: none; font-size: 13px; color: var(--text-primary); cursor: pointer; }
</style>
