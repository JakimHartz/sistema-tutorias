<template>
  <!-- Barra de navegación superior -->
  <NavBar :usuario="usuarioLogueado" @cerrar-sesion="cerrarSesion" />

  <main class="container main-content">

    <!-- Pantalla de Login -->
    <LoginForm v-if="!usuarioLogueado" @login-exitoso="alIniciarSesion" />

    <!-- Layout con sidebar + contenido dinámico -->
    <div v-else class="dashboard-layout">

      <SidebarMenu
        :rol="usuarioLogueado.rol"
        :menu-activo="menuActivo"
        @cambiar-menu="menuActivo = $event"
      />

      <section class="content-viewer">
        <!-- Banner de notificaciones global -->
        <AlertBanner :error="mensajeError" :exito="notificacion" />

        <!-- ── Vistas del Administrador ─────────────────── -->
        <template v-if="usuarioLogueado.rol === 'admin'">
          <DashboardAdmin
            v-if="menuActivo === 'dash'"
            @mensaje="recibirMensaje"
          />
          <GestionAlumnos
            v-if="menuActivo === 'alumnos'"
            @mensaje="recibirMensaje"
          />
          <GestionProfesores
            v-if="menuActivo === 'profesores'"
            @mensaje="recibirMensaje"
          />
        </template>

        <!-- ── Vistas del Profesor ──────────────────────── -->
        <template v-if="usuarioLogueado.rol === 'profesor'">
          <MisAlumnos
            v-if="menuActivo === 'mis_alumnos'"
            :profesor-id="usuarioLogueado.id"
            @iniciar-tutoria="irATutoria"
          />
          <RegistrarTutoria
            v-if="menuActivo === 'tutorias'"
            :alumno="alumnoSeleccionado"
            :profesor-id="usuarioLogueado.id"
            @bitacora-guardada="regresarAMisAlumnos"
            @mensaje="recibirMensaje"
          />
        </template>
      </section>
    </div>
  </main>
</template>

<script>
// Importamos todos los componentes desde sus respectivos archivos
import NavBar          from './components/NavBar.vue'
import LoginForm       from './components/LoginForm.vue'
import SidebarMenu     from './components/SidebarMenu.vue'
import AlertBanner     from './components/AlertBanner.vue'
import DashboardAdmin  from './components/admin/DashboardAdmin.vue'
import GestionAlumnos  from './components/admin/GestionAlumnos.vue'
import GestionProfesores from './components/admin/GestionProfesores.vue'
import MisAlumnos      from './components/profesor/MisAlumnos.vue'
import RegistrarTutoria from './components/profesor/RegistrarTutoria.vue'

export default {
  // Registrar componentes para poder usarlos en el template
  components: {
    NavBar, LoginForm, SidebarMenu, AlertBanner,
    DashboardAdmin, GestionAlumnos, GestionProfesores,
    MisAlumnos, RegistrarTutoria
  },

  data() {
    return {
      // Estado global del usuario autenticado
      usuarioLogueado: null,
      // Controla qué sección del menú está activa
      menuActivo: 'dash',
      // Mensajes de notificación globales
      mensajeError: '',
      notificacion: '',
      // Alumno que el profesor selecciona para abrir la bitácora
      alumnoSeleccionado: null,
    }
  },

  methods: {
    // Se ejecuta cuando LoginForm emite 'login-exitoso' con los datos del usuario
    alIniciarSesion(user) {
      this.usuarioLogueado = user
      // Llevar al admin al dashboard y al profesor a su lista de alumnos
      this.menuActivo = user.rol === 'admin' ? 'dash' : 'mis_alumnos'
    },

    // Limpiar todo el estado al cerrar sesión
    cerrarSesion() {
      this.usuarioLogueado  = null
      this.alumnoSeleccionado = null
      this.mensajeError     = ''
      this.notificacion     = ''
    },

    // El componente MisAlumnos emite este evento cuando el profesor pulsa "Iniciar Bitácora"
    irATutoria(alumno) {
      this.alumnoSeleccionado = alumno
      this.menuActivo = 'tutorias'
    },

    // Después de guardar la bitácora, volver a la lista
    regresarAMisAlumnos() {
      this.alumnoSeleccionado = null
      this.menuActivo = 'mis_alumnos'
    },

    // Los componentes hijos emiten { tipo: 'error'|'exito', texto: '...' }
    recibirMensaje({ tipo, texto }) {
      this.mensajeError  = tipo === 'error' ? texto : ''
      this.notificacion  = tipo === 'exito'  ? texto : ''
    }
  }
}
</script>
