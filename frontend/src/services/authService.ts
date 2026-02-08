import { api } from 'src/boot/axios'
import type { AuthResponse, User } from 'src/types/auth'

const TOKEN_KEY = 'token'
const USER_KEY = 'user'

export async function login(email: string, password: string) {
  const { data } = await api.post<AuthResponse>('/login', { email, password })
  return data
}

export function saveSession(token: string, user: User) {
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export function clearSession() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}

export function getSessionUser(): User | null {
  try {
    const rawUser = localStorage.getItem(USER_KEY)
    return rawUser ? (JSON.parse(rawUser) as User) : null
  } catch {
    return null
  }
}
