<template>
  <div>
    <h2>👨‍🏫 Registro de Personal Docente / Tutores</h2>

    <article style="max-width: 600px;">
      <h3>Alta de Profesor</h3>
      <form @submit.prevent="guardarProfesor">
        <label>
          Nombre del Docente
          <input type="text" v-model="form.nombre" placeholder="Ej. Dr. Armando Mendoza" required />
        </label>
        <label>
          Número de Empleado (será su usuario de acceso)
          <input type="text" v-model="form.num_empleado" placeholder="Ej. EMP-883" required />
        </label>
        <label>
          Contraseña de Acceso
          <input type="password" v-model="form.password" placeholder="Establece una contraseña" required />
        </label>
        <button type="submit" class="primary" :disabled="cargando">
          {{ cargando ? 'Registrando...' : 'Registrar en la Base de Datos' }}
        </button>
      </form>
    </article>
  </div>
</template>

<script>
import { API_BASE } from '../../api.js'

export default {
  emits: ['mensaje'],

  data() {
    return {
      form: { nombre: '', num_empleado: '', password: '' },
      cargando: false,
    }
  },

  methods: {
    async guardarProfesor() {
      this.cargando = true
      try {
        const resp = await fetch(`${API_BASE}/AuthController.php?action=registrar_profesor`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form),
        })
        const res = await resp.json()

        if (resp.ok && res.status === 'success') {
          this.$emit('mensaje', { tipo: 'exito', texto: res.message })
          this.form = { nombre: '', num_empleado: '', password: '' }
        } else {
          this.$emit('mensaje', { tipo: 'error', texto: res.message || 'Error al registrar.' })
        }
      } catch {
        this.$emit('mensaje', { tipo: 'error', texto: 'Fallo de comunicación con el servidor.' })
      } finally {
        this.cargando = false
      }
    },
  },
}
</script>
