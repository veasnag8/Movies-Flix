import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '../api/client'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('mf_user') || 'null'))
  const token = ref(localStorage.getItem('mf_token') || '')
  const loading = ref(false)
  const error = ref('')

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  function persist(nextUser, nextToken) {
    user.value = nextUser
    token.value = nextToken
    localStorage.setItem('mf_user', JSON.stringify(nextUser))
    localStorage.setItem('mf_token', nextToken)
  }

  function clear() {
    user.value = null
    token.value = ''
    localStorage.removeItem('mf_user')
    localStorage.removeItem('mf_token')
  }

  async function login(payload) {
    loading.value = true
    error.value = ''
    try {
      const { data } = await api.post('/login', payload)
      persist(data.user, data.token)
      return data
    } catch (e) {
      error.value =
        e.response?.data?.message ||
        e.response?.data?.error ||
        (e.code === 'ECONNABORTED' ? 'Server timeout. Try again.' : null) ||
        e.message ||
        'Login failed'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function register(payload) {
    loading.value = true
    error.value = ''
    try {
      const { data } = await api.post('/register', payload)
      persist(data.user, data.token)
      return data
    } catch (e) {
      error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || 'Registration failed'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      // ignore
    }
    clear()
  }

  async function fetchMe() {
    if (!token.value) return null
    const { data } = await api.get('/me')
    user.value = data.user
    localStorage.setItem('mf_user', JSON.stringify(data.user))
    return data.user
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    fetchMe,
    clear,
  }
})
