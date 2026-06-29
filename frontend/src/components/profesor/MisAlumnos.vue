<template>
  <div>
    <h2>👥 Mis Alumnos Asignados</h2>
    <p class="txt-muted">Lista oficial de estudiantes bajo su tutoría este periodo.</p>

    <article>
      <table class="striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Matrícula</th>
            <th>Nombre del Estudiante</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(al, index) in alumnos" :key="al.id">
            <td>{{ index + 1 }}</td>
            <td><code>{{ al.matricula }}</code></td>
            <td><strong>{{ al.nombre }}</strong></td>
            <td>
              <!-- Emitir evento con el alumno seleccionado hacia App.vue -->
              <button class="btn-sm outline secondary" @click="$emit('iniciar-tutoria', al)">
                📝 Iniciar Bitácora
              </button>
            </td>
          </tr>
          <tr v-if="!cargando && alumnos.length === 0">
            <td colspan="4" class="txt-center txt-muted">
              No tienes alumnos asignados todavía. Contacta al administrador.
            </td>
          </tr>
          <tr v-if="cargando">
            <td colspan="4" class="txt-center txt-muted">Cargando lista...</td>
          </tr>
        </tbody>
      </table>
    </article>
  </div>
</template>

<script>
import { API_BASE } from '../../api.js'

export default {
  props: {
    // ID del profesor autenticado, para pedir al backend solo SUS alumnos
    profesorId: { type: Number, required: true },
  },
  emits: ['iniciar-tutoria'],

  data() {
    return {
      alumnos: [],
      cargando: true,
    }
  },

  async mounted() {
    await this.cargar()
  },

  methods: {
    async cargar() {
      this.cargando = true
      try {
        const resp = await fetch(
          `${API_BASE}/SesionController.php?action=alumnos_asignados&profesor_id=${this.profesorId}`
        )
        this.alumnos = await resp.json()
      } catch {
        // Error silencioso: la tabla mostrará el mensaje de "sin alumnos"
        this.alumnos = []
      } finally {
        this.cargando = false
      }
    },
  },
}
</script>
