<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { kh } from '../../i18n/kh'

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
        <RouterLink to="/" class="brand-mark">
          <span class="latin text-3xl">{{ kh.brand }}</span>
          <span class="khmer">{{ kh.brandKh }}</span>
        </RouterLink>
        <p class="mt-2 text-xs tracking-widest text-flix-gold/60">{{ kh.adminPanel }}</p>
      </div>
      <nav class="flex gap-2 overflow-x-auto px-3 pb-4 md:flex-col md:gap-1">
        <RouterLink
          class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10"
          active-class="!bg-flix-red !text-white"
          to="/admin"
        >
          {{ kh.dashboard }}
        </RouterLink>
        <RouterLink
          class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10"
          active-class="!bg-flix-red !text-white"
          to="/admin/movies"
        >
          {{ kh.movies }}
        </RouterLink>
        <RouterLink
          class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10"
          active-class="!bg-flix-red !text-white"
          to="/admin/categories"
        >
          {{ kh.categories }}
        </RouterLink>
        <RouterLink
          class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10"
          active-class="!bg-flix-red !text-white"
          to="/admin/users"
        >
          {{ kh.users }}
        </RouterLink>
        <button class="rounded px-3 py-2 text-left text-sm text-white/50 hover:bg-white/10" @click="logout">
          {{ kh.logout }}
        </button>
      </nav>
    </aside>
    <main class="p-4 md:p-8">
      <RouterView />
    </main>
  </div>
</template>
