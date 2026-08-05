import axios from 'axios'

function resolveBaseUrl() {
  const raw = (import.meta.env.VITE_API_URL || '/api').trim().replace(/\/+$/, '')

  // Allow either https://xxx.onrender.com or https://xxx.onrender.com/api
  if (/^https?:\/\//i.test(raw) && !/\/api$/i.test(raw)) {
    return `${raw}/api`
  }

  return raw || '/api'
}

const api = axios.create({
  baseURL: resolveBaseUrl(),
  headers: {
    Accept: 'application/json',
  },
  timeout: 60000,
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('mf_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error)
)

export default api
