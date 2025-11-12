<template>

  <!-- <ul>
    <li v-for="veh in vehicles" :key="veh.id">
      {{ veh}}
    </li>
  </ul> -->

  <form :class="['form-auto', tipoSelezionato.toLowerCase()]" name="preventivo-form" id="preventivo-form">
    <!-- Selettore tipo veicolo -->
    <div class="align-items-end d-flex flex-row justify-content-between mb-3 align-end">
      <div class="tipo-veicolo">
        <p class="pb-2">Scegli la tipologia di veicolo</p>
        <div class="selettore-veicolo">
          <div v-for="tipo in tipi" :key="tipo" :class="['tipo', { active: tipoSelezionato === tipo }]"
            @click="tipoSelezionato = tipo">
            <i :class="tipo.toLowerCase()" alt="Logo"></i>
            {{ tipo }}
          </div>
        </div>
      </div>

      <div class="scelta-veicolo">
        <p class="label">Scegli l'auto</p>
        <div class="veicolo-selezione">

          <select id="veicolo" v-model="veicoloSelezionato">
            <option v-for="v in veicoliFiltrati" :key="v.id" :value="v">
              {{ v.acf.nome }}
            </option>
          </select>

          <div class="immagine-veicolo">
            <div v-if="veicoloSelezionato && veicoloSelezionato.acf?.copertina">
              <img :src="veicoloSelezionato.acf.copertina" alt="Immagine veicolo" />
            </div>
            <div v-else class="placeholder-immagine"></div>
          </div>

        </div>
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
          <i class="marker" alt="Logo"></i>
          <select name="location_ritiro" v-model="ritiro">
            <option disabled value="">Seleziona...</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.acf.nome">
              {{ loc.acf.nome }}
            </option>
          </select>
        </div>
      </div>

      <div class="form-control">
        <label for="location_consegna">Punto di riconsegna</label>
        <div class="form-field-container">
          <i class="marker" alt="Logo"></i>
          <select for="location_consegna" v-model="riconsegna" :disabled="stessoUfficio">
            <option disabled value="">Seleziona...</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.acf.nome">
              {{ loc.acf.nome }}
            </option>
          </select>
        </div>
      </div>

      <!-- Date e ore -->
      <div class="form-control datetime-hour">
        <label>Data ritiro</label>
        <div class="form-field-container double-input">
          <i class="calendar" alt="Logo"></i>
          <VueDatePicker v-model="dataRitiro" :time-config="{ enableTimePicker: false }" :enableTimePicker="false"
            :enable-time-picker="false" :hide-time-header="true" :disabled-dates="giorniDisabilitati" :min-date="oggi"
            :format="'dd/MM/yyyy'" :model-type="'format'" placeholder="Data ritiro" :locale="it" />
          <div class="separatore"></div>

          <select v-model="oraRitiro" placeholder="orario ritiro">
            <option disabled value="">Ora</option>
            <option v-for="ora in orariDisponibiliGenerico" :key="ora" :value="ora">
              {{ ora }}
            </option>
          </select>
        </div>
      </div>

      <div class="form-control datetime-hour">
        <label>Data riconsegna</label>
        <div class="form-field-container double-input">
          <img :src="themeUrl + '/assets/img/form-calendar.svg'" alt="Logo" />
          <i class="calendar" alt="Logo"></i>
          <VueDatePicker v-model="dataRiconsegna" :time-config="{ enableTimePicker: false }" :hide-time-header="true"
            :disabled-dates="giorniDisabilitati" :min-date="dataRitiro || oggi" format="dd/MM/yyyy"
            placeholder="Data consegna" />
          <div class="separatore"></div>
          <!-- @update:model-value="aggiornaOrariDisponibili('riconsegna')" -->
          <select v-model="oraRiconsegna">
            <option disabled value="">Ora</option>
            <option v-for="ora in orariDisponibiliGenerico" :key="ora" :value="ora">
              {{ ora }}
            </option>
          </select>
        </div>
      </div>

      <div class="form-control">
        <div class="form-field-container">
          <i class="phone" alt="Logo"></i>
          <input type="tel" v-model="telefono" placeholder="Telefono" />
        </div>
      </div>

      <div class="form-control">
        <div class="form-field-container">
          <i class="email" alt="Logo"></i>
          <input type="email" v-model="email" placeholder="Email" />
        </div>
      </div>

      <div></div>

      <div class="submit-box ">
        <div class="d-flex align-items-center gap-3 justify-content-end">
          <!-- Checkbox Privacy -->
          <label class="d-flex align-items-center gap-2">
            <input type="checkbox" v-model="accettaPrivacy" />
            <span>Accetto la <a href="/privacy-policy" target="_blank">privacy policy</a></span>
          </label>

          <!-- Pulsante Submit -->
          <button type="button" class="btn btn-ptimary btn-invio" @click="inviaRichiesta" :disabled="!accettaPrivacy">
            Richiedi preventivo
          </button>
        </div>
      </div>

    </div>

    <!-- <pre>{{ statoForm }}</pre> -->


  </form>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { VueDatePicker } from "@vuepic/vue-datepicker";
