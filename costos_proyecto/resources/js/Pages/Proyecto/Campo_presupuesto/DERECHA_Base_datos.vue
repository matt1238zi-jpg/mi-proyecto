<!-- resources/js/Pages/Proyecto/Compo_presupuesto/PanelDerecho.vue -->
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  proyecto: Object,
  recursos: Array,
  cargando: Boolean,
  unidad: Function,
  money: Function,
  tipo: String,
  q: String,
})
const emit = defineEmits(['update:tipo','update:q','buscar','add'])

const uploading = ref(false)
const progress  = ref(0)
const importErrors = ref([])   // [{row, col, msg}]
const importSummary = ref(null)
const fileInput = ref(null)

function triggerExcel() {
  importErrors.value = []
  importSummary.value = null
  fileInput.value?.click()
}

async function onPickFile(e) {
  const file = e.target.files?.[0]
  if (!file) return
  uploading.value = true
  progress.value = 0
  importErrors.value = []
  importSummary.value = null

  try {
    const fd = new FormData()
    fd.append('file', file)

    const { data } = await axios.post('/api/recursos/import', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (ev) => {
        if (ev.total) progress.value = Math.round((ev.loaded * 100) / ev.total)
      }
    })

    // Estructura esperada: { ok, imported, warnings[], errors: [{row,col,msg}] }
    importSummary.value = { imported: data.imported ?? 0, warnings: data.warnings ?? [] }
    importErrors.value  = data.errors ?? []

    // Si hubo importaciones exitosas, refresca la lista
    if (data.ok) emit('buscar')

  } catch (err) {
    const msg = err.response?.data?.message || 'Error al importar.'
    importSummary.value = { imported: 0, warnings: [`${msg}`] }
  } finally {
    uploading.value = false
    progress.value = 0
    // resetea input para permitir mismo archivo de nuevo
    if (fileInput.value) fileInput.value.value = ''
  }
}
</script>

<template>
  <aside class="w-[36%] max-w-[520px] bg-white border-l shadow-sm p-3 overflow-y-auto">
    <div class="flex items-center justify-between">
      <h3 class="font-semibold">Base de datos</h3>
      <div class="text-xs text-gray-500">Proyecto: {{ proyecto.codigo }}</div>
    </div>

    <!-- Filtros -->
    <div class="mt-3 flex gap-2">
      <select :value="tipo" @change="$emit('update:tipo', $event.target.value); $emit('buscar')"
              class="border rounded-lg px-2 py-1 text-sm">
        <option value="">Todos</option>
        <option value="MATERIAL">Material</option>
        <option value="OBRERO">Obrero</option>
        <option value="EQUIPO">Equipo</option>
      </select>

      <input :value="q" @input="$emit('update:q', $event.target.value)"
             @keyup.enter="$emit('buscar')"
             placeholder="Filtrar (enter)…"
             class="flex-1 border rounded-lg px-3 py-1 text-sm" />

      <button @click="$emit('buscar')"
              class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm">Filtrar</button>
    </div>

    <!-- Importar Excel -->
    <div class="mt-3 p-3 rounded-xl bg-indigo-50 border border-indigo-100">
      <div class="flex items-center justify-between gap-2">
        <div>
          <p class="text-sm font-medium">Importar Excel</p>
          <p class="text-xs text-gray-600">
            Formato: <code>codigo, nombre, tipo(MATERIAL|OBRERO|EQUIPO), unidad(m|m2|m3|kg|ud), precio_unitario, moneda(BOB|USD), vigente_desde(YYYY-MM-DD), vigente_hasta(opc), fuente(opc)</code>
          </p>
        </div>
        <div class="flex items-center gap-2">
          <input ref="fileInput" type="file" accept=".xlsx,.xls" class="hidden" @change="onPickFile">
          <button @click="triggerExcel"
                  class="px-3 py-1 rounded-lg bg-indigo-500 text-white hover:opacity-90 text-sm">Seleccionar</button>
        </div>
      </div>

      <div v-if="uploading" class="mt-3">
        <div class="h-2 bg-white rounded">
          <div class="h-2 bg-indigo-500 rounded" :style="{ width: progress+'%' }"></div>
        </div>
        <p class="text-xs text-gray-600 mt-1">Subiendo… {{ progress }}%</p>
      </div>

      <!-- Resumen / Warnings -->
      <div v-if="importSummary" class="mt-3">
        <div class="text-sm">
          <span class="font-medium">Importados:</span> {{ importSummary.imported }}
        </div>
        <ul v-if="importSummary.warnings?.length" class="mt-1 list-disc ml-5 text-xs text-amber-700">
          <li v-for="(w,i) in importSummary.warnings" :key="i">{{ w }}</li>
        </ul>
      </div>

      <!-- Errores por celda -->
      <div v-if="importErrors.length" class="mt-3">
        <p class="text-sm font-medium text-red-700">Errores encontrados (no se importaron esas filas):</p>
        <div class="max-h-40 overflow-y-auto mt-1 border rounded">
          <table class="w-full text-xs">
            <thead class="bg-red-50 text-red-700">
              <tr>
                <th class="p-1 text-left">Fila</th>
                <th class="p-1 text-left">Columna</th>
                <th class="p-1 text-left">Mensaje</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(e,i) in importErrors" :key="i" class="border-t">
                <td class="p-1">{{ e.row }}</td>
                <td class="p-1">{{ e.col }}</td>
                <td class="p-1">{{ e.msg }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Lista de recursos -->
    <div class="mt-3">
      <div v-if="cargando" class="text-sm text-gray-500">Cargando…</div>
      <div v-else class="rounded-xl border divide-y">
        <div v-for="r in recursos" :key="r.id"
             class="px-3 py-2 hover:bg-indigo-50 cursor-pointer flex items-center justify-between"
             @dblclick="$emit('add', r)">
          <div>
            <div class="text-sm font-medium">{{ r.codigo }} — {{ r.nombre }}</div>
            <div class="text-xs text-gray-500">
              {{ r.tipo }} · und {{ unidad(r.unidad_id) }} · {{ money(r.precio_unitario) }}
            </div>
          </div>
          <button class="text-indigo-600 text-sm" @click="$emit('add', r)">Añadir</button>
        </div>
      </div>
    </div>
  </aside>
</template>
