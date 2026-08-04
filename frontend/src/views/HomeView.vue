<script setup>
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import HeroBanner from '../components/HeroBanner.vue'
import MovieRow from '../components/MovieRow.vue'
import { useAuthStore } from '../stores/auth'
import { useMovieStore } from '../stores/movies'
import { kh } from '../i18n/kh'

const auth = useAuthStore()
const movies = useMovieStore()

onMounted(async () => {
  await movies.fetchHome()
  if (auth.isAuthenticated) {
    await movies.fetchContinueWatching()
  }
})
</script>

<template>
  <div>
    <div v-if="movies.loading && !movies.meta.hero" class="flex min-h-screen items-center justify-center text-white/60">
      {{ kh.loading }}
    </div>

    <template v-else>
      <HeroBanner
        v-if="movies.meta.hero"
        :movie="movies.meta.hero"
      />
      <section v-else class="relative flex min-h-[70vh] items-center justify-center px-4 text-center">
        <div class="fade-up">
          <div class="brand-mark items-center">
            <span class="latin text-4xl sm:text-6xl md:text-7xl">{{ kh.brand }}</span>
            <span class="khmer mt-2 tracking-[0.15em]">{{ kh.brandKh }}</span>
          </div>
          <div class="mx-auto mt-4 gold-line" />
          <p class="mt-4 text-flix-gold-soft/80">{{ kh.tagline }}</p>
          <p class="mt-2 text-white/60">{{ kh.connectSheets }}</p>
        </div>
      </section>

      <div class="-mt-8 space-y-1 pb-12 sm:-mt-12 sm:space-y-2 sm:pb-16 md:-mt-16">
        <MovieRow
          v-if="auth.isAuthenticated && movies.continueWatching.length"
          :title="kh.continueWatching"
          :movies="movies.continueWatching.map((i) => i.movie)"
        />
        <MovieRow :title="kh.trendingNow" :movies="movies.meta.trending" />
        <MovieRow :title="kh.latestMovies" :movies="movies.meta.latest" />
        <MovieRow :title="kh.recommended" :movies="movies.meta.recommended" />

        <section v-if="movies.categories.length" class="px-4 md:px-8">
          <h2 class="mb-3 text-lg font-semibold text-flix-gold-soft sm:text-xl md:text-2xl">{{ kh.categories }}</h2>
          <div class="row-scroll">
            <RouterLink
              v-for="category in movies.categories"
              :key="category.id"
              :to="{ name: 'search', query: { category: category.name } }"
              class="relative h-24 w-40 shrink-0 overflow-hidden border border-flix-gold/20 sm:h-28 sm:w-48 md:h-32 md:w-56"
            >
              <img
                v-if="category.image"
                :src="category.image"
                :alt="category.name"
                class="h-full w-full object-cover"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-black/20" />
              <span class="absolute inset-0 flex items-center justify-center px-2 text-center text-sm font-semibold sm:text-lg">
                {{ category.name }}
              </span>
            </RouterLink>
          </div>
        </section>

        <MovieRow
          v-for="category in movies.categories"
          :key="`row-${category.id}`"
          :title="category.name"
          :movies="movies.movies.filter((m) => m.category === category.name)"
        />
      </div>

      <p v-if="movies.error" class="px-4 pb-8 text-center text-sm text-red-400 md:px-8">
        {{ movies.error }}
      </p>
    </template>
  </div>
</template>
