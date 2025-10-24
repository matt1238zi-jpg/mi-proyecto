<script setup>
const props = defineProps({
  partida: Object,
  apu: Object,
  lineas: Array,
  resumen: Object,
  unidad: Function,
  money: Function,
  totalPU: [Number, String]
})
const emit = defineEmits(['save-apu','update-linea','delete-linea'])
</script>

<template>
      <main class="flex-1 p-4 overflow-y-auto">
      <div v-if="!selectedPartida" class="text-gray-500">Selecciona una partida para editar su APU…</div>

      <div v-else>
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-semibold">APU · {{ selectedPartida.codigo }} — {{ selectedPartida.descripcion }}</h2>
          <div class="text-sm text-gray-500">Unidad: <b>{{ u(selectedPartida.unidad_id) }}</b></div>
        </div>

        <!-- Encabezado rendimiento y % -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
          <div class="rounded-xl bg-white shadow p-3">
            <label class="text-xs text-gray-500">Rendimiento (cant/unid obra)</label>
            <input type="number" min="0" step="0.000001"
                   v-model.number="apu.rendimiento"
                   @change="guardarApu"
                   class="w-full border rounded-lg px-2 py-1">
          </div>
          <div class="rounded-xl bg-white shadow p-3">
            <label class="text-xs text-gray-500">% Indirectos</label>
            <input type="number" step="0.01" v-model.number="apu.indirectos_pct" @change="guardarApu"
                   class="w-full border rounded-lg px-2 py-1">
          </div>
          <div class="rounded-xl bg-white shadow p-3">
            <label class="text-xs text-gray-500">% Utilidad</label>
            <input type="number" step="0.01" v-model.number="apu.utilidad_pct" @change="guardarApu"
                   class="w-full border rounded-lg px-2 py-1">
          </div>
          <div class="rounded-xl bg-white shadow p-3">
            <label class="text-xs text-gray-500">% IVA</label>
            <input type="number" step="0.01" v-model.number="apu.iva_pct" @change="guardarApu"
                   class="w-full border rounded-lg px-2 py-1">
          </div>
        </div>

        <!-- Tabla de líneas -->
        <div class="rounded-2xl bg-white shadow overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
              <tr>
                <th class="p-2 text-left w-24">Tipo</th>
                <th class="p-2 text-left">Insumo / Parámetro</th>
                <th class="p-2 text-center w-16">Und</th>
                <th class="p-2 text-right w-28">Cant.</th>
                <th class="p-2 text-right w-28">P.Unit</th>
                <th class="p-2 text-right w-32">Parcial (Bs)</th>
                <th class="p-2 w-12"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="l in lineas" :key="l.id" class="border-t">
                <td class="p-2 text-gray-500">{{ l.recurso?.tipo || '—' }}</td>
                <td class="p-2">{{ l.recurso?.codigo }} — {{ l.recurso?.nombre }}</td>
                <td class="p-2 text-center">{{ u(l.recurso?.unidad_id) }}</td>

                <td class="p-2 text-right">
                  <input type="number" step="0.000001" min="0"
                         v-model.number="l.cantidad" @change="updateLinea(l)"
                         class="w-full border rounded-lg px-2 py-1 text-right">
                </td>

                <td class="p-2 text-right">
                  <input type="number" step="0.01" min="0"
                         v-model.number="l.precio_unitario" @change="updateLinea(l)"
                         class="w-full border rounded-lg px-2 py-1 text-right">
                </td>

                <td class="p-2 text-right font-medium">{{ money(l.parcial) }}</td>
                <td class="p-2 text-right">
                  <button class="text-red-600" @click="deleteLinea(l)">✕</button>
                </td>
              </tr>

              <tr v-if="lineas.length===0">
                <td colspan="7" class="p-4 text-center text-gray-500">Sin líneas aún. Elige recursos desde la derecha.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Resumen inferior -->
        <div class="grid md:grid-cols-4 gap-3 mt-4">
          <div class="rounded-xl bg-white shadow p-3">
            <div class="text-xs text-gray-500">Materiales</div>
            <div class="text-lg font-semibold">{{ money(resumen.MATERIAL) }}</div>
          </div>
          <div class="rounded-xl bg-white shadow p-3">
            <div class="text-xs text-gray-500">Obrero</div>
            <div class="text-lg font-semibold">{{ money(resumen.OBRERO) }}</div>
          </div>
          <div class="rounded-xl bg-white shadow p-3">
            <div class="text-xs text-gray-500">Equipo</div>
            <div class="text-lg font-semibold">{{ money(resumen.EQUIPO) }}</div>
          </div>
          <div class="rounded-xl bg-white shadow p-3">
            <div class="text-xs text-gray-500">Total APU</div>
            <div class="text-lg font-semibold">{{ money(resumen.TOTAL) }}</div>
            <div class="text-xs text-gray-500">PU = Total / Rendimiento → <b>{{ money(totalPU) }}</b></div>
          </div>
        </div>
      </div>
    </main>
</template>
