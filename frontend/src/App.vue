<script setup>
import { computed } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const isAdminRoute = computed(() => route.path.startsWith('/admin'))

async function onLogout() {
  await auth.logout()
  router.push({ name: 'home' })
}
</script>

<template>
  <div class="min-h-screen bg-flix-dark text-white">
    <header
      v-if="!isAdminRoute"
      class="fixed inset-x-0 top-0 z-50 transition-colors"
      :class="route.name === 'home' ? 'bg-gradient-to-b from-black/90 to-transparent' : 'bg-black/90 backdrop-blur'"
    >
      <div class="mx-auto flex max-w-7xl items-center gap-6 px-4 py-4 md:px-8">
        <RouterLink to="/" class="font-display text-3xl tracking-wide text-flix-red md:text-4xl">
          MOVIES FLIX KH
        </RouterLink>

        <nav class="hidden items-center gap-5 text-sm text-white/80 md:flex">
          <RouterLink to="/" class="hover:text-white">Home</RouterLink>
          <RouterLink to="/search" class="hover:text-white">Browse</RouterLink>
          <RouterLink v-if="auth.isAuthenticated" to="/my-list" class="hover:text-white">My List</RouterLink>
          <RouterLink v-if="auth.isAdmin" to="/admin" class="hover:text-white">Admin</RouterLink>
        </nav>

        <div class="ml-auto flex items-center gap-3">
          <RouterLink
            to="/search"
            class="rounded-full border border-white/20 px-3 py-1.5 text-sm text-white/80 hover:bg-white/10"
          >
            Search
          </RouterLink>

          <template v-if="auth.isAuthenticated">
            <span class="hidden text-sm text-white/70 sm:inline">{{ auth.user?.name }}</span>
            <button
              class="rounded bg-white/10 px-3 py-1.5 text-sm hover:bg-white/20"
              @click="onLogout"
            >
              Logout
            </button>
          </template>
          <template v-else>
            <RouterLink to="/login" class="rounded bg-flix-red px-4 py-1.5 text-sm font-semibold hover:bg-flix-red-dark">
              Sign In
            </RouterLink>
          </template>
        </div>
      </div>
    </header>

    <main :class="isAdminRoute ? '' : 'pt-0'">
      <RouterView />
    </main>
  </div>
</template>
