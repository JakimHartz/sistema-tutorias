<template>
  <header class="navbar-header">
    <nav class="container">
      <ul>
        <li><strong class="brand-title">TutorTrack • Sistema de Control de Tutorías</strong></li>
      </ul>
      <!-- Solo se muestra cuando hay un usuario logueado -->
      <ul v-if="usuario">
        <li>
          <span class="user-badge">
            {{ usuario.nombre }} <strong>({{ usuario.rol.toUpperCase() }})</strong>
          </span>
        </li>
        <li>
          <!-- Emite el evento al padre (App.vue) para que limpie el estado global -->
          <button class="outline contrast btn-sm" @click="$emit('cerrar-sesion')">
            Cerrar Sesión
          </button>
        </li>
      </ul>
    </nav>
  </header>
</template>

<script>
export default {
  // Props: datos que el padre (App.vue) nos pasa hacia abajo
  props: {
    usuario: { type: Object, default: null }
  },
  // Emits: eventos que este componente puede enviar hacia arriba al padre
  emits: ['cerrar-sesion']
}
</script>

<style scoped>
.navbar-header {
  background-color: #111418;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  padding: 0.75rem 0;
  border-bottom: 1px solid #2d3748;
  margin-bottom: 2rem;
}
.brand-title {
  color: #10ea93;
  font-size: 1.3rem;
  font-weight: 800;
  letter-spacing: 0.5px;
}
.user-badge {
  color: #90e0ef;
  font-size: 0.9rem;
  margin-right: 1rem;
  background: #1a2333;
  padding: 0.3rem 0.7rem;
  border-radius: 6px;
}
</style>
