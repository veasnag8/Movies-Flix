<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  router.push('/')
}
</script>

<template>
  <div class="min-h-screen bg-neutral-950 text-white md:grid md:grid-cols-[240px_1fr]">
    <aside class="border-b border-white/10 bg-black md:min-h-screen md:border-b-0 md:border-r">
      <div class="px-5 py-6">
        <RouterLink to="/" class="font-display text-3xl text-flix-red">MOVIES FLIX KH</RouterLink>
        <p class="mt-1 text-xs uppercase tracking-widest text-white/40">Admin Panel</p>
      </div>
      <nav class="flex gap-2 overflow-x-auto px-3 pb-4 md:flex-col md:gap-1">
        <RouterLink
          class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10"
          active-class="!bg-flix-red !text-white"
          to="/admin"
        >
          Dashboard
        </RouterLink>
        <RouterLink
          class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10"
          active-class="!bg-flix-red !text-white"
          to="/admin/movies"
        >
          Movies
        </RouterLink>
        <RouterLink
          class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10"
          active-class="!bg-flix-red !text-white"
          to="/admin/categories"
        >
          Categories
        </RouterLink>
        <RouterLink
          class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10"
          active-class="!bg-flix-red !text-white"
          to="/admin/users"
        >
          Users
        </RouterLink>
        <button class="rounded px-3 py-2 text-left text-sm text-white/50 hover:bg-white/10" @click="logout">
          Logout
        </button>
      </nav>
    </aside>
    <main class="p-4 md:p-8">
      <RouterView />
    </main>
  </div>
</template>
