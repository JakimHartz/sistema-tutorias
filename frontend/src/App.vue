<template>
  <!-- BARRA DE NAVEGACIÓN SUPERIOR MODERNA -->
  <header class="navbar-header">
    <nav class="container">
      <ul>
        <li><strong class="brand-title">🎓 Sistema de Control de Tutorías</strong></li>
      </ul>
      <ul v-if="usuarioLogueado">
        <li>
          <span class="user-badge">
            ⚡ {{ usuarioLogueado.nombre }} <strong>({{ usuarioLogueado.rol.toUpperCase() }})</strong>
          </span>
        </li>
        <li><button class="outline contrast btn-sm" @click="cerrarSesion">Cerrar Sesión</button></li>
      </ul>
    </nav>
  </header>

  <main class="container main-content">
    
    <!-- 1. PANTALLA DE LOGIN -->
    <div v-if="!usuarioLogueado" class="login-wrapper">
      <article class="login-card">
        <h3 class="txt-center">🔐 Iniciar Sesión</h3>
        <p v-if="mensajeError" class="error-banner">{{ mensajeError }}</p>
        
        <form @submit.prevent="ejecutarLogin">
          <label for="num_empleado">Usuario / Número de Empleado
            <input type="text" id="num_empleado" v-model="loginData.num_empleado" placeholder="Ej: ADMIN01 o EMP-883" required>
          </label>

          <label for="password">Contraseña
            <input type="password" id="password" v-model="loginData.password" placeholder="••••••••" required>
          </label>

          <button type="submit" class="primary w-100">Ingresar al Sistema</button>
        </form>
      </article>
    </div>

    <!-- 2. DISEÑO PARA USUARIOS LOGUEADOS (ADMIN / PROFESOR) -->
    <div v-else class="dashboard-layout">
      
      <!-- MENU DE NAVEGACIÓN LATERAL -->
      <aside class="sidebar-menu">
        <article class="menu-card">
          <h5>Navegación</h5>
          <ul class="menu-links">
            <!-- Menús de Admin -->
            <template v-if="usuarioLogueado.rol === 'admin'">
              <li><a href="#" @click.prevent="menuActivo = 'dash'" :class="{active: menuActivo === 'dash'}">📊 Dashboard General</a></li>
              <li><a href="#" @click.prevent="menuActivo = 'alumnos'" :class="{active: menuActivo === 'alumnos'}">👨‍🎓 Gestión Alumnos</a></li>
              <li><a href="#" @click.prevent="menuActivo = 'profesores'" :class="{active: menuActivo === 'profesores'}">👨‍🏫 Gestión Profesores</a></li>
            </template>
            
            <!-- Menús de Profesor -->
            <template v-if="usuarioLogueado.rol === 'profesor'">
              <li><a href="#" @click.prevent="menuActivo = 'mis_alumnos'" :class="{active: menuActivo === 'mis_alumnos'}">👥 Alumnos Asignados</a></li>
              <li><a href="#" @click.prevent="menuActivo = 'tutorias'" :class="{active: menuActivo === 'tutorias'}">📝 Registrar Tutoría</a></li>
            </template>
          </ul>
        </article>
      </aside>

      <!-- CONTENIDO DINÁMICO CENTRAL -->
      <section class="content-viewer">
        <p v-if="notificacion" class="success-banner">{{ notificacion }}</p>
        <p v-if="mensajeError" class="error-banner">{{ mensajeError }}</p>

        <!-- ========================================== -->
        <!-- VISTAS DEL ADMINISTRADOR                   -->
        <!-- ========================================== -->
        
        <!-- A. DASHBOARD ADMIN -->
        <div v-if="usuarioLogueado.rol === 'admin' && menuActivo === 'dash'">
          <h2>📊 Dashboard General de Asignaciones</h2>
          <p class="txt-muted">Visualiza en tiempo real qué profesor tiene a cargo a cada estudiante.</p>
          
          <div class="grid">
            <div v-for="prof in adminDashboardData.profesores_asignaciones" :key="prof.id">
              <article class="dash-card">
                <header>
                  <strong>{{ prof.nombre }}</strong> <br>
                  <small class="txt-muted">Num. Empleado: {{ prof.num_empleado }}</small>
                </header>
                <span class="badge">{{ prof.total_alumnos }} Alumnos asignados</span>
                
                <ul class="mini-list" v-if="prof.alumnos.length > 0">
                  <li v-for="al in prof.alumnos" :key="al.id">
                    🔹 {{ al.nombre }} <code class="micro-text">({{ al.matricula }})</code>
                  </li>
                </ul>
                <p v-else class="txt-muted micro-text">Sin alumnos a cargo actualmente.</p>
              </article>
            </div>
          </div>

          <!-- Alumnos Huérfanos -->
          <article class="warning-card" v-if="adminDashboardData.alumnos_sin_profesor?.length > 0">
            <h5>⚠️ Alumnos Sin Profesor Asignado</h5>
            <div class="grid-tags">
              <span v-for="al in adminDashboardData.alumnos_sin_profesor" :key="al.id" class="tag-alert">
                {{ al.nombre }} ({{ al.matricula }})
              </span>
            </div>
          </article>
        </div>

        <!-- B. GESTIÓN DE ALUMNOS (ADMIN) -->
        <div v-if="usuarioLogueado.rol === 'admin' && menuActivo === 'alumnos'">
          <h2>👨‍🎓 Registro y Carga de Estudiantes</h2>
          <div class="grid">
            <article>
              <h3>Alta Manual</h3>
              <form @submit.prevent="guardarAlumnoManual">
                <label>Nombre Completo
                  <input type="text" v-model="nuevoAlumno.nombre" placeholder="Ej. Juan Pérez López" required>
                </label>
                <label>Matrícula (Exactamente 10 dígitos)
                  <input type="text" v-model="nuevoAlumno.matricula" maxlength="10" minlength="10" pattern="\d{10}" placeholder="Ej. 2026010293" required>
                  <small>Debe contener sólo números (10 caracteres).</small>
                </label>
                <label>Asignar Profesor Tutor
                  <select v-model="nuevoAlumno.profesor_id">
                    <option :value="null">-- Dejar sin asignar por ahora --</option>
                    <option v-for="p in listaProfesores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                  </select>
                </label>
                <button type="submit" class="w-100">Guardar Alumno</button>
              </form>
            </article>

            <article>
              <h3>📁 Carga Masiva (CSV)</h3>
              <p>Sube tu lista de asistencia en un archivo separado por comas.</p>
              <input type="file" ref="fileAlumnos" accept=".csv" @change="subirCSVAlumnos">
              <blockquote class="micro-text">
                <strong>Formato del archivo:</strong><br>
                Matrícula(10 digitos), Nombre Completo<br>
                Ejemplo: 2026112233,David Aquino
              </blockquote>
            </article>
          </div>
        </div>

        <!-- C. GESTIÓN DE PROFESORES (ADMIN) -->
        <div v-if="usuarioLogueado.rol === 'admin' && menuActivo === 'profesores'">
          <h2>👨‍🏫 Registro de Personal Docente / Tutores</h2>
          <article style="max-width: 600px;">
            <h3>Alta de Profesor</h3>
            <form @submit.prevent="guardarProfesorReal">
              <label>Nombre del Docente
                <input type="text" v-model="nuevoProfesor.nombre" placeholder="Ej. Dr. Armando Mendoza" required>
              </label>
              <label>Número de Empleado (Código de acceso)
                <input type="text" v-model="nuevoProfesor.num_empleado" placeholder="Ej. EMP-883" required>
              </label>
              <label>Contraseña de Acceso
                <input type="password" v-model="nuevoProfesor.password" placeholder="Establece una contraseña" required>
              </label>
              <button type="submit" class="primary">Registrar en la Base de Datos</button>
            </form>
          </article>
        </div>


        <!-- ========================================== -->
        <!-- VISTAS DEL PROFESOR                        -->
        <!-- ========================================== -->
        
        <!-- A. VER MIS ALUMNOS ASIGNADOS -->
        <div v-if="usuarioLogueado.rol === 'profesor' && menuActivo === 'mis_alumnos'">
          <h2>👥 Mis Alumnos Asignados</h2>
          <p class="txt-muted">Lista oficial de estudiantes bajo tu tutoría académica este periodo.</p>
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
                <tr v-for="(al, index) in alumnosAsignados" :key="al.id">
                  <td>{{ index + 1 }}</td>
                  <td><code>{{ al.matricula }}</code></td>
                  <td><strong>{{ al.nombre }}</strong></td>
                  <td>
                    <button class="btn-sm secondary outline" @click="irATutoria(al)">📝 Iniciar Bitácora</button>
                  </td>
                </tr>
                <tr v-if="alumnosAsignados.length === 0">
                  <td colspan="4" class="txt-center txt-muted">No tienes alumnos asignados todavía.</td>
                </tr>
              </tbody>
            </table>
          </article>
        </div>

        <!-- B. FORMULARIO DE TUTORÍAS AVANZADO -->
        <div v-if="usuarioLogueado.rol === 'profesor' && menuActivo === 'tutorias'">
          <h2>📝 Registro de Sesión de Tutoría</h2>
          <div v-if="alumnoSeleccionado">
            <article>
              <header>Estudiante en Atención: <strong>{{ alumnoSeleccionado.nombre }}</strong> (Matrícula: {{ alumnoSeleccionado.matricula }})</header>
              
              <form @submit.prevent="guardarBitacoraAvanzada">
                <div class="grid">
                  <!-- Control de asistencia -->
                  <fieldset>
                    <legend><strong>Asistencia:</strong></legend>
                    <label><input type="radio" v-model="nuevaSesion.asistencia" value="asistio"> Cumplió con la cita</label>
                    <label><input type="radio" v-model="nuevaSesion.asistencia" value="falta"> Inasistencia</label>
                  </fieldset>

                  <!-- Control de Nivel Académico -->
                  <fieldset>
                    <legend><strong>Nivel Académico del Alumno:</strong></legend>
                    <label><input type="radio" v-model="nuevaSesion.nivel" value="Excelente/Regular"> Excelente / Regular</label>
                    <label><input type="radio" v-model="nuevaSesion.nivel" value="Asesorias Requeridas"> Requiere Regularización</label>
                    <label><input type="radio" v-model="nuevaSesion.nivel" value="Condicional"> Estatus Crítico / Condicional</label>
                  </fieldset>
                </div>

                <!-- Control de Deserción -->
                <fieldset class="alert-box-selection">
                  <legend><strong>🚨 ¿Detectas riesgo inminente de deserción escolar?</strong></legend>
                  <label><input type="radio" v-model="nuevaSesion.desercion" value="Bajo Riesgo"> No, se mantiene estable</label>
                  <label><input type="radio" v-model="nuevaSesion.desercion" value="RIESGO MODERADO"> Riesgo Moderado (Problemas externos)</label>
                  <label><input type="radio" v-model="nuevaSesion.desercion" value="RIESGO ALTO (Critico)"> Riesgo Alto / Alerta de Abandono</label>
                </fieldset>

                <label>Observaciones de la Sesión y Compromisos
                  <textarea v-model="nuevaSesion.observaciones" rows="4" placeholder="Escribe el reporte detallado aquí..." required></textarea>
                </label>

                <button type="submit" class="primary">Guardar Reporte en Bitácora</button>
              </form>
            </article>
          </div>
          <div v-else>
            <article class="txt-center">
              <p class="txt-muted">Por favor, ve al menú "Alumnos Asignados" y selecciona un estudiante para abrir la bitácora.</p>
            </article>
          </div>

          <!-- Reportes del Profesor -->
          <article class="download-section">
            <h4>📥 Descarga de Reportes Generados</h4>
            <p>Genera instantáneamente tu archivo Excel/CSV combinando el Historial, el Diagnóstico Psicopedagógico y las Alertas de Deserción Escolar.</p>
            <button class="contrast" @click="descargarReporte">Descargar Reporte General (.CSV)</button>
          </article>
        </div>

      </section>
    </div>
  </main>
