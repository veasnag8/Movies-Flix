import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/client'

export const useMovieStore = defineStore('movies', () => {
  const movies = ref([])
  const meta = ref({
    trending: [],
    latest: [],
    recommended: [],
    hero: null,
  })
  const categories = ref([])
  const current = ref(null)
  const related = ref([])
  const searchResults = ref([])
  const continueWatching = ref([])
  const favorites = ref([])
  const loading = ref(false)
  const error = ref('')

  async function fetchHome() {
    loading.value = true
    error.value = ''
    try {
      const [moviesRes, categoriesRes] = await Promise.all([
        api.get('/movies'),
        api.get('/categories'),
      ])
      movies.value = moviesRes.data.data || []
      meta.value = moviesRes.data.meta || meta.value
      categories.value = categoriesRes.data.data || []
    } catch (e) {
      error.value = e.response?.data?.message || 'Failed to load movies'
    } finally {
      loading.value = false
    }
  }

  async function fetchMovie(id) {
    loading.value = true
    error.value = ''
    try {
      const { data } = await api.get(`/movies/${id}`)
      current.value = data.data
      related.value = data.related || []
      return data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Movie not found'
      current.value = null
      throw e
    } finally {
      loading.value = false
    }
  }

  async function search(q) {
    const { data } = await api.get('/search', { params: { q } })
    searchResults.value = data.data || []
    return searchResults.value
  }

  async function fetchContinueWatching() {
    try {
      const { data } = await api.get('/watch-history')
      continueWatching.value = (data.data || []).filter((item) => item.movie)
    } catch {
      continueWatching.value = []
    }
  }

  async function saveProgress(movieId, progress) {
    await api.post('/watch-history', { movie_id: movieId, progress })
  }

  async function fetchFavorites() {
    const { data } = await api.get('/favorites')
    favorites.value = data.data || []
  }

  async function toggleFavorite(movieId) {
    const exists = favorites.value.some((f) => String(f.movie_id) === String(movieId))
    if (exists) {
      await api.delete(`/favorites/${movieId}`)
    } else {
      await api.post('/favorites', { movie_id: movieId })
    }
    await fetchFavorites()
  }

  return {
    movies,
    meta,
    categories,
    current,
    related,
    searchResults,
    continueWatching,
    favorites,
    loading,
    error,
    fetchHome,
    fetchMovie,
    search,
    fetchContinueWatching,
    saveProgress,
    fetchFavorites,
    toggleFavorite,
  }
})
