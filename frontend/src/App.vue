<template>
  <nav class="container">
    <ul>
      <li><strong>🎓 Sistema de Tutorías</strong></li>
    </ul>
    <ul v-if="usuarioLogueado">
      <li><span>Bienvenido, <strong>{{ usuarioLogueado.nombre }}</strong> ({{ usuarioLogueado.rol }})</span></li>
      <li><button class="outline secondary" @click="cerrarSesion">Cerrar Sesión</button></li>
    </ul>
  </nav>

  <main class="container">
    
    <div v-if="!usuarioLogueado" class="login-container">
      <article>
        <h3>🔐 Iniciar Sesión</h3>
        <p v-if="mensajeError" class="error-msg">{{ mensajeError }}</p>
        <form @submit.prevent="ejecutarLogin">
          <label for="num_empleado">Número de Empleado (Usuario)</label>
          <input type="text" id="num_empleado" v-model="loginData.num_empleado" placeholder="Ej: ADMIN01 o EMP-883" required>

          <label for="password">Contraseña</label>
          <input type="password" id="password" v-model="loginData.password" placeholder="••••••••" required>

          <button type="submit">Ingresar al Sistema</button>
        </form>
      </article>
    </div>

    <div v-else-if="usuarioLogueado.rol === 'admin'">
      <h2>🛠️ Panel de Administración</h2>
      <p v-if="notificacion" class="success-msg">{{ notificacion }}</p>
      <p v-if="mensajeError" class="error-msg">{{ mensajeError }}</p>

      <div class="grid">
        <article>
          <h3>👨‍🎓 Alta de Alumnos</h3>
          <form @submit.prevent="guardarAlumnoManual">
            <label>Nombre Completo <input type="text" v-model="nuevoAlumno.nombre" placeholder="Ej. Juan Pérez" required></label>
            <label>Matrícula <input type="text" v-model="nuevoAlumno.matricula" placeholder="Ej. 2026XYZ" required></label>
            <button type="submit">Guardar Alumno</button>
          </form>
          <hr />
          <label><strong>📁 Carga masiva (CSV)</strong></label>
          <input type="file" ref="fileAlumnos" accept=".csv" @change="subirCSVAlumnos">
        </article>

        <article>
          <h3>👨‍🏫 Alta de Profesores</h3>
          <form @submit.prevent="notificacion = 'Profesor registrado con éxito (Simulado)'">
            <label>Nombre del Profesor <input type="text" placeholder="Ej. Dra. Blanca" required></label>
            <label>ID Empleado <input type="text" placeholder="Ej. EMP-999" required></label>
            <button type="submit">Guardar Profesor</button>
          </form>
        </article>
      </div>
    </div>

    <div v-else-if="usuarioLogueado.rol === 'profesor'">
      <h2>👨‍🏫 Panel del Profesor</h2>
      <p v-if="notificacion" class="success-msg">{{ notificacion }}</p>

      <div class="grid">
        <article style="flex: 1;">
          <h3>👥 Mis Alumnos Asignados</h3>
          <ul class="lista-alumnos">
            <li v-for="al in alumnosAsignados" :key="al.id">
              <a href="#" @click.prevent="seleccionarAlumno(al)" :class="{ active: alumnoSeleccionado?.id === al.id }">
                {{ al.nombre }} ({{ al.matricula }})
              </a>
            </li>
          </ul>
        </article>

        <article style="flex: 2;">
          <h3>📝 Registrar Sesión de Tutoría</h3>
          <div v-if="alumnoSeleccionado">
            <p>Atendiendo a: <strong>{{ alumnoSeleccionado.nombre }}</strong></p>
            
            <form @submit.prevent="guardarBitacora">
              <fieldset>
                <legend>Asistencia:</legend>
                <label><input type="radio" v-model="nuevaSesion.asistencia" value="asistio"> Asistió</label>
                <label><input type="radio" v-model="nuevaSesion.asistencia" value="falta"> Falta</label>
              </fieldset>

              <label>Observaciones de la Sesión
                <textarea v-model="nuevaSesion.observaciones" rows="4" placeholder="Detalles académicos..." required></textarea>
              </label>

              <button type="submit">Guardar Sesión / Bitácora</button>
            </form>
          </div>
          <div v-else>
            <p class="txt-muted">Selecciona un alumno de la lista de la izquierda para comenzar.</p>
          </div>
        </article>
      </div>

      <article>
        <h3>📊 Exportar Reporte de Tutorías</h3>
        <p>Genera el archivo CSV que integra automáticamente el diagnóstico psicopedagógico y las alertas de deserción.</p>
        <button class="secondary" @click="descargarReporte">📥 Descargar Reporte Completo (CSV)</button>
      </article>
    </div>

  </main>
