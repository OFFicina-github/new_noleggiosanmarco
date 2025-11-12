import { createApp } from 'vue'
import App from './app.vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

// Trova il container nel DOM
const el = document.getElementById('vue-app')

if (el) {
  const componentType = el.dataset.component || 'form' // default form

  // ✅ Crea l’app e assegnala a una variabile
  const app = createApp(App, { componentType })

  // ✅ Registra il componente globalmente
  app.component('VueDatePicker', VueDatePicker)

  app.config.globalProperties.$themeUrl = window.themeUrl || '/wp-content/themes/hello-elementor-child';

  // ✅ Monta l’app
  app.mount('#vue-app')
}
