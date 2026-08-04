<script setup>
import { onMounted } from 'vue'
import MovieCard from '../components/MovieCard.vue'
import { useMovieStore } from '../stores/movies'
import { kh } from '../i18n/kh'

const store = useMovieStore()

onMounted(async () => {
  await store.fetchFavorites()
  await store.fetchContinueWatching()
})
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 pb-16 pt-20 sm:pt-24 md:px-8">
    <h1 class="font-display text-3xl tracking-wide text-flix-gold-soft sm:text-4xl">{{ kh.myList }}</h1>
    <div class="gold-line mt-2" />

    <section class="mt-10">
      <h2 class="mb-4 text-xl font-semibold">{{ kh.favorites }}</h2>
      <div v-if="!store.favorites.length" class="text-white/60">{{ kh.noFavorites }}</div>
      <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        <MovieCard
          v-for="item in store.favorites.filter((f) => f.movie)"
          :key="item.id"
          :movie="item.movie"
        />
      </div>
    </section>

    <section class="mt-12">
      <h2 class="mb-4 text-xl font-semibold">{{ kh.continueWatching }}</h2>
      <div v-if="!store.continueWatching.length" class="text-white/60">{{ kh.nothingInProgress }}</div>
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
