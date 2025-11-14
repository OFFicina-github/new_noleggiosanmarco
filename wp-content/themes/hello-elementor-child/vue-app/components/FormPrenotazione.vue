<template>
  <form :class="['form-auto', tipoSelezionato.toLowerCase()]" name="preventivo-form" id="preventivo-form">
    <!-- Tipo veicolo -->
    <div class="box-veicoli align-items-end d-flex flex-row justify-content-between mb-3 align-end">
      <div class="tipo-veicolo">
        <p class="pb-2"><strong>Scegli la tipologia di veicolo</strong></p>
        <div class="selettore-veicolo">
          <div v-for="tipo in tipi" :key="tipo" :class="['tipo', { active: tipoSelezionato === tipo }]"
            @click="tipoSelezionato = tipo">
            <i :class="tipo.toLowerCase()" alt="Logo"></i>
            {{ tipo }}
          </div>
        </div>
      </div>

      <div class="scelta-veicolo">
        <p class="label"><strong>Scegli il veicolo</strong></p>
        <div class="veicolo-selezione">
          <select id="veicolo" v-model="veicoloSelezionato" required>
            <option disabled value="">Seleziona...</option>
            <option v-for="v in veicoliFiltrati" :key="v.id" :value="v">
              {{ v.acf.nome }}
            </option>
          </select>

          <div class="immagine-veicolo">
            <img v-if="veicoloSelezionato && veicoloSelezionato.acf?.copertina" :src="veicoloSelezionato.acf.copertina"
              alt="Immagine veicolo" />
            <img v-else:src="themeUrl + '/assets/img/missing-car.svg'" alt="Logo" />
          </div>
        </div>
      </div>
    </div>

    <!-- Checkbox stesso punto -->
    <div class="mb-3">
      <label class="d-flex align-items-center gap-2 pointer">
        <input type="checkbox" v-model="stessoUfficio" />
        <span style="font-size: 12px;">Riconsegna nello stesso punto</span>
      </label>
    </div>

    <!-- Location -->
    <div class="form-container align-items-end">

      <div class="form-control" :class="{ full: stessoUfficio }">
        <label for="location_ritiro">Punto di ritiro</label>
        <div class="form-field-container">
          <i class="marker"></i>
          <select name="location_ritiro" v-model="ritiro" required>
            <option disabled value="">Seleziona...</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.acf.nome">{{ loc.acf.nome }}</option>
          </select>
        </div>
      </div>

      <div v-if="!stessoUfficio" class="form-control">
        <label for="location_consegna">Punto di riconsegna</label>
        <div class="form-field-container">
          <i class="marker"></i>
          <select name="location_consegna" v-model="riconsegna" required>
            <option disabled value="">Seleziona...</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.acf.nome">{{ loc.acf.nome }}</option>
          </select>
        </div>
      </div>

      <!-- Date e ore -->
      <div class="form-control datetime-hour">
        <label>Data ritiro</label>
        <div class="form-field-container double-input">
          <i class="calendar"></i>
          <VueDatePicker v-model="dataRitiro" :disabled="!ritiro" :time-config="{ enableTimePicker: false }"
            :hide-time-header="true" :disabled-dates="giorniDisabilitatiRitiro" :min-date="oggi" :format="'dd/MM/yyyy'"
            :model-type="'format'" placeholder="Data ritiro" :locale="it" />
          <div class="separatore"></div>

          <select v-model="oraRitiro" :disabled="!ritiro || !dataRitiro" required>
            <option disabled value="">Ora</option>
            <option v-for="ora in orariDisponibiliGenerico" :key="ora" :value="ora">{{ ora }}</option>
          </select>
        </div>
      </div>

      <div class="form-control datetime-hour">
        <label>Data riconsegna</label>
        <div class="form-field-container double-input">
          <i class="calendar"></i>
          <VueDatePicker v-model="dataRiconsegna" :disabled="!ritiro || (!stessoUfficio && !riconsegna)"
            :time-config="{ enableTimePicker: false }" :hide-time-header="true"
            :disabled-dates="giorniDisabilitatiRiconsegna" :min-date="dataRitiro || oggi" format="dd/MM/yyyy"
            placeholder="Data riconsegna" :locale="it" />
          <div class="separatore"></div>

          <select v-model="oraRiconsegna" :disabled="!ritiro || (!stessoUfficio && !riconsegna) || !dataRiconsegna"
            required>
            <option disabled value="">Ora</option>
            <option v-for="ora in orariDisponibiliGenerico" :key="ora" :value="ora">{{ ora }}</option>
          </select>
        </div>
      </div>

      <!-- Contatti -->
      <div class="form-control">
        <div class="form-field-container">
          <i class="phone"></i>
          <input type="tel" v-model.trim="telefono" placeholder="Telefono" required />
        </div>
      </div>

      <div class="form-control">
        <div class="form-field-container">
          <i class="email"></i>
          <input type="email" v-model.trim="email" placeholder="Email" required />
        </div>
      </div>

      <!-- Submit -->
      <div class="submit-box">
        <div class="d-flex align-items-center gap-3 justify-content-end">
          <label class="d-flex align-items-center gap-2">
            <input type="checkbox" v-model="accettaPrivacy" />
            <span>Accetto la <a href="/privacy-policy" target="_blank">privacy policy</a></span>
          </label>

          <button type="button" class="btn btn-primary btn-invio" @click="inviaRichiesta" :disabled="!formValido">
            Richiedi preventivo
          </button>
        </div>
      </div>
    </div>
  </form>

  <pre>{{ statoForm }}</pre>

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

