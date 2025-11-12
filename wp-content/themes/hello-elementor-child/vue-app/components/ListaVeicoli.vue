<template>
  <div>
    <h2>Elenco Veicoli</h2>
    <ul>
      <li v-for="veh in vehicles" :key="veh.id">
        {{ veh.acf.nome }}
      </li>
    </ul>
  </div>
</template>

<script setup>
defineProps({
  vehicles: Array,
});
</script>
