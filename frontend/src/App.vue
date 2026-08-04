<script setup>
import { computed } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { kh } from './i18n/kh'

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
      :class="route.name === 'home' ? 'bg-gradient-to-b from-black/95 to-transparent' : 'bg-black/90 backdrop-blur'"
    >
      <div class="mx-auto flex max-w-7xl items-center gap-6 px-4 py-4 md:px-8">
        <RouterLink to="/" class="brand-mark shrink-0">
          <span class="latin text-3xl md:text-4xl">{{ kh.brand }}</span>
          <span class="khmer hidden sm:block">{{ kh.brandKh }}</span>
        </RouterLink>

        <nav class="hidden items-center gap-5 text-sm text-white/80 md:flex">
          <RouterLink to="/" class="hover:text-flix-gold">{{ kh.home }}</RouterLink>
          <RouterLink to="/search" class="hover:text-flix-gold">{{ kh.browse }}</RouterLink>
          <RouterLink v-if="auth.isAuthenticated" to="/my-list" class="hover:text-flix-gold">{{ kh.myList }}</RouterLink>
          <RouterLink v-if="auth.isAdmin" to="/admin" class="hover:text-flix-gold">{{ kh.admin }}</RouterLink>
        </nav>

        <div class="ml-auto flex items-center gap-3">
          <RouterLink
            to="/search"
            class="rounded border border-flix-gold/30 px-3 py-1.5 text-sm text-flix-gold-soft hover:bg-flix-gold/10"
          >
            {{ kh.search }}
          </RouterLink>

          <template v-if="auth.isAuthenticated">
            <span class="hidden text-sm text-white/70 sm:inline">{{ auth.user?.name }}</span>
            <button
              class="rounded bg-white/10 px-3 py-1.5 text-sm hover:bg-white/20"
              @click="onLogout"
            >
              {{ kh.logout }}
            </button>
          </template>
          <template v-else>
            <RouterLink to="/login" class="rounded bg-flix-red px-4 py-1.5 text-sm font-semibold hover:bg-flix-red-dark">
              {{ kh.signIn }}
            </RouterLink>
          </template>
        </div>
      </div>
      <div class="h-px bg-gradient-to-r from-transparent via-flix-gold/50 to-transparent" />
    </header>

    <main :class="isAdminRoute ? '' : 'pt-0'">
      <RouterView />
    </main>
  </div>
</template>
