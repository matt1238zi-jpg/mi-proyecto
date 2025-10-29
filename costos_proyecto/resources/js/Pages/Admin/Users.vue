<template>
  <div class="p-4 md:p-6">
    <div class="mb-5 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Usuarios</h1>
        <p class="text-sm text-gray-500">Administración de cuentas del sistema</p>
      </div>
      <button @click="openCreate" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:opacity-90">
        + Nuevo usuario
      </button>
    </div>

    <!-- Filtros -->
    <div class="mb-3 flex flex-col md:flex-row gap-2">
      <input v-model.trim="q" @keyup.enter="load" class="rounded-xl border px-3 py-2 w-full md:w-72"
             placeholder="Buscar (nombre, email, usuario)"/>
      <select v-model.number="perPage" @change="load" class="rounded-xl border px-3 py-2 w-32">
        <option :value="10">10</option>
        <option :value="25">25</option>
        <option :value="50">50</option>
      </select>
    </div>

    <div v-if="loading" class="text-sm text-gray-500">Cargando…</div>
    <p v-if="error" class="text-sm text-red-600 mb-2">{{ error }}</p>

    <!-- Tabla -->
    <div class="rounded-2xl bg-white shadow">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500">
            <th class="py-2 px-3">Nombre</th>
            <th class="py-2 px-3">Email</th>
            <th class="py-2 px-3">Usuario</th>
            <th class="py-2 px-3">Rol</th>
            <th class="py-2 px-3 w-24"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in items" :key="u.id" class="border-t">
            <td class="py-2 px-3">{{ u.nombre_completo }}</td>
            <td class="py-2 px-3">{{ u.email }}</td>
            <td class="py-2 px-3">{{ u.username }}</td>
            <td class="py-2 px-3">
              <span class="px-2 py-1 rounded-full text-xs"
                    :class="u.rol_id===1 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700'">
                {{ u.rol_id===1 ? 'Administrador' : 'Usuario' }}
              </span>
            </td>
            <td class="py-2 px-3 text-right">
              <button @click="openEdit(u)" class="text-indigo-600 hover:underline mr-2">Editar</button>
              <button @click="confirmDelete(u)" class="text-red-600 hover:underline">Borrar</button>
            </td>
          </tr>
          <tr v-if="!loading && items.length===0">
            <td colspan="5" class="py-6 text-center text-gray-500">Sin resultados.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
      <div>Mostrando {{ items.length }} de {{ total }} usuarios</div>
      <div class="flex gap-2">
        <button :disabled="page<=1" @click="page--, load()" class="px-3 py-1 rounded-lg bg-white border disabled:opacity-50">«</button>
        <span>Página {{ page }}</span>
        <button :disabled="page*perPage>=total" @click="page++, load()" class="px-3 py-1 rounded-lg bg-white border disabled:opacity-50">»</button>
      </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div v-if="open" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">{{ isEdit ? 'Editar usuario' : 'Nuevo usuario' }}</h3>
          <button @click="close" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="text-sm text-gray-600">Nombre completo</label>
            <input v-model.trim="form.nombre_completo" class="w-full rounded-xl border px-3 py-2"/>
          </div>
          <div>
            <label class="text-sm text-gray-600">Email</label>
            <input type="email" v-model.trim="form.email" class="w-full rounded-xl border px-3 py-2"/>
          </div>
          <div>
            <label class="text-sm text-gray-600">Usuario</label>
            <input v-model.trim="form.username" class="w-full rounded-xl border px-3 py-2"/>
          </div>
          <div>
            <label class="text-sm text-gray-600">Rol</label>
            <select v-model.number="form.rol_id" class="w-full rounded-xl border px-3 py-2">
              <option :value="1">Administrador</option>
              <option :value="2">Usuario</option>
            </select>
          </div>

          <div v-if="!isEdit">
            <label class="text-sm text-gray-600">Contraseña</label>
            <input type="password" v-model="form.password" class="w-full rounded-xl border px-3 py-2"/>
          </div>
          <div v-if="!isEdit">
            <label class="text-sm text-gray-600">Confirmar</label>
            <input type="password" v-model="form.password_confirmation" class="w-full rounded-xl border px-3 py-2"/>
          </div>
        </div>

        <p v-if="formMsg" class="text-sm mt-2" :class="formMsg.startsWith('⚠') ? 'text-red-600':'text-emerald-700'">{{ formMsg }}</p>

        <div class="mt-4 flex justify-end gap-2">
          <button @click="close" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200">Cancelar</button>
          <button :disabled="saving" @click="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:opacity-90 disabled:opacity-70">
            {{ saving ? 'Guardando…' : (isEdit ? 'Actualizar' : 'Crear') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm borrar -->
    <div v-if="delOpen" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow">
        <p class="mb-4">¿Eliminar al usuario <b>{{ target?.nombre_completo }}</b>? Esta acción no se puede deshacer.</p>
        <div class="flex justify-end gap-2">
          <button @click="delOpen=false" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200">Cancelar</button>
          <button @click="doDelete" class="px-4 py-2 rounded-xl bg-red-600 text-white hover:opacity-90">Eliminar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(false)
const error = ref('')
const q = ref('')
const page = ref(1)
const perPage = ref(10)
const total = ref(0)
const items = ref([])

async function load () {
  loading.value = true; error.value=''
  try {
    const { data } = await axios.get('/api/admin/users', {
      params: { q: q.value, page: page.value, per_page: perPage.value }
    })
    items.value = data.data || data.items || []
    total.value = data.total ?? items.value.length
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo cargar usuarios'
  } finally {
    loading.value = false
  }
}

onMounted(load)

/* ----- Crear / Editar ----- */
const open = ref(false)
const isEdit = ref(false)
const saving = ref(false)
const formMsg = ref('')
const form = reactive({
  id: null,
  nombre_completo: '',
  email: '',
  username: '',
  rol_id: 2,
  password: '',
  password_confirmation: ''
})

function resetForm () {
  form.id = null
  form.nombre_completo = ''
  form.email = ''
  form.username = ''
  form.rol_id = 2
  form.password = ''
  form.password_confirmation = ''
  formMsg.value = ''
}

function openCreate () { resetForm(); isEdit.value=false; open.value=true }
function openEdit (u) {
  resetForm(); isEdit.value=true; open.value=true
  Object.assign(form, {
    id: u.id,
    nombre_completo: u.nombre_completo,
    email: u.email,
    username: u.username,
    rol_id: u.rol_id
  })
}
function close () { open.value=false }

async function submit () {
  formMsg.value=''; saving.value=true
  try {
    if (isEdit.value) {
      await axios.put(`/api/admin/users/${form.id}`, {
        nombre_completo: form.nombre_completo,
        email: form.email,
        username: form.username,
        rol_id: form.rol_id
      })
    } else {
      await axios.post('/api/admin/users', {
        nombre_completo: form.nombre_completo,
        email: form.email,
        username: form.username,
        rol_id: form.rol_id,
        password: form.password,
        password_confirmation: form.password_confirmation
      })
    }
    close(); await load()
  } catch (e) {
    formMsg.value = '⚠ ' + (e.response?.data?.message || 'Error al guardar')
  } finally {
    saving.value=false
  }
}

/* ----- Eliminar ----- */
const delOpen = ref(false)
const target = ref(null)
function confirmDelete (u) { target.value=u; delOpen.value=true }
async function doDelete () {
  try {
    await axios.delete(`/api/admin/users/${target.value.id}`)
    delOpen.value=false
    await load()
  } catch (e) {
    delOpen.value=false
    error.value = e.response?.data?.message || 'No se pudo eliminar'
  }
}
</script>
