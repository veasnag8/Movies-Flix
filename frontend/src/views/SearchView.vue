<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import MovieCard from '../components/MovieCard.vue'
import { useMovieStore } from '../stores/movies'

const route = useRoute()
const store = useMovieStore()
const query = ref(route.query.q || '')
const searching = ref(false)

const results = computed(() => {
  const category = route.query.category
  let list = store.searchResults.length || query.value
    ? store.searchResults
    : store.movies

  if (category) {
    list = list.filter((m) => m.category === category)
  }
  return list
})

async function runSearch() {
  searching.value = true
  try {
    if (query.value.trim()) {
      await store.search(query.value.trim())
    } else {
      await store.fetchHome()
      store.searchResults = store.movies
    }
  } finally {
    searching.value = false
  }
}

onMounted(async () => {
  await store.fetchHome()
  if (query.value) {
    await runSearch()
  } else {
    store.searchResults = store.movies
  }
})

watch(
  () => route.query.category,
  () => {
    if (!query.value) {
      store.searchResults = store.movies
    }
  }
)
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 pb-16 pt-24 md:px-8">
    <h1 class="font-display text-4xl tracking-wide">Browse</h1>
    <form class="mt-6 flex gap-3" @submit.prevent="runSearch">
      <input
        v-model="query"
        type="search"
        placeholder="Search titles, genres, languages..."
        class="w-full rounded border border-white/15 bg-neutral-900 px-4 py-3 outline-none focus:border-flix-red"
      />
      <button class="rounded bg-flix-red px-5 py-3 font-semibold hover:bg-flix-red-dark">
        Search
      </button>
    </form>

    <p v-if="route.query.category" class="mt-4 text-sm text-white/60">
      Category: {{ route.query.category }}
    </p>

    <div v-if="searching" class="mt-10 text-white/60">Searching...</div>
    <div v-else-if="!results.length" class="mt-10 text-white/60">No movies found.</div>
    <div v-else class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
      <MovieCard v-for="movie in results" :key="movie.id" :movie="movie" />
    </div>
  </div>
</template>
