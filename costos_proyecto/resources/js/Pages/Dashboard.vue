<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

/* ---------- Dashboard data ---------- */
const loading = ref(true)
const kpis = ref({ proyectos_activos: 0, monto_contratado: 0, avance_fisico: 0, desviacion: 0 })
const recientes = ref([])
const role = ref(0)
const error = ref('')

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.get('/api/dashboard/summary')
    role.value = data.role
    kpis.value = data.kpis
    recientes.value = data.recientes
  } catch (e) {
    error.value = 'No se pudo cargar el dashboard'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  load()
  fetchUser()
})

const money = n => new Intl.NumberFormat('es-BO', { style:'currency', currency:'BOB' }).format(n ?? 0)

/* ---------- Perfil / Usuario ---------- */
const me = ref(null)
const profileOpen = ref(false)
const savingProfile = ref(false)
const profileMsg = ref('')

const form = ref({
  nombre_completo: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: ''
})

async function fetchUser () {
  try {
    const { data } = await axios.get('/api/auth/me')
    me.value = data?.user || data
    // precarga formulario
    if (me.value) {
      form.value.nombre_completo = me.value.nombre_completo || ''
      form.value.email = me.value.email || ''
      form.value.username = me.value.username || ''
    }
  } catch (e) {
    // noop
  }
}

function openProfile () {
  profileMsg.value = ''
  profileOpen.value = true
}

