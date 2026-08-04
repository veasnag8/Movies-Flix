<script setup>
import { RouterLink } from 'vue-router'
import { kh } from '../i18n/kh'

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
    <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-transparent" />
    <div class="absolute inset-0 bg-gradient-to-t from-flix-dark via-transparent to-black/50" />
    <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-flix-gold via-flix-red to-transparent opacity-80" />

    <div class="relative z-10 mx-auto flex min-h-[88vh] max-w-7xl items-end px-4 pb-24 pt-28 md:px-8">
      <div class="fade-up max-w-xl">
        <p class="mb-2 text-sm font-semibold tracking-[0.2em] text-flix-gold">{{ kh.featured }}</p>
        <div class="gold-line mb-4" />
        <h1 class="font-display text-5xl leading-none tracking-wide md:text-7xl">
          {{ movie.title }}
        </h1>
        <p class="mt-4 line-clamp-3 text-base text-white/80 md:text-lg">
          {{ movie.description }}
        </p>
        <div class="mt-3 flex flex-wrap gap-3 text-sm text-white/70">
          <span class="text-flix-gold">★ {{ movie.rating }}</span>
          <span>{{ movie.year }}</span>
          <span>{{ movie.duration }}</span>
          <span>{{ movie.quality }}</span>
          <span>{{ movie.category }}</span>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
          <RouterLink
            :to="{ name: 'watch', params: { id: movie.id } }"
            class="rounded bg-flix-red px-6 py-3 text-sm font-bold hover:bg-flix-red-dark"
          >
            {{ kh.watchNow }}
          </RouterLink>
          <RouterLink
            :to="{ name: 'movie', params: { id: movie.slug || movie.id } }"
            class="rounded border border-flix-gold/40 bg-black/30 px-6 py-3 text-sm font-semibold text-flix-gold-soft backdrop-blur hover:bg-flix-gold/10"
          >
            {{ kh.moreInfo }}
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>