</template>

<script>
export default {
  data() {
    return {
      API_BASE: "http://localhost/sistema-tutorias/backend/controllers",
      usuarioLogueado: null,
      mensajeError: "",
      notificacion: "",
      
      // Formulario de Login
      loginData: { num_empleado: "", password: "" },
      
      // Formulario Alumno Manual
      nuevoAlumno: { nombre: "", matricula: "" },
      
      // Datos del profesor
      alumnosAsignados: [],
      alumnoSeleccionado: null,
      nuevaSesion: { asistencia: "asistio", observaciones: "" }
    };
  },
  methods: {
    async ejecutarLogin() {
      this.mensajeError = "";
      try {
        const resp = await fetch(`${this.API_BASE}/AuthController.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.loginData)
        });
        const res = await resp.json();
        if (resp.ok) {
          this.usuarioLogueado = res.user;
          if (this.usuarioLogueado.rol === 'profesor') {
            this.cargarAlumnosAsignados();
          }
        } else {
          this.mensajeError = res.message;
        }
      } catch (e) {
        this.mensajeError = "No se pudo conectar al servidor de XAMPP.";
      }
    },
    async guardarAlumnoManual() {
      this.notificacion = "";
      this.mensajeError = "";
      try {
        const resp = await fetch(`${this.API_BASE}/AlumnoController.php?action=crear_manual`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.nuevoAlumno)
        });
        const res = await resp.json();
        if (resp.ok) {
          this.notificacion = res.message;
          this.nuevoAlumno = { nombre: "", matricula: "" };
        } else {
          this.mensajeError = res.message;
        }
      } catch (e) {
        this.mensajeError = "Error al conectar con la API de alumnos.";
      }
    },
    async subirCSVAlumnos(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      const formData = new FormData();
      formData.append("archivo", file);

      try {
        const resp = await fetch(`${this.API_BASE}/AlumnoController.php?action=carga_masiva`, {
          method: "POST",
          body: formData
        });
        const res = await resp.json();
        if (resp.ok) {
          this.notificacion = `Carga masiva completada: ${res.detalles.nuevos_alumnos} añadidos, ${res.detalles.duplicados_omitidos} duplicados.`;
          this.$refs.fileAlumnos.value = ""; // Limpiar input
        }
      } catch (e) {
        this.mensajeError = "Error al procesar el archivo CSV.";
      }
    },
    async cargarAlumnosAsignados() {
      try {
        const resp = await fetch(`${this.API_BASE}/SesionController.php?action=alumnos_asignados&profesor_id=${this.usuarioLogueado.id}`);
        this.alumnosAsignados = await resp.json();
      } catch (e) {
        console.error("Error al cargar alumnos.");
      }
    },
    seleccionarAlumno(alumno) {
      this.alumnoSeleccionado = alumno;
      this.nuevaSesion = { asistencia: "asistio", observaciones: "" };
    },
    async guardarBitacora() {
      this.notificacion = "";
      const payload = {
        alumno_id: this.alumnoSeleccionado.id,
        profesor_id: this.usuarioLogueado.id,
        asistencia: this.nuevaSesion.asistencia,
        observaciones: this.nuevaSesion.observaciones
      };

      try {
        const resp = await fetch(`${this.API_BASE}/SesionController.php?action=guardar_bitacora`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        if (resp.ok) {
          this.notificacion = "¡Bitácora guardada con éxito!";
          this.alumnoSeleccionado = null;
        }
      } catch (e) {
        alert("Error al guardar la bitácora.");
      }
    },
    descargarReporte() {
      // Abre el archivo CSV directamente en otra pestaña gatillando la descarga forzada de PHP
      window.open(`${this.API_BASE}/SesionController.php?action=exportar_reporte&profesor_id=${this.usuarioLogueado.id}`);
    },
    cerrarSesion() {
      this.usuarioLogueado = null;
      this.loginData = { num_empleado: "", password: "" };
      this.alumnoSeleccionado = null;
      this.notificacion = "";
      this.mensajeError = "";
    }
  }
};
</script>

<style scoped>
.login-container { max-width: 500px; margin: 4rem auto; }
.error-msg { background: #ffcdd2; color: #b71c1c; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem; }
.success-msg { background: #c8e6c9; color: #1b5e20; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem; }
.txt-muted { color: gray; font-style: italic; }
.lista-alumnos a.active { font-weight: bold; text-decoration: underline; color: var(--primary); }
hr { margin: 1.5rem 0; }
</style>