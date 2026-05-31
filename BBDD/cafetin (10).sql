-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 31-05-2026 a las 14:45:25
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
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(16, 12, 20.00, 'efectivo', '', '2025-11-07 15:57:43'),
(17, 9, 50.00, 'ajuste', 'Ajuste automático al marcar como pagado', '2025-11-12 05:54:44'),
(18, 10, 6.00, 'ajuste', 'Ajuste automático al marcar como pagado', '2025-11-12 05:54:44'),
(19, 11, 1.00, 'ajuste', 'Ajuste automático al marcar como pagado', '2025-11-12 05:54:44'),
(20, 12, 80.00, 'ajuste', 'Ajuste automático al marcar como pagado', '2025-11-12 05:54:44'),
(21, 13, 3.00, 'ajuste', 'Ajuste automático al marcar como pagado', '2025-11-12 05:54:44'),
(22, 14, 1.00, 'efectivo', '(Parte en Bs: Bs 300.00 a tasa 300)', '2025-11-12 17:41:39'),
(23, 15, 1.00, 'efectivo', '(Parte en USD)', '2025-12-12 16:27:27'),
(24, 15, 2.00, 'efectivo', '(Parte en Bs: Bs 1000.00 a tasa 500)', '2025-12-12 16:27:27'),
(25, 17, 4.00, 'efectivo', '(Parte en USD)', '2026-02-03 21:09:51'),
(26, 18, 1.00, 'efectivo', '(Parte en USD)', '2026-02-03 21:09:51'),
(27, 18, 1.00, 'efectivo', '(Parte en Bs: Bs 2000.00 a tasa 500)', '2026-02-03 21:09:51'),
(28, 19, 1.00, 'efectivo', '(Parte en Bs: Bs 2000.00 a tasa 500)', '2026-02-03 21:09:51'),
(29, 20, 2.00, 'efectivo', '(Parte en Bs: Bs 2000.00 a tasa 500)', '2026-02-03 21:09:51'),
(30, 15, 2.00, 'efectivo', '(Parte en USD)', '2026-02-03 21:25:46'),
(31, 15, 1.00, 'efectivo', '(Parte en Bs: Bs 1500.00 a tasa 500)', '2026-02-03 21:25:46'),
(32, 16, 2.00, 'efectivo', '(Parte en Bs: Bs 1500.00 a tasa 500)', '2026-02-03 21:25:46'),
(33, 14, 1.00, 'transferencia', 'Pago mitad y mitad (Parte en USD)', '2026-03-26 13:43:09'),
(34, 14, 1.00, 'transferencia', 'Pago mitad y mitad (Parte en Bs: Bs 600.00 a tasa 600)', '2026-03-26 13:43:09'),
(35, 21, 2.00, 'transferencia', '(Parte en USD)', '2026-04-23 13:18:19'),
(36, 21, 1.43, 'transferencia', '(Parte en Bs: Bs 999.98 a tasa 700)', '2026-04-23 13:18:20');

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
-- Estructura de tabla para la tabla `backup_abonos_pendientes`
--

