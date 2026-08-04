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
  <section class="relative min-h-[72vh] overflow-hidden sm:min-h-[80vh] md:min-h-[88vh]">
    <img
      :src="movie.banner_url || movie.poster_url"
      :alt="movie.title"
      class="hero-kenburns absolute inset-0 h-full w-full object-cover object-center"
    />
    <div class="absolute inset-0 bg-gradient-to-t from-flix-dark via-black/55 to-black/40 md:bg-gradient-to-r md:from-black md:via-black/75 md:to-transparent" />
    <div class="absolute inset-0 bg-gradient-to-t from-flix-dark via-transparent to-transparent md:via-transparent" />
    <div class="absolute inset-y-0 left-0 hidden w-1 bg-gradient-to-b from-flix-gold via-flix-red to-transparent opacity-80 sm:block" />

    <div class="relative z-10 mx-auto flex min-h-[72vh] max-w-7xl items-end px-4 pb-16 pt-24 sm:min-h-[80vh] sm:pb-20 sm:pt-28 md:min-h-[88vh] md:px-8 md:pb-24">
      <div class="fade-up w-full max-w-2xl">
        <p class="font-khmer mb-1 text-xs text-flix-gold sm:mb-2 sm:text-sm">{{ kh.featured }}</p>
        <div class="gold-line mb-3 sm:mb-4" />
        <h1 class="hero-title text-[1.85rem] sm:text-4xl md:text-6xl lg:text-7xl">
          {{ movie.title }}
        </h1>
        <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-white/80 sm:mt-4 sm:text-base md:text-lg">
          {{ movie.description }}
        </p>
        <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-xs text-white/70 sm:text-sm">
          <span class="text-flix-gold">★ {{ movie.rating }}</span>
          <span>{{ movie.year }}</span>
          <span>{{ movie.duration }}</span>
          <span>{{ movie.quality }}</span>
          <span>{{ movie.category }}</span>
        </div>
        <div class="mt-5 flex w-full flex-col gap-2 sm:mt-8 sm:w-auto sm:flex-row sm:flex-wrap sm:gap-3">
          <RouterLink
            :to="{ name: 'watch', params: { id: movie.id } }"
            class="rounded bg-flix-red px-5 py-3 text-center text-sm font-bold hover:bg-flix-red-dark"
          >
            {{ kh.watchNow }}
          </RouterLink>
          <RouterLink
            :to="{ name: 'movie', params: { id: movie.slug || movie.id } }"
            class="rounded border border-flix-gold/40 bg-black/40 px-5 py-3 text-center text-sm font-semibold text-flix-gold-soft backdrop-blur hover:bg-flix-gold/10"
          >
            {{ kh.moreInfo }}
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>
