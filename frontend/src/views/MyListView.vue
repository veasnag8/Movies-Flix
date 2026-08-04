<script setup>
import { onMounted } from 'vue'
import MovieCard from '../components/MovieCard.vue'
import { useMovieStore } from '../stores/movies'

const store = useMovieStore()

onMounted(async () => {
  await store.fetchFavorites()
  await store.fetchContinueWatching()
})
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 pb-16 pt-24 md:px-8">
    <h1 class="font-display text-4xl tracking-wide">My List</h1>

    <section class="mt-10">
      <h2 class="mb-4 text-xl font-semibold">Favorites</h2>
      <div v-if="!store.favorites.length" class="text-white/60">No favorites yet.</div>
      <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        <MovieCard
          v-for="item in store.favorites.filter((f) => f.movie)"
          :key="item.id"
          :movie="item.movie"
        />
      </div>
    </section>

    <section class="mt-12">
      <h2 class="mb-4 text-xl font-semibold">Continue Watching</h2>
      <div v-if="!store.continueWatching.length" class="text-white/60">Nothing in progress.</div>
      <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        <MovieCard
          v-for="item in store.continueWatching"
          :key="item.id"
          :movie="item.movie"
        />
      </div>
    </section>
  </div>
</template>
