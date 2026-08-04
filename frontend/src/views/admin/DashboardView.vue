<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../api/client'

const stats = ref({
  movies: 0,
  categories: 0,
  users: 0,
})

onMounted(async () => {
  const [movies, categories, users] = await Promise.all([
    api.get('/admin/movies'),
    api.get('/admin/categories'),
    api.get('/admin/users'),
  ])
  stats.value = {
    movies: movies.data.data?.length || 0,
    categories: categories.data.data?.length || 0,
    users: users.data.data?.length || 0,
  }
})
</script>

<template>
  <div>
    <h1 class="text-3xl font-semibold">Dashboard</h1>
    <p class="mt-1 text-white/60">Manage Movies Flix content stored in Google Sheets.</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
      <div class="rounded-sm border border-white/10 bg-neutral-900 p-5">
        <p class="text-sm text-white/50">Movies</p>
        <p class="mt-2 text-4xl font-bold">{{ stats.movies }}</p>
        <RouterLink to="/admin/movies" class="mt-3 inline-block text-sm text-flix-red">Manage →</RouterLink>
      </div>
      <div class="rounded-sm border border-white/10 bg-neutral-900 p-5">
        <p class="text-sm text-white/50">Categories</p>
        <p class="mt-2 text-4xl font-bold">{{ stats.categories }}</p>
        <RouterLink to="/admin/categories" class="mt-3 inline-block text-sm text-flix-red">Manage →</RouterLink>
      </div>
      <div class="rounded-sm border border-white/10 bg-neutral-900 p-5">
        <p class="text-sm text-white/50">Users</p>
        <p class="mt-2 text-4xl font-bold">{{ stats.users }}</p>
        <RouterLink to="/admin/users" class="mt-3 inline-block text-sm text-flix-red">Manage →</RouterLink>
      </div>
    </div>
  </div>
</template>
