# EduMentor RAG - Plataforma de Tutoría Académica

Plataforma web de apoyo académico donde los estudiantes hacen preguntas sobre sus cursos y el asistente responde usando **solo el material que subió el docente**, indicando de qué documento y página salió la respuesta.

**Curso:** Aplicaciones Distribuidas I · **Grupo 03**
**Universidad Nacional del Santa** · E.P. Ingeniería de Sistemas e Informática
**Docente:** Dr. Mirko Manrique Ronceros

## Ejecutar: http://localhost/edumentor/

## Cuentas de prueba

### Docente
* Usuario: docente@uns.edu.pe
* Contraseña: docente123

### Estudiante
* Usuario: estudiante@uns.edu.pe
* Contraseña: estudiante123

*(Son cuentas de demostración, no hay datos reales.)*

## Integrantes

* Luis Santiago Patricio Miranda (Coordinador)
* Eddy Joseph Giraldo Barreto
* Diego Jeanpiere Hinostroza Melo
* David Daniel Carlin Jimenez
* Victor Rodolfo Tufinio Moreno

## Funcionalidades

### Módulo Docente
* **Cursos:** Registro y listado de asignaturas.
* **Material académico:** Subida de archivos (PDF o DOCX), registro del contenido por fragmentos y eliminación de materiales.
* **Banco de preguntas:** Creación de preguntas de práctica para cada curso.
* **Consultas:** Revisión de las preguntas que hacen los estudiantes.
* **Estadísticas:** Total de consultas, utilidad percibida de las respuestas y reportes de calidad.

### Módulo Estudiante
* **Asistente RAG:** Preguntas en lenguaje natural sobre el material de un curso.
* **Fuentes:** Cada respuesta muestra el documento, la unidad y la página usados.
* **Valoración:** Se puede marcar la respuesta como útil, parcialmente útil o no útil, y reportar si es incorrecta, incompleta, de fuente no relacionada o no sustentada.
* **Historial:** Consulta de preguntas y respuestas anteriores.
* **Evaluaciones:** Práctica con las preguntas del curso y calificación automática.

## Cómo instalarlo

### Requisitos
* XAMPP (Apache, PHP y MySQL)
* Un navegador web
* Git (opcional, también sirve descargar el ZIP desde GitHub)

### Pasos
1. Copia la carpeta `edumentor` dentro de `C:\xampp\htdocs\`.
2. Abre el panel de XAMPP e inicia **Apache** y **MySQL**.
3. Entra a `http://localhost/phpmyadmin`, ve a la pestaña **Importar** y selecciona el archivo `database/edumentor.sql`. Esto crea la base de datos `edumentor` con sus tablas y datos de ejemplo.
4. Abre `http://localhost/edumentor/` e inicia sesión con una de las cuentas de prueba.

> Si vuelves a importar `edumentor.sql`, se borran los datos guardados y la base regresa a su estado inicial.

## Prueba rápida

1. Entra como docente y registra un curso nuevo en **Mis cursos**.
2. En **Material académico**, sube un archivo y pega parte de su texto.
3. Cierra sesión y entra como estudiante.
4. En **Asistente RAG**, elige el curso y pregunta algo del texto que pegaste.
5. Revisa la respuesta con su fuente, valórala y mírala en **Mi historial**.

Los datos se guardan en MySQL, puedes comprobarlos en phpMyAdmin dentro de la base `edumentor`.

## Estructura del proyecto

* `*.php` (login, cursos, materiales, asistente...): páginas que ve el usuario.
* `backend/`: lógica del sistema (sesión, acceso a datos, asistente y API en JSON).
* `database/`: script `edumentor.sql` y conexión a MySQL.
* `includes/`: menú y pie de página compartidos.
* `css/` y `js/`: estilos y comportamiento de la interfaz.
* `uploads/`: archivos subidos por el docente.

## Arquitectura y Tecnologías

* **Lenguaje:** PHP
* **Base de Datos:** MySQL (XAMPP) con 8 tablas relacionadas
* **Acceso a datos:** PDO con consultas preparadas
* **Frontend:** HTML5, CSS3 y JavaScript
* **Sesiones y roles:** control de acceso diferenciado para docente y estudiante
* **Conexión a la base de datos:** única y reutilizada (patrón Singleton, en `conexion.php`)
* **Control de versiones:** Git / GitHub

## Base de datos

Tablas principales: `usuarios`, `cursos`, `materiales`, `fragmentos`, `consultas`, `consulta_fuentes`, `valoraciones` y `preguntas_banco`.

La tabla `consulta_fuentes` une cada consulta con los fragmentos que sirvieron para responderla, y así se pueden mostrar las fuentes de cada respuesta.

## Si algo no funciona

* **"No se pudo conectar con la base de datos":** revisa que MySQL esté en verde en XAMPP y que hayas importado `edumentor.sql`.
* **La página no abre (error 404):** revisa que Apache esté en verde y que la carpeta se llame `edumentor` dentro de `htdocs`.
* **"Correo o contraseña incorrectos":** usa las cuentas de prueba tal cual están escritas o vuelve a importar el SQL.
* **No se puede subir un archivo:** verifica que exista la carpeta `uploads/` y que el archivo sea PDF o DOCX de hasta 10 MB.
* **MySQL no inicia:** otro programa puede estar usando el puerto 3306.
* **MySQL con contraseña:** la conexión usa `root` sin contraseña. Si la tuya tiene, edita `database/conexion.php` solo en tu computadora.

## Estado del proyecto (Primer avance)

### Ya funciona
* Navegación completa con menús según el rol.
* Inicio de sesión con permisos para docente y estudiante.
* Base de datos MySQL con datos de ejemplo.
* Registro de cursos, materiales (con archivo real), consultas, valoraciones y preguntas guardados en MySQL.
* Estadísticas de uso para el docente.

### Pendiente para los siguientes avances
* **Inteligencia artificial:** el asistente actual busca por palabras clave. Se integrará un modelo de lenguaje real y se comparará contra esta línea base.
* **Extracción automática del texto** de los documentos (hoy se pega manualmente).
* **Contraseñas cifradas** (las cuentas de prueba están sin cifrar).
* **Migración a Laravel** con patrón MVC.

## Datos

Los cursos, documentos, preguntas y consultas incluidos son **datos de ejemplo creados por el grupo**, no información real de estudiantes.
