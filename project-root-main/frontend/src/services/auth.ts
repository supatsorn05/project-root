import http from './http'

export async function login(username: string, password: string) {
  const { data } = await http.post('/login', { username, password })
  localStorage.setItem('token', data.token) // token มี role ฝังอยู่
  return data
}
export function logout() {
  localStorage.removeItem('token')
}