// Stati base
const tipi = ["Auto", "Furgone", "Pulmino"];
const tipoSelezionato = ref("Auto");
const veicoloSelezionato = ref(null);
const ritiro = ref("");
const riconsegna = ref("");
const stessoUfficio = ref(false);
const dataRitiro = ref(null);
const oraRitiro = ref("");
const dataRiconsegna = ref(null);
const oraRiconsegna = ref("");
const telefono = ref("");
const email = ref("");
const accettaPrivacy = ref(false);

const oggi = new Date();

// Regex semplice per validare l'email
const emailValida = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()));

// Orari generici
const orariDisponibiliGenerico = ref([
  "9:00", "9:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30",
  "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30",
  "17:00", "17:30", "18:00",
]);

// 🔥 Filtro + ordinamento per ID crescente
const veicoliFiltrati = computed(() => {
  return props.vehicles
    .filter(
      v => v.acf.tipo?.toLowerCase() === tipoSelezionato.value.toLowerCase()
    )
    .sort((a, b) => Number(a.acf.id) - Number(b.acf.id)); // Ordinamento per ID
});

// 🔥 Quando cambia tipo o cambiano i veicoli filtrati -> seleziona quello con ID più basso
watch(veicoliFiltrati, (nuovi) => {
  if (nuovi?.length > 0) {
    veicoloSelezionato.value = nuovi[0]; // primo della lista ordinata = ID più basso
  } else {
    veicoloSelezionato.value = null;
  }
}, { immediate: true });

const giorniDisabilitatiRitiro = (date) => {
  const today = new Date();
  if (date < today.setHours(0, 0, 0, 0)) return true;
  if (!ritiro.value) return true;

  const loc = props.locations.find(l => l.acf.nome === ritiro.value);
  if (!loc || !loc.acf.aperture) return true;

  // Normalizzazione: elimina accenti e trasforma in minuscolo
  const normalize = (str) =>
    str
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");

  // Giorni aperti normalizzati
  const giorniAperti = loc.acf.aperture.map(a => normalize(a.giorno));

  // Giorno attuale normalizzato
  const giorniSettimana = [
    "domenica",
    "lunedi",
    "martedi",
    "mercoledi",
    "giovedi",
    "venerdi",
    "sabato"
  ];
  const giornoCorrente = giorniSettimana[date.getDay()];

  return !giorniAperti.includes(giornoCorrente);
};

