-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 29-10-2025 a las 10:16:35
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cafetin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `abonos`
--

DROP TABLE IF EXISTS `abonos`;
CREATE TABLE IF NOT EXISTS `abonos` (
  `id_abono` int NOT NULL AUTO_INCREMENT,
  `id_credito` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'efectivo',
  `observaciones` text COLLATE utf8mb4_general_ci,
  `fecha_abono` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_abono`),
  KEY `id_credito` (`id_credito`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  PRIMARY KEY (`id_admin`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajero`
--

DROP TABLE IF EXISTS `cajero`;
CREATE TABLE IF NOT EXISTS `cajero` (
  `id_cajero` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_ini` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_cajero`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE IF NOT EXISTS `cliente` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `alias` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `id_usuario` int NOT NULL,
  PRIMARY KEY (`id_cliente`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `alias`, `descripcion`, `id_usuario`) VALUES
(1, 'ini', 'Cliente: angel nahin manzano manzano - Tel: 04241827066', 1),
(2, 'uni', 'Cliente: isis brito - Tel: 04241456666', 2),
(3, 'uni', 'Cliente: jeremy manzano - Tel: 04160480190', 3),
(4, 'uni', 'Cliente: nini manzano - Tel: 04241827066', 4),
(5, 'trabajo', 'Cliente: jona manzano - Tel: 04241456666', 5),
(6, 'quimica', 'Cliente: juan perez - Tel: 09089887685', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

DROP TABLE IF EXISTS `configuraciones`;
CREATE TABLE IF NOT EXISTS `configuraciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `valor` text COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `tipo` enum('texto','numero','decimal','booleano','fecha') COLLATE utf8mb4_general_ci DEFAULT 'texto',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usuario_actualizacion` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `descripcion`, `tipo`, `fecha_creacion`, `fecha_actualizacion`, `usuario_actualizacion`, `activo`) VALUES
(1, 'tasa_dolar', '36.00', 'Tasa de cambio del dólar estadounidense', 'decimal', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(2, 'moneda_principal', 'BS', 'Moneda principal del sistema', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(3, 'nombre_empresa', 'Cafetín', 'Nombre de la empresa', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(4, 'direccion_empresa', '', 'Dirección de la empresa', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(5, 'telefono_empresa', '', 'Teléfono de la empresa', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(6, 'email_empresa', '', 'Email de la empresa', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(7, 'iva_porcentaje', '16.00', 'Porcentaje de IVA aplicable', 'decimal', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(8, 'formato_fecha', 'Y-m-d', 'Formato de fecha del sistema', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(9, 'zona_horaria', 'America/Caracas', 'Zona horaria del sistema', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(10, 'idioma_sistema', 'es', 'Idioma del sistema', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `credito`
--

DROP TABLE IF EXISTS `credito`;
CREATE TABLE IF NOT EXISTS `credito` (
  `id_credito` int NOT NULL AUTO_INCREMENT,
  `id_cajero` int DEFAULT NULL,
  `id_cliente` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `cantidad` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_cre` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_pago` datetime DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  PRIMARY KEY (`id_credito`),
  KEY `id_cajero` (`id_cajero`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_producto` (`id_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `credito`
--

INSERT INTO `credito` (`id_credito`, `id_cajero`, `id_cliente`, `id_producto`, `cantidad`, `total`, `fecha_cre`, `fecha_pago`, `estado`) VALUES
(1, 1, 1, 3, 1, 3.00, '2025-10-29 03:20:34', NULL, 'pendiente'),
(2, 1, 1, 3, 1, 3.00, '2025-10-29 04:08:36', NULL, 'pendiente'),
(3, 1, 1, 2, 1, 100.00, '2025-10-29 04:08:36', NULL, 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_tasa`
--

DROP TABLE IF EXISTS `historial_tasa`;
CREATE TABLE IF NOT EXISTS `historial_tasa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tasa_anterior` decimal(10,2) DEFAULT NULL,
  `tasa_nueva` decimal(10,2) NOT NULL,
  `usuario` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_cambio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `motivo` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

DROP TABLE IF EXISTS `inventario`;
CREATE TABLE IF NOT EXISTS `inventario` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `nombre_produc` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `caja_produc` int DEFAULT '0',
  `cantidad_caja` int DEFAULT '0',
  `precio_caja` decimal(10,2) NOT NULL,
  `precio_produc` decimal(10,2) NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `precio_venta` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_producto`, `nombre_produc`, `caja_produc`, `cantidad_caja`, `precio_caja`, `precio_produc`, `activo`, `precio_venta`) VALUES
(1, 'pepsi', 12, 24, 2000.00, 83.33, 0, 100.00),
(2, 'pepsi litro', 12, 24, 2000.00, 83.33, 1, 100.00),
(3, 'OREO', 10, 12, 30.00, 2.50, 1, 3.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

DROP TABLE IF EXISTS `persona`;
CREATE TABLE IF NOT EXISTS `persona` (
  `id_persona` int NOT NULL AUTO_INCREMENT,
  `cedula` int NOT NULL,
  `nombre` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `apellido` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_persona`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id_persona`, `cedula`, `nombre`, `apellido`, `telefono`) VALUES
(1, 30729911, 'angel', 'manzano', '04241827066'),
(2, 30885890, 'isis', 'brito', '04241456666'),
(3, 29698636, 'jeremy', 'manzano', '04160480190'),
(4, 16392828, 'nini', 'manzano', '04241827066'),
(5, 12251860, 'jona', 'manzano', '04241456666'),
(6, 12345678, 'juan', 'perez', '09089887685');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preferencias_usuario`
--

DROP TABLE IF EXISTS `preferencias_usuario`;
CREATE TABLE IF NOT EXISTS `preferencias_usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `moneda_preferida` enum('BS','USD','AMBAS') COLLATE utf8mb4_general_ci DEFAULT 'BS',
  `sonidos_notificacion` tinyint(1) DEFAULT '1',
  `confirmacion_ventas` tinyint(1) DEFAULT '1',
  `auto_imprimir` tinyint(1) DEFAULT '0',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario` (`usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

DROP TABLE IF EXISTS `preguntas`;
CREATE TABLE IF NOT EXISTS `preguntas` (
  `id_pregunta` int NOT NULL AUTO_INCREMENT,
  `pregunta` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_pregunta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

DROP TABLE IF EXISTS `respuestas`;
CREATE TABLE IF NOT EXISTS `respuestas` (
  `id_respuesta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `id_pregunta` int DEFAULT NULL,
  `respuesta` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_respuesta`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_pregunta` (`id_pregunta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`) VALUES
(1, 'cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `id_persona` int DEFAULT NULL,
  `usuario` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `id_rol` int DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `id_persona` (`id_persona`),
  KEY `id_rol` (`id_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_persona`, `usuario`, `contrasena`, `id_rol`) VALUES
(1, 1, 'angel nahinmanzano m', '$2y$10$B/.xcAN4K.P63yMlj2.gYep6xhrTYMeyoQPdd226wpFp8xGGllTNa', 1),
(2, 2, 'isisbrito', '$2y$10$t7fZxA6rJcb1aEliBR8jkO0NSft0AtAS4gP5UmnZleuhdSqJMdI2m', 1),
(3, 3, 'jeremymanzano', '$2y$10$6R2C7swhlZJyeaSS5LL8/OBH9S/wRpRB6ZhzrKotSt5wiUgjJRtTu', 1),
(4, 4, 'ninimanzano', '$2y$10$sK3oFLaA4LmuXeCMCqGIoeWX6tDoCy6dyXwMc7SE.XytnlObdF.DG', 1),
(5, 5, 'jonamanzano', '$2y$10$vfU0mo1OlUUtZfreP7L7J.H.9./idygwS8Vp0QloKWeSIXud4Lyj6', 1),
(6, 6, 'juanperez', '$2y$10$kd77YUDKO9CNFI2JHlEGi.WS4GyYyyOC9DYAEYR9ZKK8k.lhTY7Oa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

DROP TABLE IF EXISTS `ventas`;
CREATE TABLE IF NOT EXISTS `ventas` (
  `id_venta` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int DEFAULT NULL,
  `id_cajero` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `cantidad` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_venta` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_venta`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_cajero` (`id_cajero`),
  KEY `id_producto` (`id_producto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
