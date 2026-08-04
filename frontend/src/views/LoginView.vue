<script setup>
import { reactive } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({
  email: '',
  password: '',
})

async function submit() {
  await auth.login(form)
  router.push(route.query.redirect || '/')
}
</script>

<template>
  <div class="relative flex min-h-screen items-center justify-center px-4">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,#3f0d12,transparent_45%),linear-gradient(#0b0b0b,#1a0505)]" />
    <form
      class="relative z-10 w-full max-w-md rounded-sm bg-black/75 p-8 shadow-2xl backdrop-blur"
      @submit.prevent="submit"
    >
      <h1 class="font-display text-4xl text-flix-red">MOVIES FLIX KH</h1>
      <p class="mt-2 text-sm text-white/60">Sign in to watch and continue your list</p>

      <label class="mt-8 block text-sm text-white/70">Email</label>
      <input
        v-model="form.email"
        type="email"
        required
        class="mt-1 w-full rounded border border-white/10 bg-neutral-900 px-3 py-3 outline-none focus:border-flix-red"
      />

      <label class="mt-4 block text-sm text-white/70">Password</label>
      <input
        v-model="form.password"
        type="password"
        required
        class="mt-1 w-full rounded border border-white/10 bg-neutral-900 px-3 py-3 outline-none focus:border-flix-red"
      />

      <p v-if="auth.error" class="mt-3 text-sm text-red-400">{{ auth.error }}</p>

      <button
        type="submit"
        class="mt-6 w-full rounded bg-flix-red py-3 font-semibold hover:bg-flix-red-dark disabled:opacity-60"
        :disabled="auth.loading"
      >
        {{ auth.loading ? 'Signing in...' : 'Sign In' }}
      </button>

      <p class="mt-6 text-sm text-white/60">
        New to Movies Flix KH?
        <RouterLink to="/register" class="text-white hover:underline">Sign up now</RouterLink>
      </p>
    </form>
  </div>
</template>
