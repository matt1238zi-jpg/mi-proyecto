<script setup>
const props = defineProps({
  proyecto: Object,
  capitulos: Array,
  partidas: Array,
  selectedId: [Number, null],
  unidad: Function,
  money: Function,
  filtro: { type: String, default: '' }
})
const emit = defineEmits(['update:filtro','select'])
</script>
<template>
<aside class="w-[32%] max-w-[430px] bg-white border-r shadow-sm flex flex-col">
      <div class="p-3 border-b">
        <h2 class="font-semibold">Proyecto: {{ proyecto.nombre }} ({{ proyecto.codigo }})</h2>
        <input v-model="filtroArbol" placeholder="Filtrar partidas…" class="mt-2 w-full rounded-lg border px-3 py-2">
      </div>

      <div class="p-3 overflow-y-auto">
        <template v-for="c in capitulos" :key="c.id">
          <div class="text-xs text-gray-500 font-medium mt-3">{{ c.codigo }} — {{ c.nombre }}</div>
          <div v-for="p in partidasFiltradas.filter(x => x.capitulo_id === c.id)" :key="p.id"
               @click="seleccionarPartida(p)"
               class="mt-1 rounded-lg px-3 py-2 cursor-pointer hover:bg-indigo-50"
               :class="selectedPartida?.id===p.id ? 'bg-indigo-100' : '' ">
            <div class="text-sm font-medium">{{ p.codigo }} — {{ p.descripcion }}</div>
            <div class="text-xs text-gray-500">
              {{ p.cantidad }} {{ u(p.unidad_id) }} · PU {{ money(p.precio_unitario) }}
            </div>
          </div>
        </template>
      </div>
    </aside>
</template>