<script setup>
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
</script>

<template>
   <aside class="w-[36%] max-w-[520px] bg-white border-l shadow-sm p-3 overflow-y-auto">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Base de datos</h3>
        <div class="text-xs text-gray-500">Proyecto: {{ proyecto.codigo }}</div>
      </div>

      <div class="mt-3 flex gap-2">
        <select v-model="recTipo" @change="buscarRecursos"
                class="border rounded-lg px-2 py-1 text-sm">
          <option value="">Todos</option>
          <option value="MATERIAL">Material</option>
          <option value="OBRERO">Obrero</option>
          <option value="EQUIPO">Equipo</option>
        </select>
        <input v-model="recQ" @keyup.enter="buscarRecursos"
               placeholder="Filtrar (enter)…"
               class="flex-1 border rounded-lg px-3 py-1 text-sm" />
        <button @click="buscarRecursos"
                class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm">Filtrar</button>
      </div>

      <div class="mt-3">
        <div v-if="cargandoRec" class="text-sm text-gray-500">Cargando…</div>
        <div v-else class="rounded-xl border divide-y">
          <div v-for="r in recursos" :key="r.id"
               class="px-3 py-2 hover:bg-indigo-50 cursor-pointer flex items-center justify-between"
               @dblclick="abrirCantidad(r)">
            <div>
              <div class="text-sm font-medium">{{ r.codigo }} — {{ r.nombre }}</div>
              <div class="text-xs text-gray-500">
                {{ r.tipo }} · und {{ u(r.unidad_id) }} · {{ money(r.precio_unitario) }}
              </div>
            </div>
            <button class="text-indigo-600 text-sm" @click="abrirCantidad(r)">Añadir</button>
          </div>
        </div>
      </div>
    </aside>
</template>
