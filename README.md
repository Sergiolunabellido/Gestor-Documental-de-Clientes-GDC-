Aplicación Web de Gestión Interna

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![jQuery](https://img.shields.io/badge/jQuery-3.7-0769AD?logo=jquery&logoColor=white)
![Status](https://img.shields.io/badge/status-academic_project-blue)
![License](https://img.shields.io/badge/license-educational-lightgrey)

Aplicación web **monolítica en PHP + MySQL** para **gestión interna de
usuarios, clientes y archivos**, con autenticación segura, carga de
archivos SQL y visualización dinámica de datos mediante **AJAX**.

---

# 📚 Tabla de Contenidos

- [Descripción](#-descripción)
- [Stack Tecnológico](#-stack-tecnológico)
- [Arquitectura](#-arquitectura)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Flujo de Funcionamiento](#-flujo-de-funcionamiento)
- [Acciones Backend](#-acciones-backend)
- [Base de Datos](#-base-de-datos)
- [Módulos Funcionales](#-módulos-funcionales)
- [Gestión de Archivos](#-gestión-de-archivos)
- [Seguridad](#-seguridad)
- [Instalación](#-instalación)
- [Inventario Técnico](#-inventario-técnico)

---

# 📖 Descripción

Sistema web diseñado para la **administración interna de información**,
permitiendo:

- gestión de **usuarios**
- gestión de **clientes**
- subida y administración de **archivos**
- carga y exploración de **archivos SQL**
- visualización tabular de datos

La aplicación utiliza **AJAX** para actualizar vistas sin recargar la
página.

---

# 🧰 Stack Tecnológico

Tecnología      Función

---

PHP 8.x         Backend
MySQL 8.x       Base de datos
Bootstrap 5.3   Interfaz UI
jQuery 3.7      AJAX y manipulación DOM

---

# 🏗 Arquitectura

La aplicación implementa un **Front Controller** centralizado y
comunicación mediante **AJAX**.

Frontend (Bootstrap + jQuery)
        │
        │ AJAX (accion)
        ▼
index.php
        │
        ▼
controllers/controller.php
        │
        ▼
lib/ (lógica de negocio)
        │
        ▼
MySQL Database
Características:

- enrutamiento mediante `accion`
- vistas modulares
- lógica separada en librerías
- sesiones persistidas en base de datos

---

# 📂 Estructura del Proyecto

/
├── index.php
├── configuracion/
├── controllers/
├── includes/
├── help/
├── lib/
├── mod/
├── themes/
└── assets/
### Directorios principales

Carpeta         Descripción

---

configuracion   configuración de entorno y BD
controllers     controlador principal
lib             lógica de negocio
mod             módulos funcionales
themes          layout y componentes UI
assets          archivos, imágenes, logs y cache

---

# 🔄 Flujo de Funcionamiento

### 1. Carga inicial

`themes/view.php` decide mostrar:

- pantalla de **login**
- **home** de la aplicación

según el estado de sesión.

---

### 2. Login

Petición AJAX:

accion = login
Proceso:

1. Validación de credenciales
2. Creación de sesión en DB
3. Devolución de contenido HTML parcial

---

### 3. Navegación

Las opciones del menú lateral envían:

POST → controller.php
accion = nombreAccion
Respuesta típica:

```json
{
 "estaticos": "...",
 "contenido": "...",
 "data": {}
}
```
---

# ⚙ Acciones Backend

## Acciones de vista

- login
- logout
- logoutAutomatico
- registro
- perfil
- fuente
- archivo
- detalleTabla
- detalleCliente
- usuarios

---

## Operaciones

- cambiarFoto
- subirArchivo
- listarArchivosCliente
- generarTablas
- cambiarContrasenia
- renovarSesion
- modificarUsuario
- eliminarUsuarios
- crearCarpetaCliente
- eliminarCliente
- eliminarArchivoCliente
- eliminarArchivosCliente
- verificarSesion
- modificarEstadoUsuario

---

# 🗄 Base de Datos

Configuración en:

configuracion/configuracion.php
Ejemplo:

host = localhost
user = sergioymanu
password = sergioymanu
database = app2026
---

## Tablas utilizadas

- Usuario
- sesiones_usuario
- Cliente
- Archivo
- vltfddb

---

# 🧩 Módulos Funcionales

Módulo           Función

---

login            autenticación
home             dashboard
usuarios         gestión de usuarios
perfil           perfil de usuario
fuente           gestión de clientes
detalleCliente   archivos de cliente
archivo          carga SQL
detalleTabla     visualización datos
estatico         navbar y layout

---

# 📁 Gestión de Archivos

### Fotos de perfil

assets/images/user_<id>_<usuario>/
### Archivos de cliente

assets/archivosC/cliente_<id>/
### SQL subidos

assets/archivoSQL/
### Cache

assets/cache/
### Logs

assets/logs/app-YYYY-MM-DD.log
---

# 🔐 Seguridad

Implementado:

- password_hash()
- password_verify()
- sesiones persistentes en DB
- validación de sesión
- logout automático

### Mejoras recomendadas

- variables de entorno para credenciales
- cookies `Secure` y `HttpOnly`
- protección CSRF
- validación más estricta de uploads

---

# 🚀 Instalación

## 1. Configurar entorno

Editar:

configuracion/configuracion.php
---

## 2. Crear base de datos

```sql
CREATE DATABASE app2026;
```
---

## 3. Crear tablas

- Usuario
- Cliente
- Archivo
- sesiones_usuario

---

## 4. Importar SQL opcional

vltfddb.sql
---

## 5. Ejecutar servidor

Apache / Nginx o:

php -S localhost:8000
---

## 6. Abrir aplicación

http://localhost/index.php
---

# 📊 Inventario Técnico

Métrica          Valor

---

Total archivos   193
Tamaño           \~11.9 MB

---

# 📦 Distribución por extensión

Tipo   Cantidad

---

png    65
php    27
css    22
map    22
js     18
log    15
html   10
jpg    5
sql    3
svg    3
json   2

---

# 👨‍💻 Proyecto Académico

Proyecto orientado a práctica de:

- desarrollo **PHP backend**
- manejo de **MySQL**
- interacción **AJAX**
- organización modular de aplicaciones web
