<script setup>
import { onMounted, computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import MovieRow from '../components/MovieRow.vue'
import { useAuthStore } from '../stores/auth'
import { useMovieStore } from '../stores/movies'
import { kh } from '../i18n/kh'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const store = useMovieStore()

const movie = computed(() => store.current)

onMounted(async () => {
  await store.fetchMovie(route.params.id)
  if (auth.isAuthenticated) {
    await store.fetchFavorites()
  }
})

async function watchNow() {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: `/watch/${movie.value.id}` } })
    return
  }
  router.push({ name: 'watch', params: { id: movie.value.id } })
}

async function onFavorite() {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login' })
    return
  }
  await store.toggleFavorite(movie.value.id)
}

const isFavorite = computed(() =>
  store.favorites.some((f) => String(f.movie_id) === String(movie.value?.id))
)
</script>

<template>
  <div class="min-h-screen pb-16">
    <div v-if="store.loading && !movie" class="flex min-h-screen items-center justify-center text-white/60">
      {{ kh.loadingShort }}
    </div>

    <template v-else-if="movie">
      <div class="relative h-[48vh] min-h-[320px] overflow-hidden md:h-[58vh]">
        <img
          :src="movie.banner_url || movie.poster_url"
          :alt="movie.title"
          class="h-full w-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-flix-dark via-black/40 to-black/30" />
      </div>

      <div class="relative z-10 mx-auto -mt-40 grid max-w-7xl gap-8 px-4 md:grid-cols-[220px_1fr] md:px-8">
        <img
          v-if="movie.poster_url"
          :src="movie.poster_url"
          :alt="movie.title"
          class="mx-auto w-48 rounded shadow-2xl md:mx-0 md:w-full"
        />

        <div class="fade-up">
          <h1 class="font-display text-5xl tracking-wide md:text-6xl">{{ movie.title }}</h1>
          <div class="mt-3 flex flex-wrap gap-3 text-sm text-white/70">
            <span class="text-green-400">★ {{ movie.rating }}</span>
            <span>{{ movie.year }}</span>
            <span>{{ movie.duration }}</span>
            <span>{{ movie.language }}</span>
            <span>{{ movie.quality }}</span>
            <span class="rounded border border-white/30 px-2 py-0.5">{{ movie.category }}</span>
          </div>
          <p class="mt-5 max-w-3xl text-base leading-relaxed text-white/80 md:text-lg">
            {{ movie.description }}
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <button
              class="rounded bg-flix-red px-6 py-3 text-sm font-bold uppercase tracking-wide hover:bg-flix-red-dark"
              @click="watchNow"
            >
              {{ kh.watchNow }}
            </button>
            <button
              class="rounded bg-white/15 px-6 py-3 text-sm font-semibold hover:bg-white/25"
              @click="onFavorite"
            >
              {{ isFavorite ? kh.inMyList : kh.addMyList }}
            </button>
            <a
              v-if="movie.trailer_url"
              :href="movie.trailer_url"
              target="_blank"
              rel="noopener"
              class="rounded border border-flix-gold/40 px-6 py-3 text-sm font-semibold text-flix-gold-soft hover:bg-flix-gold/10"
            >
              {{ kh.trailer }}
            </a>
          </div>
        </div>
      </div>

      <div class="mt-12">
        <MovieRow :title="kh.moreLikeThis" :movies="store.related" />
      </div>
    </template>

    <div v-else class="flex min-h-screen flex-col items-center justify-center gap-4">
      <p class="text-white/70">{{ store.error || kh.movieNotFound }}</p>
      <RouterLink to="/" class="text-flix-red">{{ kh.backHome }}</RouterLink>
    </div>
  </div>
</template>
