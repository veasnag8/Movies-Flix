<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useMovieStore } from '../stores/movies'
import { kh } from '../i18n/kh'

const route = useRoute()
const router = useRouter()
const store = useMovieStore()

const videoRef = ref(null)
const playerShellRef = ref(null)
const showControls = ref(true)
const isPlaying = ref(false)
const currentTime = ref(0)
const duration = ref(0)
const playbackRate = ref(1)
const qualityLabel = ref('Auto')
const selectedQuality = ref('auto')
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

const isComingSoon = computed(() => movie.value && !playerUrl.value)

const progressPercent = computed(() => {
  if (!duration.value) return 0
  return Math.min(100, (currentTime.value / duration.value) * 100)
})

const timeLabel = computed(() => formatTime(currentTime.value))
const durationLabel = computed(() => formatTime(duration.value))

function formatTime(seconds) {
  const value = Number.isFinite(seconds) ? Math.max(0, seconds) : 0
  const hrs = Math.floor(value / 3600)
  const mins = Math.floor((value % 3600) / 60)
  const secs = Math.floor(value % 60)

  if (hrs > 0) {
    return `${hrs}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }

  return `${mins}:${String(secs).padStart(2, '0')}`
}

function revealControls() {
  showControls.value = true
  clearTimeout(hideTimer)
  hideTimer = setTimeout(() => {
    if (isPlaying.value) {
      showControls.value = false
    }
  }, 2500)
}

async function onLoadedMetadata() {
  if (!videoRef.value) return

  duration.value = videoRef.value.duration || 0

  const history = store.continueWatching.find(
    (item) => String(item.movie_id) === String(route.params.id)
  )

  if (history && Number(history.progress) > 0) {
    videoRef.value.currentTime = Number(history.progress)
    currentTime.value = Number(history.progress)
  }
}

function onTimeUpdate() {
  if (!videoRef.value || !movie.value) return

  currentTime.value = videoRef.value.currentTime || 0
  duration.value = videoRef.value.duration || duration.value

  clearTimeout(saveTimer)
  saveTimer = setTimeout(() => {
    store.saveProgress(movie.value.id, videoRef.value.currentTime)
  }, 4000)
}

function onPlay() {
  isPlaying.value = true
  revealControls()
}

function onPause() {
  isPlaying.value = false
  showControls.value = true
}

function togglePlay() {
  if (!videoRef.value) return
  if (videoRef.value.paused) {
    videoRef.value.play()
  } else {
    videoRef.value.pause()
  }
}

function seekTo(event) {
  if (!videoRef.value) return
  const value = Number(event.target.value)
  if (Number.isNaN(value)) return
  videoRef.value.currentTime = value
  currentTime.value = value
}

function skip(seconds) {
  if (!videoRef.value) return
  videoRef.value.currentTime = Math.max(0, (videoRef.value.currentTime || 0) + seconds)
}

function setSpeed(rate) {
  playbackRate.value = rate
  if (videoRef.value) {
    videoRef.value.playbackRate = rate
  }
}

function setQuality(label) {
  selectedQuality.value = label
  qualityLabel.value = label === 'auto' ? 'Auto' : label
}

async function toggleFullscreen() {
  const el = playerShellRef.value || document.documentElement
  if (document.fullscreenElement) {
    await document.exitFullscreen()
    return
  }

  await el.requestFullscreen?.()
}

async function onWheel(event) {
  if (!videoRef.value) return
  if (event.deltaY < 0) {
    skip(5)
  } else {
    skip(-5)
  }
}

onMounted(async () => {
  await store.fetchMovie(route.params.id)
  await store.fetchContinueWatching()

  if (movie.value && !isDirectMp4.value && !isComingSoon.value) {
    store.saveProgress(movie.value.id, Number(1))
  }

  await nextTick()

  if (videoRef.value) {
    videoRef.value.playbackRate = playbackRate.value
  }
})

watch(
  () => movie.value,
  async () => {
    await nextTick()
    if (videoRef.value) {
      videoRef.value.playbackRate = playbackRate.value
    }
  }
)

onBeforeUnmount(() => {
  clearTimeout(hideTimer)
  clearTimeout(saveTimer)
  if (videoRef.value && movie.value && !isComingSoon.value) {
    store.saveProgress(movie.value.id, videoRef.value.currentTime || 0)
  }
})
</script>

<template>
  <div class="min-h-screen bg-[radial-gradient(circle_at_top,#2a0b0e_0%,#080808_42%,#000_100%)] text-white">
    <div v-if="!movie" class="flex min-h-screen items-center justify-center text-white/60">
      {{ kh.loadingShort }}
    </div>

    <template v-else>
      <div class="mx-auto flex max-w-7xl flex-col gap-6 px-3 py-4 sm:px-4 md:px-8 md:py-6">
        <div class="flex flex-wrap items-center gap-3 text-sm text-white/60">
          <button class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 hover:bg-white/10" @click="router.back()">
            Back
          </button>
          <RouterLink :to="{ name: 'movie', params: { id: movie.slug || movie.id } }" class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 hover:bg-white/10">
            Details
          </RouterLink>
          <span v-if="movie.category" class="rounded-full border border-flix-gold/30 px-3 py-1.5 text-flix-gold-soft">
            {{ movie.category }}
          </span>
        </div>

        <div ref="playerShellRef" class="overflow-hidden rounded-2xl border border-white/10 bg-black shadow-2xl shadow-black/40">
          <div class="relative aspect-video w-full bg-black" @mousemove="revealControls" @touchstart="revealControls" @wheel.prevent="onWheel">
            <video
              v-if="isDirectMp4"
              ref="videoRef"
              class="h-full w-full object-contain"
              :src="playerUrl"
              :poster="movie.banner_url || movie.poster_url"
              autoplay
              playsinline
              @loadedmetadata="onLoadedMetadata"
              @timeupdate="onTimeUpdate"
              @play="onPlay"
              @pause="onPause"
            >
              <track
                v-if="movie.subtitle_url"
                kind="subtitles"
                :src="movie.subtitle_url"
                srclang="km"
                label="Khmer"
                default
              />
            </video>

            <iframe
              v-else-if="playerUrl"
              class="h-full w-full border-0"
              :src="playerUrl"
              allow="autoplay; fullscreen"
              allowfullscreen
            />

            <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-black via-neutral-950 to-black text-white/70">
              Coming Soon
            </div>

            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent transition-opacity duration-300" :class="showControls ? 'opacity-100' : 'opacity-0'">
              <div class="pointer-events-auto flex h-full flex-col justify-between p-4 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                  <div class="max-w-3xl">
                    <p class="text-xs uppercase tracking-[0.35em] text-flix-gold-soft/80">Now Playing</p>
                    <h1 class="mt-2 text-2xl font-black uppercase tracking-wide sm:text-4xl">{{ movie.title }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-white/65 sm:text-base">
                      Use the custom player controls below for speed, quality, and fullscreen.
                    </p>
                  </div>
                  <div class="hidden rounded-full border border-white/10 bg-black/50 px-4 py-2 text-sm text-white/80 sm:block">
                    {{ timeLabel }} / {{ durationLabel }}
                  </div>
                </div>

                <div class="space-y-3">
                  <div class="h-1.5 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-gradient-to-r from-flix-red to-flix-gold" :style="{ width: `${progressPercent}%` }" />
                  </div>

                  <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <button class="rounded-full bg-white/10 px-3 py-2 text-sm hover:bg-white/20" @click="togglePlay">
                      {{ isPlaying ? 'Pause' : 'Play' }}
                    </button>
                    <button class="rounded-full bg-white/10 px-3 py-2 text-sm hover:bg-white/20" @click="skip(-10)">-10s</button>
                    <button class="rounded-full bg-white/10 px-3 py-2 text-sm hover:bg-white/20" @click="skip(10)">+10s</button>

                    <div class="flex items-center gap-2 rounded-full bg-white/10 px-3 py-2">
                      <span class="text-xs uppercase tracking-widest text-white/50">Speed</span>
                      <select
                        :value="playbackRate"
                        class="bg-transparent text-sm outline-none"
                        @change="setSpeed(Number($event.target.value))"
                      >
                        <option :value="0.75">0.75x</option>
                        <option :value="1">1x</option>
                        <option :value="1.25">1.25x</option>
                        <option :value="1.5">1.5x</option>
                        <option :value="1.75">1.75x</option>
                        <option :value="2">2x</option>
                      </select>
                    </div>

                    <div class="flex items-center gap-2 rounded-full bg-white/10 px-3 py-2">
                      <span class="text-xs uppercase tracking-widest text-white/50">Quality</span>
                      <select
                        :value="selectedQuality"
                        class="bg-transparent text-sm outline-none"
                        @change="setQuality($event.target.value)"
                      >
                        <option value="auto">Auto</option>
                        <option v-if="movie.quality" :value="movie.quality">{{ movie.quality }}</option>
                      </select>
                    </div>

                    <button class="ml-auto rounded-full border border-white/15 bg-flix-gold/10 px-4 py-2 text-sm font-semibold text-flix-gold-soft hover:bg-flix-gold/20" @click="toggleFullscreen">
                      Fullscreen
                    </button>
                  </div>

                  <input
                    v-if="isDirectMp4"
                    class="w-full accent-flix-red"
                    type="range"
                    min="0"
                    :max="duration || 0"
                    step="0.1"
                    :value="currentTime"
                    @input="seekTo"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
          <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
            <h2 class="text-xl font-bold text-flix-gold-soft">Playback</h2>
            <div class="mt-3 grid gap-3 text-sm text-white/70 sm:grid-cols-2">
              <div class="rounded-xl bg-black/30 p-4">
                <p class="text-white/40">Current time</p>
                <p class="mt-1 text-lg font-semibold text-white">{{ timeLabel }}</p>
              </div>
              <div class="rounded-xl bg-black/30 p-4">
                <p class="text-white/40">Duration</p>
                <p class="mt-1 text-lg font-semibold text-white">{{ durationLabel }}</p>
              </div>
              <div class="rounded-xl bg-black/30 p-4">
                <p class="text-white/40">Speed</p>
                <p class="mt-1 text-lg font-semibold text-white">{{ playbackRate }}x</p>
              </div>
              <div class="rounded-xl bg-black/30 p-4">
                <p class="text-white/40">Quality</p>
                <p class="mt-1 text-lg font-semibold text-white">{{ qualityLabel }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
            <h2 class="text-xl font-bold text-flix-gold-soft">Movie Info</h2>
            <p class="mt-3 line-clamp-4 text-sm leading-relaxed text-white/70">{{ movie.description }}</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
              <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1">{{ movie.year }}</span>
              <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1">{{ movie.language }}</span>
              <span class="rounded-full border border-white/10 bg-black/30 px-3 py-1">{{ movie.quality }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
