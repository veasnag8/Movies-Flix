<script setup>
import MovieCard from './MovieCard.vue'

defineProps({
  title: {
    type: String,
    required: true,
  },
  movies: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section v-if="movies.length" class="relative z-10 mb-8 px-4 md:px-8">
    <h2 class="mb-3 text-xl font-semibold tracking-wide text-flix-gold-soft md:text-2xl">{{ title }}</h2>
    <div class="row-scroll">
      <MovieCard v-for="movie in movies" :key="movie.id" :movie="movie" />
    </div>
  </section>
</template>
