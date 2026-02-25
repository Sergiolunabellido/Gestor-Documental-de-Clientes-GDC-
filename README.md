# 🚀 ProyectoPracticas - Aplicación Web PHP + MySQL

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.8-7952B3?style=for-the-badge&logo=bootstrap)
![jQuery](https://img.shields.io/badge/jQuery-3.7.1-0769AD?style=for-the-badge&logo=jquery)
![Estado](https://img.shields.io/badge/Estado-En%20desarrollo-orange?style=for-the-badge)

</div>

## ✨ Descripción General

Aplicación web monolítica construida en **PHP + MySQL + jQuery + Bootstrap** con gestión de:

- 👤 autenticación y sesiones de usuario
- 🧑‍💼 administración de usuarios (alta, baja lógica, modificación, estado)
- 🧾 perfil y foto de usuario
- 📁 clientes y archivos por cliente (subida múltiple + drag & drop + eliminación individual/masiva)
- 🗃️ carga de SQL (`.sql`) para recrear/refrescar la tabla principal `vltfddb`
- 📊 visualización tabular de datos SQL

El proyecto usa un patrón de entrada única en `index.php`, con peticiones AJAX al `controller.php` para cambiar vistas y ejecutar acciones.

---

## 🧠 Arquitectura Técnica

## 1) Front Controller

- `index.php` inicia sesión, carga configuración, librerías y enruta todas las peticiones `POST` al controlador.
- Si no es `POST`, renderiza `themes/view.php` (contenedor principal de la app).

## 2) Controlador central

- `controllers/controller.php` procesa `$_POST['accion']`.
- Crea modelos: `Usuario`, `Sesion`, `Cliente`, `Archivo`.
- Devuelve respuestas JSON para frontend.

## 3) Capa de dominio / datos

- `lib/usuario.php`
- `lib/sesion.php`
- `lib/cliente.php`
- `lib/archivo.php`
- `lib/db.php` (conexión PDO)

## 4) Vistas

- `themes/view.php` compone:
  - zona estática (`navbar` + `slider/sliderAdmin`)
  - zona dinámica de contenido (`mod/*/*.php`)
- los módulos se cargan vía AJAX desde `mod/*`.

## 5) Assets

- `assets/images`: imágenes de UI y fotos de perfil
- `assets/archivosC`: ficheros subidos por cliente
- `assets/archivoSQL`: SQL cargado por interfaz
- `assets/cache`: JSON de control
- `assets/logs`: logs diarios de aplicación

---

## 🗺️ Flujo Funcional Principal

1. 🔐 Login/registro desde `mod/login/login.php` + `mod/login/js/script.js`.
2. ✅ `controller.php` valida credenciales y crea sesión en DB (`sesiones_usuario`).
3. 🧭 Según rol (`admin`), se muestra `sliderAdmin.php` o `slider.php`.
4. 📂 Navegación por módulos: `home`, `usuarios`, `perfil`, `fuente`, `archivo`, `detalleCliente`, `detalleTabla`.
5. 💾 Todas las operaciones se hacen por AJAX contra `index.php` con `accion`.
6. ⏱️ Existe temporizador de sesión con renovación y cierre automático.

---

## 🧩 Acciones Backend (`accion` en `controller.php`)

### Vistas

- `login`
- `logout`
- `logoutAutomatico`
- `registro`
- `perfil`
- `fuente`
- `archivo`
- `detalleTabla`
- `detalleCliente`
- `usuarios`

### Funciones / Operaciones

- `cambiarFoto`
- `subirArchivo`
- `listarArchivosCliente`
- `generarTablas`
- `cambiarContrasenia`
- `renovarSesion`
- `modificarUsuario`
- `eliminarUsuarios`
- `crearCarpetaCliente`
- `eliminarCliente`
- `eliminarArchivoCliente`
- `eliminarArchivosCliente`
- `verificarSesion`
- `modificarEstadoUsuario`

---

## 🗄️ Base de Datos

### Configuración declarada

Archivo: `configuracion/configuracion.php`

- Host: `localhost`
- Usuario: `sergioymanu`
- Password: `sergioymanu`
- DB: `app2026`

### Tablas usadas por código

- `Usuario`
- `sesiones_usuario`
- `Cliente`
- `Archivo`
- `vltfddb` (carga/importación SQL dinámica)

### SQL incluido en repo

- `vltfddb.sql`
- `assets/archivoSQL/vltfddb.sql`

Ambos contienen estructura + datos para la tabla `vltfddb`.

---

## 🔐 Seguridad y Sesiones

- Hash de contraseñas con `password_hash()`.
- Validación de contraseña con `password_verify()`.
- Sesión persistente opcional con cookie `remember`.
- Control de sesión activa por tabla `sesiones_usuario`.
- `guard.php` valida sesión activa en casi todas las acciones.
- Logout explícito y logout automático al cerrar navegador.

---

## 🖼️ Módulos de Interfaz

- `mod/login`: login, registro, cambio de contraseña, recordarme, control de expiración.
- `mod/home`: pantalla principal.
- `mod/usuarios`: listado, filtros, modificación, eliminación, alta de estado.
- `mod/perfil`: datos de perfil + subida/cambio de foto.
- `mod/fuente`: gestión visual de carpetas/clientes.
- `mod/detalleCliente`: subida/listado de archivos por cliente, eliminación individual (por archivo) y eliminación masiva (botón "Eliminar Todos").
- `mod/archivo`: carga SQL y acceso a visualización de tabla.
- `mod/detalleTabla`: renderizado de tabla SQL en HTML.
- `mod/estatico`: navbar y sliders por rol.

---

## 🧾 Logging

`includes/app.php` contiene `debug($msg, $nivel)` y escribe en:

- `assets/logs/app-YYYY-MM-DD.log`

Logs detectados en el proyecto (13 archivos):

- `app-2026-01-27.log`
- `app-2026-01-28.log`
- `app-2026-02-02.log`
- `app-2026-02-03.log`
- `app-2026-02-09.log`
- `app-2026-02-10.log`
- `app-2026-02-11.log`
- `app-2026-02-16.log`
- `app-2026-02-17.log`
- `app-2026-02-18.log`
- `app-2026-02-23.log`
- `app-2026-02-24.log`
- `app-2026-02-25.log`

---

## 📊 Inventario Técnico del Repositorio

- Total de archivos: **191**
- Tamaño total aprox.: **12,466,206 bytes** (~11.89 MB)

### Recuento por extensión

- `.png`: 65
- `.php`: 27
- `.css`: 22
- `.map`: 22
- `.js`: 18
- `.log`: 13
- `.html`: 10
- `.jpg`: 6
- `.svg`: 3
- `.json`: 2
- `.sql`: 2
- `.md`: 1

### Recuento por directorio principal

- `assets`: 25 archivos
- `bootstrap-5.3.8-dist`: 47 archivos
- `configuracion`: 1 archivo
- `controllers`: 1 archivo
- `help`: 1 archivo
- `includes`: 1 archivo
- `js`: 1 archivo
- `lib`: 6 archivos
- `mod`: 38 archivos
- `themes`: 66 archivos
- raíz: 4 archivos (`index.php`, `README.md`, `vltfddb.sql`, `favicon.svg`)

## ⚙️ Cómo ejecutar en local

1. Tener PHP 8+ y MySQL 8+.
2. Ajustar `configuracion/configuracion.php`:
   - `_ROOT_`
   - `_URI_`
   - `_SERVER_`, `_USER_`, `_PASSWORD_`, `_DBNAME_`
3. Crear BD `app2026`.
4. Crear tablas de aplicación (`Usuario`, `Cliente`, `Archivo`, `sesiones_usuario`).
5. (Opcional) importar `vltfddb.sql` para datos de tabla técnica.
6. Levantar servidor apuntando a la raíz del proyecto y abrir `index.php`.

---

## 🧪 Observaciones técnicas detectadas

- El repositorio contiene datos de entorno real (logs, rutas, recursos subidos).
- Se incluyen credenciales en texto plano en `configuracion/configuracion.php`.
- El importador SQL está orientado a recargar **solo** la tabla `vltfddb`.
- Existen módulos plantilla vacíos (`index.html`, algunos `css/js` sin contenido) usados como estructura base.

---

## 👨‍💻 Autoría

Proyecto de prácticas orientado a gestión interna de usuarios/clientes/archivos con interfaz web clásica y backend PHP procedural + orientado a objetos por modelos.
