-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-11-2025 a las 18:53:36
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `salon_belleza_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_estilista` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Confirmada','En Proceso','Completada','Cancelada') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_cliente`, `id_estilista`, `id_servicio`, `fecha_cita`, `hora_inicio`, `hora_fin`, `total`, `estado`, `observaciones`, `fecha_registro`) VALUES
(1, 1, 1, 1, '2025-10-15', '09:00:00', '09:45:00', 12.00, 'Confirmada', 'Corte básico', '2025-10-14 09:08:22'),
(2, 2, 1, 3, '2025-10-15', '10:00:00', '11:30:00', 25.00, 'Pendiente', 'Aplicación de tinte', '2025-10-14 09:08:22'),
(3, 3, 2, 5, '2025-10-16', '14:00:00', '15:00:00', 15.00, 'Completada', 'Pedicure Spa', '2025-10-14 09:08:22'),
(4, 4, 1, 6, '2025-10-17', '13:00:00', '14:00:00', 20.00, 'Confirmada', 'Peinado de fiesta', '2025-10-14 09:08:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cita_especificacion`
--

CREATE TABLE `cita_especificacion` (
  `id_cita` int(11) NOT NULL,
  `id_especificacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cita_especificacion`
--

INSERT INTO `cita_especificacion` (`id_cita`, `id_especificacion`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `telefono`, `correo`, `fecha_registro`) VALUES
(1, 'Lucía', 'Hernández', '7123-4567', 'lucia.hdz@example.com', '2025-10-14 09:08:22'),
(2, 'Javier', 'Castro', '7256-7788', 'javier.cast@example.com', '2025-10-14 09:08:22'),
(3, 'Paola', 'Reyes', '7133-9977', 'paola.reyes@example.com', '2025-10-14 09:08:22'),
(4, 'Verónica', 'Rivas', '7690-4455', 'vero.rivas@example.com', '2025-10-14 09:08:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `combos`
--

CREATE TABLE `combos` (
  `id_combo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `descuento` decimal(5,2) DEFAULT 0.00,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `combos`
--

INSERT INTO `combos` (`id_combo`, `nombre`, `descripcion`, `precio_total`, `descuento`, `estado`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 'Combo Relax Total', 'Incluye manicure, pedicure y masaje capilar', 30.00, 10.00, 'Activo', '2025-10-01', '2025-12-31'),
(2, 'Brillo y Estilo', 'Corte + Tinte + Peinado profesional', 50.00, 15.00, 'Activo', '2025-10-10', '2025-11-30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `combo_servicio`
--

CREATE TABLE `combo_servicio` (
  `id_combo` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `combo_servicio`
--

INSERT INTO `combo_servicio` (`id_combo`, `id_servicio`) VALUES
(1, 4),
(1, 5),
(2, 1),
(2, 3),
(2, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especificaciones`
--

CREATE TABLE `especificaciones` (
  `id_especificacion` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('Tiempo','Precio','Ambos') DEFAULT 'Ambos',
  `valor_precio` decimal(10,2) DEFAULT 0.00,
  `valor_tiempo` int(11) DEFAULT 0,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especificaciones`
--

INSERT INTO `especificaciones` (`id_especificacion`, `id_servicio`, `nombre`, `descripcion`, `tipo`, `valor_precio`, `valor_tiempo`, `estado`) VALUES
(1, 1, 'Cabello Largo', 'Aplica un costo adicional por largo', 'Ambos', 5.00, 15, 'Activo'),
(2, 3, 'Cabello Tinturado Previamente', 'Incrementa el tiempo de aplicación', 'Tiempo', 0.00, 20, 'Activo'),
(3, 4, 'Retiro de Esmalte', 'Aplica si el cliente llega con esmalte anterior', 'Ambos', 2.00, 10, 'Activo'),
(4, 6, 'Peinado con Plancha', 'Incluye uso de plancha o rizadora', 'Ambos', 3.50, 10, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estilistas`
--

CREATE TABLE `estilistas` (
  `id_estilista` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `experiencia_anios` int(11) DEFAULT 0,
  `disponible` enum('Sí','No') DEFAULT 'Sí'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estilistas`
--

INSERT INTO `estilistas` (`id_estilista`, `id_usuario`, `especialidad`, `experiencia_anios`, `disponible`) VALUES
(1, 3, 'Colorista y Cortes de Dama', 5, 'Sí'),
(2, 2, 'Atención al Cliente y Citas', 2, 'Sí');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estilista_servicio`
--

CREATE TABLE `estilista_servicio` (
  `id_estilista` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estilista_servicio`
--

INSERT INTO `estilista_servicio` (`id_estilista`, `id_servicio`) VALUES
(1, 1),
(1, 3),
(1, 6),
(2, 4),
(2, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_pagos`
--

CREATE TABLE `historial_pagos` (
  `id_pago` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `metodo_pago` enum('Efectivo','Tarjeta','Transferencia','Otro') DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha_pago` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_pagos`
--

INSERT INTO `historial_pagos` (`id_pago`, `id_cita`, `metodo_pago`, `monto`, `fecha_pago`) VALUES
(1, 1, 'Efectivo', 12.00, '2025-10-15 09:50:00'),
(2, 3, 'Tarjeta', 15.00, '2025-10-16 15:10:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id_horario` int(11) NOT NULL,
  `id_estilista` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `id_estilista`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(1, 1, 'Lunes', '08:00:00', '17:00:00'),
(2, 1, 'Martes', '08:00:00', '17:00:00'),
(3, 1, 'Miércoles', '08:00:00', '17:00:00'),
(4, 1, 'Jueves', '08:00:00', '17:00:00'),
(5, 1, 'Viernes', '08:00:00', '17:00:00'),
(6, 2, 'Sábado', '09:00:00', '15:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas`
--

CREATE TABLE `ofertas` (
  `id_oferta` int(11) NOT NULL,
  `tipo` enum('Servicio','Combo','General') DEFAULT 'Servicio',
  `id_servicio` int(11) DEFAULT NULL,
  `id_combo` int(11) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `porcentaje_descuento` decimal(5,2) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('Activa','Inactiva') DEFAULT 'Activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ofertas`
--

INSERT INTO `ofertas` (`id_oferta`, `tipo`, `id_servicio`, `id_combo`, `nombre`, `descripcion`, `porcentaje_descuento`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(1, 'Servicio', 1, NULL, 'Martes de Corte', '20% de descuento en cortes de cabello los martes', 20.00, '2025-10-01', '2025-12-31', 'Activa'),
(2, 'Servicio', 4, NULL, 'Manicure + Pedicure', '10% en servicios de uñas', 10.00, '2025-10-01', '2025-11-15', 'Activa'),
(3, 'General', NULL, NULL, 'Promoción Halloween', '15% en todos los servicios del 30 al 31 de octubre', 15.00, '2025-10-30', '2025-10-31', 'Activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Recepcionista', 'Gestión de citas y clientes'),
(3, 'Estilista', 'Atiende servicios y registra disponibilidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id_servicio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `duracion_base` int(11) NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id_servicio`, `nombre`, `descripcion`, `precio_base`, `duracion_base`, `estado`, `fecha_creacion`) VALUES
(1, 'Corte de Cabello Mujer', 'Corte básico con lavado y secado', 12.00, 45, 'Activo', '2025-10-14 09:08:22'),
(2, 'Corte de Cabello Hombre', 'Corte clásico o moderno con máquina y tijera', 8.00, 30, 'Activo', '2025-10-14 09:08:22'),
(3, 'Tinte Completo', 'Aplicación de tinte completo, incluye lavado y secado', 25.00, 90, 'Activo', '2025-10-14 09:08:22'),
(4, 'Manicure Tradicional', 'Limpieza, limado y esmalte básico', 10.00, 40, 'Activo', '2025-10-14 09:08:22'),
(5, 'Pedicure Spa', 'Tratamiento relajante de pies con exfoliación y esmalte', 15.00, 60, 'Activo', '2025-10-14 09:08:22'),
(6, 'Peinado de Fiesta', 'Peinado elegante con productos profesionales', 20.00, 60, 'Activo', '2025-10-14 09:08:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `clave` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `usuario`, `clave`, `id_rol`, `estado`, `fecha_registro`) VALUES
(1, 'Carlos', 'Orellana', 'admin@salonbelleza.com', '7777-8888', 'admin', '123456', 1, 'Activo', '2025-10-14 09:08:22'),
(2, 'María', 'Gómez', 'maria@salonbelleza.com', '7000-1111', 'maria', '123456', 2, 'Activo', '2025-10-14 09:08:22'),
(3, 'Andrea', 'Flores', 'andrea@salonbelleza.com', '7555-2222', 'andrea', '123456', 3, 'Activo', '2025-10-14 09:08:22');

--
-- Estructura de tabla para la tabla `pagos`
--
CREATE TABLE `pagos` (
  `id_pago` INT(11) NOT NULL,
  `id_cita` INT(11) NOT NULL,
  `metodo` VARCHAR(50),
  `monto` DECIMAL(10,2),
  `referencia` VARCHAR(100),
  `estado` ENUM('Pendiente','Completado','Fallido') DEFAULT 'Pendiente',
  `fecha_pago` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_estilista` (`id_estilista`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `cita_especificacion`
--
ALTER TABLE `cita_especificacion`
  ADD PRIMARY KEY (`id_cita`,`id_especificacion`),
  ADD KEY `id_especificacion` (`id_especificacion`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `combos`
--
ALTER TABLE `combos`
  ADD PRIMARY KEY (`id_combo`);

--
-- Indices de la tabla `combo_servicio`
--
ALTER TABLE `combo_servicio`
  ADD PRIMARY KEY (`id_combo`,`id_servicio`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `especificaciones`
--
ALTER TABLE `especificaciones`
  ADD PRIMARY KEY (`id_especificacion`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `estilistas`
--
ALTER TABLE `estilistas`
  ADD PRIMARY KEY (`id_estilista`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `estilista_servicio`
--
ALTER TABLE `estilista_servicio`
  ADD PRIMARY KEY (`id_estilista`,`id_servicio`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `historial_pagos`
--
ALTER TABLE `historial_pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horario`),
  ADD KEY `id_estilista` (`id_estilista`);

--
-- Indices de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD PRIMARY KEY (`id_oferta`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_combo` (`id_combo`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `combos`
--
ALTER TABLE `combos`
  MODIFY `id_combo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `especificaciones`
--
ALTER TABLE `especificaciones`
  MODIFY `id_especificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estilistas`
--
ALTER TABLE `estilistas`
  MODIFY `id_estilista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `historial_pagos`
--
ALTER TABLE `historial_pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  MODIFY `id_oferta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_estilista`) REFERENCES `estilistas` (`id_estilista`),
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`);

--
-- Filtros para la tabla `cita_especificacion`
--
ALTER TABLE `cita_especificacion`
  ADD CONSTRAINT `cita_especificacion_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`),
  ADD CONSTRAINT `cita_especificacion_ibfk_2` FOREIGN KEY (`id_especificacion`) REFERENCES `especificaciones` (`id_especificacion`);

--
-- Filtros para la tabla `combo_servicio`
--
ALTER TABLE `combo_servicio`
  ADD CONSTRAINT `combo_servicio_ibfk_1` FOREIGN KEY (`id_combo`) REFERENCES `combos` (`id_combo`),
  ADD CONSTRAINT `combo_servicio_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`);

--
-- Filtros para la tabla `especificaciones`
--
ALTER TABLE `especificaciones`
  ADD CONSTRAINT `especificaciones_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`);

--
-- Filtros para la tabla `estilistas`
--
ALTER TABLE `estilistas`
  ADD CONSTRAINT `estilistas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `estilista_servicio`
--
ALTER TABLE `estilista_servicio`
  ADD CONSTRAINT `estilista_servicio_ibfk_1` FOREIGN KEY (`id_estilista`) REFERENCES `estilistas` (`id_estilista`),
  ADD CONSTRAINT `estilista_servicio_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`);

--
-- Filtros para la tabla `historial_pagos`
--
ALTER TABLE `historial_pagos`
  ADD CONSTRAINT `historial_pagos_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`);

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`id_estilista`) REFERENCES `estilistas` (`id_estilista`);

--
-- Filtros para la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD CONSTRAINT `ofertas_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`),
  ADD CONSTRAINT `ofertas_ibfk_2` FOREIGN KEY (`id_combo`) REFERENCES `combos` (`id_combo`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

--
-- Campo nuevo para la tabla `citas`
--
ALTER TABLE citas ADD COLUMN estado_pago VARCHAR(50) DEFAULT 'Pendiente';

--
-- Campo nuevo para la tabla `cliente`
--
ALTER TABLE cliente ADD stado enum('Activo','Inactivo') DEFAULT 'Activo';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
