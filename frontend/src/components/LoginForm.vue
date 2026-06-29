<template>
  <div class="login-wrapper">
    <article class="login-card">
      <h3 class="txt-center">Iniciar Sesión</h3>

      <!-- Error local (solo visible en esta pantalla) -->
      <p v-if="error" class="error-banner">{{ error }}</p>

      <form @submit.prevent="ejecutarLogin">
        <label for="num_empleado">
          Usuario / Número de Empleado
          <input
            id="num_empleado"
            type="text"
            v-model="form.num_empleado"
            placeholder="Ej: admin01 o EMP-123"
            required
          />
        </label>

        <label for="password">
          Contraseña
          <input
            id="password"
            type="password"
            v-model="form.password"
            placeholder="••••••••"
            required
          />
        </label>

        <button type="submit" class="primary w-100" :disabled="cargando">
          <strong>{{ cargando ? 'Verificando...' : 'Ingresar al Sistema' }}</strong>
        </button>
      </form>
    </article>
  </div>
</template>

<script>
import { API_BASE } from '../api.js'

export default {
  emits: ['login-exitoso'],

  data() {
    return {
      form: { num_empleado: '', password: '' },
      error: '',
      cargando: false,
    }
  },

  methods: {
    async ejecutarLogin() {
      this.error    = ''
      this.cargando = true

      try {
        const resp = await fetch(`${API_BASE}/AuthController.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form),
        })
        const res = await resp.json()

        if (resp.ok) {
          // Éxito: avisar al padre con los datos del usuario
          this.$emit('login-exitoso', res.user)
        } else {
          this.error = res.message
        }
      } catch {
        this.error = 'No se pudo conectar con el servidor. Verifica que XAMPP esté activo.'
      } finally {
        this.cargando = false
      }
    },
  },
}
</script>

<style scoped>
.login-wrapper { max-width: 460px; margin: 4rem auto; }
.login-card {
  background-color: #1a1f26 !important;
  border: 1px solid #2d3748;
  border-top: 4px solid #10ea93;
  padding: 2.5rem;
  box-shadow: 0 15px 35px rgba(0,0,0,0.4);
}
.login-card h3 { color: #ffffff; }
</style>
