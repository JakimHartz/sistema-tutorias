<template>
  <aside class="sidebar-menu">
    <article class="menu-card">
      <h5>Navegación</h5>
      <ul class="menu-links">

        <!-- Opciones exclusivas del administrador -->
        <template v-if="rol === 'admin'">
          <li>
            <a href="#" @click.prevent="$emit('cambiar-menu', 'dash')"
               :class="{ active: menuActivo === 'dash' }">
              📊 Dashboard General
            </a>
          </li>
          <li>
            <a href="#" @click.prevent="$emit('cambiar-menu', 'alumnos')"
               :class="{ active: menuActivo === 'alumnos' }">
              👨‍🎓 Gestión de Alumnos
            </a>
          </li>
          <li>
            <a href="#" @click.prevent="$emit('cambiar-menu', 'profesores')"
               :class="{ active: menuActivo === 'profesores' }">
              👨‍🏫 Gestión de Profesores
            </a>
          </li>
        </template>

        <!-- Opciones exclusivas del profesor -->
        <template v-if="rol === 'profesor'">
          <li>
            <a href="#" @click.prevent="$emit('cambiar-menu', 'mis_alumnos')"
               :class="{ active: menuActivo === 'mis_alumnos' }">
              👥 Alumnos Asignados
            </a>
          </li>
          <li>
            <a href="#" @click.prevent="$emit('cambiar-menu', 'tutorias')"
               :class="{ active: menuActivo === 'tutorias' }">
              📝 Registrar Tutoría
            </a>
          </li>
        </template>

      </ul>
    </article>
  </aside>
</template>

<script>
export default {
  props: {
    rol:        { type: String, required: true },
    menuActivo: { type: String, required: true },
  },
  emits: ['cambiar-menu'],
}
</script>

<style scoped>
.sidebar-menu { width: 250px; flex-shrink: 0; }
.menu-card    { padding: 1.2rem; }
.menu-card h5 {
  border-bottom: 1px solid #2d3748;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
  color: #a0aec0 !important;
}
.menu-links            { list-style: none; padding: 0; margin: 0; }
.menu-links li         { margin-bottom: 0.6rem; }
.menu-links a {
  display: block; padding: 0.6rem; border-radius: 6px;
  text-decoration: none; color: #cbd5e1; transition: all 0.2s ease;
}
.menu-links a:hover  { background-color: #2d3748; color: #ffffff; }
.menu-links a.active { background-color: #10ea93; color: #111418; font-weight: bold; }
</style>
