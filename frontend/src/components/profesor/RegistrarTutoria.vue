<template>
  <div>
    <h2>📝 Registro de Sesión de Tutoría</h2>

    <!-- Si hay alumno seleccionado, mostrar el formulario -->
    <div v-if="alumno">
      <article>
        <header>
          Estudiante en atención:
          <strong>{{ alumno.nombre }}</strong>
          <span class="txt-muted"> — Matrícula: <code>{{ alumno.matricula }}</code></span>
        </header>

        <form @submit.prevent="guardarBitacora">
          <div class="grid">
            <!-- Asistencia -->
            <fieldset>
              <legend><strong>Asistencia:</strong></legend>
              <label>
                <input type="radio" v-model="sesion.asistencia" value="asistio" />
                ✅ Cumplió con la cita
              </label>
              <label>
                <input type="radio" v-model="sesion.asistencia" value="falta" />
                ❌ Inasistencia
              </label>
            </fieldset>

            <!-- Nivel académico -->
            <fieldset>
              <legend><strong>Nivel académico:</strong></legend>
              <label>
                <input type="radio" v-model="sesion.nivel" value="Excelente/Regular" />
                Excelente / Regular
              </label>
              <label>
                <input type="radio" v-model="sesion.nivel" value="Asesorias Requeridas" />
                Requiere regularización
              </label>
              <label>
                <input type="radio" v-model="sesion.nivel" value="Condicional" />
                Estatus crítico / Condicional
              </label>
            </fieldset>
          </div>

          <!-- Alerta de deserción -->
          <fieldset class="alert-box-selection">
            <legend><strong>🚨 ¿Detecta riesgo inminente de deserción?</strong></legend>
            <label>
              <input type="radio" v-model="sesion.desercion" value="Bajo Riesgo" />
              No, el alumno se mantiene estable
            </label>
            <label>
              <input type="radio" v-model="sesion.desercion" value="RIESGO MODERADO" />
              Riesgo moderado (problemas externos detectados)
            </label>
            <label>
              <input type="radio" v-model="sesion.desercion" value="RIESGO ALTO (Critico)" />
              🔴 Riesgo alto / Alerta de abandono
            </label>
          </fieldset>

          <!-- Observaciones -->
          <label>
            Observaciones de la sesión y compromisos del alumno
            <textarea
              v-model="sesion.observaciones"
              rows="4"
              placeholder="Escribe el reporte detallado aquí: acuerdos, canalización a psicología, seguimiento pendiente..."
              required
            ></textarea>
          </label>

          <button type="submit" class="primary" :disabled="guardando">
            {{ guardando ? 'Guardando...' : '💾 Guardar Reporte en Bitácora' }}
          </button>
        </form>
      </article>
    </div>

    <!-- Si no hay alumno seleccionado -->
    <div v-else>
      <article class="txt-center">
        <p style="padding:2rem 0;">
          📋 Ve al menú <strong>"Alumnos Asignados"</strong> y pulsa
          <em>"Iniciar Bitácora"</em> en el alumno que deseas atender.
        </p>
      </article>
    </div>

    <!-- Sección de descarga de reporte (siempre visible para el profesor) -->
    <article class="download-section">
      <h4>📥 Exportar Reporte General</h4>
      <p class="txt-muted">
        Descarga un CSV con el historial completo de tutorías, diagnóstico psicopedagógico
        y alertas de deserción de todos tus alumnos.
      </p>
      <button class="contrast" @click="descargarReporte">
        ⬇️ Descargar reporte (.CSV)
      </button>
    </article>
  </div>
</template>

<script>
import { API_BASE } from '../../api.js'

export default {
  props: {
    // Alumno seleccionado desde MisAlumnos (puede ser null)
    alumno:     { type: Object, default: null },
    // ID del profesor autenticado
    profesorId: { type: Number, required: true },
  },
  emits: ['bitacora-guardada', 'mensaje'],

  data() {
    return {
      sesion: {
        asistencia:   'asistio',
        nivel:        'Excelente/Regular',
        desercion:    'Bajo Riesgo',
        observaciones: '',
      },
      guardando: false,
    }
  },

  methods: {
    async guardarBitacora() {
      this.guardando = true

      // Empaquetamos todos los campos en las observaciones para que el backend
      // los pueda exportar en el CSV sin cambiar el esquema de la tabla
      const observacionesCompuestas =
        `[Nivel: ${this.sesion.nivel}] [Riesgo Deserción: ${this.sesion.desercion}] - Nota: ${this.sesion.observaciones}`

      const payload = {
        alumno_id:    this.alumno.id,
        profesor_id:  this.profesorId,
        asistencia:   this.sesion.asistencia,
        observaciones: observacionesCompuestas,
      }

      try {
        const resp = await fetch(`${API_BASE}/SesionController.php?action=guardar_bitacora`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        })
        if (resp.ok) {
          this.$emit('mensaje', { tipo: 'exito', texto: '✅ Bitácora e indicadores guardados exitosamente.' })
          // Avisamos al padre (App.vue) para que limpie el alumno y regrese a la lista
          this.$emit('bitacora-guardada')
        } else {
          const res = await resp.json()
          this.$emit('mensaje', { tipo: 'error', texto: res.message })
        }
      } catch {
        this.$emit('mensaje', { tipo: 'error', texto: 'No se pudo guardar la sesión. Revisa la conexión.' })
      } finally {
        this.guardando = false
      }
    },

    descargarReporte() {
      // Abrir la URL en una nueva pestaña fuerza la descarga del CSV
      window.open(`${API_BASE}/SesionController.php?action=exportar_reporte&profesor_id=${this.profesorId}`)
    },
  },
}
</script>
