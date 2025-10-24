import '../css/app.css'
import { createApp } from 'vue'
import RootApp from './RootApp.vue'   // tu UI de login/registro
import axios from 'axios'

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
const csrf = document.querySelector('meta[name="csrf-token"]')
if (csrf) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf.getAttribute('content')

const el = document.getElementById('app')
if (el) createApp(RootApp).mount('#app')