import { getCurrentInstance } from "vue";
import { it } from "date-fns/locale";

const props = defineProps({
  locations: Array,
  vehicles: Array,
});

const themeUrl = getCurrentInstance().appContext.config.globalProperties.$themeUrl;

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
const telefono = ref("");
const email = ref("");
const veicoloSelezionato = ref(null);
const oggi = new Date();

const orariDisponibiliGenerico = ref([
  "9:00",
  "9:30",
  "10:00",
  "10:30",
  "11:00",
  "11:30",
  "12:00",
  "12:30",
  "13:00",
  "13:30",
  "14:00",
  "14:30",
  "15:00",
  "15:30",
  "16:00",
  "16:30",
  "17:00",
  "17:30",
  "18:00",
]);

const veicoliFiltrati = computed(() =>
  props.vehicles.filter(
    (v) => v.acf.tipo?.toLowerCase() === tipoSelezionato.value.toLowerCase()
  )
);

watch(
  () => props.vehicles,
  (nuoviVeicoli) => {
    if (nuoviVeicoli && nuoviVeicoli.length > 0 && !veicoloSelezionato.value) {
      const primaAuto = nuoviVeicoli.find(
        (v) => v.acf.tipo?.toLowerCase() === 'auto'
      )
      if (primaAuto) veicoloSelezionato.value = primaAuto;
    }
  },
  { immediate: true }
)

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
// DA FARE BENE



// Sincronizza riconsegna = ritiro se “stesso ufficio”
watch(stessoUfficio, (val) => {
  if (val) riconsegna.value = ritiro.value;
});

watch(dataRitiro, (newVal) => {
  if (newVal) {
    const d = new Date(newVal);
    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();
    dataRitiro = `${day}/${month}/${year}`;
  }
});

const formatDate = (date) => {
  const d = new Date(date);
  const day = String(d.getDate()).padStart(2, "0");
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const year = d.getFullYear();
  return `${day}/${month}/${year}`;
};

watch(veicoliFiltrati, (nuoviVeicoli) => {
  if (nuoviVeicoli.length > 0) {
    veicoloSelezionato.value = nuoviVeicoli[0];
  } else {
    veicoloSelezionato.value = null;
  }
});

// Stato del form (debug)
const statoForm = computed(() => ({
  tipoSelezionato: tipoSelezionato.value,
  ritiro: ritiro.value,
  riconsegna: riconsegna.value,
  dataRitiro: formatDate(dataRitiro.value),
  // dataRitiro: dataRitiro.value,
  oraRitiro: oraRitiro.value,
  dataRiconsegna: dataRiconsegna.value,
  oraRiconsegna: oraRiconsegna.value,
  telefono: telefono.value,
  email: email.value,
  veicoloSelezionato: veicoloSelezionato.value,
}));
</script>
