-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-09-2025 a las 22:06:49
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
-- Base de datos: `neuroeduca_sistema_crm`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `idactividad` int(11) NOT NULL,
  `nombre` varchar(250) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `tipo` varchar(25) NOT NULL,
  `prioridad` varchar(25) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `direccion` varchar(250) DEFAULT NULL,
  `direccion_referencia` varchar(250) DEFAULT NULL,
  `enlace` varchar(500) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `idestado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`idactividad`, `nombre`, `idusuario`, `fecha`, `hora_inicio`, `hora_fin`, `tipo`, `prioridad`, `descripcion`, `direccion`, `direccion_referencia`, `enlace`, `fecha_creacion`, `idestado`) VALUES
(16, 'Llamada a Miriam', 1, '2025-09-25', '11:28:00', '11:58:00', 'llamada', 'alta', NULL, NULL, NULL, NULL, '2025-09-25 14:29:09', 1),
(18, 'Llamada', 1, '2025-10-02', '11:30:00', '12:00:00', 'reunion', 'alta', NULL, NULL, NULL, NULL, '2025-09-29 15:30:22', 1),
(19, 'Reunión con Miriam', 1, '2025-10-02', '12:30:00', '13:00:00', 'reunion', 'alta', 'Llevar cuaderno', 'calle 123', 'ednfkjdfnsk', NULL, '2025-09-30 11:39:17', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades_clientes`
--

