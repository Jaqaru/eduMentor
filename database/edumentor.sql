-- Diseño y datos iniciales de base de datos EduMentor RAG
-- Grupo 03 - Aplicaciones Distribuidas I - UNS
CREATE DATABASE IF NOT EXISTS edumentor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edumentor;

-- 1. Tabla de Usuarios y Roles
DROP TABLE IF EXISTS valoraciones;
DROP TABLE IF EXISTS consulta_fuentes;
DROP TABLE IF EXISTS consultas;
DROP TABLE IF EXISTS preguntas_banco;
DROP TABLE IF EXISTS fragmentos;
DROP TABLE IF EXISTS materiales;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL UNIQUE,
  clave VARCHAR(255) NOT NULL,
  rol ENUM('docente','estudiante','admin') NOT NULL DEFAULT 'estudiante',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla de Cursos
CREATE TABLE cursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  docente_id INT NOT NULL,
  codigo VARCHAR(10) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  ciclo TINYINT,
  descripcion TEXT,
  FOREIGN KEY (docente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 3. Tabla de Materiales Académicos Autorizados
CREATE TABLE materiales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  curso_id INT NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  unidad VARCHAR(60),
  tipo VARCHAR(50) DEFAULT 'Separata',
  archivo VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- 4. Tabla de Fragmentos Procesados (Base de Conocimiento RAG)
CREATE TABLE fragmentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  material_id INT NOT NULL,
  pagina INT,
  texto TEXT NOT NULL,
  FULLTEXT KEY ft_fragmentos_texto (texto),
  FOREIGN KEY (material_id) REFERENCES materiales(id) ON DELETE CASCADE
);

-- 5. Tabla de Consultas Académicas al Asistente
CREATE TABLE consultas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT NOT NULL,
  curso_id INT NOT NULL,
  pregunta TEXT NOT NULL,
  respuesta TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- 6. Tabla de Asociación de Fuentes Recuperadas por Consulta
CREATE TABLE consulta_fuentes (
  consulta_id INT NOT NULL,
  fragmento_id INT NOT NULL,
  PRIMARY KEY (consulta_id, fragmento_id),
  FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE CASCADE,
  FOREIGN KEY (fragmento_id) REFERENCES fragmentos(id) ON DELETE CASCADE
);

-- 7. Tabla de Valoraciones y Reportes de Calidad
CREATE TABLE valoraciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  consulta_id INT NOT NULL,
  utilidad ENUM('util','parcial','no_util') NULL,
  reporte ENUM('incorrecta','incompleta','fuente_no_relacionada','no_sustentada') NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_valoracion_consulta (consulta_id),
  FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE CASCADE
);

