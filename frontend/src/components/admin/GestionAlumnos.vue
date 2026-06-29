<template>
  <div>
    <h2>👨‍🎓 Registro y Carga de Estudiantes</h2>

    <div class="grid">
      <!-- Alta manual -->
      <article>
        <h3>✏️ Alta Manual</h3>
        <form @submit.prevent="guardarManual">
          <label>
            Nombre Completo
            <input type="text" v-model="nuevoAlumno.nombre" placeholder="Ej. Juan Pérez López" required />
          </label>
          <label>
            Matrícula (10 dígitos numéricos)
            <input
              type="text" v-model="nuevoAlumno.matricula"
              maxlength="10" minlength="10"
              pattern="\d{10}" placeholder="Ej. 2026010293" required
            />
            <small>Solo números, exactamente 10 caracteres.</small>
          </label>
          <label>
            Asignar Profesor Tutor
            <select v-model="nuevoAlumno.profesor_id">
              <option :value="null">-- Sin asignar por ahora --</option>
              <option v-for="p in profesores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
            </select>
          </label>
          <button type="submit" class="w-100">Guardar Alumno</button>
        </form>
      </article>

      <!-- Carga masiva CSV -->
      <article>
        <h3>📁 Carga Masiva (CSV)</h3>
        <p>Sube tu lista de estudiantes en formato CSV.</p>
        <input type="file" ref="fileInput" accept=".csv" @change="subirCSV" />
        <blockquote class="micro-text" style="margin-top: 1rem;">
          <strong>Formato requerido:</strong><br>
          Matrícula (10 dígitos), Nombre Completo<br>
          Ejemplo: <code>2026112233,David Aquino Ruiz</code>
        </blockquote>
      </article>
    </div>

    <!-- Tabla de asignación de tutores -->
    <article class="download-section" style="margin-top: 2rem;">
      <h3>🔗 Control de Asignaciones Tutor → Alumno</h3>
      <p class="txt-muted">
        Modifica el tutor de cualquier alumno en tiempo real seleccionando la lista desplegable.
      </p>
      <table class="striped">
        <thead>
          <tr>
            <th>Matrícula</th>
            <th>Alumno</th>
            <th>Tutor Actual</th>
            <th>Cambiar Tutor</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="al in todosLosAlumnos" :key="al.id">
            <td><code>{{ al.matricula }}</code></td>
            <td><strong>{{ al.nombre }}</strong></td>
            <td>
              <span v-if="al.tutor_nombre" class="badge" style="color:#90e0ef;background:#1a2333;">
                {{ al.tutor_nombre }}
              </span>
              <span v-else class="badge" style="color:#feb2b2;background:#4a1d1d;">⚠️ Sin Tutor</span>
            </td>
            <td>
              <select
                :value="al.profesor_id"
                @change="reasignarTutor(al.id, $event.target.value)"
                style="margin:0;padding:0.3rem;"
              >
                <option :value="null">-- Sin asignar --</option>
                <option v-for="p in profesores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
              </select>
            </td>
          </tr>
          <tr v-if="todosLosAlumnos.length === 0">
            <td colspan="4" class="txt-center txt-muted">No hay alumnos registrados aún.</td>
          </tr>
        </tbody>
      </table>
    </article>
  </div>
</template>

<script>
import { API_BASE } from '../../api.js'

export default {
  emits: ['mensaje'],

  data() {
    return {
      nuevoAlumno: { nombre: '', matricula: '', profesor_id: null },
      profesores: [],
      // Datos del dashboard para construir la tabla unificada
      dashboard: { profesores_asignaciones: [], alumnos_sin_profesor: [] },
    }
  },

  computed: {
    // Combina alumnos con tutor y alumnos sin tutor en una sola lista para la tabla
    todosLosAlumnos() {
      const lista = []
      this.dashboard.profesores_asignaciones.forEach(prof => {
        prof.alumnos.forEach(al => {
          lista.push({ ...al, profesor_id: prof.id, tutor_nombre: prof.nombre })
        })
      })
      this.dashboard.alumnos_sin_profesor?.forEach(al => {
        lista.push({ ...al, profesor_id: null, tutor_nombre: null })
      })
      return lista
    },
  },

  async mounted() {
    // Cargar datos al entrar a esta sección
    await Promise.all([this.cargarDashboard(), this.cargarProfesores()])
  },

  methods: {
    async cargarDashboard() {
      const resp = await fetch(`${API_BASE}/AlumnoController.php?action=dashboard_admin`)
      this.dashboard = await resp.json()
    },

    async cargarProfesores() {
      const resp = await fetch(`${API_BASE}/AlumnoController.php?action=listar_profesores`)
      this.profesores = await resp.json()
    },

    async guardarManual() {
      try {
        const resp = await fetch(`${API_BASE}/AlumnoController.php?action=crear_manual`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.nuevoAlumno),
        })
        const res = await resp.json()
        if (resp.ok) {
          this.$emit('mensaje', { tipo: 'exito', texto: res.message })
          this.nuevoAlumno = { nombre: '', matricula: '', profesor_id: null }
          await this.cargarDashboard()
        } else {
          this.$emit('mensaje', { tipo: 'error', texto: res.message })
        }
      } catch {
        this.$emit('mensaje', { tipo: 'error', texto: 'Error al crear el alumno.' })
      }
    },

    async subirCSV(event) {
      const file = event.target.files[0]
      if (!file) return

      const formData = new FormData()
      formData.append('archivo', file)

      try {
        const resp = await fetch(`${API_BASE}/AlumnoController.php?action=carga_masiva`, {
          method: 'POST',
          body: formData,
        })
        const res = await resp.json()
        if (resp.ok) {
          this.$emit('mensaje', {
            tipo: 'exito',
            texto: `Carga completada: ${res.detalles.nuevos_alumnos} nuevos, ${res.detalles.duplicados_omitidos} duplicados omitidos.`,
          })
          this.$refs.fileInput.value = ''
          await this.cargarDashboard()
        } else {
          this.$emit('mensaje', { tipo: 'error', texto: res.message })
        }
      } catch {
        this.$emit('mensaje', { tipo: 'error', texto: 'El archivo no cumple el formato CSV requerido.' })
      }
    },

    async reasignarTutor(alumnoId, nuevoProfesorId) {
      // Convertir cadena vacía o "null" a null real de JavaScript
      const profesorPayload = (nuevoProfesorId === 'null' || nuevoProfesorId === '') ? null : parseInt(nuevoProfesorId)
      try {
        const resp = await fetch(`${API_BASE}/AlumnoController.php?action=asignar_tutor`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ alumno_id: alumnoId, profesor_id: profesorPayload }),
        })
        const res = await resp.json()
        if (resp.ok) {
          this.$emit('mensaje', { tipo: 'exito', texto: 'Tutor actualizado correctamente.' })
          await this.cargarDashboard()
        } else {
          this.$emit('mensaje', { tipo: 'error', texto: res.message })
        }
      } catch {
        this.$emit('mensaje', { tipo: 'error', texto: 'No se pudo actualizar la asignación.' })
      }
    },
  },
}
</script>
