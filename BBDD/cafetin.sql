-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 11-11-2025 a las 09:27:24
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
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `abonos`
--

INSERT INTO `abonos` (`id_abono`, `id_credito`, `monto`, `metodo_pago`, `observaciones`, `fecha_abono`) VALUES
(1, 6, 2.00, 'efectivo', '', '2025-11-07 13:47:24'),
(2, 1, 3.00, 'efectivo', '', '2025-11-07 14:06:58'),
(3, 2, 3.00, 'efectivo', '', '2025-11-07 14:06:58'),
(4, 3, 34.00, 'efectivo', '', '2025-11-07 14:06:58'),
(5, 3, 12.00, 'transferencia', '', '2025-11-07 14:07:21'),
(6, 3, 10.00, 'efectivo', '', '2025-11-07 14:07:35'),
(7, 3, 12.00, 'efectivo', '', '2025-11-07 14:10:48'),
(8, 3, 32.00, 'efectivo', '', '2025-11-07 14:23:55'),
(9, 4, 36.00, 'efectivo', '', '2025-11-07 14:23:55'),
(10, 5, 3.00, 'efectivo', '', '2025-11-07 14:23:55'),
(11, 6, 1.00, 'efectivo', '', '2025-11-07 14:23:55'),
(12, 7, 2.00, 'transferencia', '', '2025-11-07 14:25:22'),
(13, 7, 1.00, '', '', '2025-11-07 14:25:32'),
(14, 8, 1.00, '', '', '2025-11-07 14:25:32'),
(15, 9, 50.00, 'transferencia', '', '2025-11-07 15:56:53'),
(16, 12, 20.00, 'efectivo', '', '2025-11-07 15:57:43');

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admin`
--

INSERT INTO `admin` (`id_admin`, `id_usuario`) VALUES
(1, 7);

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cajero`
--

INSERT INTO `cajero` (`id_cajero`, `id_usuario`, `activo`, `fecha_ini`, `fecha_fin`) VALUES
(1, 8, 1, '2025-10-29', NULL),
(2, 9, 1, '2025-10-29', NULL);

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
(1, 'tasa_dolar', '300', 'Tasa de cambio del dólar estadounidense', 'decimal', '2025-10-29 09:42:03', '2025-10-29 17:12:30', 'admin', 1),
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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `credito`
--

