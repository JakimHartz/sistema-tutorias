<template>
  <!-- v-if para que no ocupe espacio cuando no hay mensaje -->
  <p v-if="exito" class="success-banner">{{ exito }}</p>
  <p v-if="error" class="error-banner">{{ error }}</p>
</template>

<script>
export default {
  props: {
    exito: { type: String, default: '' },
    error: { type: String, default: '' },
  },
}
</script>