</template>

<script>
export default {
  data() {
    return {
      API_BASE: "http://localhost/sistema-tutorias/backend/controllers",
      usuarioLogueado: null,
      menuActivo: "dash",
      mensajeError: "",
      notificacion: "",

      loginData: { num_empleado: "", password: "" },
      nuevoAlumno: { nombre: "", matricula: "", profesor_id: null },
      nuevoProfesor: { nombre: "", num_empleado: "", password: "" },
      
      listaProfesores: [],
      adminDashboardData: { profesores_asignaciones: [], alumnos_sin_profesor: [] },
      alumnosAsignados: [],
      alumnoSeleccionado: null,
      
      nuevaSesion: { asistencia: "asistio", nivel: "Excelente/Regular", desercion: "Bajo Riesgo", observaciones: "" }
    };
  },
  methods: {
    async ejecutarLogin() {
      this.limpiarAlertas();
      try {
        const resp = await fetch(`${this.API_BASE}/AuthController.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.loginData)
        });
        const res = await resp.json();
        if (resp.ok) {
          this.usuarioLogueado = res.user;
          if (this.usuarioLogueado.rol === 'admin') {
            this.menuActivo = 'dash';
            this.cargarDashboardAdmin();
            this.cargarProfesoresSelect();
          } else {
            this.menuActivo = 'mis_alumnos';
            this.cargarAlumnosAsignados();
          }
        } else {
          this.mensajeError = res.message;
        }
      } catch (e) {
        this.mensajeError = "Fallo de comunicación con el servidor Apache de XAMPP.";
      }
    },

    async cargarDashboardAdmin() {
      try {
        const resp = await fetch(`${this.API_BASE}/AlumnoController.php?action=dashboard_admin`);
        this.adminDashboardData = await resp.json();
      } catch (e) {
        console.error("Error al poblar el dashboard.");
      }
    },

    async cargarProfesoresSelect() {
      try {
        const resp = await fetch(`${this.API_BASE}/AlumnoController.php?action=listar_profesores`);
        this.listaProfesores = await resp.json();
      } catch (e) {
        console.error("Error al cargar docentes.");
      }
    },

    async guardarProfesorReal() {
      this.limpiarAlertas();
      try {
        const resp = await fetch(`${this.API_BASE}/AuthController.php?action=registrar_profesor`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.nuevoProfesor)
        });
        const res = await resp.json();
        if (resp.ok) {
          this.notificacion = res.message;
          this.nuevoProfesor = { nombre: "", num_empleado: "", password: "" };
          this.cargarDashboardAdmin();
          this.cargarProfesoresSelect();
        } else {
          this.mensajeError = res.message;
        }
      } catch (e) {
        this.mensajeError = "Error registrando al profesor.";
      }
    },

    async guardarAlumnoManual() {
      this.limpiarAlertas();
      try {
        const resp = await fetch(`${this.API_BASE}/AlumnoController.php?action=crear_manual`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.nuevoAlumno)
        });
        const res = await resp.json();
        if (resp.ok) {
          this.notificacion = res.message;
          this.nuevoAlumno = { nombre: "", matricula: "", profesor_id: null };
          this.cargarDashboardAdmin();
        } else {
          this.mensajeError = res.message;
        }
      } catch (e) {
        this.mensajeError = "Error en la creación del alumno.";
      }
    },

    async subirCSVAlumnos(event) {
      this.limpiarAlertas();
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
          this.notificacion = `Hoja cargada: ${res.detalles.nuevos_alumnos} nuevos alumnos indexados de forma correcta.`;
          this.$refs.fileAlumnos.value = "";
          this.cargarDashboardAdmin();
        }
      } catch (e) {
        this.mensajeError = "El archivo cargado no cumple las directrices CSV.";
      }
    },

    async cargarAlumnosAsignados() {
      try {
        const resp = await fetch(`${this.API_BASE}/SesionController.php?action=alumnos_asignados&profesor_id=${this.usuarioLogueado.id}`);
        this.alumnosAsignados = await resp.json();
      } catch (e) {
        console.error("Error al traer los alumnos del tutor.");
      }
    },

    irATutoria(alumno) {
      this.alumnoSeleccionado = alumno;
      this.menuActivo = 'tutorias';
      this.nuevaSesion = { asistencia: "asistio", nivel: "Excelente/Regular", desercion: "Bajo Riesgo", observaciones: "" };
    },

    async guardarBitacoraAvanzada() {
      this.limpiarAlertas();
      // Empaquetamos las variables académicas adicionales dentro del cuerpo de observaciones de manera legible
      // Así el backend las añade automáticamente al CSV sin romper tablas
      const observacionesCompuestas = `[Nivel: ${this.nuevaSesion.nivel}] [Riesgo Deserción: ${this.nuevaSesion.desercion}] - Nota: ${this.nuevaSesion.observaciones}`;

      const payload = {
        alumno_id: this.alumnoSeleccionado.id,
        profesor_id: this.usuarioLogueado.id,
        asistencia: this.nuevaSesion.asistencia,
        observaciones: observacionesCompuestas
      };

      try {
        const resp = await fetch(`${this.API_BASE}/SesionController.php?action=guardar_bitacora`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        if (resp.ok) {
          this.notificacion = "¡Bitácora e indicadores escolares guardados exitosamente!";
          this.alumnoSeleccionado = null;
          this.menuActivo = 'mis_alumnos';
        }
      } catch (e) {
        this.mensajeError = "No se pudo almacenar la sesión.";
      }
    },

    descargarReporte() {
      window.open(`${this.API_BASE}/SesionController.php?action=exportar_reporte&profesor_id=${this.usuarioLogueado.id}`);
    },

    cerrarSesion() {
      this.usuarioLogueado = null;
      this.loginData = { num_empleado: "", password: "" };
      this.alumnoSeleccionado = null;
      this.limpiarAlertas();
    },
    limpiarAlertas() {
      this.notificacion = "";
      this.mensajeError = "";
    }
  }
};
</script>

<style scoped>
.navbar-header { background-color: #1a1e24; box-shadow: 0 2px 5px rgba(0,0,0,0.2); padding: 0.5rem 0; margin-bottom: 2rem; }
.brand-title { color: #fff; font-size: 1.2rem; }
.user-badge { color: #90e0ef; font-size: 0.9rem; margin-right: 1rem; }
.btn-sm { padding: 0.25rem 0.7rem; font-size: 0.8rem; }
.login-wrapper { max-width: 460px; margin: 4rem auto; }
.login-card { border-top: 4px solid #10ea93; padding: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.dashboard-layout { display: flex; gap: 2rem; align-items: flex-start; }
.sidebar-menu { width: 260px; flex-shrink: 0; }
.menu-card { padding: 1rem; background-color: #f8f9fa; border-radius: 8px; }
.menu-links { list-style: none; padding: 0; margin: 0; }
.menu-links li { margin-bottom: 0.5rem; }
.menu-links a { display: block; padding: 0.5rem; border-radius: 4px; text-decoration: none; color: #495057; }
.menu-links a:hover { background-color: #e9ecef; }
.menu-links a.active { background-color: #10ea93; color: #1a1e24; font-weight: bold; }
.content-viewer { flex-grow: 1; min-width: 0; }
.error-banner { background-color: #ffdde1; color: #c01e2e; padding: 0.75rem; border-radius: 6px; margin-bottom: 1.5rem; border-left: 5px solid #c01e2e; font-size: 0.9rem; }
.success-banner { background-color: #d4edda; color: #155724; padding: 0.75rem; border-radius: 6px; margin-bottom: 1.5rem; border-left: 5px solid #155724; font-size: 0.9rem; }
.txt-muted { color: #6c757d; }
.txt-center { text-align: center; }
.w-100 { width: 100%; }
.micro-text { font-size: 0.75rem; color: #6c757d; }
.dash-card { border-left: 4px solid #00b4d8; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.badge { background: #e2e8f0; color: #4a5568; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; display: inline-block; margin: 0.5rem 0; }
.mini-list { padding-left: 1.2rem; margin: 0.5rem 0 0 0; font-size: 0.85rem; }
.warning-card { background: #fff3cd; border: 1px solid #ffeeba; padding: 1rem; border-radius: 6px; margin-top: 2rem; }
.grid-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem; }
.tag-alert { background: #fff; border: 1px solid #ffeeba; padding: 0.25rem 0.6rem; border-radius: 4px; font-size: 0.8rem; color: #856404; font-weight: bold; }
.alert-box-selection { background: #fff5f5; border: 1px solid #fed7d7; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
.download-section { background: #edf2f7; border: 1px solid #e2e8f0; padding: 1.5rem; border-radius: 8px; margin-top: 2rem; }
</style>