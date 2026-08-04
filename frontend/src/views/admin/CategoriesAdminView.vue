<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../../api/client'

const categories = ref([])
const editingId = ref(null)
const form = reactive({ name: '', image: '' })
const message = ref('')

async function load() {
  const { data } = await api.get('/admin/categories')
  categories.value = data.data || []
}

function edit(category) {
  editingId.value = category.id
  form.name = category.name
  form.image = category.image || ''
}

function reset() {
  editingId.value = null
  form.name = ''
  form.image = ''
}

async function save() {
  if (editingId.value) {
    await api.put(`/admin/categories/${editingId.value}`, { ...form })
    message.value = 'Category updated.'
  } else {
    await api.post('/admin/categories', { ...form })
    message.value = 'Category created.'
  }
  reset()
  await load()
}

async function remove(id) {
  if (!confirm('Delete category?')) return
  await api.delete(`/admin/categories/${id}`)
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <h1 class="text-3xl font-semibold">Categories</h1>

    <form class="mt-6 flex flex-wrap gap-3 rounded-sm border border-white/10 bg-neutral-900 p-4" @submit.prevent="save">
      <input v-model="form.name" required placeholder="Category name" class="min-w-[200px] flex-1 rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.image" placeholder="Image URL" class="min-w-[200px] flex-1 rounded border border-white/10 bg-black px-3 py-2" />
      <button class="rounded bg-flix-red px-4 py-2 font-semibold">{{ editingId ? 'Update' : 'Add' }}</button>
      <button v-if="editingId" type="button" class="rounded bg-white/10 px-4 py-2" @click="reset">Cancel</button>
    </form>
    <p v-if="message" class="mt-2 text-sm text-green-400">{{ message }}</p>

    <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="category in categories"
        :key="category.id"
        class="overflow-hidden rounded-sm border border-white/10 bg-neutral-900"
      >
        <img v-if="category.image" :src="category.image" class="h-32 w-full object-cover" :alt="category.name" />
        <div class="flex items-center justify-between p-4">
          <h3 class="font-semibold">{{ category.name }}</h3>
          <div class="space-x-2 text-sm">
            <button class="text-flix-red" @click="edit(category)">Edit</button>
            <button class="text-white/50" @click="remove(category.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
