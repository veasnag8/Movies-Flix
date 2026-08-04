<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useMovieStore } from '../stores/movies'
import { kh } from '../i18n/kh'

const route = useRoute()
const router = useRouter()
const store = useMovieStore()

const videoRef = ref(null)
const showControls = ref(true)
let hideTimer = null
let saveTimer = null

const movie = computed(() => store.current)

const isDirectMp4 = computed(() => {
  const url = movie.value?.stream_url || ''
  return url.includes('.mp4') || url.includes('gtv-videos-bucket')
})

const playerUrl = computed(() => {
  if (!movie.value) return ''
  if (isDirectMp4.value) return movie.value.stream_url
  return movie.value.embed_url || movie.value.stream_url
})

onMounted(async () => {
  await store.fetchMovie(route.params.id)
  await store.fetchContinueWatching()

  const history = store.continueWatching.find(
    (item) => String(item.movie_id) === String(route.params.id)
  )
  if (history && videoRef.value && Number(history.progress) > 0) {
    videoRef.value.currentTime = Number(history.progress)
  }

  // For Drive iframe playback, persist a lightweight continue-watching ping
  if (movie.value && !isDirectMp4.value) {
    store.saveProgress(movie.value.id, Number(history?.progress || 1))
  }
})

onBeforeUnmount(() => {
  clearTimeout(hideTimer)
  clearTimeout(saveTimer)
  if (videoRef.value && movie.value) {
    store.saveProgress(movie.value.id, videoRef.value.currentTime || 0)
  }
})

function onTimeUpdate() {
  if (!videoRef.value || !movie.value) return
  clearTimeout(saveTimer)
  saveTimer = setTimeout(() => {
    store.saveProgress(movie.value.id, videoRef.value.currentTime)
  }, 4000)
}

function revealControls() {
  showControls.value = true
  clearTimeout(hideTimer)
  hideTimer = setTimeout(() => {
    showControls.value = false
  }, 2500)
}

function toggleFullscreen() {
  const el = videoRef.value || document.documentElement
  if (document.fullscreenElement) {
    document.exitFullscreen()
  } else {
    el.requestFullscreen?.()
  }
}
</script>

<template>
  <div class="relative min-h-screen bg-black" @mousemove="revealControls">
    <div v-if="!movie" class="flex min-h-screen items-center justify-center text-white/60">
      {{ kh.loadingShort }}
    </div>

    <template v-else>
      <video
        v-if="isDirectMp4"
        ref="videoRef"
        class="h-screen w-full bg-black object-contain"
        :src="playerUrl"
        controls
        autoplay
        @timeupdate="onTimeUpdate"
      >
        <track
          v-if="movie.subtitle_url"
          kind="subtitles"
          :src="movie.subtitle_url"
          srclang="km"
          label="ខ្មែរ"
          default
        />
      </video>

      <iframe
        v-else-if="playerUrl"
        class="h-screen w-full border-0"
        :src="playerUrl"
        allow="autoplay; fullscreen"
        allowfullscreen
      />

      <div v-else class="flex min-h-screen items-center justify-center text-white/70">
        មិនមានវីដេអូសម្រាប់ចំណងជើងនេះ។
      </div>

      <div
        class="pointer-events-none absolute inset-x-0 top-0 bg-gradient-to-b from-black/80 to-transparent p-4 transition"
        :class="showControls ? 'opacity-100' : 'opacity-0'"
      >
        <div class="pointer-events-auto flex items-center gap-4">
          <button class="rounded bg-white/10 px-3 py-2 text-sm" @click="router.back()">← ត្រឡប់</button>
          <div>
            <h1 class="text-lg font-semibold">{{ movie.title }}</h1>
            <p class="text-xs text-white/60">{{ kh.continueWatching }}</p>
          </div>
          <button class="ml-auto rounded bg-white/10 px-3 py-2 text-sm" @click="toggleFullscreen">
            ពេញអេក្រង់
          </button>
          <RouterLink
            :to="{ name: 'movie', params: { id: movie.slug || movie.id } }"
            class="rounded bg-white/10 px-3 py-2 text-sm"
          >
            {{ kh.moreInfo }}
          </RouterLink>
        </div>
      </div>
    </template>
  </div>
</template>
