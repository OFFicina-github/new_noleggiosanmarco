<template>
  <form class="form-auto" :class="tipo">
    <!-- Selettore tipo veicolo -->
    <div class="d-flex flex-row justify-content-between mb-3">
      <div class="tipo-veicolo">
        <p>Scegli la tipologia di veicolo</p>
        <div v-for="tipo in tipi" :key="tipo" :class="('tipo', { active: tipoSelezionato === tipo })"
          @click="tipoSelezionato = tipo">
          {{ tipo }}
        </div>
      </div>

      <div>
        <!-- Inserisci elenco auto da veicoli in base al tipo selezionato -->
        Box Veicoli scelta
      </div>
    </div>

    <div class="form-container">
      <!-- Location -->
      <!-- <label for="same_location">
          Riconsegna nello stesso ufficio
          <input name="same_location" type="checkbox" v-model="stessoUfficio" />
        </label> -->

      <div class="form-control">
        <label for="location_ritiro">Punto di ritiro</label>
        <div class="form-field-container">
          <i class="fas fa fa-instagram"></i>
          <select name="location_ritiro" v-model="ritiro">
            <option disabled value="">Seleziona...</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.acf.nome }}
            </option>
          </select>
        </div>
      </div>

      <div class="form-control">
        <label for="location_consegna">Punto di riconsegna</label>
        <div class="form-field-container">
          <select for="location_consegna" v-model="riconsegna" :disabled="stessoUfficio">
            <option disabled value="">Seleziona...</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.acf.nome }}
            </option>
          </select>
        </div>
      </div>

      <!-- Date e ore -->
      <div class="form-control datetime-hour">
        <label>Data ritiro</label>
        <div class="form-field-container double-input">
          <VueDatePicker v-model="dataRitiro" :time-config="{ enableTimePicker: false }" :hide-time-header="true"
            :disabled-dates="giorniDisabilitati" :min-date="oggi" format="dd/MM/yyyy" placeholder="Seleziona data" />
          <select v-model="oraRitiro" placeholder="orario ritiro">
            <option disabled value="">Seleziona orario</option>
            <option v-for="ora in orariDisponibiliRitiro" :key="ora" :value="ora">
              {{ ora }}
            </option>
          </select>
        </div>
      </div>

      <!-- <div class="form-control datetime-hour">
        <label>Data riconsegna</label>
        <VueDatePicker
          v-model="dataRiconsegna"
          :time-config="{ enableTimePicker: false }"
          :hide-time-header="true"
          :disabled-dates="giorniDisabilitati"
          :min-date="dataRitiro || oggi"
          format="dd/MM/yyyy"
          placeholder="Seleziona data"
          @update:model-value="aggiornaOrariDisponibili('riconsegna')"
        />
        <select v-model="oraRiconsegna">
          <option disabled value="">Seleziona orario</option>
          <option v-for="ora in orariDisponibiliRiconsegna" :key="ora" :value="ora">
            {{ ora }}
          </option>
        </select>
      </div> -->

    </div>

    <pre>{{ statoForm }}</pre>
  </form>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { VueDatePicker } from "@vuepic/vue-datepicker";

defineProps({
  locations: Array,
  vehicles: Array,
});

// Stato base
const tipi = ["Auto", "Furgone", "Pulmino"];
const tipoSelezionato = ref("Auto");

const ritiro = ref("");
const riconsegna = ref("");
const stessoUfficio = ref(false);

const dataRitiro = ref(null);
const oraRitiro = ref("");
const dataRiconsegna = ref(null);
const oraRiconsegna = ref("");

const oggi = new Date();

// Computed: dati location correnti
const locationRitiro = computed(() => locations.find((l) => l.id === ritiro.value));
const locationRiconsegna = computed(() =>
  locations.find((l) => l.id === riconsegna.value)
);

// Disabilita giorni non disponibili
const giorniDisabilitati = (date) => {
  const oggi = new Date();
  if (date < oggi.setHours(0, 0, 0, 0)) return true; // niente date passate
  // Se location selezionata, controlla se aperta
  if (!locationRitiro.value) return false;
  const giorniDisponibili = locationRitiro.value.acf.giorni_disponibili || []; // es: ["Lunedì","Martedì"]
  const giorniSettimana = [
    "Domenica",
    "Lunedì",
    "Martedì",
    "Mercoledì",
    "Giovedì",
    "Venerdì",
    "Sabato",
  ];
  const giorno = giorniSettimana[date.getDay()];
  return !giorniDisponibili.includes(giorno);
};

// Orari dinamici in base alla location
const orariDisponibiliRitiro = ref([]);
const orariDisponibiliRiconsegna = ref([]);

function aggiornaOrariDisponibili(tipo) {
  if (tipo === "ritiro" && locationRitiro.value) {
    orariDisponibiliRitiro.value = locationRitiro.value.acf.orari || [];
  }
  if (tipo === "riconsegna" && locationRiconsegna.value) {
    orariDisponibiliRiconsegna.value = locationRiconsegna.value.acf.orari || [];
  }
}

// Sincronizza riconsegna = ritiro se “stesso ufficio”
watch(stessoUfficio, (val) => {
  if (val) riconsegna.value = ritiro.value;
});

// Stato del form (debug)
const statoForm = computed(() => ({
  tipoSelezionato: tipoSelezionato.value,
  ritiro: ritiro.value,
  riconsegna: riconsegna.value,
  dataRitiro: dataRitiro.value,
  oraRitiro: oraRitiro.value,
  dataRiconsegna: dataRiconsegna.value,
  oraRiconsegna: oraRiconsegna.value,
}));
</script>
