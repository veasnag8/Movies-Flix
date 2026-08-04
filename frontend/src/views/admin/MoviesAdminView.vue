<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../../api/client'

const movies = ref([])
const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const message = ref('')
const error = ref('')
const editingId = ref(null)

const form = reactive({
  title: '',
  slug: '',
  description: '',
  poster_url: '',
  banner_url: '',
  category: '',
  year: '',
  duration: '',
  rating: '',
  language: 'English',
  quality: '1080p',
  drive_video_id: '',
  trailer_url: '',
  subtitle_url: '',
  status: 'active',
  video: null,
  poster: null,
  banner: null,
})

function resetForm() {
  editingId.value = null
  Object.assign(form, {
    title: '',
    slug: '',
    description: '',
    poster_url: '',
    banner_url: '',
    category: '',
    year: '',
    duration: '',
    rating: '',
    language: 'English',
    quality: '1080p',
    drive_video_id: '',
    trailer_url: '',
    subtitle_url: '',
    status: 'active',
    video: null,
    poster: null,
    banner: null,
  })
}

async function load() {
  loading.value = true
  try {
    const [m, c] = await Promise.all([
      api.get('/admin/movies'),
      api.get('/admin/categories'),
    ])
    movies.value = m.data.data || []
    categories.value = c.data.data || []
  } finally {
    loading.value = false
  }
}

function editMovie(movie) {
  editingId.value = movie.id
  Object.assign(form, {
    title: movie.title || '',
    slug: movie.slug || '',
    description: movie.description || '',
    poster_url: movie.poster_url || '',
    banner_url: movie.banner_url || '',
    category: movie.category || '',
    year: movie.year || '',
    duration: movie.duration || '',
    rating: movie.rating || '',
    language: movie.language || 'English',
    quality: movie.quality || '1080p',
    drive_video_id: movie.drive_video_id || '',
    trailer_url: movie.trailer_url || '',
    subtitle_url: movie.subtitle_url || '',
    status: movie.status || 'active',
    video: null,
    poster: null,
    banner: null,
  })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function saveMovie() {
  saving.value = true
  message.value = ''
  error.value = ''
  try {
    const data = new FormData()
    Object.entries(form).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        data.append(key, value)
      }
    })

    if (editingId.value) {
      data.append('_method', 'PUT')
      await api.post(`/admin/movie/${editingId.value}`, data)
      message.value = 'Movie updated.'
    } else {
      await api.post('/admin/movie', data)
      message.value = 'Movie created.'
    }
    resetForm()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || 'Save failed'
  } finally {
    saving.value = false
  }
}

async function removeMovie(id) {
  if (!confirm('Delete this movie?')) return
  await api.delete(`/admin/movie/${id}`)
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-3xl font-semibold">Movies</h1>
      <button
        v-if="editingId"
        class="rounded bg-white/10 px-3 py-2 text-sm"
        @click="resetForm"
      >
        Cancel edit
      </button>
    </div>

    <form class="mt-6 grid gap-3 rounded-sm border border-white/10 bg-neutral-900 p-4 md:grid-cols-2" @submit.prevent="saveMovie">
      <input v-model="form.title" required placeholder="Title" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.slug" placeholder="Slug (optional)" class="rounded border border-white/10 bg-black px-3 py-2" />
      <textarea v-model="form.description" placeholder="Description" rows="3" class="rounded border border-white/10 bg-black px-3 py-2 md:col-span-2" />
      <select v-model="form.category" class="rounded border border-white/10 bg-black px-3 py-2">
        <option value="">Select category</option>
        <option v-for="c in categories" :key="c.id" :value="c.name">{{ c.name }}</option>
      </select>
      <input v-model="form.year" placeholder="Year" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.duration" placeholder="Duration e.g. 2h 10m" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.rating" placeholder="Rating" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.language" placeholder="Language" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.quality" placeholder="Quality" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.drive_video_id" placeholder="Google Drive Video ID" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.poster_url" placeholder="Poster URL" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.banner_url" placeholder="Banner URL" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.trailer_url" placeholder="Trailer URL" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.subtitle_url" placeholder="Subtitle URL (.vtt)" class="rounded border border-white/10 bg-black px-3 py-2" />
      <select v-model="form.status" class="rounded border border-white/10 bg-black px-3 py-2">
        <option value="active">active</option>
        <option value="inactive">inactive</option>
        <option value="draft">draft</option>
      </select>
      <label class="text-sm text-white/60">
        Upload video to Drive
        <input type="file" accept="video/*" class="mt-1 block w-full text-sm" @change="form.video = $event.target.files[0]" />
      </label>
      <label class="text-sm text-white/60">
        Upload poster
        <input type="file" accept="image/*" class="mt-1 block w-full text-sm" @change="form.poster = $event.target.files[0]" />
      </label>
      <label class="text-sm text-white/60">
        Upload banner
        <input type="file" accept="image/*" class="mt-1 block w-full text-sm" @change="form.banner = $event.target.files[0]" />
      </label>
      <div class="md:col-span-2">
        <button class="rounded bg-flix-red px-5 py-2 font-semibold hover:bg-flix-red-dark disabled:opacity-60" :disabled="saving">
          {{ saving ? 'Saving...' : editingId ? 'Update Movie' : 'Add Movie' }}
        </button>
        <p v-if="message" class="mt-2 text-sm text-green-400">{{ message }}</p>
        <p v-if="error" class="mt-2 text-sm text-red-400">{{ error }}</p>
      </div>
    </form>

    <div class="mt-8 overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="border-b border-white/10 text-white/50">
          <tr>
            <th class="px-2 py-3">Title</th>
            <th class="px-2 py-3">Category</th>
            <th class="px-2 py-3">Year</th>
            <th class="px-2 py-3">Status</th>
            <th class="px-2 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="movie in movies" :key="movie.id" class="border-b border-white/5">
            <td class="px-2 py-3">{{ movie.title }}</td>
            <td class="px-2 py-3">{{ movie.category }}</td>
            <td class="px-2 py-3">{{ movie.year }}</td>
            <td class="px-2 py-3">{{ movie.status }}</td>
            <td class="px-2 py-3">
              <button class="mr-2 text-flix-red" @click="editMovie(movie)">Edit</button>
              <button class="text-white/50 hover:text-red-400" @click="removeMovie(movie.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="loading" class="mt-4 text-white/50">Loading...</p>
    </div>
  </div>
</template>
