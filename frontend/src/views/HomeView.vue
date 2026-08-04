<script setup>
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import HeroBanner from '../components/HeroBanner.vue'
import MovieRow from '../components/MovieRow.vue'
import { useAuthStore } from '../stores/auth'
import { useMovieStore } from '../stores/movies'

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
      Loading Movies Flix...
    </div>

    <template v-else>
      <HeroBanner
        v-if="movies.meta.hero"
        :movie="movies.meta.hero"
      />
      <section v-else class="flex min-h-[70vh] items-center justify-center bg-gradient-to-b from-neutral-900 to-flix-dark px-4 text-center">
        <div>
          <h1 class="font-display text-6xl text-flix-red">MOVIES FLIX</h1>
          <p class="mt-4 text-white/70">Connect Google Sheets to load your catalog.</p>
        </div>
      </section>

      <div class="-mt-16 space-y-2 pb-16">
        <MovieRow
          v-if="auth.isAuthenticated && movies.continueWatching.length"
          title="Continue Watching"
          :movies="movies.continueWatching.map((i) => i.movie)"
        />
        <MovieRow title="Trending Now" :movies="movies.meta.trending" />
        <MovieRow title="Latest Movies" :movies="movies.meta.latest" />
        <MovieRow title="Recommended" :movies="movies.meta.recommended" />

        <section v-if="movies.categories.length" class="px-4 md:px-8">
          <h2 class="mb-3 text-xl font-semibold md:text-2xl">Categories</h2>
          <div class="row-scroll">
            <RouterLink
              v-for="category in movies.categories"
              :key="category.id"
              :to="{ name: 'search', query: { category: category.name } }"
              class="relative h-28 w-48 overflow-hidden rounded-sm md:h-32 md:w-56"
            >
              <img
                v-if="category.image"
                :src="category.image"
                :alt="category.name"
                class="h-full w-full object-cover"
              />
              <div class="absolute inset-0 bg-black/50" />
              <span class="absolute inset-0 flex items-center justify-center text-lg font-semibold">
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