async function saveProfile () {
  profileMsg.value = ''
  savingProfile.value = true
  try {
    // PATCH a /profile (usa tu ProfileController existente)
    const payload = {
      nombre_completo: form.value.nombre_completo,
      email: form.value.email,
      username: form.value.username,
    }
    // si quiere cambiar contraseña
    if (form.value.password) {
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }

    await axios.patch('/profile', payload)
    profileMsg.value = 'Perfil actualizado ✅'
    await fetchUser()
    // limpia campos de password
    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (e) {
    const msg = e.response?.data?.message || 'No se pudo actualizar el perfil'
    profileMsg.value = `⚠️ ${msg}`
  } finally {
    savingProfile.value = false
  }
}

async function doLogout () {
  try {
    await axios.post('/api/auth/logout')
  } catch (e) {
    // igual redirigimos
  } finally {
    window.location.href = '/auth'
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#f6f7fb]">
    <div class="mx-auto max-w-6xl px-4 py-8">
      <!-- Encabezado -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-semibold">Inicio</h1>
          <p class="text-sm text-gray-500">Resumen general de tus proyectos</p>
        </div>

        <div class="flex items-center gap-2">
          <button @click="openProfile"
                  class="px-3 py-2 rounded-xl bg-white shadow text-gray-700 hover:bg-gray-50">
            Ver perfil
          </button>
          <button @click="doLogout"
                  class="px-3 py-2 rounded-xl bg-gray-200 text-gray-700 hover:bg-gray-300">
            Cerrar sesión
          </button>
<a v-if="role !== 2"
   href="/proyectos/nuevo"
   class="px-4 py-2 rounded-xl bg-indigo-500 text-white hover:opacity-90 transition">
  + Nuevo proyecto
</a>

        </div>
      </div>

      <!-- KPIs -->
      <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl p-5 bg-white shadow animate-fade-in-up">
          <p class="text-xs text-gray-500">Proyectos activos</p>
          <p class="text-2xl font-semibold mt-1">{{ kpis.proyectos_activos }}</p>
        </div>
        <div class="rounded-2xl p-5 bg-white shadow animate-fade-in-up">
          <p class="text-xs text-gray-500">Monto contratado</p>
          <p class="text-2xl font-semibold mt-1">{{ money(kpis.monto_contratado) }}</p>
        </div>
        <div class="rounded-2xl p-5 bg-white shadow animate-fade-in-up">
          <p class="text-xs text-gray-500">Avance físico promedio</p>
          <p class="text-2xl font-semibold mt-1">{{ kpis.avance_fisico }}%</p>
        </div>
        <div class="rounded-2xl p-5 bg-white shadow animate-fade-in-up">
          <p class="text-xs text-gray-500">Desviación promedio</p>
          <p class="text-2xl font-semibold mt-1"
             :class="kpis.desviacion > 5 ? 'text-red-600' : (kpis.desviacion > 0 ? 'text-amber-600':'text-emerald-600')">
            {{ kpis.desviacion }}%
          </p>
        </div>
      </div>

      <!-- Proyectos recientes -->
      <div class="mt-8 rounded-2xl bg-white shadow p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold">Proyectos recientes</h2>
          <a href="/proyectos" class="text-indigo-600 text-sm hover:underline">Ver todos</a>
        </div>

        <div v-if="loading" class="text-sm text-gray-500">Cargando…</div>
        <div v-else-if="error" class="text-sm text-red-600">{{ error }}</div>

        <div v-else>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500">
                <th class="py-2">Proyecto</th>
                <th class="py-2">Cliente</th>
                <th class="py-2">Estado</th>
                <th class="py-2">Monto</th>
                <th class="py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in recientes" :key="p.id" class="border-t">
                <td class="py-2">{{ p.nombre }}</td>
                <td class="py-2">{{ p.cliente || '—' }}</td>
                <td class="py-2">
                  <span class="px-2 py-1 rounded-full text-xs"
                        :class="{
                          'bg-emerald-50 text-emerald-700': p.estado === 'ejecucion',
                          'bg-amber-50 text-amber-700': p.estado === 'planificacion',
                          'bg-gray-100 text-gray-700': p.estado !== 'ejecucion' && p.estado !== 'planificacion'
                        }">
                    {{ p.estado || '—' }}
                  </span>
                </td>
                <td class="py-2">{{ money(p.monto_contratado) }}</td>
                <td class="py-2 text-right">
                  <a :href="`/proyectos/${p.id}`" class="text-indigo-600 hover:underline">Ver</a>
                </td>
              </tr>
              <tr v-if="recientes.length === 0">
                <td colspan="5" class="py-6 text-center text-gray-500">Sin proyectos aún.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Callout de importación -->
      <div class="mt-6 rounded-2xl p-5 bg-gradient-to-r from-indigo-50 to-emerald-50 border border-indigo-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">¿Vienes de Prescom?</p>
            <p class="text-sm text-gray-600">Importa tu presupuesto en CSV/Excel y continúa aquí.</p>
          </div>
          <a href="/proyectos/importar" class="px-4 py-2 rounded-xl bg-indigo-500 text-white hover:opacity-90 transition">
            Importar Presupuesto
          </a>
        </div>
      </div>
    </div>

    <!-- Modal Perfil -->
    <div v-if="profileOpen"
         class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="w-full max-w-lg rounded-2xl bg-white shadow-lg p-6 animate-fade-in-up">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Mi perfil</h3>
          <button class="text-gray-500 hover:text-gray-700" @click="profileOpen=false">✕</button>
        </div>

        <div class="space-y-3">
          <div>
            <label class="text-sm text-gray-600">Nombre completo</label>
            <input v-model="form.nombre_completo" class="w-full rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
          </div>
          <div>
            <label class="text-sm text-gray-600">Email</label>
            <input type="email" v-model="form.email" class="w-full rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
          </div>
          <div>
            <label class="text-sm text-gray-600">Usuario</label>
            <input v-model="form.username" class="w-full rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
          </div>

          <details class="mt-2">
            <summary class="cursor-pointer text-sm text-gray-600">Cambiar contraseña</summary>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
              <input type="password" placeholder="Nueva contraseña"
                     v-model="form.password"
                     class="rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
              <input type="password" placeholder="Confirmar contraseña"
                     v-model="form.password_confirmation"
                     class="rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
            </div>
          </details>

          <p v-if="profileMsg" class="text-sm mt-1"
             :class="profileMsg.includes('⚠️') ? 'text-red-600' : 'text-emerald-700'">
            {{ profileMsg }}
          </p>

          <div class="pt-3 flex justify-end gap-2">
            <button class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200"
                    @click="profileOpen=false">Cancelar</button>
            <button :disabled="savingProfile"
                    class="px-4 py-2 rounded-xl bg-indigo-500 text-white hover:opacity-90 disabled:opacity-70"
                    @click="saveProfile">
              {{ savingProfile ? 'Guardando...' : 'Guardar cambios' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(8px); }
  100% { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fade-in-up .45s ease-out both; }
</style>
