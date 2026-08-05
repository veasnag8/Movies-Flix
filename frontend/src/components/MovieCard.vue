<script setup>
import { computed, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
  movie: {
    type: Object,
    required: true,
  },
})

function toThumb(url) {
  if (!url) return ''
  const driveId =
    url.match(/[?&]id=([^&]+)/)?.[1] ||
    url.match(/\/d\/([^/]+)/)?.[1] ||
    url.match(/thumbnail\/?[^?]*[?&]id=([^&]+)/)?.[1] ||
    url.match(/uc\?[^?]*[?&]id=([^&]+)/)?.[1]
  if (driveId && url.includes('drive.google.com')) {
    return `https://drive.google.com/thumbnail?id=${driveId}&sz=w400`
  }
  if (driveId && url.includes('googleusercontent.com')) {
    return `https://drive.google.com/thumbnail?id=${driveId}&sz=w400`
  }
  return url
}

const thumb = computed(() => toThumb(props.movie.poster_url || props.movie.banner_url || ''))
const hasVideo = computed(() => Boolean(props.movie.drive_video_id || props.movie.stream_url || props.movie.embed_url))
const thumbFailed = ref(false)

watch(
  () => thumb.value,
  () => {
    thumbFailed.value = false
  }
)
</script>

<template>
  <RouterLink
    :to="{ name: 'movie', params: { id: movie.slug || movie.id } }"
    class="group relative block w-[7.25rem] overflow-hidden rounded-sm bg-flix-card transition duration-300 hover:-translate-y-1 hover:z-10 sm:w-36 md:w-44"
  >
    <div class="aspect-[2/3] overflow-hidden bg-neutral-900">
      <img
        v-if="thumb && !thumbFailed"
        :src="thumb"
        :alt="movie.title"
        class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
        loading="lazy"
        @error="thumbFailed = true"
      />
      <div v-else class="flex h-full items-center justify-center bg-neutral-800 p-2 text-center text-[11px] leading-snug text-white/60 sm:p-3 sm:text-sm">
        {{ movie.title }}
      </div>
    </div>
    <div
      v-if="!hasVideo"
      class="absolute left-2 top-2 rounded bg-flix-gold px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-black shadow sm:text-xs"
    >
      Coming Soon
    </div>
    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black via-black/80 to-transparent p-2 opacity-100 transition sm:p-3 md:opacity-0 md:group-hover:opacity-100">
      <p class="line-clamp-2 text-[11px] font-semibold leading-snug sm:text-sm">{{ movie.title }}</p>
      <p class="mt-0.5 text-[10px] text-white/70 sm:text-xs">{{ movie.year }} · ★ {{ movie.rating }}</p>
    </div>
  </RouterLink>
</template>
