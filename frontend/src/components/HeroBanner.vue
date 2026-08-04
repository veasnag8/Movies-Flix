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
  <section class="relative min-h-[88vh] overflow-hidden">
    <img
      :src="movie.banner_url || movie.poster_url"
      :alt="movie.title"
      class="hero-kenburns absolute inset-0 h-full w-full object-cover"
    />
    <div class="absolute inset-0 bg-gradient-to-r from-black via-black/75 to-transparent" />
    <div class="absolute inset-0 bg-gradient-to-t from-flix-dark via-transparent to-black/40" />

    <div class="relative z-10 mx-auto flex min-h-[88vh] max-w-7xl items-end px-4 pb-24 pt-28 md:px-8">
      <div class="fade-up max-w-xl">
        <p class="mb-2 text-sm uppercase tracking-[0.35em] text-flix-red">Featured</p>
        <h1 class="font-display text-5xl leading-none tracking-wide md:text-7xl">
          {{ movie.title }}
        </h1>
        <p class="mt-4 line-clamp-3 text-base text-white/80 md:text-lg">
          {{ movie.description }}
        </p>
        <div class="mt-3 flex flex-wrap gap-3 text-sm text-white/70">
          <span>★ {{ movie.rating }}</span>
          <span>{{ movie.year }}</span>
          <span>{{ movie.duration }}</span>
          <span>{{ movie.quality }}</span>
          <span>{{ movie.category }}</span>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
          <RouterLink
            :to="{ name: 'watch', params: { id: movie.id } }"
            class="rounded bg-flix-red px-6 py-3 text-sm font-bold uppercase tracking-wide hover:bg-flix-red-dark"
          >
            Watch Now
          </RouterLink>
          <RouterLink
            :to="{ name: 'movie', params: { id: movie.slug || movie.id } }"
            class="rounded bg-white/20 px-6 py-3 text-sm font-semibold backdrop-blur hover:bg-white/30"
          >
            More Info
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>
