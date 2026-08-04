<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import api from '../../api/client'

const movies = ref([])
const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const saveStep = ref('')
const message = ref('')
const error = ref('')
const editingId = ref(null)
const posterPreview = ref('')
const bannerPreview = ref('')
const videoName = ref('')

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
  language: 'Khmer',
  quality: '1080p',
  drive_video_id: '',
  trailer_url: '',
  subtitle_url: '',
  status: 'active',
  video: null,
  poster: null,
  banner: null,
})

function imageSrc(url) {
  if (!url) return ''
  const driveId =
    url.match(/[?&]id=([^&]+)/)?.[1] ||
    url.match(/\/d\/([^/]+)/)?.[1] ||
    url.match(/thumbnail\?id=([^&]+)/)?.[1]
  if (driveId && (url.includes('drive.google.com') || url.includes('googleusercontent.com'))) {
    return `https://drive.google.com/thumbnail?id=${driveId}&sz=w600`
  }
  return url
}

function resetForm() {
  editingId.value = null
  posterPreview.value = ''
  bannerPreview.value = ''
  videoName.value = ''
  saveStep.value = ''
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
    language: 'Khmer',
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
  videoName.value = ''
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
    language: movie.language || 'Khmer',
    quality: movie.quality || '1080p',
    drive_video_id: movie.drive_video_id || '',
    trailer_url: movie.trailer_url || '',
    subtitle_url: movie.subtitle_url || '',
    status: movie.status || 'active',
    video: null,
    poster: null,
    banner: null,
  })
  posterPreview.value = imageSrc(movie.poster_url)
  bannerPreview.value = imageSrc(movie.banner_url)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function onVideoFile(e) {
  const file = e.target.files?.[0]
  if (!file) return
  if (!file.type.startsWith('video/')) {
    error.value = 'Please choose a VIDEO file (mp4, mkv, ...).'
    e.target.value = ''
    return
  }
  form.video = file
  videoName.value = file.name
  error.value = ''
}

function onPosterFile(e) {
  const file = e.target.files?.[0]
  if (!file) return
  if (!file.type.startsWith('image/')) {
    error.value = 'Thumbnail must be an IMAGE (jpg, png, webp).'
    e.target.value = ''
    return
  }
  form.poster = file
  posterPreview.value = URL.createObjectURL(file)
  error.value = ''
}

function onBannerFile(e) {
  const file = e.target.files?.[0]
  if (!file) return
  if (!file.type.startsWith('image/')) {
    error.value = 'Banner must be an IMAGE (jpg, png, webp).'
    e.target.value = ''
    return
  }
  form.banner = file
  bannerPreview.value = URL.createObjectURL(file)
  error.value = ''
}

function clearVideo() {
  form.video = null
  videoName.value = ''
}

function clearPoster() {
  form.poster = null
  posterPreview.value = imageSrc(form.poster_url)
}

function clearBanner() {
  form.banner = null
  bannerPreview.value = imageSrc(form.banner_url)
}

watch(
  () => form.poster_url,
  (url) => {
    if (!form.poster) posterPreview.value = imageSrc(url)
  }
)

watch(
  () => form.banner_url,
  (url) => {
    if (!form.banner) bannerPreview.value = imageSrc(url)
  }
)

