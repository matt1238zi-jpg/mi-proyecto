<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  openNewModal: { type: Boolean, default: false },
  proyecto: { type: Object, default: null }
})

const showNew = ref(props.openNewModal)
const saving = ref(false)
const msg = ref('')
const proyectos = ref([]) // ← lista de proyectos
const loading = ref(true)

const today = () => new Date().toISOString().slice(0, 10)
const form = ref({
  nombre: '',
  cliente: '',
  ubicacion: '',
  fecha_inicio: today(),
  fecha_fin: '',
})

// --- Cargar proyectos existentes ---
onMounted(async () => {
  try {
    const { data } = await axios.get('/api/proyectos')
    proyectos.value = data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

// --- Crear proyecto nuevo ---
async function crearProyecto() {
  msg.value = ''
  saving.value = true
  try {
    const { data } = await axios.post('/api/proyectos', form.value)
    if (data.ok) {
      window.location.href = `/proyectos/${data.id}/presupuesto`
    }
  } catch (e) {
    msg.value = e.response?.data?.message || 'No se pudo crear el proyecto'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#f6f7fb]">
    <div class="mx-auto max-w-6xl px-4 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Proyectos Activos</h1>
        <div class="flex gap-2">
          <button @click="showNew = true"
                  class="px-4 py-2 rounded-xl bg-indigo-500 text-white hover:opacity-90 transition">
            + Nuevo proyecto
          </button>
          <a href="/dashboard" class="text-indigo-600 hover:underline text-sm mt-2">← Volver</a>
        </div>
      </div>

      <!-- Lista de proyectos -->
      <div v-if="loading" class="text-sm text-gray-500">Cargando proyectos…</div>
      <div v-else-if="proyectos.length === 0" class="text-sm text-gray-400">
        No hay proyectos registrados aún.
      </div>

      <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="p in proyectos" :key="p.id"
             class="rounded-2xl bg-white p-5 shadow hover:shadow-md transition">
          <h2 class="font-semibold text-lg mb-1">{{ p.nombre }}</h2>
          <p class="text-sm text-gray-500 mb-2">
            Código: <b>{{ p.codigo }}</b><br>
            Cliente: {{ p.cliente || '—' }}<br>
            Estado: <span class="uppercase text-indigo-600">{{ p.estado }}</span>
          </p>
          <div class="flex justify-end mt-3">
            <a :href="`/proyectos/${p.id}/presupuesto`"
               class="px-4 py-2 rounded-xl bg-indigo-500 text-white hover:opacity-90 text-sm">
              Ir a Costos Unitarios
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: Nuevo Proyecto -->
    <div v-if="showNew" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"></div>
    <div v-if="showNew" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="w-full max-w-xl rounded-2xl bg-white shadow-lg p-6 animate-fade-in-up">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Nuevo proyecto</h3>
        </div>

        <div class="space-y-3">
          <div>
            <label class="text-sm text-gray-600">Nombre *</label>
            <input v-model.trim="form.nombre" required
                   class="w-full rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="text-sm text-gray-600">Cliente (opcional)</label>
              <input v-model.trim="form.cliente"
                     class="w-full rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
            </div>
            <div>
              <label class="text-sm text-gray-600">Ubicación (opcional)</label>
              <input v-model.trim="form.ubicacion"
                     class="w-full rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="text-sm text-gray-600">Fecha inicio *</label>
              <input type="date" v-model="form.fecha_inicio" required
                     class="w-full rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
            </div>
            <div>
              <label class="text-sm text-gray-600">Fecha fin (opcional)</label>
              <input type="date" v-model="form.fecha_fin"
                     class="w-full rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-200" />
            </div>
          </div>

          <p class="text-xs text-gray-500">
            La <b>moneda</b> se fija en <b>BOB</b> automáticamente (moneda_id = 1).<br>
            El <b>estado</b> inicia en <b>PLANIFICACION</b>.
          </p>

          <p v-if="msg" class="text-sm text-red-600">{{ msg }}</p>

          <div class="pt-3 flex justify-end gap-2">
            <button @click="showNew = false"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700">
              Cancelar
            </button>
            <button :disabled="saving || !form.nombre"
                    @click="crearProyecto"
                    class="px-4 py-2 rounded-xl bg-indigo-500 text-white hover:opacity-90 disabled:opacity-60">
              {{ saving ? 'Creando...' : 'Crear proyecto' }}
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
