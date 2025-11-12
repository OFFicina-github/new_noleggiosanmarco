<template>
  <div class="vue-app">
    <div v-if="loading">Caricamento...</div>

    <div v-else>
      <FormPrenotazione
        v-if="componentType === 'form'"
        :locations="locations"
        :vehicles="vehicles"
      />
      <ListaVeicoli
        v-else-if="componentType === 'lista'"
        :vehicles="vehicles"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FormPrenotazione from './components/FormPrenotazione.vue'
import ListaVeicoli from './components/ListaVeicoli.vue'

// Riceve il tipo di componente dal main.js
const props = defineProps({
  componentType: { type: String, default: 'form' }
})

const locations = ref([])
const vehicles = ref([])
const loading = ref(true)

const fetchData = async () => {
  try {
    const [locRes, vehRes] = await Promise.all([
      fetch(`${wpData.root}wp/v2/location?per_page=100`),
      fetch(`${wpData.root}wp/v2/veicolo?per_page=100`)
    ])
    locations.value = await locRes.json()
    vehicles.value = await vehRes.json()
  } catch (err) {
    console.error('Errore nel caricamento dati:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>
