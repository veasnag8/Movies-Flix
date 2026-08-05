<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { kh } from './i18n/kh'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const menuOpen = ref(false)

const isAdminRoute = computed(() => route.path.startsWith('/admin'))

watch(
  () => route.fullPath,
  () => {
    menuOpen.value = false
  }
)

async function onLogout() {
  menuOpen.value = false
  await auth.logout()
  router.push({ name: 'home' })
}

onMounted(async () => {
  if (!auth.token) return

  try {
    await auth.fetchMe()
  } catch {
    // If the token is invalid, fetchMe() clears the saved session.
  }
})
</script>

<template>
  <div class="min-h-screen bg-flix-dark text-white">
    <header
      v-if="!isAdminRoute"
      class="fixed inset-x-0 top-0 z-50 transition-colors"
      :class="route.name === 'home' && !menuOpen ? 'bg-gradient-to-b from-black/95 to-transparent' : 'bg-black/95 backdrop-blur'"
    >
      <div class="mx-auto flex max-w-7xl items-center gap-2 px-3 py-3 sm:gap-4 sm:px-4 md:px-8 md:py-4">
        <RouterLink to="/" class="brand-mark min-w-0 shrink">
          <span class="latin text-[1.35rem] sm:text-2xl md:text-4xl">{{ kh.brand }}</span>
          <span class="khmer">{{ kh.brandKh }}</span>
        </RouterLink>

        <nav class="ml-2 hidden items-center gap-5 text-sm text-white/80 lg:flex">
          <RouterLink to="/" class="hover:text-flix-gold">{{ kh.home }}</RouterLink>
          <RouterLink to="/search" class="hover:text-flix-gold">{{ kh.browse }}</RouterLink>
          <RouterLink v-if="auth.isAuthenticated" to="/my-list" class="hover:text-flix-gold">{{ kh.myList }}</RouterLink>
          <RouterLink v-if="auth.isAdmin" to="/admin" class="hover:text-flix-gold">{{ kh.admin }}</RouterLink>
        </nav>

        <div class="ml-auto flex items-center gap-2 sm:gap-3">
          <RouterLink
            to="/search"
            class="inline-flex h-9 w-9 items-center justify-center rounded border border-flix-gold/30 text-flix-gold-soft hover:bg-flix-gold/10 sm:h-auto sm:w-auto sm:px-3 sm:py-1.5 sm:text-sm"
            :aria-label="kh.search"
          >
            <span class="sm:hidden">⌕</span>
            <span class="hidden sm:inline">{{ kh.search }}</span>
          </RouterLink>

          <template v-if="auth.isAuthenticated">
            <span class="hidden max-w-[8rem] truncate text-sm text-white/70 md:inline">{{ auth.user?.name }}</span>
            <button
              class="hidden rounded bg-white/10 px-3 py-1.5 text-sm hover:bg-white/20 sm:inline-flex"
              @click="onLogout"
            >
              {{ kh.logout }}
            </button>
          </template>
          <template v-else>
            <RouterLink
              to="/login"
              class="rounded bg-flix-red px-3 py-1.5 text-xs font-semibold hover:bg-flix-red-dark sm:px-4 sm:text-sm"
            >
              {{ kh.signIn }}
            </RouterLink>
          </template>

          <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded border border-white/15 text-lg lg:hidden"
            :aria-expanded="menuOpen"
            aria-label="Menu"
            @click="menuOpen = !menuOpen"
          >
            {{ menuOpen ? '✕' : '☰' }}
          </button>
        </div>
      </div>

      <nav
        v-if="menuOpen"
        class="border-t border-white/10 bg-black/98 px-4 py-3 lg:hidden"
      >
        <div class="flex flex-col gap-1 text-sm">
          <RouterLink class="rounded px-3 py-2.5 hover:bg-white/10" to="/">{{ kh.home }}</RouterLink>
          <RouterLink class="rounded px-3 py-2.5 hover:bg-white/10" to="/search">{{ kh.browse }}</RouterLink>
          <RouterLink v-if="auth.isAuthenticated" class="rounded px-3 py-2.5 hover:bg-white/10" to="/my-list">{{ kh.myList }}</RouterLink>
          <RouterLink v-if="auth.isAdmin" class="rounded px-3 py-2.5 hover:bg-white/10" to="/admin">{{ kh.admin }}</RouterLink>
          <button
            v-if="auth.isAuthenticated"
            class="rounded px-3 py-2.5 text-left text-white/70 hover:bg-white/10"
            @click="onLogout"
          >
            {{ kh.logout }}
          </button>
        </div>
      </nav>

      <div class="h-px bg-gradient-to-r from-transparent via-flix-gold/50 to-transparent" />
    </header>

    <main :class="isAdminRoute ? '' : 'pt-0'">
      <RouterView />
    </main>
  </div>
</template>
