function getToken(): string {
  return localStorage.getItem('token') || ''
}
function getRoleFromToken(t: string): string | null {
  try {
    const payload = JSON.parse(atob(t.split('.')[1])) as { role?: string; exp?: number }
    if (payload?.exp && payload.exp * 1000 < Date.now()) return null
    return payload.role ?? null
  } catch { return null }
}
export function authGuard(to: any, _from: any, next: any) {
  const needs = to.matched.some((r: any) => r.meta?.requiresAuth)
  if (!needs) return next()

  const t = getToken()
  const role = t ? getRoleFromToken(t) : null
  if (!t || !role) return next({ name: 'login', query: { redirect: to.fullPath } })

  const ok = to.matched.every((r: any) => {
    const roles: string[] = r.meta?.roles || []
    return roles.length === 0 || roles.includes(role!)
  })
  if (!ok) return next({ name: 'forbidden' })
  next()
}