const giorniDisabilitatiRiconsegna = (date) => {
  const today = new Date();
  if (date < today.setHours(0, 0, 0, 0)) return true;

  // Normalizza stringhe (rimuove accenti)
  const normalize = (str) =>
    str
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");

  // Scegli la location corretta
  let loc;

  if (stessoUfficio.value) {
    // Riconsegna = ritiro
    loc = props.locations.find(l => l.acf.nome === ritiro.value);
  } else {
    // Location selezionata nel campo Riconsegna
    loc = props.locations.find(l => l.acf.nome === riconsegna.value);
  }

  // Se non c’è la location -> disabilita tutto
  if (!loc || !loc.acf.aperture) return true;

  // Giorni aperti normalizzati
  const giorniAperti = loc.acf.aperture.map(a => normalize(a.giorno));

  // Giorno corrente del calendario normalizzato
  const giorniSettimana = [
    "domenica",
    "lunedi",
    "martedi",
    "mercoledi",
    "giovedi",
    "venerdi",
    "sabato"
  ];

  const giornoCorrente = giorniSettimana[date.getDay()];

  // Ritorna TRUE se il giorno va disabilitato
  return !giorniAperti.includes(giornoCorrente);
};

const formatDate = date => {
  const d = new Date(date);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}/${month}/${year}`;
}

// Validazione generale
const formValido = computed(() => {
  const campiBase = [
    tipoSelezionato.value,
    veicoloSelezionato.value,
    ritiro.value,
    dataRitiro.value,
    oraRitiro.value,
    telefono.value.trim(),
    emailValida.value,
    accettaPrivacy.value,
  ];

  if (!stessoUfficio.value) {
    campiBase.push(riconsegna.value, dataRiconsegna.value, oraRiconsegna.value);
  }

  return campiBase.every(Boolean);
});

// Invio form
async function inviaRichiesta() {
  if (!formValido.value) {
    alert("Compila tutti i campi obbligatori in modo corretto prima di procedere.");
    return;
  }

  const payload = statoForm.value; // l'oggetto che hai già

  try {
    const res = await fetch(`${wpData.root}noleggio/v1/preventivo`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpData.nonce
      },
      body: JSON.stringify(payload),
    });

    if (!res.ok) {
      throw new Error('Errore nella risposta del server');
    }

    // 3️⃣ DataLayer.push
    // window.dataLayer = window.dataLayer || [];
    // window.dataLayer.push({
    //   event: 'preventivo_inviato',
    //   mezzo: payload.tipoSelezionato,
    //   veicolo: payload.veicoloSelezionato?.acf?.nome || '',
    //   location_ritiro: payload.ritiro,
    //   location_riconsegna: payload.riconsegna,
    // });

    // 4️⃣ Redirect
    // window.location.href = '/grazie/'; // pagina di thank-you

  } catch (err) {
    console.error('Errore invio preventivo:', err);
    alert('Si è verificato un errore, riprova più tardi.');
  }
}


// Stato del form (debug)
const statoForm = computed(() => ({
  tipoSelezionato: tipoSelezionato.value,
  ritiro: ritiro.value,
  riconsegna: stessoUfficio.value ? ritiro.value : riconsegna.value,
  stessoUfficio: stessoUfficio.value,
  dataRitiro: formatDate(dataRitiro.value),
  oraRitiro: oraRitiro.value,
  dataRiconsegna: dataRiconsegna.value ? formatDate(dataRiconsegna.value) : null,
  oraRiconsegna: oraRiconsegna.value,
  telefono: telefono.value,
  email: email.value,
  accettaPrivacy: accettaPrivacy.value,
  veicoloSelezionato: veicoloSelezionato.value,
}));

</script>
