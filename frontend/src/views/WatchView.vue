<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
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
const speedMenuOpen = ref(false)
const qualityMenuOpen = ref(false)
const videoError = ref('')
let hideTimer = null
let saveTimer = null

const movie = computed(() => store.current)

const isDirectMp4 = computed(() => {
  const url = movie.value?.stream_url || ''
  return url.includes('.mp4') || url.includes('gtv-videos-bucket')
})

const isDriveVideo = computed(() => {
  const url = movie.value?.stream_url || movie.value?.embed_url || ''
  return url.includes('drive.google.com')
})

const playerUrl = computed(() => {
  if (!movie.value) return ''
  if (isDirectMp4.value) return movie.value.stream_url
  if (movie.value.embed_url) return movie.value.embed_url
  if (movie.value.stream_url && !isDriveVideo.value) return movie.value.stream_url
  return ''
})

const isComingSoon = computed(() => movie.value && !playerUrl.value)
const canUseVideoTag = computed(() => Boolean(movie.value?.stream_url) && (isDirectMp4.value || !isDriveVideo.value))

const progressPercent = computed(() => {
  if (!duration.value) return 0
  return Math.min(100, (currentTime.value / duration.value) * 100)
})

const hasTiming = computed(() => duration.value > 0)
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

function closeMenus() {
  speedMenuOpen.value = false
  qualityMenuOpen.value = false
}

function revealControls() {
  showControls.value = true
  clearTimeout(hideTimer)
  hideTimer = setTimeout(() => {
    if (isPlaying.value) showControls.value = false
  }, 2200)
}

function onLoadedMetadata() {
  if (!videoRef.value) return
  videoError.value = ''
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
  videoError.value = ''
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
  if (videoRef.value) videoRef.value.playbackRate = rate
  speedMenuOpen.value = false
}

function setQuality(label) {
  selectedQuality.value = label
  qualityLabel.value = label === 'auto' ? 'Auto' : label
  qualityMenuOpen.value = false
}

function toggleSpeedMenu() {
  speedMenuOpen.value = !speedMenuOpen.value
  qualityMenuOpen.value = false
  revealControls()
}

function toggleQualityMenu() {
  qualityMenuOpen.value = !qualityMenuOpen.value
  speedMenuOpen.value = false
  revealControls()
}

async function toggleFullscreen() {
  const el = playerShellRef.value || document.documentElement
  if (document.fullscreenElement) {
    await document.exitFullscreen()
  } else {
    await el.requestFullscreen?.()
  }
}

function onBodyClick() {
  closeMenus()
}

function onVideoError() {
  if (movie.value?.embed_url && !videoError.value) {
    videoError.value = 'Browser playback failed. Trying the Drive preview instead.'
    return
  }

  videoError.value = 'This video cannot be played directly in the browser. Please check the Drive sharing settings or upload the file again through the admin panel.'
  isPlaying.value = false
  showControls.value = true
}

async function onWheel(event) {
  if (!videoRef.value) return
  skip(event.deltaY < 0 ? 5 : -5)
}