async function saveMovie() {
  saving.value = true
  message.value = ''
  error.value = ''
  saveStep.value = form.video
    ? 'កំពុងផ្ទុកវីដេអូទៅ Google Drive... / Uploading video...'
    : 'កំពុងរក្សាទុកភាពយន្ត... / Saving movie...'

  try {
    const data = new FormData()
    Object.entries(form).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        data.append(key, value)
      }
    })

    if (form.poster || form.banner) {
      saveStep.value = 'កំពុងផ្ទុករូបភាព... / Uploading images...'
    }

    if (editingId.value) {
      data.append('_method', 'PUT')
      await api.post(`/admin/movie/${editingId.value}`, data)
      message.value = 'បានធ្វើបច្ចុប្បន្នភាព / Movie updated.'
    } else {
      await api.post('/admin/movie', data)
      message.value = 'បានបន្ថែមភាពយន្ត / Movie created.'
    }
    resetForm()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || e.response?.data?.error || 'Save failed'
  } finally {
    saving.value = false
    saveStep.value = ''
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
  <div class="relative">
    <!-- Full-page loading overlay while saving -->
    <div
      v-if="saving"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 px-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-sm rounded-lg border border-flix-gold/30 bg-neutral-950 p-6 text-center shadow-2xl">
        <div class="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-flix-gold/30 border-t-flix-red" />
        <p class="mt-4 text-base font-semibold text-white">{{ saveStep || 'កំពុងរក្សាទុក...' }}</p>
        <p class="mt-2 text-sm text-white/50">សូមរង់ចាំ — កុំបិទទំព័រ / Please wait</p>
      </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-3xl font-semibold">ភាពយន្ត / Movies</h1>
      <button
        v-if="editingId"
        class="rounded bg-white/10 px-3 py-2 text-sm"
        :disabled="saving"
        @click="resetForm"
      >
        Cancel edit
      </button>
    </div>

    <form
      class="mt-6 grid gap-4 rounded-sm border border-flix-gold/20 bg-neutral-900 p-4 md:grid-cols-2"
      @submit.prevent="saveMovie"
    >
      <input
        v-model="form.title"
        required
        placeholder="Title / ចំណងជើង *"
        class="rounded border border-white/10 bg-black px-3 py-2"
        :disabled="saving"
      />
      <input
        v-model="form.slug"
        placeholder="Slug (optional)"
        class="rounded border border-white/10 bg-black px-3 py-2"
        :disabled="saving"
      />
      <textarea
        v-model="form.description"
        placeholder="Description / ពិពណ៌នា"
        rows="3"
        class="rounded border border-white/10 bg-black px-3 py-2 md:col-span-2"
        :disabled="saving"
      />
      <select v-model="form.category" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving">
        <option value="">Select category / ជ្រើសប្រភេទ</option>
        <option v-for="c in categories" :key="c.id" :value="c.name">{{ c.name }}</option>
      </select>
      <input v-model="form.year" placeholder="Year / ឆ្នាំ" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving" />
      <input v-model="form.duration" placeholder="Duration e.g. 2h 10m" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving" />
      <input v-model="form.rating" placeholder="Rating / ពិន្ទុ" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving" />
      <input v-model="form.language" placeholder="Language" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving" />
      <input v-model="form.quality" placeholder="Quality" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving" />
      <input v-model="form.drive_video_id" placeholder="Drive Video ID (optional)" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving" />
      <input v-model="form.trailer_url" placeholder="Trailer URL" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving" />
      <input v-model="form.subtitle_url" placeholder="Subtitle .vtt URL" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving" />
      <select v-model="form.status" class="rounded border border-white/10 bg-black px-3 py-2" :disabled="saving">
        <option value="active">active</option>
        <option value="inactive">inactive</option>
        <option value="draft">draft</option>
      </select>

      <!-- Upload section -->
      <div class="md:col-span-2">
        <h2 class="mb-3 text-lg font-semibold text-flix-gold-soft">ផ្ទុកឯកសារ / Upload files</h2>
        <div class="grid gap-4 lg:grid-cols-3">
          <!-- VIDEO -->
          <label class="group relative flex cursor-pointer flex-col rounded-lg border-2 border-dashed border-white/20 bg-black/50 p-4 transition hover:border-flix-red hover:bg-black/70">
            <input
              type="file"
              accept="video/*"
              class="absolute inset-0 cursor-pointer opacity-0"
              :disabled="saving"
              @change="onVideoFile"
            />
            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-flix-red/20 text-xl text-flix-red">▶</div>
            <p class="font-semibold">1. វីដេអូ / Video</p>
            <p class="mt-1 text-xs text-white/50">MP4, MKV, MOV — upload to Google Drive</p>
            <p v-if="videoName" class="mt-3 truncate rounded bg-white/10 px-2 py-1 text-xs text-flix-gold-soft">
              ✓ {{ videoName }}
            </p>
            <p v-else class="mt-3 text-sm text-white/70">ចុចដើម្បីជ្រើសវីដេអូ</p>
            <button
              v-if="videoName"
              type="button"
              class="relative z-10 mt-2 self-start text-xs text-white/50 underline"
              @click.stop.prevent="clearVideo"
            >
              Clear
            </button>
          </label>

          <!-- THUMBNAIL / POSTER -->
          <div class="rounded-lg border-2 border-dashed border-white/20 bg-black/50 p-4 transition hover:border-flix-gold">
            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-flix-gold/20 text-xl text-flix-gold">🖼</div>
            <p class="font-semibold">2. រូបតូច / Thumbnail</p>
            <p class="mt-1 text-xs text-white/50">JPG, PNG, WEBP — poster card image</p>
            <div class="mt-3 flex gap-3">
              <div class="h-24 w-16 shrink-0 overflow-hidden rounded bg-neutral-800">
                <img v-if="posterPreview" :src="posterPreview" alt="Thumb" class="h-full w-full object-cover" />
                <div v-else class="flex h-full items-center justify-center text-[10px] text-white/30">Preview</div>
              </div>
              <div class="min-w-0 flex-1 space-y-2">
                <label class="relative block cursor-pointer rounded bg-flix-gold/15 px-3 py-2 text-center text-sm text-flix-gold-soft hover:bg-flix-gold/25">
                  ជ្រើសរូបតូច
                  <input type="file" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" :disabled="saving" @change="onPosterFile" />
                </label>
                <input
                  v-model="form.poster_url"
                  placeholder="or paste image URL"
                  class="w-full rounded border border-white/10 bg-black px-2 py-1.5 text-xs"
                  :disabled="saving"
                />
                <button v-if="form.poster" type="button" class="text-xs text-white/50 underline" @click="clearPoster">Clear file</button>
              </div>
            </div>
          </div>

          <!-- BANNER -->
          <div class="rounded-lg border-2 border-dashed border-white/20 bg-black/50 p-4 transition hover:border-flix-gold">
            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-flix-gold/20 text-xl text-flix-gold">▣</div>
            <p class="font-semibold">3. រូបធំ / Banner</p>
            <p class="mt-1 text-xs text-white/50">JPG, PNG — wide hero image</p>
            <div class="mt-3 space-y-2">
              <div class="aspect-video overflow-hidden rounded bg-neutral-800">
                <img v-if="bannerPreview" :src="bannerPreview" alt="Banner" class="h-full w-full object-cover" />
                <div v-else class="flex h-full items-center justify-center text-[10px] text-white/30">Preview</div>
              </div>
              <label class="relative block cursor-pointer rounded bg-flix-gold/15 px-3 py-2 text-center text-sm text-flix-gold-soft hover:bg-flix-gold/25">
                ជ្រើសរូបធំ
                <input type="file" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0" :disabled="saving" @change="onBannerFile" />
              </label>
              <input
                v-model="form.banner_url"
                placeholder="or paste banner URL"
                class="w-full rounded border border-white/10 bg-black px-2 py-1.5 text-xs"
                :disabled="saving"
              />
              <button v-if="form.banner" type="button" class="text-xs text-white/50 underline" @click="clearBanner">Clear file</button>
            </div>
          </div>
        </div>
      </div>

      <div class="md:col-span-2">
        <button
          type="submit"
          class="inline-flex min-w-[180px] items-center justify-center gap-2 rounded bg-flix-red px-6 py-3 font-semibold hover:bg-flix-red-dark disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="saving"
        >
          <span
            v-if="saving"
            class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
          />
          {{
            saving
              ? 'កំពុងរក្សាទុក...'
              : editingId
                ? 'Update Movie'
                : 'បន្ថែមភាពយន្ត / Add Movie'
          }}
        </button>
        <p v-if="message" class="mt-2 text-sm text-green-400">{{ message }}</p>
        <p v-if="error" class="mt-2 text-sm text-red-400">{{ error }}</p>
      </div>
    </form>

    <div class="mt-8 overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="border-b border-white/10 text-white/50">
          <tr>
            <th class="px-2 py-3">Thumb</th>
            <th class="px-2 py-3">Title</th>
            <th class="px-2 py-3">Category</th>
            <th class="px-2 py-3">Year</th>
            <th class="px-2 py-3">Status</th>
            <th class="px-2 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="movie in movies" :key="movie.id" class="border-b border-white/5">
            <td class="px-2 py-3">
              <div class="h-16 w-11 overflow-hidden rounded bg-neutral-800">
                <img
                  v-if="movie.poster_url || movie.banner_url"
                  :src="imageSrc(movie.poster_url || movie.banner_url)"
                  :alt="movie.title"
                  class="h-full w-full object-cover"
                  loading="lazy"
                />
              </div>
            </td>
            <td class="px-2 py-3">{{ movie.title }}</td>
            <td class="px-2 py-3">{{ movie.category }}</td>
            <td class="px-2 py-3">{{ movie.year }}</td>
            <td class="px-2 py-3">{{ movie.status }}</td>
            <td class="px-2 py-3">
              <button class="mr-2 text-flix-red" :disabled="saving" @click="editMovie(movie)">Edit</button>
              <button class="text-white/50 hover:text-red-400" :disabled="saving" @click="removeMovie(movie.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="loading" class="mt-4 text-white/50">Loading...</p>
      <p v-else-if="!movies.length" class="mt-4 text-white/50">No movies yet.</p>
    </div>
  </div>
</template>
