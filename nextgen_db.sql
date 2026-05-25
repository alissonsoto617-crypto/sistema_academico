
CREATE DATABASE IF NOT EXISTS `nextgen_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `nextgen_db`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

DROP TABLE IF EXISTS `calificaciones`;
CREATE TABLE `calificaciones` (
  `id_calificacion` int NOT NULL,
  `id_inscripcion` int NOT NULL,
  `tareas` decimal(5,2) NOT NULL,
  `laboratorios` decimal(5,2) NOT NULL,
  `examen_final` decimal(5,2) NOT NULL,
  `nota` decimal(4,2) GENERATED ALWAYS AS ((((`tareas` * 0.3) + (`laboratorios` * 0.3)) + (`examen_final` * 0.4))) STORED
) ;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id_calificacion`, `id_inscripcion`, `tareas`, `laboratorios`, `examen_final`) VALUES
(1, 1, '9.00', '8.00', '9.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

DROP TABLE IF EXISTS `carreras`;
CREATE TABLE `carreras` (
  `id_carrera` int NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `carreras`
--

INSERT INTO `carreras` (`id_carrera`, `nombre`) VALUES
(1, 'Ingenieria en Sistema'),
(2, 'Ingenieria en Matematicas'),
(3, 'Odontologia'),
(4, 'Software'),
(5, 'Contabilidad');

-- --------------------------------------------------------


DROP TABLE IF EXISTS `estudiantes`;
CREATE TABLE `estudiantes` (
  `id_estudiante` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `id_carrera` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `nombre`, `apellido`, `correo`, `telefono`, `id_carrera`) VALUES
(1, 'Ana', 'Martinez', 'ana_est@gmail.com', NULL, 1),
(2, 'Luis', 'Perez', 'luis_est@gmail.com', NULL, 2),
(3, 'Josue', 'Varela', 'gomezvarelajosuealexander@gmail.com', '1234-5678', 1),
(4, 'juan ', 'Varela', 'juaner@gmail.com', '1445-6667', 4),
(5, 'pedro', 'sola', 'pedror@gmail.com', '7676-5588', 5),
(6, 'alisson', 'Alvarado', 'alisson@mail.com', '9898-5556', 4),
(7, 'Fernando', 'Ochoa', 'fercho@gmail.com', '5654-8723', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

DROP TABLE IF EXISTS `grupos`;
CREATE TABLE `grupos` (
  `id_grupo` int NOT NULL,
  `id_materia` int NOT NULL,
  `id_maestro` int NOT NULL,
  `cupo_maximo` int NOT NULL
) ;

--
-- Volcado de datos para la tabla `grupos`
--

INSERT INTO `grupos` (`id_grupo`, `id_materia`, `id_maestro`, `cupo_maximo`) VALUES
(1, 1, 1, 30),
(2, 2, 2, 30),
(3, 3, 1, 20),
(4, 4, 1, 2),
(5, 5, 1, 2),
(6, 6, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

DROP TABLE IF EXISTS `inscripciones`;
CREATE TABLE `inscripciones` (
  `id_inscripcion` int NOT NULL,
  `id_estudiante` int NOT NULL,
  `id_grupo` int NOT NULL,
  `periodo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id_inscripcion`, `id_estudiante`, `id_grupo`, `periodo`) VALUES
(1, 1, 1, '2026-02'),
(2, 3, 1, '2026-02'),
(3, 4, 4, '2026-02'),
(4, 6, 4, '2026-02'),
(5, 7, 5, '2026-02');

--
-- Disparadores `inscripciones`
--
DROP TRIGGER IF EXISTS `validar_carrera_inscripcion`;
DELIMITER $$
CREATE TRIGGER `validar_carrera_inscripcion` BEFORE INSERT ON `inscripciones` FOR EACH ROW BEGIN
    DECLARE carrera_est INT;
    DECLARE carrera_mat INT;

    -- carrera del estudiante
    SELECT id_carrera INTO carrera_est
    FROM estudiantes
    WHERE id_estudiante = NEW.id_estudiante;

    -- carrera de la materia
    SELECT m.id_carrera INTO carrera_mat
    FROM grupos g
    JOIN materias m ON g.id_materia = m.id_materia
    WHERE g.id_grupo = NEW.id_grupo;

    IF carrera_est != carrera_mat THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No puede inscribirse a materias de otra carrera';
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `validar_prerrequisitos`;
DELIMITER $$
CREATE TRIGGER `validar_prerrequisitos` BEFORE INSERT ON `inscripciones` FOR EACH ROW BEGIN
    DECLARE faltantes INT;

    SELECT COUNT(*) INTO faltantes
    FROM prerrequisitos pr
    WHERE pr.id_materia = (
        SELECT id_materia FROM grupos WHERE id_grupo = NEW.id_grupo
    )
    AND pr.id_requisito NOT IN (
        SELECT g.id_materia
        FROM inscripciones i
        JOIN grupos g ON i.id_grupo = g.id_grupo
        JOIN calificaciones c ON i.id_inscripcion = c.id_inscripcion
        WHERE i.id_estudiante = NEW.id_estudiante
        AND c.nota >= 6
    );

    IF faltantes > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No cumple con los prerrequisitos';
    END IF;

END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `validar_reinscripcion`;
DELIMITER $$
CREATE TRIGGER `validar_reinscripcion` BEFORE INSERT ON `inscripciones` FOR EACH ROW BEGIN
    DECLARE existe INT;

    SELECT COUNT(*) INTO existe
    FROM inscripciones i
    JOIN calificaciones c ON i.id_inscripcion = c.id_inscripcion
    JOIN grupos g ON i.id_grupo = g.id_grupo
    WHERE i.id_estudiante = NEW.id_estudiante
    AND g.id_materia = (
        SELECT id_materia FROM grupos WHERE id_grupo = NEW.id_grupo
    )
    AND c.nota >= 6;

    IF existe > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ya aprobo esta materia, no puede inscribirse de nuevo';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestros`
--

DROP TABLE IF EXISTS `maestros`;
CREATE TABLE `maestros` (
  `id_maestro` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `especialidad` varchar(100) NOT NULL,
  `correo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `maestros`
--

INSERT INTO `maestros` (`id_maestro`, `nombre`, `apellido`, `especialidad`, `correo`) VALUES
(1, 'Carlos', 'Lopez', 'Bases de Datos', 'carlos@school.com'),
(2, 'Maria', 'Gomez', 'Programacion', 'maria@school.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

DROP TABLE IF EXISTS `materias`;
CREATE TABLE `materias` (
  `id_materia` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_carrera` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre`, `id_carrera`) VALUES
(1, 'Calculo 1', 1),
(2, 'Calculo I', 2),
(3, 'Anatomia', 3),
(4, 'Programacion 1', 4),
(5, 'Programacion 2', 4),
(6, 'Contabilidad 1', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prerrequisitos`
--

DROP TABLE IF EXISTS `prerrequisitos`;
CREATE TABLE `prerrequisitos` (
  `id_materia` int NOT NULL,
  `id_requisito` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id_calificacion`),
  ADD UNIQUE KEY `id_inscripcion` (`id_inscripcion`);

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id_carrera`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id_estudiante`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id_grupo`),
  ADD UNIQUE KEY `id_materia` (`id_materia`,`id_maestro`),
  ADD KEY `id_maestro` (`id_maestro`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id_inscripcion`),
  ADD UNIQUE KEY `id_estudiante` (`id_estudiante`,`id_grupo`,`periodo`),
  ADD KEY `id_grupo` (`id_grupo`);

--
-- Indices de la tabla `maestros`
--
ALTER TABLE `maestros`
  ADD PRIMARY KEY (`id_maestro`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `prerrequisitos`
--
ALTER TABLE `prerrequisitos`
  ADD PRIMARY KEY (`id_materia`,`id_requisito`),
  ADD KEY `id_requisito` (`id_requisito`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id_calificacion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id_carrera` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id_estudiante` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `grupos`
--
ALTER TABLE `grupos`
  MODIFY `id_grupo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id_inscripcion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `maestros`
--
ALTER TABLE `maestros`
  MODIFY `id_maestro` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripciones` (`id_inscripcion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`id_carrera`) REFERENCES `carreras` (`id_carrera`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD CONSTRAINT `grupos_ibfk_1` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `grupos_ibfk_2` FOREIGN KEY (`id_maestro`) REFERENCES `maestros` (`id_maestro`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`id_carrera`) REFERENCES `carreras` (`id_carrera`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `prerrequisitos`
--
ALTER TABLE `prerrequisitos`
  ADD CONSTRAINT `prerrequisitos_ibfk_1` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prerrequisitos_ibfk_2` FOREIGN KEY (`id_requisito`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    rol ENUM('admin','profesor') NOT NULL
);
INSERT INTO usuarios (usuario, password, rol)
VALUES
('admin', '1234', 'admin'),
('profe1', '1234', 'profesor');