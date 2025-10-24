<template>
  <div class="mx-auto max-w-3xl px-4 py-10">
    <div class="grid md:grid-cols-1 gap-8 items-start"> <!-- 1 sola columna -->
      <div class="card animate-fade-in-up">              <!-- tarjeta izquierda -->
        <h1 class="text-3xl font-semibold mb-1">Sistema de Costos</h1>
        <p class="text-sm text-gray-500 mb-6">Accede o crea tu cuenta.</p>

        <div class="flex gap-2 mb-6">
          <button @click="mode='login'"
                  :class="mode==='login' ? 'bg-indigo-500 text-white' : 'bg-gray-100'"
                  class="px-4 py-2 rounded-xl transition">Iniciar Sesión</button>
          <button @click="mode='register'"
                  :class="mode==='register' ? 'bg-indigo-500 text-white' : 'bg-gray-100'"
                  class="px-4 py-2 rounded-xl transition">Registrarse</button>
        </div>

        <LoginForm v-if="mode==='login'" class="animate-fade-in" />
        <RegisterForm v-else class="animate-fade-in" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import LoginForm from './Components/Auth/LoginForm.vue'
import RegisterForm from './Components/Auth/RegisterForm.vue'
const mode = ref('login')
</script>