INSERT INTO `credito` (`id_credito`, `id_cajero`, `id_cliente`, `id_producto`, `cantidad`, `total`, `fecha_cre`, `fecha_pago`, `estado`) VALUES
(1, 1, 1, 3, 1, 3.00, '2025-10-29 03:20:34', NULL, 'pagado'),
(2, 1, 1, 3, 1, 3.00, '2025-10-29 04:08:36', NULL, 'pagado'),
(3, 1, 1, 2, 1, 100.00, '2025-10-29 04:08:36', NULL, 'pagado'),
(4, 1, 1, 3, 12, 36.00, '2025-10-29 13:00:39', NULL, 'pagado'),
(5, 1, 1, 3, 1, 3.00, '2025-11-07 07:07:47', NULL, 'pagado'),
(6, 1, 1, 3, 1, 3.00, '2025-11-07 07:08:08', NULL, 'pagado'),
(7, 1, 1, 3, 1, 3.00, '2025-11-07 10:24:34', NULL, 'pagado'),
(8, 1, 1, 1, 1, 1.00, '2025-11-07 10:24:34', NULL, 'pagado'),
(9, 1, 1, 2, 1, 100.00, '2025-11-07 10:26:55', NULL, 'parcial'),
(10, 1, 1, 3, 2, 6.00, '2025-11-07 10:27:15', NULL, 'pendiente'),
(11, 1, 1, 1, 1, 1.00, '2025-11-07 10:27:15', NULL, 'pendiente'),
(12, 1, 2, 2, 1, 100.00, '2025-11-07 10:40:17', NULL, 'parcial'),
(13, 1, 1, 3, 1, 3.00, '2025-11-07 10:43:22', NULL, 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_producto`
--

DROP TABLE IF EXISTS `historial_producto`;
CREATE TABLE IF NOT EXISTS `historial_producto` (
  `id_historial` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cajas_agregar` int NOT NULL,
  `unidades_por_caja` int NOT NULL,
  `unidades_sueltas_agregar` int NOT NULL,
  `unidades_agregadas_total` int NOT NULL,
  `precio_venta_usd` decimal(10,2) NOT NULL,
  `precio_venta_bs` decimal(12,2) NOT NULL,
  `tasa_dolar` decimal(10,2) NOT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `id_producto` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `historial_producto`
--

INSERT INTO `historial_producto` (`id_historial`, `id_producto`, `fecha_registro`, `cajas_agregar`, `unidades_por_caja`, `unidades_sueltas_agregar`, `unidades_agregadas_total`, `precio_venta_usd`, `precio_venta_bs`, `tasa_dolar`, `observacion`) VALUES
(6, 4, '2025-11-10 14:25:09', 2, 12, 0, 24, 2.00, 600.00, 300.00, '');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_tasa`
--

INSERT INTO `historial_tasa` (`id`, `tasa_anterior`, `tasa_nueva`, `usuario`, `fecha_cambio`, `motivo`) VALUES
(1, 36.00, 219.87, 'admin', '2025-10-29 10:29:49', ''),
(2, 219.87, 300.00, 'admin', '2025-10-29 17:12:30', '');

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
  `cantidad_total` int NOT NULL,
  `precio_caja` decimal(10,2) NOT NULL,
  `precio_produc` decimal(10,2) NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `precio_venta` decimal(10,2) NOT NULL,
  `unidades_sueltas` int DEFAULT '0',
  PRIMARY KEY (`id_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_producto`, `nombre_produc`, `caja_produc`, `cantidad_caja`, `cantidad_total`, `precio_caja`, `precio_produc`, `activo`, `precio_venta`, `unidades_sueltas`) VALUES
(4, 'pepsi', 4, 12, 48, 20.00, 1.67, 1, 2.00, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset`
--

DROP TABLE IF EXISTS `password_reset`;
CREATE TABLE IF NOT EXISTS `password_reset` (
  `id_reset` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT '0',
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reset`),
  UNIQUE KEY `token` (`token`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `password_reset`
--

INSERT INTO `password_reset` (`id_reset`, `id_usuario`, `token`, `expiracion`, `usado`, `creado`) VALUES
(1, 9, 'f21bcd17f67a8508d7bea976aa11144c6a554294562e888ee3c6997539241499', '2025-11-10 09:19:05', 1, '2025-11-10 12:49:05'),
(2, 9, 'e4b8891c911d3e582f341c56d2de0c976b167b71151dc5513746cd74c81ca35c', '2025-11-10 09:30:48', 0, '2025-11-10 13:00:48'),
(3, 9, '4350490329aafdc193af16c1399f7af08cbe4a23ab7a099e2e835adbbdfaf5b2', '2025-11-10 09:42:59', 0, '2025-11-10 13:12:59'),
(4, 9, 'efc3cf282c6f7af676806a1a8d6d37fe15a8baaebfd4f61db5e1145917d164c2', '2025-11-10 09:45:36', 0, '2025-11-10 13:15:36'),
(5, 9, 'b76befa536e02c35ce0ea7b91ec43c42f5c5c051e1357b2ac5ffbab1031eacc0', '2025-11-10 09:52:08', 0, '2025-11-10 13:22:08'),
(6, 9, '01cb8b16fe6eecfe8fb1c979903eef5b398c295ba8faa735882989a502d9a858', '2025-11-10 09:56:21', 0, '2025-11-10 13:26:21');

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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id_persona`, `cedula`, `nombre`, `apellido`, `telefono`) VALUES
(1, 30729911, 'angel', 'manzano', '04241827066'),
(2, 30885890, 'isis', 'brito', '04241456666'),
(3, 29698636, 'jeremy', 'manzano', '04160480190'),
(4, 16392828, 'nini', 'manzano', '04241827066'),
(5, 12251860, 'jona', 'manzano', '04241456666'),
(6, 12345678, 'juan', 'perez', '09089887685'),
(7, 0, 'Administrador', 'Sistema', '0000000000'),
(8, 0, 'luis', 'peres', '04160480190'),
(9, 0, 'nini', 'manzano', '04241827066');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preferencias_usuario`
--

INSERT INTO `preferencias_usuario` (`id`, `usuario`, `moneda_preferida`, `sonidos_notificacion`, `confirmacion_ventas`, `auto_imprimir`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'cajero1', 'BS', 1, 1, 0, '2025-10-29 10:45:12', '2025-10-29 10:45:12'),
(2, 'cajero2', 'BS', 1, 1, 0, '2025-11-07 11:04:45', '2025-11-07 11:04:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

DROP TABLE IF EXISTS `preguntas`;
CREATE TABLE IF NOT EXISTS `preguntas` (
  `id_pregunta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `pregunta` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_pregunta`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id_pregunta`, `id_usuario`, `pregunta`) VALUES
