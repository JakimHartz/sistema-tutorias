# TutorTrack — Sistema de Control de Tutorías

> Herramienta web para el seguimiento académico de alumnos por parte de sus tutores asignados.  
> Proyecto final de la materia **Tecnologías Web I** — UNSIS

---

## 🎥 Video de Demostración

> 🔗 **[Se insertará aquí el enlace público de YouTube / Drive / Loom]**

---

## 📋 Descripción del Proyecto

TutorTrack es un sistema web que permite:

- Al **administrador** registrar alumnos (manualmente o por CSV masivo) y asignarles un profesor tutor.
- Al **administrador** dar de alta nuevos profesores.
- Al **profesor** consultar su lista de alumnos y registrar sesiones de tutoría (bitácora).
- Al **profesor** generar y descargar un reporte CSV con historial, nivel académico y alertas de deserción.

---

## 🛠️ Tecnologías y Versiones

| Capa | Tecnología | Versión |
|:---|:---|:---|
| Frontend | Vue.js (Options API) | 3.x (vía Vite 5.0.0) |
| Estilos | Pico.css | 2.x |
| Backend | PHP | 8.x |
| Base de datos | MySQL (MariaDB) | 10.x (incluida en XAMPP) |
| Servidor local | Apache (XAMPP) | 8.x |
| Runtime JS | Node.js | 18+ |

---

## 🗂️ Arquitectura del Proyecto

```
sistema-tutorias/
│
├── backend/                        # Backend MVC en PHP puro
│   ├── config/
│   │   └── Database.php            # Conexión PDO a MySQL
│   ├── models/                     # Clases que encapsulan las consultas SQL
│   │   ├── Alumno.php
│   │   ├── Profesor.php
│   │   ├── Sesion.php
│   │   └── Usuario.php
│   ├── controllers/                # Controladores: reciben peticiones HTTP y devuelven JSON
│   │   ├── AuthController.php      # Login + registro de profesores
│   │   ├── AlumnoController.php    # CRUD alumnos, carga CSV, asignación de tutores
│   │   └── SesionController.php    # Bitácora de tutorías y exportación CSV
│   └── schema.sql                  # Script SQL para crear la BD (¡ejecutar primero!)
│
├── frontend/                       # Frontend SPA con Vue.js
│   ├── src/
│   │   ├── main.js                 # Punto de entrada; importa Vue, Pico y estilos globales
│   │   ├── api.js                  # URL base del backend (centralizada)
│   │   ├── style.css               # Tema oscuro personalizado (override de Pico.css)
│   │   ├── App.vue                 # Coordinador global de estado y navegación
│   │   └── components/
│   │       ├── NavBar.vue          # Barra de navegación superior
│   │       ├── LoginForm.vue       # Pantalla de inicio de sesión
│   │       ├── SidebarMenu.vue     # Menú lateral dinámico (admin / profesor)
│   │       ├── AlertBanner.vue     # Banners reutilizables de éxito / error
│   │       ├── admin/
│   │       │   ├── DashboardAdmin.vue    # Resumen de asignaciones
│   │       │   ├── GestionAlumnos.vue    # Alta manual, CSV masivo, tabla de tutores
│   │       │   └── GestionProfesores.vue # Registro de nuevos profesores
│   │       └── profesor/
│   │           ├── MisAlumnos.vue        # Lista de alumnos del tutor
│   │           └── RegistrarTutoria.vue  # Bitácora + exportar reporte
│   └── package.json
│
└── README.md
```

### Flujo de datos (Frontend → Backend)

```
Vue.js (fetch) ──→ PHP Controller ──→ PHP Model ──→ MySQL
                ←── JSON response ←── PDO query ←──
```

---

## ⚙️ Instalación y Ejecución

### Requisitos previos
- **XAMPP** instalado y ejecutando Apache + MySQL
- **Node.js** v18 o superior
- La carpeta del proyecto colocada en `C:/xampp/htdocs/` (Windows) o `/opt/lampp/htdocs/` (Linux)

---

### Paso 1 — Crear la base de datos

1. Abre **phpMyAdmin** en `http://localhost/phpmyadmin`
2. Haz clic en **Importar** → selecciona el archivo `backend/schema.sql`
3. Pulsa **Ejecutar**

Esto crea la base de datos `sistema_tutorias` con tablas y datos de prueba.

**Credenciales iniciales:**

| Rol | Usuario | Contraseña |
|:---|:---|:---|
| Admin | `admin01` | `admin123` |
| Profesor | `EMP-001` | `prof123` |

---

### Paso 2 — Iniciar el servidor frontend

```bash
cd sistema-tutorias/frontend
npm install          # Solo la primera vez
npm run dev          # Inicia en http://localhost:5173
```

---

### Paso 3 — Verificar el backend

Abre en el navegador:

```
http://localhost/sistema-tutorias/backend/controllers/AlumnoController.php?action=listar_profesores
```

Deberías ver un JSON con los profesores registrados.

---

## 🔌 Endpoints principales de la API

### AuthController.php

| Método | URL | Descripción |
|:---|:---|:---|
| POST | `/AuthController.php` | Login (devuelve `{ user: { id, nombre, rol } }`) |
| POST | `/AuthController.php?action=registrar_profesor` | Registrar nuevo profesor |

### AlumnoController.php

| Método | URL | Descripción |
|:---|:---|:---|
| POST | `/AlumnoController.php?action=crear_manual` | Alta manual de alumno |
| POST | `/AlumnoController.php?action=carga_masiva` | Carga de CSV (multipart/form-data) |
| POST | `/AlumnoController.php?action=asignar_tutor` | Reasignar tutor a un alumno |
| GET  | `/AlumnoController.php?action=listar_profesores` | Lista de profesores |
| GET  | `/AlumnoController.php?action=dashboard_admin` | Dashboard de asignaciones |

### SesionController.php

| Método | URL | Descripción |
|:---|:---|:---|
| GET  | `/SesionController.php?action=alumnos_asignados&profesor_id={id}` | Alumnos del profesor |
| POST | `/SesionController.php?action=guardar_bitacora` | Guardar sesión de tutoría |
| GET  | `/SesionController.php?action=exportar_reporte&profesor_id={id}` | Descargar reporte CSV |

---

## 📄 Formato del archivo CSV para carga masiva

El archivo debe estar separado por comas, **sin encabezados**:

```
2024010001,Ana Lucía Torres Vega
2024010002,Luis Fernando García Ruiz
2024010003,Karla Sofía Mendoza Pérez
```

---

## 👤 Autor

- **Joaquín Emanuel Pinacho García**
- **Materia:** Tecnologías Web I — UNSIS
