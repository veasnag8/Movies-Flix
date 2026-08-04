<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../../api/client'

const users = ref([])
const editingId = ref(null)
const message = ref('')
const error = ref('')
const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'user',
  avatar: '',
})

function reset() {
  editingId.value = null
  Object.assign(form, { name: '', email: '', password: '', role: 'user', avatar: '' })
}

async function load() {
  const { data } = await api.get('/admin/users')
  users.value = data.data || []
}

function edit(user) {
  editingId.value = user.id
  form.name = user.name
  form.email = user.email
  form.role = user.role || 'user'
  form.avatar = user.avatar || ''
  form.password = ''
}

async function save() {
  message.value = ''
  error.value = ''
  try {
    const payload = { ...form }
    if (!payload.password) delete payload.password

    if (editingId.value) {
      await api.put(`/admin/users/${editingId.value}`, payload)
      message.value = 'User updated.'
    } else {
      await api.post('/admin/users', payload)
      message.value = 'User created.'
    }
    reset()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || 'Save failed'
  }
}

async function remove(id) {
  if (!confirm('Delete user?')) return
  await api.delete(`/admin/users/${id}`)
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <h1 class="text-3xl font-semibold">Users</h1>

    <form class="mt-6 grid gap-3 rounded-sm border border-white/10 bg-neutral-900 p-4 md:grid-cols-2" @submit.prevent="save">
      <input v-model="form.name" required placeholder="Name" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.email" required type="email" placeholder="Email" class="rounded border border-white/10 bg-black px-3 py-2" />
      <input v-model="form.password" :required="!editingId" type="password" :placeholder="editingId ? 'New password (optional)' : 'Password'" class="rounded border border-white/10 bg-black px-3 py-2" />
      <select v-model="form.role" class="rounded border border-white/10 bg-black px-3 py-2">
        <option value="user">user</option>
        <option value="admin">admin</option>
      </select>
      <input v-model="form.avatar" placeholder="Avatar URL" class="rounded border border-white/10 bg-black px-3 py-2 md:col-span-2" />
      <div class="md:col-span-2 flex gap-2">
        <button class="rounded bg-flix-red px-4 py-2 font-semibold">{{ editingId ? 'Update User' : 'Add User' }}</button>
        <button v-if="editingId" type="button" class="rounded bg-white/10 px-4 py-2" @click="reset">Cancel</button>
      </div>
      <p v-if="message" class="md:col-span-2 text-sm text-green-400">{{ message }}</p>
      <p v-if="error" class="md:col-span-2 text-sm text-red-400">{{ error }}</p>
    </form>

    <div class="mt-8 overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="border-b border-white/10 text-white/50">
          <tr>
            <th class="px-2 py-3">Name</th>
            <th class="px-2 py-3">Email</th>
            <th class="px-2 py-3">Role</th>
            <th class="px-2 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="border-b border-white/5">
            <td class="px-2 py-3">{{ user.name }}</td>
            <td class="px-2 py-3">{{ user.email }}</td>
            <td class="px-2 py-3">{{ user.role }}</td>
            <td class="px-2 py-3">
              <button class="mr-2 text-flix-red" @click="edit(user)">Edit</button>
              <button class="text-white/50" @click="remove(user.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