CREATE TABLE `actividades_clientes` (
  `id` int(11) NOT NULL,
  `idactividad` int(11) NOT NULL,
  `idreferencia` int(11) NOT NULL,
  `tipo_cliente` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades_clientes`
--

INSERT INTO `actividades_clientes` (`id`, `idactividad`, `idreferencia`, `tipo_cliente`) VALUES
(48, 16, 2, 'empresa'),
(49, 16, 3, 'cliente'),
(53, 18, 1, 'cliente'),
(54, 19, 3, 'cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `act_archivos`
--

CREATE TABLE `act_archivos` (
  `idarchivo` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idreferencia` int(11) DEFAULT NULL,
  `tipo_cliente` enum('cliente','empresa') DEFAULT 'cliente',
  `nombre` varchar(255) NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `peso` int(11) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `act_correos`
--

CREATE TABLE `act_correos` (
  `idcorreo` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idreferencia` int(11) DEFAULT NULL,
  `tipo_cliente` enum('cliente','empresa') DEFAULT 'cliente',
  `asunto` varchar(255) NOT NULL,
  `idplantilla` int(11) NOT NULL,
  `estado` enum('borrador','enviado','programado','fallido') DEFAULT 'borrador',
  `fecha_envio` datetime DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `act_whatsapp`
--

CREATE TABLE `act_whatsapp` (
  `idwhatsapp` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idreferencia` int(11) DEFAULT NULL,
  `tipo_cliente` enum('cliente','empresa') DEFAULT 'cliente',
  `mensaje` text NOT NULL,
  `idplantilla` int(11) DEFAULT NULL,
  `estado` enum('borrador','enviado','programado','fallido') DEFAULT 'borrador',
  `fecha_envio` datetime DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `idarea` int(11) NOT NULL,
  `area` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `areas`
--

INSERT INTO `areas` (`idarea`, `area`) VALUES
(1, 'SISTEMAS'),
(2, 'DISEÑO'),
(3, 'AUDIOVISUALES'),
(4, 'ADMINISTRACION');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campos_extra`
--

CREATE TABLE `campos_extra` (
  `idcampo` int(11) NOT NULL,
  `idreferencia` int(11) DEFAULT NULL,
  `tabla` varchar(50) NOT NULL,
  `campo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `valor_inicial` text DEFAULT NULL,
  `tipo_dato` enum('texto','numero','booleano','fecha','opciones') DEFAULT 'texto',
  `longitud` int(11) DEFAULT NULL,
  `requerido` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `campos_extra`
--

INSERT INTO `campos_extra` (`idcampo`, `idreferencia`, `tabla`, `campo`, `nombre`, `valor_inicial`, `tipo_dato`, `longitud`, `requerido`, `fecha_creacion`) VALUES
(5, 3, 'clientes', 'estado_civil', 'Estado civil', '[\"Soltero\",\"Casado\",\"Viudo\",\"Divorciado\"]', 'opciones', 25, 1, '2025-09-26 19:52:55'),
(6, NULL, 'clientes', 'tiene_seguro', 'Tiene seguro', '1', 'booleano', NULL, 1, '2025-09-29 19:24:21'),
(18, NULL, 'clientes', 'sexo', 'Sexo', '[\"Femenino\",\"Masculino\"]', 'opciones', NULL, 1, '2025-09-30 17:31:28'),
(19, NULL, 'clientes', 'pensión', 'Pensión', '[\"ONP\",\"AFP\"]', 'opciones', NULL, 1, '2025-09-30 17:31:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `idcliente` int(11) NOT NULL,
  `nombres` varchar(250) NOT NULL,
  `apellidos` varchar(250) NOT NULL,
  `num_doc` varchar(11) DEFAULT NULL,
  `telefono` int(9) DEFAULT NULL,
  `correo` varchar(250) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `idusuario` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `foto` varchar(1000) DEFAULT NULL,
  `estado_civil` varchar(25) DEFAULT NULL,
  `tiene_seguro` tinyint(1) DEFAULT NULL,
  `sexo` varchar(255) DEFAULT NULL,
  `pensión` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`idcliente`, `nombres`, `apellidos`, `num_doc`, `telefono`, `correo`, `fecha_creacion`, `idusuario`, `idestado`, `foto`, `estado_civil`, `tiene_seguro`, `sexo`, `pensión`) VALUES
(1, 'Juan', 'Pérez', '76565657', 996756456, 'juanperez@gmail.com', '2025-09-25 09:49:00', 9, 2, 'assets/img/usuariohombre1.png', NULL, NULL, NULL, NULL),
(3, 'Miriam', 'Tudelano', '88755785', 942565804, 'miriamtudelano@gmail.com', '2025-09-25 09:49:00', 9, 1, 'uploads/clientes/cliente_68cc6489d0a45.png', 'Casado', 1, 'Masculino', NULL),
(6, 'Pancho', 'Rodríguez', NULL, NULL, NULL, '2025-09-25 09:49:00', 9, NULL, 'assets/img/usuariodefault.png', 'Casado', NULL, NULL, NULL),
(7, 'Marco Alejandro', 'Soto Ruiz', '52252528', 995222661, 'sdgg@gmail.com', '2025-09-27 09:06:59', 1, 1, 'assets/img/usuariodefault.png', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_proyectos`
--

CREATE TABLE `clientes_proyectos` (
  `id` int(11) NOT NULL,
  `idreferencia` int(11) NOT NULL,
  `tipo_cliente` varchar(25) NOT NULL,
  `idproyecto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes_proyectos`
--

INSERT INTO `clientes_proyectos` (`id`, `idreferencia`, `tipo_cliente`, `idproyecto`) VALUES
(1, 1, 'cliente', 1),
(2, 3, 'cliente', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colaboradores`
--

CREATE TABLE `colaboradores` (
  `idcolaborador` int(11) NOT NULL,
  `nombres` varchar(250) NOT NULL,
  `apellidos` varchar(250) NOT NULL,
  `dni` varchar(8) NOT NULL,
  `telefono` varchar(9) NOT NULL,
  `correo` varchar(250) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `idestado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `diccionario_campos`
--

CREATE TABLE `diccionario_campos` (
  `iddiccionario` int(11) NOT NULL,
  `tabla` varchar(50) NOT NULL,
  `campo` varchar(100) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `tipo_dato` enum('texto','numero','booleano','fecha','opciones') DEFAULT 'texto',
  `longitud` int(11) DEFAULT NULL,
  `requerido` tinyint(1) DEFAULT 0,
  `valor_inicial` text DEFAULT NULL,
  `contexto` varchar(50) DEFAULT 'general',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `diccionario_campos`
--

INSERT INTO `diccionario_campos` (`iddiccionario`, `tabla`, `campo`, `nombre`, `descripcion`, `tipo_dato`, `longitud`, `requerido`, `valor_inicial`, `contexto`, `fecha_creacion`) VALUES
(1, 'clientes', 'idcliente', 'idcliente', 'idcliente', 'numero', NULL, 1, NULL, 'clientes', '2025-09-27 14:50:59'),
(2, 'clientes', 'nombres', 'nombres', 'nombres', 'texto', 250, 1, NULL, 'clientes', '2025-09-27 14:50:59'),
(3, 'clientes', 'apellidos', 'apellidos', 'apellidos', 'texto', 250, 1, NULL, 'clientes', '2025-09-27 14:50:59'),
(4, 'clientes', 'num_doc', 'num_doc', 'num doc', 'texto', 11, 0, NULL, 'clientes', '2025-09-27 14:50:59'),
(5, 'clientes', 'telefono', 'telefono', 'telefono', 'numero', NULL, 0, NULL, 'clientes', '2025-09-27 14:50:59'),
(6, 'clientes', 'correo', 'correo', 'correo', 'texto', 250, 0, NULL, 'clientes', '2025-09-27 14:50:59'),
(7, 'clientes', 'fecha_creacion', 'fecha_creacion', 'fecha creacion', 'fecha', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(8, 'clientes', 'idusuario', 'idusuario', 'idusuario', 'numero', NULL, 0, NULL, 'general', '2025-09-27 14:50:59'),
(9, 'clientes', 'idestado', 'idestado', 'idestado', 'numero', NULL, 0, NULL, 'clientes', '2025-09-27 14:50:59'),
(10, 'clientes', 'foto', 'foto', 'foto', 'texto', 1000, 0, NULL, 'clientes', '2025-09-27 14:50:59'),
(11, 'clientes', 'extra', 'extra', 'extra', 'texto', 2147483647, 0, NULL, 'clientes', '2025-09-27 14:50:59'),
(16, 'empresas', 'idempresa', 'idempresa', 'idempresa', 'numero', NULL, 1, NULL, 'empresas', '2025-09-27 14:50:59'),
(17, 'empresas', 'razon_social', 'razon_social', 'razon social', 'texto', 250, 1, NULL, 'empresas', '2025-09-27 14:50:59'),
(18, 'empresas', 'ruc', 'ruc', 'ruc', 'texto', 11, 0, NULL, 'empresas', '2025-09-27 14:50:59'),
(19, 'empresas', 'direccion', 'direccion', 'direccion', 'texto', 250, 0, NULL, 'empresas', '2025-09-27 14:50:59'),
(20, 'empresas', 'direccion_referencia', 'direccion_referencia', 'direccion referencia', 'texto', 250, 0, NULL, 'empresas', '2025-09-27 14:50:59'),
(21, 'empresas', 'foto', 'foto', 'foto', 'texto', 1000, 0, NULL, 'empresas', '2025-09-27 14:50:59'),
(22, 'empresas', 'fecha_creacion', 'fecha_creacion', 'fecha creacion', 'fecha', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(23, 'empresas', 'idusuario', 'idusuario', 'idusuario', 'numero', NULL, 0, NULL, 'general', '2025-09-27 14:50:59'),
(24, 'empresas', 'extra', 'extra', 'extra', 'texto', 2147483647, 0, NULL, 'empresas', '2025-09-27 14:50:59'),
(31, 'actividades', 'idactividad', 'idactividad', 'idactividad', 'numero', NULL, 1, NULL, 'actividades', '2025-09-27 14:50:59'),
(32, 'actividades', 'nombre', 'nombre', 'nombre', 'texto', 250, 1, NULL, 'actividades', '2025-09-27 14:50:59'),
(33, 'actividades', 'idusuario', 'idusuario', 'idusuario', 'numero', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(34, 'actividades', 'fecha', 'fecha', 'fecha', 'fecha', NULL, 1, NULL, 'actividades', '2025-09-27 14:50:59'),
(35, 'actividades', 'hora_inicio', 'hora_inicio', 'hora inicio', 'fecha', NULL, 1, NULL, 'actividades', '2025-09-27 14:50:59'),
(36, 'actividades', 'hora_fin', 'hora_fin', 'hora fin', 'fecha', NULL, 1, NULL, 'actividades', '2025-09-27 14:50:59'),
(37, 'actividades', 'tipo', 'tipo', 'tipo', 'texto', 25, 1, NULL, 'actividades', '2025-09-27 14:50:59'),
(38, 'actividades', 'prioridad', 'prioridad', 'prioridad', 'texto', 25, 1, NULL, 'actividades', '2025-09-27 14:50:59'),
(39, 'actividades', 'fecha_creacion', 'fecha_creacion', 'fecha creacion', 'fecha', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(40, 'actividades', 'idestado', 'idestado', 'idestado', 'numero', NULL, 1, NULL, 'actividades', '2025-09-27 14:50:59'),
(41, 'actividades', 'extra', 'extra', 'extra', 'texto', 2147483647, 0, NULL, 'actividades', '2025-09-27 14:50:59'),
(46, 'proyectos', 'idproyecto', 'idproyecto', 'idproyecto', 'numero', NULL, 1, NULL, 'proyectos', '2025-09-27 14:50:59'),
(47, 'proyectos', 'nombre', 'nombre', 'nombre', 'texto', 250, 1, NULL, 'proyectos', '2025-09-27 14:50:59'),
(48, 'proyectos', 'fecha_inicio', 'fecha_inicio', 'fecha inicio', 'fecha', NULL, 1, NULL, 'proyectos', '2025-09-27 14:50:59'),
(49, 'proyectos', 'descripcion', 'descripcion', 'descripcion', 'texto', 5000, 1, NULL, 'proyectos', '2025-09-27 14:50:59'),
(50, 'proyectos', 'presupuesto', 'presupuesto', 'presupuesto', 'texto', NULL, 1, NULL, 'proyectos', '2025-09-27 14:50:59'),
(51, 'proyectos', 'prioridad', 'prioridad', 'prioridad', 'texto', 25, 1, NULL, 'proyectos', '2025-09-27 14:50:59'),
(52, 'proyectos', 'fecha_creacion', 'fecha_creacion', 'fecha creacion', 'fecha', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(53, 'proyectos', 'idusuario', 'idusuario', 'idusuario', 'numero', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(54, 'proyectos', 'idestado', 'idestado', 'idestado', 'numero', NULL, 1, NULL, 'proyectos', '2025-09-27 14:50:59'),
(55, 'proyectos', 'idarea', 'idarea', 'idarea', 'numero', NULL, 1, NULL, 'proyectos', '2025-09-27 14:50:59'),
(61, 'notas', 'idnota', 'idnota', 'idnota', 'numero', NULL, 1, NULL, 'notas', '2025-09-27 14:50:59'),
(62, 'notas', 'idusuario', 'idusuario', 'idusuario', 'numero', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(63, 'notas', 'idreferencia', 'idreferencia', 'idreferencia', 'numero', NULL, 1, NULL, 'notas', '2025-09-27 14:50:59'),
(64, 'notas', 'tipo', 'tipo', 'tipo', 'texto', 50, 1, NULL, 'notas', '2025-09-27 14:50:59'),
(65, 'notas', 'contenido', 'contenido', 'contenido', 'texto', 65535, 1, NULL, 'notas', '2025-09-27 14:50:59'),
(66, 'notas', 'fecha_creacion', 'fecha_creacion', 'fecha creacion', 'fecha', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(68, 'correos', 'idcorreo', 'idcorreo', 'idcorreo', 'numero', NULL, 1, NULL, 'correos', '2025-09-27 14:50:59'),
(69, 'correos', 'idusuario', 'idusuario', 'idusuario', 'numero', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(70, 'correos', 'enviado', 'enviado', 'enviado', 'numero', NULL, 1, NULL, 'correos', '2025-09-27 14:50:59'),
(71, 'act_whatsapp', 'idwhatsapp', 'idwhatsapp', 'idwhatsapp', 'numero', NULL, 1, NULL, 'act_whatsapp', '2025-09-27 14:50:59'),
(72, 'act_whatsapp', 'idusuario', 'idusuario', 'idusuario', 'numero', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(73, 'act_whatsapp', 'idreferencia', 'idreferencia', 'idreferencia', 'numero', NULL, 0, NULL, 'act_whatsapp', '2025-09-27 14:50:59'),
(74, 'act_whatsapp', 'tipo_cliente', 'tipo_cliente', 'tipo cliente', 'texto', 7, 0, NULL, 'act_whatsapp', '2025-09-27 14:50:59'),
(75, 'act_whatsapp', 'mensaje', 'mensaje', 'mensaje', 'texto', 65535, 1, NULL, 'act_whatsapp', '2025-09-27 14:50:59'),
(76, 'act_whatsapp', 'idplantilla', 'idplantilla', 'idplantilla', 'numero', NULL, 0, NULL, 'act_whatsapp', '2025-09-27 14:50:59'),
(77, 'act_whatsapp', 'estado', 'estado', 'estado', 'texto', 10, 0, NULL, 'act_whatsapp', '2025-09-27 14:50:59'),
(78, 'act_whatsapp', 'fecha_envio', 'fecha_envio', 'fecha envio', 'fecha', NULL, 0, NULL, 'act_whatsapp', '2025-09-27 14:50:59'),
(79, 'act_whatsapp', 'fecha_creacion', 'fecha_creacion', 'fecha creacion', 'fecha', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(86, 'act_archivos', 'idarchivo', 'idarchivo', 'idarchivo', 'numero', NULL, 1, NULL, 'act_archivos', '2025-09-27 14:50:59'),
(87, 'act_archivos', 'idusuario', 'idusuario', 'idusuario', 'numero', NULL, 1, NULL, 'general', '2025-09-27 14:50:59'),
(88, 'act_archivos', 'idreferencia', 'idreferencia', 'idreferencia', 'numero', NULL, 0, NULL, 'act_archivos', '2025-09-27 14:50:59'),
(89, 'act_archivos', 'tipo_cliente', 'tipo_cliente', 'tipo cliente', 'texto', 7, 0, NULL, 'act_archivos', '2025-09-27 14:50:59'),
(90, 'act_archivos', 'nombre', 'nombre', 'nombre', 'texto', 255, 1, NULL, 'act_archivos', '2025-09-27 14:50:59'),
(91, 'act_archivos', 'ruta', 'ruta', 'ruta', 'texto', 500, 1, NULL, 'act_archivos', '2025-09-27 14:50:59'),
(92, 'act_archivos', 'tipo', 'tipo', 'tipo', 'texto', 100, 0, NULL, 'act_archivos', '2025-09-27 14:50:59'),
(93, 'act_archivos', 'peso', 'peso', 'peso', 'numero', NULL, 0, NULL, 'act_archivos', '2025-09-27 14:50:59'),
(94, 'act_archivos', 'fecha_creacion', 'fecha_creacion', 'fecha creacion', 'fecha', NULL, 1, NULL, 'general', '2025-09-27 14:50:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidad_general`
--

CREATE TABLE `disponibilidad_general` (
  `iddisponibilidad` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') DEFAULT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `idempresa` int(11) NOT NULL,
  `razon_social` varchar(250) NOT NULL,
  `ruc` varchar(11) DEFAULT NULL,
  `direccion` varchar(250) DEFAULT NULL,
  `direccion_referencia` varchar(250) DEFAULT NULL,
  `foto` varchar(1000) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `idusuario` int(11) DEFAULT NULL,
  `extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`idempresa`, `razon_social`, `ruc`, `direccion`, `direccion_referencia`, `foto`, `fecha_creacion`, `idusuario`, `extra`) VALUES
(1, 'chifa', '20605465465', 'fgfgfgf', 'fgfgdgfg', 'assets/img/organizaciondefault.png', '2025-09-25 09:49:20', 1, NULL),
(2, 'tuercas SAC', '20604848383', 'dsdfgsdfg', 'sgfsfgdggdgg', 'assets/img/organizaciondefault.png', '2025-09-25 09:49:20', 4, NULL),
(7, 'Rifas SAC', '20604889498', 'djnskfjdsfkj', 'ednfkjdfnsk', 'assets/img/organizaciondefault.png', '2025-09-25 09:49:20', 9, NULL),
(9, 'Naranjas SAC', '20604849849', 'Calle 123, Lima, Peru', 'Av. Toronjas con Av. Manzanas', 'assets/img/organizaciondefault.png', '2025-09-25 09:49:20', 4, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas_clientes`
--

CREATE TABLE `empresas_clientes` (
  `id` int(11) NOT NULL,
  `idempresa` int(11) NOT NULL,
  `idcliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas_clientes`
--

INSERT INTO `empresas_clientes` (`id`, `idempresa`, `idcliente`) VALUES
(1, 7, 3),
(2, 1, 1),
(4, 2, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_actividades`
--

CREATE TABLE `estados_actividades` (
  `idestado` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_actividades`
--

INSERT INTO `estados_actividades` (`idestado`, `estado`) VALUES
(1, 'PENDIENTE'),
(2, 'REALIZADO'),
(3, 'VENCIDO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_clientes`
--

CREATE TABLE `estados_clientes` (
  `idestado` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_clientes`
--

INSERT INTO `estados_clientes` (`idestado`, `estado`) VALUES
(1, 'PROSPECTO'),
(2, 'CLIENTE'),
(3, 'INACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_proyectos`
--

CREATE TABLE `estados_proyectos` (
  `idestado` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_proyectos`
--

INSERT INTO `estados_proyectos` (`idestado`, `estado`) VALUES
(1, 'PLANIFICADO'),
(2, 'EN PROGRESO'),
(3, 'EN PAUSA'),
(4, 'CANCELADO'),
(5, 'TERMINADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_tareas`
--

CREATE TABLE `estados_tareas` (
  `idestado` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_usuarios`
--

CREATE TABLE `estados_usuarios` (
  `idestado` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_usuarios`
--

INSERT INTO `estados_usuarios` (`idestado`, `estado`) VALUES
(1, 'ACTIVO'),
(2, 'INACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `idnota` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idreferencia` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `contenido` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`idnota`, `idusuario`, `idreferencia`, `tipo`, `contenido`, `fecha_creacion`) VALUES
(4, 1, 16, 'actividad', 'Notita!', '2025-09-27 13:58:33'),
(5, 1, 18, 'actividad', 'Llevar cuaderno', '2025-09-29 15:30:51'),
(6, 1, 19, 'actividad', 'Notita', '2025-09-30 12:23:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `idpermiso` int(11) NOT NULL,
  `permiso` varchar(100) NOT NULL,
  `codigo` varchar(100) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `categoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`idpermiso`, `permiso`, `codigo`, `descripcion`, `categoria`) VALUES
(1, 'Añadir proyectos', 'proyectos_add', 'Permite añadir un nuevo proyecto y convierte los prospectos en clientes', 'Proyectos'),
(2, 'Añadir clientes', 'clientes_add', 'Permite añadir prospectos y empresas', 'Clientes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_roles`
--

CREATE TABLE `permisos_roles` (
  `id` int(11) NOT NULL,
  `idpermiso` int(11) NOT NULL,
  `idrol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos_roles`
--

INSERT INTO `permisos_roles` (`id`, `idpermiso`, `idrol`) VALUES
(1, 2, 1),
(2, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas_correo`
--

CREATE TABLE `plantillas_correo` (
  `idplantilla` int(11) NOT NULL,
  `contenido_html` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas_whatsapp`
--

CREATE TABLE `plantillas_whatsapp` (
  `idplantilla` int(11) NOT NULL,
  `mensaje` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE `proyectos` (
  `idproyecto` int(11) NOT NULL,
  `nombre` varchar(250) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `descripcion` varchar(5000) NOT NULL,
  `presupuesto` decimal(10,2) NOT NULL,
  `prioridad` varchar(25) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `idusuario` int(11) NOT NULL,
  `idestado` int(11) NOT NULL,
  `idarea` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`idproyecto`, `nombre`, `fecha_inicio`, `descripcion`, `presupuesto`, `prioridad`, `fecha_creacion`, `idusuario`, `idestado`, `idarea`) VALUES
(1, 'Sistema Web', '2025-09-11', 'Sistema Web CRM para proyectos de la empresa.', 5000.00, 'ALTA', '2025-09-25 09:49:51', 1, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos_colaboradores`
--

CREATE TABLE `proyectos_colaboradores` (
  `id` int(11) NOT NULL,
  `idproyecto` int(11) NOT NULL,
  `idcolaborador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_cambios`
--

CREATE TABLE `registro_cambios` (
  `idregistro` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idreferencia` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `accion` varchar(25) NOT NULL,
  `campo` varchar(50) DEFAULT NULL,
  `anterior` varchar(250) DEFAULT NULL,
  `nuevo` varchar(250) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro_cambios`
--

INSERT INTO `registro_cambios` (`idregistro`, `idusuario`, `idreferencia`, `tipo`, `accion`, `campo`, `anterior`, `nuevo`, `descripcion`, `fecha`) VALUES
(205, 1, 19, 'actividad', 'asignacion', 'clientes', NULL, '3', 'Asignación de clientes 3 a actividad actividad \"Reunión con Miriam\"', '2025-09-30 12:25:09'),
(206, 1, 3, 'cliente', 'actualizacion', 'estado_civil', 'Casado', 'Soltero', 'Actualización estado_civil de cliente Miriam Tudelano: Casado -> Soltero', '2025-09-30 12:25:47'),
(207, 1, 3, 'cliente', 'asignacion', 'usuario', NULL, '9', 'Asignación de usuario 9 a cliente cliente Miriam Tudelano', '2025-09-30 12:25:54'),
(208, 1, 3, 'cliente', 'eliminacion', 'usuario', '1', NULL, 'Eliminación de usuario 1 de cliente cliente Miriam Tudelano', '2025-09-30 12:25:54'),
(209, 1, 15, 'campo extra', 'eliminacion', NULL, NULL, NULL, 'Campo extra eliminado de tabla actividades: Activar recordatorio', '2025-09-30 12:26:08'),
(210, 1, 16, 'campo extra', 'creacion', 'activar_recordatorio', NULL, NULL, 'Campo extra creado en actividades: Activar recordatorio', '2025-09-30 12:27:48'),
(211, 1, 16, 'campo extra', 'eliminacion', NULL, NULL, NULL, 'Campo extra eliminado de tabla actividades: Activar recordatorio', '2025-09-30 12:27:54'),
(212, 1, 6, 'cliente', 'actualizacion', 'estado_civil', NULL, 'Casado', 'Actualización estado_civil de cliente Pancho Rodríguez: (vacío) -> Casado', '2025-09-30 12:29:27'),
(213, 1, 17, 'campo extra', 'creacion', 'contacto', NULL, NULL, 'Campo extra creado en empresas: Contacto', '2025-09-30 12:30:25'),
(214, 1, 17, 'campo extra', 'eliminacion', NULL, NULL, NULL, 'Campo extra eliminado de tabla empresas: Contacto', '2025-09-30 12:30:39'),
(215, 1, 14, 'campo extra', 'eliminacion', NULL, NULL, NULL, 'Campo extra eliminado de tabla clientes: Sexo', '2025-09-30 12:31:16'),
(216, 1, 18, 'campo extra', 'creacion', 'sexo', NULL, NULL, 'Campo extra creado en clientes: Sexo', '2025-09-30 12:31:28'),
(217, 1, 19, 'campo extra', 'creacion', 'pensión', NULL, NULL, 'Campo extra creado en clientes: Pensión', '2025-09-30 12:31:55'),
(218, 1, 3, 'cliente', 'actualizacion', 'estado_civil', 'Soltero', 'Casado', 'Actualización estado_civil de cliente Miriam Tudelano: Soltero -> Casado', '2025-09-30 12:32:34'),
(219, 1, 3, 'cliente', 'actualizacion', 'sexo', NULL, 'Masculino', 'Actualización sexo de cliente Miriam Tudelano: (vacío) -> Masculino', '2025-09-30 12:32:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `idrol` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`idrol`, `rol`, `descripcion`) VALUES
(1, 'ADMINISTRADOR', NULL),
(2, 'VENDEDOR', NULL),
(3, 'JEFE DE PROYECTOS', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `idtarea` int(11) NOT NULL,
  `idproyecto` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `asunto` varchar(250) NOT NULL,
  `descripcion` text NOT NULL,
  `prioridad` varchar(25) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `idestado` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas_colaboradores`
--

CREATE TABLE `tareas_colaboradores` (
  `id` int(11) NOT NULL,
  `idtarea` int(11) NOT NULL,
  `idcolaborador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuario` int(11) NOT NULL,
  `nombres` varchar(250) NOT NULL,
  `apellidos` varchar(250) NOT NULL,
  `num_doc` varchar(8) NOT NULL,
  `telefono` varchar(9) NOT NULL,
  `correo` varchar(250) NOT NULL,
  `foto` varchar(1000) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(250) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `idrol` int(11) NOT NULL,
  `idestado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idusuario`, `nombres`, `apellidos`, `num_doc`, `telefono`, `correo`, `foto`, `usuario`, `password`, `fecha_creacion`, `idrol`, `idestado`) VALUES
(1, 'Alyson', 'Quiroz', '74747478', '938388332', 'alysnsnsnsn@gmail.com', 'uploads/usuarios/cliente_68cc218394ae8.png', 'aquiroz', '$2y$10$Grki2OEyZ8K1Z6WO/krozuEPrmuSswOyZCROPLOmlUyoFtONpYxAy', '2025-09-25 09:47:45', 1, 1),
(4, 'Julian', 'Casablancas', '88755785', '999888999', 'minorbutmajor@gmail.com', 'uploads/usuarios/cliente_68cc3a81dde3d.png', 'jcasablancas', '$2y$10$CEv0Biej4kljHRdRWpBNROhLJQb2IpPX8xR14kU9jyCsMlJ2a4Ftq', '2025-09-25 09:47:45', 3, 1),
(9, 'Grecia Miranda', 'Sarmiento Vásquez', '87555522', '942565804', 'graci_msr@gmail.com', 'assets/img/usuariodefault.png', 'gsarmiento', '$2y$10$OMmCgNhYtjp257UywNWxU.dLgJruYBBK3Z.zI6G3B.a97KL0yJjLy', '2025-09-25 09:47:45', 2, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`idactividad`),
  ADD KEY `idestado` (`idestado`);

--
-- Indices de la tabla `actividades_clientes`
--
ALTER TABLE `actividades_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `act_archivos`
--
ALTER TABLE `act_archivos`
  ADD PRIMARY KEY (`idarchivo`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `act_correos`
--
ALTER TABLE `act_correos`
  ADD PRIMARY KEY (`idcorreo`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `act_whatsapp`
--
ALTER TABLE `act_whatsapp`
  ADD PRIMARY KEY (`idwhatsapp`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`idarea`);

--
-- Indices de la tabla `campos_extra`
--
ALTER TABLE `campos_extra`
  ADD PRIMARY KEY (`idcampo`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `idestado` (`idestado`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `clientes_proyectos`
--
ALTER TABLE `clientes_proyectos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idcliente` (`idreferencia`),
  ADD KEY `idproyecto` (`idproyecto`);

--
-- Indices de la tabla `colaboradores`
--
ALTER TABLE `colaboradores`
  ADD PRIMARY KEY (`idcolaborador`),
  ADD KEY `idestado` (`idestado`);

--
-- Indices de la tabla `diccionario_campos`
--
ALTER TABLE `diccionario_campos`
  ADD PRIMARY KEY (`iddiccionario`),
  ADD UNIQUE KEY `unique_tabla_campo` (`tabla`,`campo`);

--
-- Indices de la tabla `disponibilidad_general`
--
ALTER TABLE `disponibilidad_general`
  ADD PRIMARY KEY (`iddisponibilidad`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`idempresa`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `empresas_clientes`
--
ALTER TABLE `empresas_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idcliente` (`idcliente`),
  ADD KEY `idempresa` (`idempresa`);

--
-- Indices de la tabla `estados_actividades`
--
ALTER TABLE `estados_actividades`
  ADD PRIMARY KEY (`idestado`);

--
-- Indices de la tabla `estados_clientes`
--
ALTER TABLE `estados_clientes`
  ADD PRIMARY KEY (`idestado`);

--
-- Indices de la tabla `estados_proyectos`
--
ALTER TABLE `estados_proyectos`
  ADD PRIMARY KEY (`idestado`);

--
-- Indices de la tabla `estados_tareas`
--
ALTER TABLE `estados_tareas`
  ADD PRIMARY KEY (`idestado`);

--
-- Indices de la tabla `estados_usuarios`
--
ALTER TABLE `estados_usuarios`
  ADD PRIMARY KEY (`idestado`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`idnota`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`idpermiso`);

--
-- Indices de la tabla `permisos_roles`
--
ALTER TABLE `permisos_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idpermiso` (`idpermiso`),
  ADD KEY `idrol` (`idrol`);

--
-- Indices de la tabla `plantillas_correo`
--
ALTER TABLE `plantillas_correo`
  ADD PRIMARY KEY (`idplantilla`);

--
-- Indices de la tabla `plantillas_whatsapp`
--
ALTER TABLE `plantillas_whatsapp`
  ADD PRIMARY KEY (`idplantilla`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`idproyecto`),
  ADD KEY `proyectos_ibfk_1` (`idestado`),
  ADD KEY `idarea` (`idarea`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `proyectos_colaboradores`
--
ALTER TABLE `proyectos_colaboradores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idcolaborador` (`idcolaborador`),
  ADD KEY `idproyecto` (`idproyecto`);

--
-- Indices de la tabla `registro_cambios`
--
ALTER TABLE `registro_cambios`
  ADD PRIMARY KEY (`idregistro`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`idrol`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`idtarea`),
  ADD KEY `idproyecto` (`idproyecto`),
  ADD KEY `idestado` (`idestado`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `tareas_colaboradores`
--
ALTER TABLE `tareas_colaboradores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idcolaborador` (`idcolaborador`),
  ADD KEY `idtarea` (`idtarea`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idusuario`),
  ADD UNIQUE KEY `dni` (`num_doc`),
  ADD KEY `id_rol` (`idrol`),
  ADD KEY `idestado` (`idestado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `idactividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `actividades_clientes`
--
ALTER TABLE `actividades_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `act_archivos`
--
ALTER TABLE `act_archivos`
  MODIFY `idarchivo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `act_correos`
--
ALTER TABLE `act_correos`
  MODIFY `idcorreo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `act_whatsapp`
--
ALTER TABLE `act_whatsapp`
  MODIFY `idwhatsapp` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `idarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `campos_extra`
--
ALTER TABLE `campos_extra`
  MODIFY `idcampo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `clientes_proyectos`
--
ALTER TABLE `clientes_proyectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `colaboradores`
--
ALTER TABLE `colaboradores`
  MODIFY `idcolaborador` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `diccionario_campos`
--
ALTER TABLE `diccionario_campos`
  MODIFY `iddiccionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `disponibilidad_general`
--
ALTER TABLE `disponibilidad_general`
  MODIFY `iddisponibilidad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `idempresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `empresas_clientes`
--
ALTER TABLE `empresas_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estados_actividades`
--
ALTER TABLE `estados_actividades`
  MODIFY `idestado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estados_clientes`
--
ALTER TABLE `estados_clientes`
  MODIFY `idestado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estados_proyectos`
--
ALTER TABLE `estados_proyectos`
  MODIFY `idestado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estados_tareas`
--
ALTER TABLE `estados_tareas`
  MODIFY `idestado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados_usuarios`
--
ALTER TABLE `estados_usuarios`
  MODIFY `idestado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `idnota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `idpermiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `permisos_roles`
--
ALTER TABLE `permisos_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `plantillas_correo`
--
ALTER TABLE `plantillas_correo`
  MODIFY `idplantilla` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plantillas_whatsapp`
--
ALTER TABLE `plantillas_whatsapp`
  MODIFY `idplantilla` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `idproyecto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `proyectos_colaboradores`
--
ALTER TABLE `proyectos_colaboradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registro_cambios`
--
ALTER TABLE `registro_cambios`
  MODIFY `idregistro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `idtarea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas_colaboradores`
--
ALTER TABLE `tareas_colaboradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `actividades_ibfk_1` FOREIGN KEY (`idestado`) REFERENCES `estados_actividades` (`idestado`);

--
-- Filtros para la tabla `act_archivos`
--
ALTER TABLE `act_archivos`
  ADD CONSTRAINT `act_archivos_ibfk_1` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `act_correos`
--
ALTER TABLE `act_correos`
  ADD CONSTRAINT `act_correos_ibfk_1` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `act_whatsapp`
--
ALTER TABLE `act_whatsapp`
  ADD CONSTRAINT `act_whatsapp_ibfk_1` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`idestado`) REFERENCES `estados_clientes` (`idestado`),
  ADD CONSTRAINT `clientes_ibfk_2` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `clientes_proyectos`
--
ALTER TABLE `clientes_proyectos`
  ADD CONSTRAINT `clientes_proyectos_ibfk_1` FOREIGN KEY (`idreferencia`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `clientes_proyectos_ibfk_2` FOREIGN KEY (`idproyecto`) REFERENCES `proyectos` (`idproyecto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `colaboradores`
--
ALTER TABLE `colaboradores`
  ADD CONSTRAINT `colaboradores_ibfk_1` FOREIGN KEY (`idestado`) REFERENCES `estados_usuarios` (`idestado`);

--
-- Filtros para la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `empresas_clientes`
--
ALTER TABLE `empresas_clientes`
  ADD CONSTRAINT `empresas_clientes_ibfk_1` FOREIGN KEY (`idcliente`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `empresas_clientes_ibfk_2` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `permisos_roles`
--
ALTER TABLE `permisos_roles`
  ADD CONSTRAINT `permisos_roles_ibfk_1` FOREIGN KEY (`idpermiso`) REFERENCES `permisos` (`idpermiso`) ON DELETE CASCADE,
  ADD CONSTRAINT `permisos_roles_ibfk_2` FOREIGN KEY (`idrol`) REFERENCES `roles` (`idrol`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`idestado`) REFERENCES `estados_proyectos` (`idestado`),
  ADD CONSTRAINT `proyectos_ibfk_2` FOREIGN KEY (`idarea`) REFERENCES `areas` (`idarea`),
  ADD CONSTRAINT `proyectos_ibfk_3` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `proyectos_colaboradores`
--
ALTER TABLE `proyectos_colaboradores`
  ADD CONSTRAINT `proyectos_colaboradores_ibfk_1` FOREIGN KEY (`idcolaborador`) REFERENCES `colaboradores` (`idcolaborador`),
  ADD CONSTRAINT `proyectos_colaboradores_ibfk_2` FOREIGN KEY (`idproyecto`) REFERENCES `proyectos` (`idproyecto`);

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`idproyecto`) REFERENCES `proyectos` (`idproyecto`),
  ADD CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`idestado`) REFERENCES `estados_tareas` (`idestado`),
  ADD CONSTRAINT `tareas_ibfk_3` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `tareas_colaboradores`
--
ALTER TABLE `tareas_colaboradores`
  ADD CONSTRAINT `tareas_colaboradores_ibfk_1` FOREIGN KEY (`idcolaborador`) REFERENCES `colaboradores` (`idcolaborador`),
  ADD CONSTRAINT `tareas_colaboradores_ibfk_2` FOREIGN KEY (`idtarea`) REFERENCES `tareas` (`idtarea`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`idrol`) REFERENCES `roles` (`idrol`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`idestado`) REFERENCES `estados_usuarios` (`idestado`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
