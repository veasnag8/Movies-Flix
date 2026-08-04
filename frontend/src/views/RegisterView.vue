<script setup>
import { reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { kh } from '../i18n/kh'

const auth = useAuthStore()
const router = useRouter()
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

async function submit() {
  await auth.register(form)
  router.push('/')
}
</script>

<template>
  <div class="relative flex min-h-screen items-center justify-center px-4">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(200,16,46,0.35),transparent_45%),linear-gradient(#080706,#1a0a0c)]" />
    <form
      class="kh-panel relative z-10 w-full max-w-md rounded-sm bg-black/80 p-5 backdrop-blur sm:p-8"
      @submit.prevent="submit"
    >
      <div class="brand-mark">
        <span class="latin text-3xl sm:text-4xl">{{ kh.brand }}</span>
        <span class="khmer">{{ kh.brandKh }}</span>
      </div>
      <div class="gold-line mt-3" />
      <p class="mt-3 text-sm text-white/60">{{ kh.registerHint }}</p>

      <label class="mt-8 block text-sm text-flix-gold-soft/80">{{ kh.name }}</label>
      <input
        v-model="form.name"
        type="text"
        required
        class="mt-1 w-full rounded border border-white/10 bg-neutral-900 px-3 py-3 outline-none focus:border-flix-gold"
      />

      <label class="mt-4 block text-sm text-flix-gold-soft/80">{{ kh.email }}</label>
      <input
        v-model="form.email"
        type="email"
        required
        class="mt-1 w-full rounded border border-white/10 bg-neutral-900 px-3 py-3 outline-none focus:border-flix-gold"
      />

      <label class="mt-4 block text-sm text-flix-gold-soft/80">{{ kh.password }}</label>
      <div class="relative mt-1">
        <input
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          required
          minlength="6"
          class="w-full rounded border border-white/10 bg-neutral-900 px-3 py-3 pr-20 outline-none focus:border-flix-gold"
        />
        <button
          type="button"
          class="absolute inset-y-0 right-0 px-3 text-xs text-flix-gold-soft hover:text-flix-gold"
          @click="showPassword = !showPassword"
        >
          {{ showPassword ? kh.hidePassword : kh.showPassword }}
        </button>
      </div>

      <label class="mt-4 block text-sm text-flix-gold-soft/80">{{ kh.confirmPassword }}</label>
      <div class="relative mt-1">
        <input
          v-model="form.password_confirmation"
          :type="showConfirmPassword ? 'text' : 'password'"
          required
          minlength="6"
          class="w-full rounded border border-white/10 bg-neutral-900 px-3 py-3 pr-20 outline-none focus:border-flix-gold"
        />
        <button
          type="button"
          class="absolute inset-y-0 right-0 px-3 text-xs text-flix-gold-soft hover:text-flix-gold"
          @click="showConfirmPassword = !showConfirmPassword"
        >
          {{ showConfirmPassword ? kh.hidePassword : kh.showPassword }}
        </button>
      </div>

      <p v-if="auth.error" class="mt-3 text-sm text-red-400">{{ auth.error }}</p>

      <button
        type="submit"
        class="mt-6 w-full rounded bg-flix-red py-3 font-semibold hover:bg-flix-red-dark disabled:opacity-60"
        :disabled="auth.loading"
      >
        {{ auth.loading ? kh.creatingAccount : kh.signUp }}
      </button>

      <p class="mt-6 text-sm text-white/60">
        {{ kh.haveAccount }}
        <RouterLink to="/login" class="text-flix-gold hover:underline">{{ kh.signIn }}</RouterLink>
      </p>
    </form>
  </div>
</template>
