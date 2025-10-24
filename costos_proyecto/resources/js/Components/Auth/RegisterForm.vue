<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <label class="block text-sm mb-1">Nombre completo</label>
      <input v-model.trim="form.nombre_completo" type="text" class="w-full rounded-xl border px-3 py-2" required>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Email</label>
        <input v-model.trim="form.email" type="email" class="w-full rounded-xl border px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Usuario</label>
        <input v-model.trim="form.username" type="text" class="w-full rounded-xl border px-3 py-2" required>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Contraseña</label>
        <input
          v-model="form.password"
          type="password"
          @blur="touched.password = true"
          :class="[
            'w-full rounded-xl border px-3 py-2 transition focus:ring-2',
            mismatch && touched.password ? 'ring-red-300 border-red-300' : 'ring-indigo-200 border-gray-200',
            shake && mismatch ? 'animate-shake' : ''
          ]"
          required
        >
      </div>

      <div>
        <label class="block text-sm mb-1">Confirmar contraseña</label>
        <input
          v-model="form.password2"
          type="password"
          @blur="touched.password2 = true"
          :class="[
            'w-full rounded-xl border px-3 py-2 transition focus:ring-2',
            mismatch && touched.password2 ? 'ring-red-300 border-red-300' : 'ring-indigo-200 border-gray-200',
            shake && mismatch ? 'animate-shake' : ''
          ]"
          required
        >
      </div>
    </div>

    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

    <button :disabled="loading" class="w-full rounded-xl py-2 bg-indigo-500 text-white hover:opacity-90 transition">
      {{ loading ? 'Creando cuenta...' : 'Registrarme' }}
    </button>
  </form>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import axios from 'axios'

const form = reactive({
  nombre_completo: '', email: '', username: '',
  password: '', password2: ''
})

const loading = ref(false)
const error = ref('')
const touched = reactive({ password: false, password2: false })
const shake = ref(false)

// true si NO coinciden y ambos campos tienen algo
const mismatch = computed(() =>
  form.password && form.password2 && form.password !== form.password2
)

const submit = async () => {
  error.value = ''
  touched.password = true
  touched.password2 = true

  if (mismatch.value) {
    // dispara la animación shake
    shake.value = false
    requestAnimationFrame(() => { shake.value = true })
    // mensaje opcional
    error.value = 'Las contraseñas no coinciden'
    return
  }

  loading.value = true
  try {
    const payload = { ...form }; delete payload.password2
    const { data } = await axios.post('/api/auth/register', payload)
    if (data.ok) window.location.href = '/dashboard'
  } catch (e) {
    const errs = e.response?.data?.errors
    error.value = errs ? Object.values(errs).flat().join(' ')
                       : (e.response?.data?.message || 'Error al registrar')
  } finally {
    loading.value = false
  }
}
</script>

