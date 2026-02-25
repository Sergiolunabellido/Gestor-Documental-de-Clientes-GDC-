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

---

## 📁 Inventario Completo de Archivos (sin omisiones)

```text
assets\archivosC\cliente_39\boceto-dni-3-1024x688.jpg
assets\archivosC\cliente_40\logoLibreria3.jpg
assets\archivoSQL\vltfddb.sql
assets\cache\jsonBloqueados.json
assets\cache\nombreBD.json
assets\images\estado.png
assets\images\file-certificate.svg
assets\images\istockphoto-824860820-612x612.jpg
assets\images\user.svg
assets\images\user_16_sergio\user_16_sergio.jpg
assets\images\user_17_usuario\user_17_usuario.jpg
assets\images\user_5_manel\user_5_manel.jpg
assets\logs\app-2026-01-27.log
assets\logs\app-2026-01-28.log
assets\logs\app-2026-02-02.log
assets\logs\app-2026-02-03.log
assets\logs\app-2026-02-09.log
assets\logs\app-2026-02-10.log
assets\logs\app-2026-02-11.log
assets\logs\app-2026-02-16.log
assets\logs\app-2026-02-17.log
assets\logs\app-2026-02-18.log
assets\logs\app-2026-02-23.log
assets\logs\app-2026-02-24.log
assets\logs\app-2026-02-25.log
bootstrap-5.3.8-dist\css\bootstrap.css
bootstrap-5.3.8-dist\css\bootstrap.css.map
bootstrap-5.3.8-dist\css\bootstrap.min.css
bootstrap-5.3.8-dist\css\bootstrap.min.css.map
bootstrap-5.3.8-dist\css\bootstrap.rtl.css
bootstrap-5.3.8-dist\css\bootstrap.rtl.css.map
bootstrap-5.3.8-dist\css\bootstrap.rtl.min.css
bootstrap-5.3.8-dist\css\bootstrap.rtl.min.css.map
bootstrap-5.3.8-dist\css\bootstrap-grid.css
bootstrap-5.3.8-dist\css\bootstrap-grid.css.map
bootstrap-5.3.8-dist\css\bootstrap-grid.min.css
bootstrap-5.3.8-dist\css\bootstrap-grid.min.css.map
bootstrap-5.3.8-dist\css\bootstrap-grid.rtl.css
bootstrap-5.3.8-dist\css\bootstrap-grid.rtl.css.map
bootstrap-5.3.8-dist\css\bootstrap-grid.rtl.min.css
bootstrap-5.3.8-dist\css\bootstrap-grid.rtl.min.css.map
bootstrap-5.3.8-dist\css\bootstrap-reboot.css
bootstrap-5.3.8-dist\css\bootstrap-reboot.css.map
bootstrap-5.3.8-dist\css\bootstrap-reboot.min.css
bootstrap-5.3.8-dist\css\bootstrap-reboot.min.css.map
bootstrap-5.3.8-dist\css\bootstrap-reboot.rtl.css
bootstrap-5.3.8-dist\css\bootstrap-reboot.rtl.css.map
bootstrap-5.3.8-dist\css\bootstrap-reboot.rtl.min.css
bootstrap-5.3.8-dist\css\bootstrap-reboot.rtl.min.css.map
bootstrap-5.3.8-dist\css\bootstrap-utilities.css
bootstrap-5.3.8-dist\css\bootstrap-utilities.css.map
bootstrap-5.3.8-dist\css\bootstrap-utilities.min.css
bootstrap-5.3.8-dist\css\bootstrap-utilities.min.css.map
bootstrap-5.3.8-dist\css\bootstrap-utilities.rtl.css
bootstrap-5.3.8-dist\css\bootstrap-utilities.rtl.css.map
bootstrap-5.3.8-dist\css\bootstrap-utilities.rtl.min.css
bootstrap-5.3.8-dist\css\bootstrap-utilities.rtl.min.css.map
bootstrap-5.3.8-dist\js\bootstrap.bundle.js
bootstrap-5.3.8-dist\js\bootstrap.bundle.js.map
bootstrap-5.3.8-dist\js\bootstrap.bundle.min.js
bootstrap-5.3.8-dist\js\bootstrap.bundle.min.js.map
bootstrap-5.3.8-dist\js\bootstrap.esm.js
bootstrap-5.3.8-dist\js\bootstrap.esm.js.map
bootstrap-5.3.8-dist\js\bootstrap.esm.min.js
bootstrap-5.3.8-dist\js\bootstrap.esm.min.js.map
bootstrap-5.3.8-dist\js\bootstrap.js
bootstrap-5.3.8-dist\js\bootstrap.js.map
bootstrap-5.3.8-dist\js\bootstrap.min.js
bootstrap-5.3.8-dist\js\bootstrap.min.js.map
bootstrap-5.3.8-dist\js\jquery-3.7.1.js
bootstrap-5.3.8-dist\js\jquery-3.7.1.min.js
bootstrap-5.3.8-dist\js\jquery-3.7.1.slim.js
configuracion\configuracion.php
controllers\controller.php
favicon.svg
help\guard.php
includes\app.php
index.php
js\app.js
lib\archivo.php
lib\cliente.php
lib\db.php
lib\sesion.php
lib\usuario.php
lib\vistas.php
mod\archivo\archivo.php
mod\archivo\index.html
mod\archivo\js\archivo.js
mod\detalleCliente\css\styles.css
mod\detalleCliente\detalleCliente.php
mod\detalleCliente\index.html
mod\detalleCliente\js\detalleCliente.js
mod\detalleTabla\detalleTabla.php
mod\detalleTabla\index.html
mod\detalleTabla\js\detalleTabla.js
mod\estatico\css\navbar.css
mod\estatico\css\slider.css
mod\estatico\css\sliderAdmin.css
mod\estatico\js\functions.js
mod\estatico\navbar.html
mod\estatico\navbar.php
mod\estatico\slider.html
mod\estatico\slider.php
mod\estatico\sliderAdmin.php
mod\fuente\fuente.php
mod\fuente\index.html
mod\fuente\js\fuente.js
mod\home\home.php
mod\home\index.html
mod\login\css\style.css
mod\login\includes\logInA.php
mod\login\includes\registroA.php
mod\login\index.html
mod\login\js\script.js
mod\login\login.php
mod\perfil\includes\perfilA.php
mod\perfil\index.html
mod\perfil\js\funciones.js
mod\perfil\perfil.php
mod\usuarios\includes\usuariosA.php
mod\usuarios\index.html
mod\usuarios\js\funciones.js
mod\usuarios\usuarios.php
README.md
themes\icons\_blank.png
themes\icons\_page.png
themes\icons\aac.png
themes\icons\ai.png
themes\icons\aiff.png
themes\icons\avi.png
themes\icons\bmp.png
themes\icons\c.png
themes\icons\cpp.png
themes\icons\css.png
themes\icons\csv.png
themes\icons\dat.png
themes\icons\dmg.png
themes\icons\doc.png
themes\icons\dotx.png
themes\icons\dwg.png
themes\icons\dxf.png
themes\icons\eps.png
themes\icons\exe.png
themes\icons\flv.png
themes\icons\gif.png
themes\icons\h.png
themes\icons\hpp.png
themes\icons\html.png
themes\icons\ics.png
themes\icons\iso.png
themes\icons\java.png
themes\icons\jpg.png
themes\icons\js.png
themes\icons\key.png
themes\icons\less.png
themes\icons\mid.png
themes\icons\mp3.png
themes\icons\mp4.png
themes\icons\mpg.png
themes\icons\odf.png
themes\icons\ods.png
themes\icons\odt.png
themes\icons\otp.png
themes\icons\ots.png
themes\icons\ott.png
themes\icons\pdf.png
themes\icons\php.png
themes\icons\png.png
themes\icons\ppt.png
themes\icons\psd.png
themes\icons\py.png
themes\icons\qt.png
themes\icons\rar.png
themes\icons\rb.png
themes\icons\rtf.png
themes\icons\sass.png
themes\icons\scss.png
themes\icons\sql.png
themes\icons\tga.png
themes\icons\tgz.png
themes\icons\tiff.png
themes\icons\txt.png
themes\icons\wav.png
themes\icons\xls.png
themes\icons\xlsx.png
themes\icons\xml.png
themes\icons\yml.png
themes\icons\zip.png
themes\style.css
themes\view.php
vltfddb.sql
```

---

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

Si quieres, en el siguiente paso puedo generar también:

- un `README-DEV.md` solo para desarrolladores
- un diagrama de arquitectura (Mermaid)
- una guía de despliegue para servidor Ubuntu + Apache + PHP-FPM + MySQL