-- 8. Tabla de Banco de Preguntas para Evaluaciones
CREATE TABLE preguntas_banco (
  id INT AUTO_INCREMENT PRIMARY KEY,
  curso_id INT NOT NULL,
  enunciado TEXT NOT NULL,
  opcion_correcta VARCHAR(255) NOT NULL,
  opciones_incorrectas TEXT NOT NULL,
  explicacion TEXT NULL,
  FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- ========================================================
-- DATOS DE DEMOSTRACIÓN Y PRUEBA (SEEDS)
-- ========================================================

-- Usuarios de demostración (claves hasheadas con bcrypt)
-- Credenciales de prueba: docente@uns.edu.pe / docente123  |  estudiante@uns.edu.pe / estudiante123
INSERT INTO usuarios (id, nombre, correo, clave, rol) VALUES
(1, 'Docente Demo', 'docente@uns.edu.pe', '$2y$10$ky5FymM/XVOrAa2IymMWyu2Y6rYebRve3.HRKTkIntDyH.UHYRLZW', 'docente'),
(2, 'Estudiante Demo', 'estudiante@uns.edu.pe', '$2y$10$D7lcU2z9YhDIIuDP1IL66.18xS8irEaCeIUO0vQSmQLDzDjtgCryy', 'estudiante');

-- Cursos registrados
INSERT INTO cursos (id, docente_id, codigo, nombre, ciclo, descripcion) VALUES
(1, 1, '101234', 'Aplicaciones Distribuidas I', 6, 'Arquitecturas cliente-servidor, sistemas distribuidos, patrón MVC, API REST y servicios web.'),
(2, 1, '101235', 'Base de Datos II', 6, 'Optimización de consultas SQL, modelado relacional, normalización, transacciones ACID y NoSQL.'),
(3, 1, '101236', 'Ingeniería de Software', 6, 'Metodologías ágiles Scrum, requerimientos de software, pruebas unitarias y calidad.');

-- Materiales autorizados
INSERT INTO materiales (id, curso_id, titulo, unidad, tipo, archivo) VALUES
(1, 1, 'Separata Unidad 1: Fundamentos de Arquitecturas Distribuidas', 'Unidad 1', 'Separata', 'separata_u1_sistemas_distribuidos.pdf'),
(2, 1, 'Diapositivas Unidad 2: Patrón MVC y APIs REST con PHP', 'Unidad 2', 'Diapositivas', 'diapositivas_u2_mvc_api_rest.pdf'),
(3, 2, 'Guía de Laboratorio: Normalización y Modelado ER', 'Unidad 1', 'Guía', 'guia_lab_bd2_modelado.pdf');

-- Fragmentos de conocimiento para RAG
INSERT INTO fragmentos (id, material_id, pagina, texto) VALUES
(1, 1, 4, 'En la arquitectura cliente-servidor, un nodo central atiende solicitudes. En contraste, una arquitectura distribuida reparte la computación, el almacenamiento y la coordinación entre múltiples nodos que cooperan a través de una red sin un único punto de fallo.'),
(2, 1, 8, 'Las ventajas clave de las aplicaciones distribuidas incluyen alta disponibilidad, tolerancia a fallos y escalabilidad horizontal, permitiendo agregar más máquinas conforme crece la demanda del sistema.'),
(3, 2, 3, 'El patrón Modelo-Vista-Controlador (MVC) separa la aplicación en tres capas: el Modelo gestiona los datos y reglas de negocio, la Vista presenta la interfaz al usuario, y el Controlador coordina el flujo entre ambos procesando las peticiones HTTP.'),
(4, 2, 12, 'Una API REST utiliza métodos HTTP estándar (GET, POST, PUT, DELETE) e intercambia datos en formato JSON de forma desacoplada y sin estado entre clientes web, móviles y el servidor.'),
(5, 3, 2, 'La normalización de bases de datos minimiza la redundancia y evita anomalías de inserción, actualización y borrado mediante las formas normales 1FN, 2FN y 3FN.');

-- Consultas de prueba
INSERT INTO consultas (id, estudiante_id, curso_id, pregunta, respuesta) VALUES
(1, 2, 1, '¿Cuál es la diferencia entre arquitectura cliente-servidor y arquitectura distribuida?', 'En la cliente-servidor, un servidor central atiende a los clientes. En la distribuida, los componentes se reparten en varios nodos que cooperan a través de una red sin depender de un único punto de fallo.'),
(2, 2, 1, '¿Para qué sirve el patrón MVC?', 'El patrón MVC organiza la aplicación separando los datos y reglas de negocio (Modelo), la interfaz de usuario (Vista) y la lógica de control que atiende las solicitudes (Controlador).');

-- Fuentes asociadas
INSERT INTO consulta_fuentes (consulta_id, fragmento_id) VALUES
(1, 1),
(2, 3);

-- Valoraciones
INSERT INTO valoraciones (id, consulta_id, utilidad, reporte) VALUES
(1, 1, 'util', NULL),
(2, 2, 'util', NULL);

-- Banco de preguntas
INSERT INTO preguntas_banco (id, curso_id, enunciado, opcion_correcta, opciones_incorrectas, explicacion) VALUES
(1, 1, '¿Cuál es la característica principal de una arquitectura distribuida?', 'Los componentes y el cómputo se reparten en múltiples nodos interconectados.', 'Todo el procesamiento se concentra en una sola máquina sin conexión a red.||No utiliza protocolos de comunicación estándares.||Solo puede ejecutarse en dispositivos móviles.', 'Fuente: Separata Unidad 1 (pág. 4).'),
(2, 1, 'En el patrón MVC, ¿qué componente es responsable de gestionar las reglas del negocio y datos?', 'El Modelo', 'La Vista||El Controlador||El Router HTTP', 'Fuente: Diapositivas Unidad 2 (pág. 3).'),
(3, 1, '¿Qué formato estándar de datos se utiliza usualmente en una API REST?', 'JSON (JavaScript Object Notation)', 'Binario no estructurado||Archivos INI planos||Imágenes comprimidas', 'Fuente: Diapositivas Unidad 2 (pág. 12).'),
(4, 2, '¿Cuál es el beneficio primordial de la normalización de bases de datos relacionales?', 'Minimizar la redundancia y prevenir anomalías de actualización.', 'Hacer que la base de datos ocupe más espacio de disco.||Eliminar todas las claves foráneas de las tablas.||Prohibir el uso de consultas SQL SELECT.', 'Fuente: Guía de Laboratorio (pág. 2).');