(1, 0, '¿Cuál es el nombre de tu primera mascota?'),
(2, 0, '¿En qué ciudad naciste?'),
(3, 0, '¿Cuál es tu comida favorita?');

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
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuestas`
--

INSERT INTO `respuestas` (`id_respuesta`, `id_usuario`, `id_pregunta`, `respuesta`) VALUES
(12, 9, 3, '$2y$10$fVV5wjT4i62KbqB3r0W25OLw5kyHVl/isNitZ/5mSbEdpug9ltAfC'),
(11, 9, 2, '$2y$10$MrUZ12HQeRb.2sTIRshFY.lsHC7wQ20m8R.pfpzUulSf./R8xCrjq'),
(10, 9, 1, '$2y$10$PreOLtnRxIsRe9HvFaSxB.jXCeZagxh8gVcseTvCistWKUgWroRBW');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`) VALUES
(1, 'cliente'),
(2, 'cajero'),
(3, 'admin');

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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_persona`, `usuario`, `contrasena`, `id_rol`) VALUES
(1, 1, 'angel nahinmanzano m', '$2y$10$B/.xcAN4K.P63yMlj2.gYep6xhrTYMeyoQPdd226wpFp8xGGllTNa', 1),
(2, 2, 'isisbrito', '$2y$10$t7fZxA6rJcb1aEliBR8jkO0NSft0AtAS4gP5UmnZleuhdSqJMdI2m', 1),
(3, 3, 'jeremymanzano', '$2y$10$6R2C7swhlZJyeaSS5LL8/OBH9S/wRpRB6ZhzrKotSt5wiUgjJRtTu', 1),
(4, 4, 'ninimanzano', '$2y$10$sK3oFLaA4LmuXeCMCqGIoeWX6tDoCy6dyXwMc7SE.XytnlObdF.DG', 1),
(5, 5, 'jonamanzano', '$2y$10$vfU0mo1OlUUtZfreP7L7J.H.9./idygwS8Vp0QloKWeSIXud4Lyj6', 1),
(6, 6, 'juanperez', '$2y$10$kd77YUDKO9CNFI2JHlEGi.WS4GyYyyOC9DYAEYR9ZKK8k.lhTY7Oa', 1),
(7, 7, 'admin', '$2y$10$ygaGAUKzrFNnfhmqLgXyd.9AI2mrcjh2lYHiFC4lKCzn7VjLj/6HO', 3),
(8, 8, 'cajero1', '$2y$10$TGfffoBtmmr2dyh25XNmpOaB6/.Aq0Kk6oe9Xbgormj8iGeAR3/OO', 2),
(9, 9, 'cajero2', '$2y$10$pyQGiFKxe4V4shWFGUNiluxGmDn5nEpKTUL8Wz9ShFC/f3RE1r3ea', 2);

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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `id_cliente`, `id_cajero`, `id_producto`, `cantidad`, `total`, `fecha_venta`) VALUES
(1, 1, 1, 2, 1, 100.00, '2025-10-29 12:39:33'),
(2, 1, 1, 3, 2, 6.00, '2025-10-29 12:39:33'),
(3, 1, 1, 3, 1, 3.00, '2025-10-29 13:21:33'),
(4, 1, 1, 2, 1, 100.00, '2025-10-29 13:21:33'),
(5, 1, 1, 3, 1, 3.00, '2025-11-07 06:55:45'),
(6, 1, 1, 3, 1, 3.00, '2025-11-07 06:58:13'),
(7, 1, 1, 1, 1, 1.00, '2025-11-07 06:59:03'),
(8, 1, 1, 2, 1, 100.00, '2025-11-07 07:00:51'),
(9, 1, 1, 3, 1, 3.00, '2025-11-07 07:01:18'),
(10, 1, 1, 3, 1, 3.00, '2025-11-07 07:03:07'),
(11, 1, 1, 3, 1, 3.00, '2025-11-07 07:34:17'),
(12, 1, 1, 3, 1, 3.00, '2025-11-07 07:34:33'),
(13, 1, 1, 2, 1, 100.00, '2025-11-07 11:10:05'),
(14, 1, 1, 3, 1, 3.00, '2025-11-07 11:17:15'),
(15, 1, 1, 3, 1, 3.00, '2025-11-07 11:18:12'),
(16, 2, 1, 1, 1, 1.00, '2025-11-07 11:18:29'),
(17, 2, 1, 3, 2, 6.00, '2025-11-07 11:51:53'),
(18, 2, 1, 3, 2, 6.00, '2025-11-07 12:04:06');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
