import { ref, computed, watch } from 'vue'
import http from '../services/http'

export const user = ref(null) // Export directly
export const token = ref(localStorage.getItem('token') || null) // Load from localStorage



// Add a watch to debug token changes
watch(token, (newToken, oldToken) => {
  console.log('Token changed from', oldToken ? 'present' : 'absent', 'to', newToken ? 'present' : 'absent');
});
export const loading = ref(false)

export function setToken(newToken) { // Export directly
  token.value = newToken
  if (newToken) {
    localStorage.setItem('token', newToken);
  } else {
    localStorage.removeItem('token');
  }
}

export async function ensure () { // No force, no inited

  // Temporarily remove token check to force API call
  // if (!token.value) {
  //   user.value = null
  //   return null
  // }

  loading.value = true
  try {
    const res = await http.get('/me.php')
    if (res.data?.status === 'success') {
      user.value = res.data.user
    } else {
      user.value = null
      setToken(null) // Token is invalid
    }
  } catch (e) {
    user.value = null
    setToken(null) // Token is invalid
  } finally {
    loading.value = false
  }
  return user.value
}

export async function refresh () { // No force
  return ensure()
}

export async function logout () {
  if (token.value) {
    try {
      await http.post('/logout.php', {})
    } catch {}
  }
  user.value = null
  setToken(null)
  // Clear recent login suggestions
  localStorage.removeItem('ku_ids');
  localStorage.removeItem('ku_last_id');
}

export const displayName = computed(() => // Export directly
  (user.value && (user.value.full_name || user.value.username || user.value.email)) || 'User'
)

export const initials = computed(() => { // Export directly
  const n = (displayName.value || 'U').trim()
  const p = n.split(/\s+/)
  const i = (p[0]?.[0] || '') + (p[1]?.[0] || '')
  return (i || 'U').toUpperCase()
})

// Listen for storage changes to sync auth state across tabs
window.addEventListener('storage', (event) => {
  if (event.key === 'token') {
    const newToken = event.newValue;
    if (token.value !== newToken) {
      console.log('Auth state changed in another tab. Syncing...');
      setToken(newToken);
      ensure(); // Re-validate user with the new token or log out if token is null
    }
  }
});

// No default export function useAuth
