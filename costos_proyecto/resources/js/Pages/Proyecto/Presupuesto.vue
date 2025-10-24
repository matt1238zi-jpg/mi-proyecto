<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
//hijos 
import IzquierdaArbol from './Campo_presupuesto/IZQUIERDA_arbol.vue'
import CentroApu from './Campo_presupuesto/CENTRO_APU.vue'
import DerechaBase from './Campo_presupuesto/DERECHA_Base_datos.vue'

const props = defineProps({
  proyecto: { type: Object, required: true }
})

// Árbol
const capitulos = ref([])
const partidas  = ref([])
const filtroArbol = ref('')

// Selección
const selectedPartida = ref(null)
const apu     = ref(null)
const lineas  = ref([])
const resumen = ref({ MATERIAL:0, OBRERO:0, EQUIPO:0, TOTAL:0 })

// Panel derecho: base de datos
const recTipo = ref('') // '', 'MATERIAL','OBRERO','EQUIPO'
const recQ    = ref('')
const recursos = ref([])
const cargandoRec = ref(false)

// Modal cantidad (rendimiento)
const showCant = ref(false)
const cant = ref(1)
const recursoSel = ref(null)

const money = n => new Intl.NumberFormat('es-BO',{style:'currency',currency:'BOB'}).format(+n || 0)
const u = (id)=> unidades.value[id] || '—'

// Unidades simples en memoria (puedes pedirlas al backend si quieres)
const unidades = ref({
  1:'m', 2:'m2', 3:'m3', 4:'kg', 5:'unid',
})

async function loadEstructura(){
  const { data } = await axios.get(`/api/proyectos/${props.proyecto.id}/estructura`)
  capitulos.value = data.capitulos
  partidas.value  = data.partidas
}
onMounted(loadEstructura)

const partidasFiltradas = computed(()=>{
  const q = filtroArbol.value.toLowerCase()
  if(!q) return partidas.value
  return partidas.value.filter(p =>
    (p.codigo||'').toLowerCase().includes(q) ||
    (p.descripcion||'').toLowerCase().includes(q)
  )
})

async function seleccionarPartida(p){
  selectedPartida.value = p
  const { data } = await axios.get(`/api/partidas/${p.id}/apu`)
  apu.value = data.apu
  lineas.value = data.lineas
  resumen.value = data.resumen
}

async function guardarApu(){
  if(!selectedPartida.value) return
  const { data } = await axios.post(`/api/partidas/${selectedPartida.value.id}/apu`, {
    rendimiento: apu.value?.rendimiento || 1,
    indirectos_pct: apu.value?.indirectos_pct || 0,
    utilidad_pct: apu.value?.utilidad_pct || 0,
    iva_pct: apu.value?.iva_pct || 0,
  })
  apu.value = data.apu
}

async function buscarRecursos(){
  cargandoRec.value = true
  try{
    const { data } = await axios.get(`/api/proyectos/${props.proyecto.id}/recursos`, {
      params: { q: recQ.value, tipo: recTipo.value || undefined }
    })
    recursos.value = data
  } finally {
    cargandoRec.value = false
  }
}
onMounted(buscarRecursos)

function abrirCantidad(r){
  recursoSel.value = r
  cant.value = 1
  showCant.value = true
}
async function confirmarCantidad(){
  if(!apu.value){
    // crea APU si no existe
    apu.value = { rendimiento: 1 }
    await guardarApu()
  }
  const { data } = await axios.post(`/api/apu/${apu.value.id}/lineas`, {
    recurso_id: recursoSel.value.id,
    cantidad: cant.value,
    // precio_unitario: opcional
  })
  lineas.value.push(data.linea)
  await seleccionarPartida(selectedPartida.value) // recalcula resumen
  showCant.value = false
}

async function updateLinea(l){
  await axios.patch(`/api/apu/lineas/${l.id}`, {
    cantidad: l.cantidad,
    precio_unitario: l.precio_unitario,
  })
  l.parcial = (+l.cantidad||0) * (+l.precio_unitario||0)
  await seleccionarPartida(selectedPartida.value)
}

async function deleteLinea(l){
  await axios.delete(`/api/apu/lineas/${l.id}`)
  lineas.value = lineas.value.filter(x => x.id !== l.id)
  await seleccionarPartida(selectedPartida.value)
}

const totalPU = computed(()=> resumen.value.TOTAL / (apu.value?.rendimiento || 1))
</script>

<template>
  <div class="h-screen bg-[#f6f7fb] flex">
    <!-- IZQUIERDA: Árbol -->
    <IzquierdaArbol
  :proyecto="proyecto"
  :capitulos="capitulos"
  :partidas="partidasFiltradas"
  v-model:filtro="filtroArbol"
  :unidad="u"
  :money="money"
  :selected-id="selectedPartida?.id"
  @select="seleccionarPartida"
/>

    <!-- CENTRO: APU -->
<CentroApu
      :partida="selectedPartida"
      :apu="apu"
      :lineas="lineas"
      :resumen="resumen"
      :unidad="u"
      :money="money"
      :totalPU="totalPU"
      @save-apu="guardarApu"
      @update-linea="updateLinea"
      @delete-linea="deleteLinea"
    />

    <!-- DERECHA: Base de datos -->
   <DerechaBase
      :proyecto="proyecto"
      v-model:tipo="recTipo"
      v-model:q="recQ"
      :recursos="recursos"
      :cargando="cargandoRec"
      :unidad="u"
      :money="money"
      @buscar="buscarRecursos"
      @add="abrirCantidad"
    />

    <!-- Modal cantidad (rendimiento por insumo) -->
    <div v-if="showCant" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl p-5 w-full max-w-sm shadow">
        <h3 class="font-semibold mb-2">Cantidad de {{ recursoSel?.nombre }}</h3>
        <input type="number" step="0.000001" min="0" v-model.number="cant" class="w-full border rounded-lg px-3 py-2">
        <div class="flex justify-end gap-2 mt-3">
          <button class="px-3 py-2 rounded-lg bg-gray-100" @click="showCant=false">Cancelar</button>
          <button class="px-3 py-2 rounded-lg bg-indigo-500 text-white" @click="confirmarCantidad">OK</button>
        </div>
      </div>
    </div>
  </div>
</template>
