<template>
  <div>
    <h2>📊 Dashboard General de Asignaciones</h2>
    <p class="txt-muted">Visualiza qué profesor tiene a cargo a cada estudiante.</p>

    <!-- Cuadrícula de profesores -->
    <div class="profesores-grid">
      <div v-for="prof in datos.profesores_asignaciones" :key="prof.id">
        <article class="dash-card">
          <header>
            <strong>{{ prof.nombre }}</strong><br>
            <small class="txt-muted">Núm. Empleado: {{ prof.num_empleado }}</small>
          </header>
          <span class="badge">{{ prof.total_alumnos }} alumno(s) asignado(s)</span>

          <ul class="mini-list" v-if="prof.alumnos.length > 0">
            <li v-for="al in prof.alumnos" :key="al.id">
              🔹 {{ al.nombre }} <code class="micro-text">({{ al.matricula }})</code>
            </li>
          </ul>
          <p v-else class="txt-muted micro-text">Sin alumnos a cargo actualmente.</p>
        </article>
      </div>
    </div>

    <!-- Alerta de alumnos sin tutor -->
    <article class="warning-card" v-if="datos.alumnos_sin_profesor?.length > 0">
      <h5>⚠️ Alumnos Sin Profesor Asignado ({{ datos.alumnos_sin_profesor.length }})</h5>
      <div class="grid-tags">
        <span
          v-for="al in datos.alumnos_sin_profesor"
          :key="al.id"
          class="tag-alert"
        >
          {{ al.nombre }} ({{ al.matricula }})
        </span>
      </div>
    </article>

    <p v-if="cargando" class="txt-muted txt-center">Cargando datos...</p>
  </div>
</template>

<script>
import { API_BASE } from '../../api.js'

export default {
  emits: ['mensaje'],

  data() {
    return {
      cargando: true,
      datos: { profesores_asignaciones: [], alumnos_sin_profesor: [] },
    }
  },

  // Se ejecuta automáticamente cuando el componente aparece en pantalla
  async mounted() {
    await this.cargar()
  },

  methods: {
    async cargar() {
      this.cargando = true
      try {
        const resp = await fetch(`${API_BASE}/AlumnoController.php?action=dashboard_admin`)
        this.datos = await resp.json()
      } catch {
        this.$emit('mensaje', { tipo: 'error', texto: 'No se pudo cargar el dashboard.' })
      } finally {
        this.cargando = false
      }
    },
  },
}
</script>