onMounted(async () => {
  document.addEventListener('click', onBodyClick)
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

onBeforeUnmount(() => {
  document.removeEventListener('click', onBodyClick)
  clearTimeout(hideTimer)
  clearTimeout(saveTimer)
  if (videoRef.value && movie.value && !isComingSoon.value) {
    store.saveProgress(movie.value.id, videoRef.value.currentTime || 0)
  }
})
</script>

<template>
  <div class="min-h-screen bg-black text-white">
    <div v-if="!movie" class="flex min-h-screen items-center justify-center text-white/60">
      {{ kh.loadingShort }}
    </div>

    <template v-else>
      <div class="mx-auto flex max-w-[1600px] flex-col gap-4 px-2 pb-4 pt-2 sm:px-4 sm:pt-4">
        <div class="flex items-center gap-2 text-xs text-white/60 sm:text-sm">
          <button class="rounded-full bg-white/10 px-3 py-1.5 hover:bg-white/20" @click="router.back()">Back</button>
          <RouterLink :to="{ name: 'movie', params: { id: movie.slug || movie.id } }" class="rounded-full bg-white/10 px-3 py-1.5 hover:bg-white/20">
            Details
          </RouterLink>
          <span v-if="movie.category" class="rounded-full border border-flix-gold/30 bg-flix-gold/10 px-3 py-1.5 text-flix-gold-soft">
            {{ movie.category }}
          </span>
        </div>

        <div ref="playerShellRef" class="overflow-hidden rounded-xl border border-white/10 bg-[#0b0b0b] shadow-2xl shadow-black/60">
          <div class="relative aspect-video w-full bg-black" @mousemove="revealControls" @touchstart="revealControls" @wheel.prevent="onWheel">
            <video
              v-if="canUseVideoTag"
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
              @error="onVideoError"
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

            <div
              v-if="videoError"
              class="absolute inset-x-0 bottom-[4.5rem] mx-auto max-w-3xl rounded-xl border border-red-500/30 bg-black/85 px-4 py-3 text-sm text-red-200 shadow-2xl shadow-black/40"
            >
              {{ videoError }}
            </div>

            <div
              class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/95 via-black/20 to-transparent transition-opacity duration-300"
              :class="showControls ? 'opacity-100' : 'opacity-0'"
            >
              <div class="pointer-events-auto flex h-full flex-col justify-end">
                <div class="px-2 pb-2 sm:px-3 sm:pb-3">
                  <div class="rounded-xl bg-black/55 px-3 py-2.5 backdrop-blur-md sm:px-4 sm:py-3">
                    <div class="mb-2 h-1.5 overflow-hidden rounded-full bg-white/15">
                      <div class="h-full rounded-full bg-flix-red" :style="{ width: `${progressPercent}%` }" />
                    </div>

                    <div class="mb-2 flex items-center gap-2 text-xs text-white/75 sm:text-sm">
                      <p class="truncate font-semibold text-white">{{ movie.title }}</p>
                      <span class="hidden shrink-0 text-white/30 sm:inline">•</span>
                      <p class="shrink-0">
                        {{ hasTiming ? `${timeLabel} / ${durationLabel}` : 'Loading time...' }}
                      </p>
                    </div>

                    <input
                      v-if="isDirectMp4"
                      class="watch-range mb-2 w-full"
                      type="range"
                      min="0"
                      :max="duration || 0"
                      step="0.1"
                      :value="currentTime"
                      @input="seekTo"
                    />

                    <div class="watch-controls flex items-center gap-2 text-sm">
                      <button class="watch-icon-btn" @click="togglePlay" :aria-label="isPlaying ? 'Pause' : 'Play'">
                        <span v-if="isPlaying">❚❚</span>
                        <span v-else>▶</span>
                      </button>
                      <button class="watch-icon-btn" @click="skip(-10)" aria-label="Back 10 seconds">-10</button>
                      <button class="watch-icon-btn" @click="skip(10)" aria-label="Forward 10 seconds">+10</button>

                      <div class="relative">
                        <button class="watch-chip" @click.stop="toggleSpeedMenu">
                          <span class="watch-label">SPEED</span>
                          <span class="font-semibold text-white">{{ playbackRate }}x</span>
                          <span class="watch-caret">⌄</span>
                        </button>
                        <div
                          v-if="speedMenuOpen"
                          class="watch-menu absolute bottom-[calc(100%+0.45rem)] left-0 z-20 w-32 overflow-hidden"
                        >
                          <button v-for="rate in [0.75, 1, 1.25, 1.5, 1.75, 2]" :key="rate" class="menu-item" @click.stop="setSpeed(rate)">
                            {{ rate }}x
                          </button>
                        </div>
                      </div>

                      <div class="relative">
                        <button class="watch-chip" @click.stop="toggleQualityMenu">
                          <span class="watch-label">QUALITY</span>
                          <span class="font-semibold text-white">{{ qualityLabel }}</span>
                          <span class="watch-caret">⌄</span>
                        </button>
                        <div
                          v-if="qualityMenuOpen"
                          class="watch-menu absolute bottom-[calc(100%+0.45rem)] left-0 z-20 w-36 overflow-hidden"
                        >
                          <button class="menu-item" @click.stop="setQuality('auto')">Auto</button>
                          <button v-if="movie.quality" class="menu-item" @click.stop="setQuality(movie.quality)">
                            {{ movie.quality }}
                          </button>
                        </div>
                      </div>

                      <button class="watch-icon-btn ml-auto watch-fullscreen" @click="toggleFullscreen" aria-label="Fullscreen">
                        ⤢
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
          <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm sm:p-5">
            <h2 class="text-lg font-bold text-flix-gold-soft sm:text-xl">Playback</h2>
            <div class="mt-3 grid gap-3 text-sm text-white/75 sm:grid-cols-2">
              <div class="rounded-xl bg-black/30 p-4">
                <p class="text-white/40">Current time</p>
                <p class="mt-1 text-lg font-semibold text-white">{{ hasTiming ? timeLabel : 'Loading...' }}</p>
              </div>
              <div class="rounded-xl bg-black/30 p-4">
                <p class="text-white/40">Duration</p>
                <p class="mt-1 text-lg font-semibold text-white">{{ hasTiming ? durationLabel : 'Loading...' }}</p>
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

          <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm sm:p-5">
            <h2 class="text-lg font-bold text-flix-gold-soft sm:text-xl">Movie Info</h2>
            <p class="mt-3 line-clamp-4 text-sm leading-relaxed text-white/70">
              {{ movie.description }}
            </p>
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
