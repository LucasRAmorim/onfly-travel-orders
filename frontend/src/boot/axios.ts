import { boot } from 'quasar/wrappers'
import axios from 'axios'

const base = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')
const api = axios.create({
  baseURL: `${base}/api`,
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')

  if (token) {
    config.headers = config.headers ?? {}
    config.headers.Authorization = `Bearer ${token}`
  }

  config.headers = config.headers ?? {}
  config.headers.Accept = 'application/json'

  return config
})

export default boot(({ app }) => {
  app.config.globalProperties.$axios = axios
  app.config.globalProperties.$api = api
})

export { api }