DROP TABLE IF EXISTS `backup_abonos_pendientes`;
CREATE TABLE IF NOT EXISTS `backup_abonos_pendientes` (
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
-- Volcado de datos para la tabla `backup_abonos_pendientes`
--

INSERT INTO `backup_abonos_pendientes` (`id_abono`, `id_credito`, `monto`, `metodo_pago`, `observaciones`, `fecha_abono`) VALUES
(15, 9, 50.00, 'transferencia', '', '2025-11-07 15:56:53'),
(16, 12, 20.00, 'efectivo', '', '2025-11-07 15:57:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backup_credito_pendientes`
--

DROP TABLE IF EXISTS `backup_credito_pendientes`;
CREATE TABLE IF NOT EXISTS `backup_credito_pendientes` (
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
-- Volcado de datos para la tabla `backup_credito_pendientes`
--

INSERT INTO `backup_credito_pendientes` (`id_credito`, `id_cajero`, `id_cliente`, `id_producto`, `cantidad`, `total`, `fecha_cre`, `fecha_pago`, `estado`) VALUES
(9, 1, 1, 2, 1, 100.00, '2025-11-07 10:26:55', NULL, 'parcial'),
(10, 1, 1, 3, 2, 6.00, '2025-11-07 10:27:15', NULL, 'pendiente'),
(11, 1, 1, 1, 1, 1.00, '2025-11-07 10:27:15', NULL, 'pendiente'),
(12, 1, 2, 2, 1, 100.00, '2025-11-07 10:40:17', NULL, 'parcial'),
(13, 1, 1, 3, 1, 3.00, '2025-11-07 10:43:22', NULL, 'pendiente');

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cajero`
--

INSERT INTO `cajero` (`id_cajero`, `id_usuario`, `activo`, `fecha_ini`, `fecha_fin`) VALUES
(1, 8, 1, '2025-10-29', NULL),
(2, 9, 1, '2025-10-29', NULL),
(3, 11, 1, '2026-02-03', NULL),
(4, 12, 1, '2026-03-26', NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `alias`, `descripcion`, `id_usuario`) VALUES
(1, 'Ini', 'Cliente: angel manzano - Tel: 04241827066', 1),
(2, 'uni', 'Cliente: isis brito - Tel: 04241456666', 2),
(3, 'uni', 'Cliente: jeremy manzano - Tel: 04160480190', 3),
(4, 'uni', 'Cliente: nini manzano - Tel: 04241827066', 4),
(5, 'trabajo', 'Cliente: jona manzano - Tel: 04241456666', 5),
(6, 'quimica', 'Cliente: juan perez - Tel: 09089887685', 6),
(7, 'Angie', 'Cliente: Angie Olid - Tel: 04142426112', 10),
(8, 'Angie', 'Cliente: Karen Orochena - Tel: 04142426112', 13),
(9, 'Ing', 'Cliente: Carlos Ramírez - Tel: 04123456789', 14);

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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `descripcion`, `tipo`, `fecha_creacion`, `fecha_actualizacion`, `usuario_actualizacion`, `activo`) VALUES
(1, 'tasa_dolar', '700', 'Tasa de cambio del dólar estadounidense', 'decimal', '2025-10-29 09:42:03', '2026-03-26 13:46:46', 'admin', 1),
(2, 'moneda_principal', 'BS', 'Moneda principal del sistema', 'texto', '2025-10-29 09:42:03', '2026-04-23 13:29:49', 'admin', 1),
(3, 'nombre_empresa', 'Cafetín', 'Nombre de la empresa', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(4, 'direccion_empresa', '', 'Dirección de la empresa', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(5, 'telefono_empresa', '', 'Teléfono de la empresa', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(6, 'email_empresa', '', 'Email de la empresa', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(7, 'iva_porcentaje', '16.00', 'Porcentaje de IVA aplicable', 'decimal', '2025-10-29 09:42:03', '2026-04-02 15:46:59', 'admin', 1),
(8, 'formato_fecha', 'Y-m-d', 'Formato de fecha del sistema', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(9, 'zona_horaria', 'America/Caracas', 'Zona horaria del sistema', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(10, 'idioma_sistema', 'es', 'Idioma del sistema', 'texto', '2025-10-29 09:42:03', '2025-10-29 09:42:03', NULL, 1),
(11, 'descuento_maximo', '', '', 'texto', '2025-11-12 06:39:08', '2026-04-02 15:46:59', 'admin', 1),
(12, 'inventario_umbral_bajo', '40', '', 'texto', '2025-11-12 06:39:08', '2026-04-23 13:29:49', 'admin', 1),
(13, 'backup_automatico', 'false', '', 'texto', '2025-11-12 06:39:08', '2026-04-02 15:46:59', 'admin', 1),
(14, 'notificaciones_email', 'false', '', 'texto', '2025-11-12 06:39:08', '2026-04-02 15:46:59', 'admin', 1),
(15, 'grafico_grid_max', '100', '', 'texto', '2025-11-12 07:30:45', '2026-04-23 13:29:49', 'admin', 1),
(16, 'grafico_grid_step', '10', '', 'texto', '2025-11-12 07:30:45', '2026-04-23 13:29:49', 'admin', 1),
(17, 'dias_laborales', '1,2,3,4,5,6,7', '', 'texto', '2025-11-12 07:30:45', '2026-04-23 13:29:49', 'admin', 1),
(18, 'incluir_dias_sin_ventas', 'true', '', 'texto', '2025-11-12 07:30:45', '2026-04-23 13:29:49', 'admin', 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(9, 1, 1, 2, 1, 100.00, '2025-11-07 10:26:55', NULL, 'pagado'),
(10, 1, 1, 3, 2, 6.00, '2025-11-07 10:27:15', NULL, 'pagado'),
(11, 1, 1, 1, 1, 1.00, '2025-11-07 10:27:15', NULL, 'pagado'),
(12, 1, 2, 2, 1, 100.00, '2025-11-07 10:40:17', NULL, 'pagado'),
(13, 1, 1, 3, 1, 3.00, '2025-11-07 10:43:22', NULL, 'pagado'),
(14, 1, 3, 5, 2, 4.00, '2025-11-12 01:56:25', NULL, 'parcial'),
(15, 1, 7, 6, 3, 6.00, '2025-11-12 13:48:47', NULL, 'pagado'),
(16, 1, 7, 4, 1, 2.00, '2025-11-12 13:48:47', NULL, 'pagado'),
(17, 1, 1, 9, 2, 4.00, '2026-02-01 05:44:14', NULL, 'pagado'),
(18, 1, 1, 9, 1, 2.00, '2026-02-03 17:07:00', NULL, 'pagado'),
(19, 1, 1, 7, 1, 1.00, '2026-02-03 17:07:00', NULL, 'pagado'),
(20, 1, 1, 4, 1, 2.00, '2026-02-03 17:07:00', NULL, 'pagado'),
(21, 1, 1, 9, 3, 6.00, '2026-03-26 09:52:31', NULL, 'parcial'),
(22, 1, 1, 7, 1, 1.00, '2026-03-26 09:52:31', NULL, 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deuda`
--

DROP TABLE IF EXISTS `deuda`;
CREATE TABLE IF NOT EXISTS `deuda` (
  `id_deuda` int NOT NULL AUTO_INCREMENT,
  `id_cajero` int DEFAULT NULL,
  `id_cliente` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `cantidad` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_cre` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_pago` datetime DEFAULT NULL,
  `estado` enum('pendiente','pagado','parcial') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  PRIMARY KEY (`id_deuda`),
  KEY `id_cajero` (`id_cajero`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_producto` (`id_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deuda`
--

INSERT INTO `deuda` (`id_deuda`, `id_cajero`, `id_cliente`, `id_producto`, `cantidad`, `total`, `fecha_cre`, `fecha_pago`, `estado`) VALUES
(1, 1, 1, 3, 1, 3.00, '2025-10-29 03:20:34', NULL, 'pagado'),
(2, 1, 1, 3, 1, 3.00, '2025-10-29 04:08:36', NULL, 'pagado'),
(3, 1, 1, 2, 1, 100.00, '2025-10-29 04:08:36', NULL, 'pagado'),
(4, 1, 1, 3, 12, 36.00, '2025-10-29 13:00:39', NULL, 'pagado'),
(5, 1, 1, 3, 1, 3.00, '2025-11-07 07:07:47', NULL, 'pagado'),
(6, 1, 1, 3, 1, 3.00, '2025-11-07 07:08:08', NULL, 'pagado'),
(7, 1, 1, 3, 1, 3.00, '2025-11-07 10:24:34', NULL, 'pagado'),
(8, 1, 1, 1, 1, 1.00, '2025-11-07 10:24:34', NULL, 'pagado'),
(9, 1, 1, 2, 1, 100.00, '2025-11-07 10:26:55', NULL, 'pagado'),
(10, 1, 1, 3, 2, 6.00, '2025-11-07 10:27:15', NULL, 'pagado'),
(11, 1, 1, 1, 1, 1.00, '2025-11-07 10:27:15', NULL, 'pagado'),
(12, 1, 2, 2, 1, 100.00, '2025-11-07 10:40:17', NULL, 'pagado'),
(13, 1, 1, 3, 1, 3.00, '2025-11-07 10:43:22', NULL, 'pagado'),
(14, 1, 3, 5, 2, 4.00, '2025-11-12 01:56:25', NULL, 'parcial'),
(15, 1, 7, 6, 3, 6.00, '2025-11-12 13:48:47', NULL, 'pendiente'),
(16, 1, 7, 4, 1, 2.00, '2025-11-12 13:48:47', NULL, 'pendiente');

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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `historial_producto`
--

INSERT INTO `historial_producto` (`id_historial`, `id_producto`, `fecha_registro`, `cajas_agregar`, `unidades_por_caja`, `unidades_sueltas_agregar`, `unidades_agregadas_total`, `precio_venta_usd`, `precio_venta_bs`, `tasa_dolar`, `observacion`) VALUES
(6, 4, '2025-11-10 14:25:09', 2, 12, 0, 24, 2.00, 600.00, 300.00, ''),
(7, 6, '2025-11-12 13:30:43', 1, 12, 12, 24, 2.00, 600.00, 300.00, ''),
(8, 7, '2025-12-12 12:08:39', 2, 24, 1, 49, 1.00, 500.00, 500.00, ''),
(9, 10, '2026-02-03 17:17:33', 1, 8, 1, 9, 2.00, 1000.00, 500.00, ''),
(10, 11, '2026-03-26 09:36:06', 1, 12, 2, 14, 1.00, 600.00, 600.00, ''),
(11, 4, '2026-04-01 08:15:00', 3, 12, 2, 38, 2.00, 1400.00, 700.00, 'Compra proveedor semanal'),
(12, 4, '2026-04-05 09:20:00', 2, 12, 0, 24, 2.00, 1400.00, 700.00, 'Reposición rápida'),
(13, 4, '2026-04-10 07:50:00', 4, 12, 3, 51, 2.00, 1400.00, 700.00, 'Alta demanda'),
(14, 6, '2026-04-02 10:10:00', 2, 12, 5, 29, 2.00, 1400.00, 700.00, 'Compra mensual'),
(15, 6, '2026-04-08 11:00:00', 3, 12, 0, 36, 2.00, 1400.00, 700.00, 'Proveedor alterno'),
(16, 6, '2026-04-15 09:30:00', 1, 12, 10, 22, 2.00, 1400.00, 700.00, 'Compra pequeña'),
(17, 7, '2026-04-03 08:00:00', 5, 24, 0, 120, 1.00, 700.00, 700.00, 'Compra grande'),
(18, 7, '2026-04-09 08:45:00', 2, 24, 5, 53, 1.00, 700.00, 700.00, 'Reposición'),
(19, 7, '2026-04-18 07:30:00', 3, 24, 2, 74, 1.00, 700.00, 700.00, 'Stock medio'),
(20, 9, '2026-04-04 12:00:00', 6, 12, 0, 72, 2.00, 1400.00, 700.00, 'Compra mayorista'),
(21, 9, '2026-04-12 13:15:00', 3, 12, 4, 40, 2.00, 1400.00, 700.00, 'Reposición parcial'),
(22, 9, '2026-04-20 11:40:00', 4, 12, 1, 49, 2.00, 1400.00, 700.00, 'Alta rotación'),
(23, 8, '2026-04-06 09:10:00', 2, 6, 3, 15, 5.00, 3500.00, 700.00, 'Compra snack'),
(24, 8, '2026-04-14 10:25:00', 1, 6, 6, 12, 5.00, 3500.00, 700.00, 'Reposición'),
(25, 8, '2026-04-21 08:55:00', 3, 6, 0, 18, 5.00, 3500.00, 700.00, 'Compra semanal'),
(26, 10, '2026-04-07 07:40:00', 2, 8, 4, 20, 2.00, 1400.00, 700.00, 'Compra bebidas'),
(27, 10, '2026-04-16 08:20:00', 3, 8, 0, 24, 2.00, 1400.00, 700.00, 'Reposición'),
(28, 10, '2026-04-22 09:00:00', 1, 8, 6, 14, 2.00, 1400.00, 700.00, 'Compra ligera'),
(29, 11, '2026-04-08 11:30:00', 2, 12, 3, 27, 1.00, 700.00, 700.00, 'Compra snack'),
(30, 11, '2026-04-17 12:10:00', 3, 12, 0, 36, 1.00, 700.00, 700.00, 'Proveedor principal'),
(31, 11, '2026-04-22 13:00:00', 1, 12, 5, 17, 1.00, 700.00, 700.00, 'Reposición rápida');

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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_tasa`
--

INSERT INTO `historial_tasa` (`id`, `tasa_anterior`, `tasa_nueva`, `usuario`, `fecha_cambio`, `motivo`) VALUES
(1, 36.00, 219.87, 'admin', '2025-10-29 10:29:49', ''),
(2, 219.87, 300.00, 'admin', '2025-10-29 17:12:30', ''),
(3, 300.00, 500.00, 'admin', '2025-11-12 17:41:57', ''),
(4, 500.00, 600.00, 'admin', '2026-02-03 21:27:38', ''),
(5, 600.00, 700.00, 'admin', '2026-03-26 13:46:46', '');

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
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_producto`, `nombre_produc`, `caja_produc`, `cantidad_caja`, `cantidad_total`, `precio_caja`, `precio_produc`, `activo`, `precio_venta`, `unidades_sueltas`) VALUES
(6, 'Pepito', 4, 12, 53, 20.00, 1.67, 1, 2.00, 0),
(5, 'Oreo', 0, 12, 0, 20.00, 1.67, 0, 2.00, 0),
(7, 'Jukipark', 3, 24, 93, 20.00, 0.83, 1, 1.00, 0),
(4, 'pepsi', 1, 12, 17, 20.00, 1.67, 1, 2.00, 0),
(8, 'Ore grande', 1, 6, 11, 24.00, 4.00, 1, 5.00, 0),
(9, 'Arroz', 11, 12, 136, 22.00, 1.83, 1, 2.00, 0),
(10, 'Jugo', 3, 8, 25, 15.04, 1.88, 0, 2.00, 0),
(11, 'Detodito', 3, 12, 38, 10.00, 0.83, 0, 1.00, 0),
(12, 'Dorito', 11, 10, 119, 10.00, 1.00, 1, 2.00, 0);

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
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_persona`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id_persona`, `cedula`, `nombre`, `apellido`, `telefono`, `email`) VALUES
(1, 30729911, 'Angel', 'Manzano', '04241827066', NULL),
(2, 30885890, 'Isis', 'Brito', '04241456666', NULL),
(3, 29698636, 'Jeremy', 'Manzano', '04160480190', NULL),
(4, 16392828, 'Nini', 'Manzano', '04241827066', NULL),
(5, 12251860, 'Jona', 'Manzano', '04241456666', NULL),
(6, 12345678, 'Juan', 'Perez', '09089887685', NULL),
(7, 0, 'Administrador', 'Sistema', '0000000000', NULL),
(8, 0, 'Luis', 'Peres', '04160480190', NULL),
(9, 0, 'Nini', 'Manzano', '', 'angelmanzano01092003@gmail.com'),
(10, 17257322, 'Angie', 'Olid', '04142426112', NULL),
(11, 0, 'Angel', 'Manzano', '04241827066', NULL),
(12, 0, 'Isis', 'Perez', '04129961573', NULL),
(13, 15891748, 'Karen', 'Orochena', '04142426112', NULL),
(14, 12345679, 'Carlos', 'Ramírez', '04123456789', NULL);

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
(1, 'cajero1', 'BS', 1, 0, 0, '2025-10-29 10:45:12', '2025-11-12 06:47:57'),
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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(7, 7, 'admin', '$2y$10$dK/BRFNLgAMYRzAiL5CzYeAC..btX4TUHKo7MgbGhavPCn2v7VYXG', 3),
(8, 8, 'cajero1', '$2y$10$TGfffoBtmmr2dyh25XNmpOaB6/.Aq0Kk6oe9Xbgormj8iGeAR3/OO', 2),
(9, 9, 'cajero2', '$2y$10$Np8MPWd63Qqb/TSsoVHkK.B61vpaeqeWPwtzeA6UL0zj4YtqzjcZi', 2),
(10, 10, 'angieolid', '$2y$10$2wweldre1iacDJWJeWwtTuAEwVkM/AGJxHkWKOKoanHPmJKe4gRY6', 1),
(11, 11, 'Angel2003', '$2y$10$dKaCCKL6vUOqMCr6GILAXu046ghu6evrfzCNtEP78lDDQnJ09uZSe', 2),
(12, 12, 'cajero3', '$2y$10$VOkoWBBPeb6AJAzoPp...uXB1JA8N7uBIIFB/bK8vGftkVXt2pC22', 2),
(13, 13, 'karenorochena', '$2y$10$gOpb9xcbZkjmnWEErd9ASuVYVLCP8/avTYgJzHIDRhGdjYt625sLa', 1),
(14, 14, 'carlosramírez', '$2y$10$bg2nQo0CxVQIs.PPvU3bzukad9weeiZo9Q2G7klYbc40hrgAxWPyG', 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `id_cliente`, `id_cajero`, `id_producto`, `cantidad`, `total`, `fecha_venta`) VALUES
(36, 1, 1, 7, 6, 6.00, '2026-04-17 17:00:47'),
(35, 3, 1, 6, 3, 6.00, '2026-04-16 17:00:47'),
(34, 2, 1, 4, 2, 4.00, '2026-04-16 17:00:47'),
(33, 1, 1, 9, 4, 8.00, '2026-04-16 17:00:47'),
(32, 3, 1, 7, 5, 5.00, '2026-04-15 17:00:47'),
(31, 2, 1, 6, 2, 4.00, '2026-04-15 17:00:47'),
(30, 1, 1, 4, 3, 6.00, '2026-04-15 17:00:47'),
(29, 1, 1, 6, 2, 4.00, '2025-12-12 12:43:23'),
(28, 1, 1, 8, 1, 5.00, '2025-12-12 12:43:23'),
(27, 7, 1, 6, 2, 4.00, '2025-11-16 18:47:10'),
(26, 7, 1, 7, 2, 2.00, '2025-11-16 18:47:10'),
(25, 1, 1, 5, 1, 2.00, '2025-11-12 23:14:33'),
(24, 7, 1, 5, 17, 34.00, '2025-11-12 13:46:40'),
(23, 2, 1, 4, 27, 54.00, '2025-11-12 02:40:47'),
(22, 2, 1, 5, 1, 2.00, '2025-11-12 01:56:44'),
(21, 3, 1, 4, 1, 2.00, '2025-11-11 08:21:39'),
(20, 3, 1, 5, 3, 6.00, '2025-11-11 08:21:39'),
(19, 1, 1, 4, 1, 2.00, '2025-11-11 06:50:13'),
(37, 2, 1, 8, 2, 10.00, '2026-04-17 17:00:47'),
(38, 3, 1, 9, 3, 6.00, '2026-04-17 17:00:47'),
(39, 1, 1, 4, 5, 10.00, '2026-04-18 17:00:47'),
(40, 2, 1, 6, 1, 2.00, '2026-04-18 17:00:47'),
(41, 3, 1, 7, 4, 4.00, '2026-04-18 17:00:47'),
(42, 1, 1, 8, 2, 10.00, '2026-04-19 17:00:47'),
(43, 2, 1, 9, 5, 10.00, '2026-04-19 17:00:47'),
(44, 3, 1, 4, 3, 6.00, '2026-04-19 17:00:47'),
(45, 1, 1, 6, 4, 8.00, '2026-04-20 17:00:47'),
(46, 2, 1, 7, 3, 3.00, '2026-04-20 17:00:47'),
(47, 3, 1, 9, 2, 4.00, '2026-04-20 17:00:47'),
(48, 1, 1, 4, 6, 12.00, '2026-04-21 17:00:47'),
(49, 2, 1, 8, 1, 5.00, '2026-04-21 17:00:47'),
(50, 3, 1, 7, 2, 2.00, '2026-04-21 17:00:47'),
(51, 1, 1, 9, 5, 10.00, '2026-04-22 17:00:47'),
(52, 2, 1, 6, 3, 6.00, '2026-04-22 17:00:47'),
(53, 3, 1, 4, 4, 8.00, '2026-04-22 17:00:47'),
(54, 1, 1, 4, 3, 6.00, '2026-04-16 10:00:00'),
(55, 2, 1, 6, 2, 4.00, '2026-04-16 12:00:00'),
(56, 1, 1, 7, 6, 6.00, '2026-04-17 09:00:00'),
(57, 2, 1, 8, 2, 10.00, '2026-04-17 14:00:00'),
(58, 1, 1, 4, 5, 10.00, '2026-04-18 11:00:00'),
(59, 2, 1, 9, 3, 6.00, '2026-04-18 13:00:00'),
(60, 1, 1, 6, 4, 8.00, '2026-04-19 10:00:00'),
(61, 2, 1, 7, 3, 3.00, '2026-04-19 15:00:00'),
(62, 1, 1, 4, 6, 12.00, '2026-04-20 09:00:00'),
(63, 2, 1, 8, 1, 5.00, '2026-04-20 16:00:00'),
(64, 1, 1, 9, 5, 10.00, '2026-04-22 10:00:00'),
(65, 2, 1, 6, 3, 6.00, '2026-04-22 12:00:00'),
(66, 1, 1, 9, 2, 4.00, '2026-04-23 09:22:00'),
(67, 1, 1, 12, 1, 2.00, '2026-04-23 09:22:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
