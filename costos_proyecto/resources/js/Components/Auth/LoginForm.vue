<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <label class="block text-sm mb-1">Correo o usuario</label>
      <input v-model.trim="form.login" type="text" class="w-full rounded-xl border px-3 py-2" placeholder="email o username" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Contraseña</label>
      <input v-model="form.password" type="password" class="w-full rounded-xl border px-3 py-2" required>
    </div>

    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

    <button :disabled="loading" class="w-full rounded-xl py-2 bg-indigo-500 text-white hover:opacity-90 transition">
      {{ loading ? 'Ingresando...' : 'Entrar' }}
    </button>
  </form>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'

const form = reactive({ login: '', password: '' })
const loading = ref(false)
const error = ref('')

const submit = async () => {
  error.value = ''
  loading.value = true
  try {
    const { data } = await axios.post('/api/auth/login', form)
    if (data.ok) {
      window.location.href = '/dashboard' // redirige a tu dashboard
    } else {
      error.value = data.msg || 'No se pudo iniciar sesión'
    }
  } catch (e) {
    error.value = e.response?.data?.msg || 'Credenciales inválidas'
  } finally {
    loading.value = false
  }
}
</script>
