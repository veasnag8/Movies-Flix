<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  movie: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <RouterLink
    :to="{ name: 'movie', params: { id: movie.slug || movie.id } }"
    class="group relative block w-36 overflow-hidden rounded-sm bg-flix-card transition duration-300 hover:-translate-y-1 hover:scale-[1.04] hover:z-10 md:w-44"
  >
    <div class="aspect-[2/3] overflow-hidden bg-neutral-900">
      <img
        v-if="movie.poster_url"
        :src="movie.poster_url"
        :alt="movie.title"
        class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
        loading="lazy"
      />
      <div v-else class="flex h-full items-center justify-center bg-neutral-800 p-3 text-center text-sm text-white/60">
        {{ movie.title }}
      </div>
    </div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black via-black/70 to-transparent p-3 opacity-0 transition group-hover:opacity-100">
      <p class="line-clamp-2 text-sm font-semibold">{{ movie.title }}</p>
      <p class="mt-1 text-xs text-white/70">{{ movie.year }} · ★ {{ movie.rating }}</p>
    </div>
  </RouterLink>
</template>
