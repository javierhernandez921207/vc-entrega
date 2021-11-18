-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-10-2021 a las 07:43:05
-- Versión del servidor: 10.4.13-MariaDB
-- Versión de PHP: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `store`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id`, `nombre`, `icono`) VALUES
(18, 'Combos', 'gift');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `ganacia_min_pedido` double NOT NULL,
  `pago_saldo` tinyint(1) NOT NULL,
  `pago_cash` tinyint(1) NOT NULL,
  `pago_paypal` tinyint(1) NOT NULL,
  `cambiocup` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `ganacia_min_pedido`, `pago_saldo`, `pago_cash`, `pago_paypal`, `cambiocup`) VALUES
(1, 0, 1, 1, 0, 47);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuadre`
--

CREATE TABLE `cuadre` (
  `id` int(11) NOT NULL,
  `negocio_id` int(11) NOT NULL,
  `trabajador_saliente_id` int(11) DEFAULT NULL,
  `trabajador_entrante_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `total` double NOT NULL,
  `ganacia` double NOT NULL,
  `fondo` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrip` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `log`
--

INSERT INTO `log` (`id`, `usuario_id`, `fecha`, `tipo`, `descrip`) VALUES
(40, 14, '2021-04-10 04:54:21', 'RECARGA', 'Saldo aumentado en $ -2871. Total $ 0'),
(41, 14, '2021-04-10 04:55:00', 'RECARGA', 'Saldo aumentado en $ 500. Total $ 500'),
(42, 14, '2021-04-10 04:55:40', 'PEDIDO', 'Pedido cancelado id: 4 , valor: $7'),
(43, 14, '2021-04-14 05:26:59', 'PEDIDO', 'Pedido cancelado id: 5 , valor: $12'),
(44, 14, '2021-04-14 05:29:24', 'PEDIDO', 'Pedido cancelado id: 6 , valor: $12'),
(45, 14, '2021-04-14 05:31:16', 'PEDIDO', 'Pedido cancelado id: 7 , valor: $4'),
(46, 14, '2021-04-14 05:32:58', 'PEDIDO', 'Pedido cancelado id: 8 , valor: $12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migration_versions`
--

CREATE TABLE `migration_versions` (
  `version` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocio`
--

CREATE TABLE `negocio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocio_user`
--

CREATE TABLE `negocio_user` (
  `negocio_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `total` double NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `metpago` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomb_per` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ci_per` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel_per` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dir_per` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transporte_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` double NOT NULL,
  `descr` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `registro` datetime NOT NULL,
  `img` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `costo` double NOT NULL,
  `cant_min` int(11) DEFAULT NULL,
  `negocio_id` int(11) DEFAULT NULL,
  `cantidad_cuadre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `nombre`, `precio`, `descr`, `cantidad`, `categoria_id`, `registro`, `img`, `costo`, `cant_min`, `negocio_id`, `cantidad_cuadre`) VALUES
(15, 'Arroz 10 lb', 14, 'Arro', 23, 18, '2021-04-07 07:59:34', 'eabe0051288ad1778b9cff03ead232f4.jpeg', 7, 3, NULL, NULL),
(16, 'Vitanova', 10, 'Vitanova', 5, 18, '2021-04-08 19:00:22', 'a20d7002a58bd2a5441f1179528c7b3e.jpeg', 5, 3, NULL, NULL),
(17, 'Pata de Cebolla', 4, 'Cebolla', 6, 18, '2021-04-07 08:00:02', '4f6094a88cda570054873872b1be893d.webp', 2, 3, NULL, NULL),
(18, 'Pata de Ajo', 6, 'Ajo', 8, 18, '2021-04-07 09:26:47', '595f2f90718c098c22677d30ff7ae28e.jpeg', 3, 3, NULL, NULL),
(19, 'Cafe', 30, 'Paquete de cafe', 2, 18, '2021-04-08 19:02:14', '0cb53516caac65d5fc1031a2dd7645b0.jpeg', 15, 3, NULL, NULL),
(20, 'Galleta de Chocolate', 2, 'Galleta de chocolate 6 unidades', 12, 18, '2021-04-09 21:07:30', '104a7e138effad677a43310801d88cb8.jpeg', 1, 3, NULL, NULL),
(21, 'Refresco Piñata', 4, 'Refresco', 11, 18, '2021-04-09 21:09:14', '2719afc89fecac1dcd84a6498951dd58.jpeg', 2, 3, NULL, NULL),
(22, 'Picadillo', 4, 'Picadillo', 0, 18, '2021-04-07 07:54:19', '3109d727207a8a7635aff315bcdb97a4.jpeg', 2, 3, NULL, NULL),
(23, 'Barra de dulce de guayaba', 1, 'Dulceguayaba', 58, 18, '2021-04-07 08:36:24', 'a6728d1bee28794f19ab76936d3e6261.jpeg', 0.5, 3, NULL, NULL),
(24, 'Frijoles negro 10 lb', 20, 'Frijoles', 4, 18, '2021-04-07 08:00:30', 'fafd8f2a3b061c06c75f31a2d0e07b9b.jpeg', 10, 3, NULL, NULL),
(25, 'Frijoles blancos 10 lb', 20, 'Frijole', 2, 18, '2021-04-07 09:26:20', '70965a19b53ab7b0c01eded5872f5cbe.jpeg', 10, 1, NULL, NULL),
(26, 'Pollo', 18, 'Pollo', 26, 18, '2021-04-06 23:58:10', 'c82f83a867b10fad06c44f1014001773.jpeg', 9, 3, NULL, NULL),
(27, 'Salchicha 10 unidades', 5, 'Salchicha', 78, 18, '2021-04-07 09:11:30', '4dd16293e199bfece00de0e967818a59.jpeg', 2.5, 10, NULL, NULL),
(28, 'Pescado 9 lb', 9, 'Pescado', 0, 18, '2021-04-08 18:19:51', '2d23a55592eddb3a8cac49bf7bffbafd.jpeg', 4.5, 2, NULL, NULL),
(29, 'Africanita', 1, 'Africanita', 3, 18, '2021-04-09 21:07:51', 'ec62e41daf671b6bd3dc73cd71bcb459.jpeg', 0.5, 3, NULL, NULL),
(30, 'Peter de chocolate', 3, 'Peter', 3, 18, '2021-04-09 21:07:07', '50556df6f3824f72627729b1664026c8.jpeg', 1.5, 3, NULL, NULL),
(31, 'Refresco de Pomo', 5, 'Refresco', 0, 18, '2021-04-09 21:08:34', 'efd6353a9293ae66fd561c8b51ef18b0.jpeg', 2.5, 3, NULL, NULL),
(32, 'Paquete de papa prefrita', 6, 'Papas', 6, 18, '2021-04-07 07:49:39', '399babcdc394ea09e15893608ec09aee.jpeg', 3, 2, NULL, NULL),
(33, 'Tubo de jamón mediano', 30, 'Jamon', 3, 18, '2021-04-08 19:55:52', '4533744f8c19f0b959b5dc9e442883a6.jpeg', 15, 3, NULL, NULL),
(34, 'Lata de Sardina', 12, 'Sardina', 5, 18, '2021-04-06 23:43:50', '831af2914770010123989b57a70da9f5.jpeg', 6, 3, NULL, NULL),
(35, 'Huevo 30 unidades', 12, 'Cartón de huevo', 1, 18, '2021-04-10 04:26:32', '1d0fccac331808a1d134ac60a5fa9911.webp', 6, 3, NULL, NULL),
(36, 'Malta', 2, 'Malta', 0, 18, '2021-04-09 21:08:54', '6105be52bb3ee217c3ba462e25594785.png', 1, 24, NULL, NULL),
(37, 'Paleta de puerco de 23lb', 115, 'Paleta de puerco', 0, 18, '2021-04-06 23:53:40', 'e2929cf762a0674bad05ad20de4b2f2a.jpeg', 62.5, 0, NULL, NULL),
(38, 'Costilla de Cerdo 19 lb', 95, 'Costilla', 1, 18, '2021-04-07 09:24:32', '3fd84c4aa136f0fa979e65293346e2ea.png', 47.5, 0, NULL, NULL),
(39, 'Solomo con cocote 22 lb', 110, 'Solomo', 1, 18, '2021-04-07 09:21:19', 'ca643541563ed074f57b0fc8d64608d9.png', 55, 0, NULL, NULL),
(40, 'Leche Condensada', 6, 'Leche condensada', 0, 18, '2021-04-08 18:43:58', 'f6bdb1f90173011af3b36b6abd7a2556.jpeg', 3, 3, NULL, NULL),
(41, 'Aceite', 12, 'Aceite', 8, 18, '2021-04-08 21:19:24', '3196bd5eb6cd1e0dd1095f5b43a7f4c6.jpeg', 6, 3, NULL, NULL),
(42, 'Solomo 16.5 lb', 82, 'Solomo', 1, 18, '2021-04-08 21:33:48', 'bce07566177e803d4ede700b78467d10.jpeg', 41, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_pedido`
--

CREATE TABLE `producto_pedido` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` double NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `costo` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transporte`
--

CREATE TABLE `transporte` (
  `id` int(11) NOT NULL,
  `municipio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tarifa` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `transporte`
--

INSERT INTO `transporte` (`id`, `municipio`, `tarifa`) VALUES
(9, 'Remedios', 10),
(10, 'Caibarien', 10),
(11, 'Santaclara', 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transporte_pedido`
--

CREATE TABLE `transporte_pedido` (
  `id` int(11) NOT NULL,
  `municipio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tarifa` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `saldo` double NOT NULL,
  `correo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dir` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registro` datetime DEFAULT NULL,
  `id_telegram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deuda` double NOT NULL,
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `username`, `roles`, `password`, `nombre`, `apellidos`, `saldo`, `correo`, `dir`, `telf`, `registro`, `id_telegram`, `deuda`, `estado`, `is_verified`) VALUES
(13, 'ruben', '[\"ROLE_ADMIN\"]', '$argon2id$v=19$m=65536,t=4,p=1$d0FETURheUh4MkxrMm1sRg$KaIiSVEEXTGHcPw14oJ0nGtnxeoJO6C0Un8FSQ+HvrE', 'Ruben Alejandro', 'Perez-Borroto', 3393, NULL, 'Hermanos Galcia # 72', '47854124', '2020-04-25 17:29:39', '849735174', 700, '', 1),
(14, 'jhcastro', '[\"ROLE_ADMIN\"]', '$argon2id$v=19$m=65536,t=4,p=1$RGwwd1daV2Nkd2RhTWNnYg$luA1h35CFXz0MjH8lgiZmQV67/+lIGw5tO6F5OiD7cM', 'Javier', 'Hernández Castro', 558, 'jhcastro@nauta.cu', 'Máximo Gómez #12', '54307985', '2021-01-14 18:47:21', '691044021', 0, '', 1),
(15, 'lil', '[\"ROLE_USER\"]', '$argon2id$v=19$m=65536,t=4,p=1$VldURUc0bXlFWHpvaVdtcA$WPOITBjCBkN38FfTx1FtMSEEBpDyOklq2wDAMN6goWw', 'Lil Rocio', 'Freijo Olive', 0, NULL, 'Interior sin número', NULL, '2021-02-28 23:57:50', NULL, 0, '', 1),
(16, 'pepe', '[\"ROLE_TRAB\"]', '$argon2id$v=19$m=65536,t=4,p=1$VFBkNC9YRHF4VjJxaDFwUg$bEtZWojRpbIBik8I9EE2AE8c1/liWXC6EkGfVMNRWTU', 'Pepe', 'Gracía López', 220, NULL, 'asdasdasdas', '54781562', '2021-04-01 12:55:14', NULL, 0, '', 1),
(17, 'Angelica', '[]', '$argon2id$v=19$m=65536,t=4,p=1$djIwZU91My9pSWhvTmVEcQ$H3pCW0+35rNFLiXAeaEfEN4zuAH0TYBF94knNQyH13E', 'Angelica', 'Diaz', 0, NULL, NULL, NULL, '2021-04-07 08:47:48', NULL, 0, '', 1),
(18, 'Yelaine', '[]', '$argon2id$v=19$m=65536,t=4,p=1$RGxVTUxKeW8xNGxwV3NXdQ$9jh3svcMhuegR46fqLaaGR0rkUjiMA7CzvpQOucLiYk', 'Yelaine', 'Parrado', 10000, NULL, NULL, NULL, '2021-04-08 17:13:31', NULL, 0, '', 1),
(19, 'Arialys', '[]', '$argon2id$v=19$m=65536,t=4,p=1$RHBPVFpPckJKemZaYnRMcQ$HyxMlFlCoGgjMeoBjuaqs0lXhCezaZbHEK+vwkntxro', 'Arialys', 'Felgueira', 0, NULL, NULL, NULL, '2021-04-08 20:17:57', NULL, 0, '', 1),
(20, 'Betty', '[]', '$argon2id$v=19$m=65536,t=4,p=1$Q2RLSGZhMnRvSFIxL3JGVw$yvX8ShWG5LUd1YzIz78Ta08JBXnzlkfeNaWbdvCAGrM', 'Betty', 'Maribona', 0, NULL, NULL, NULL, '2021-04-10 04:58:24', NULL, 0, '', 1),
(21, 'claudiacaluff', '[]', '$argon2id$v=19$m=65536,t=4,p=1$Llp6RmVUQjZQa2Z5SE1LMw$ukhbVa3QT4LiBBEvG7+f41TmGSk+lUIGmJsNOnwgNH8', 'Claudia', 'Caluff Herrera', 0, NULL, NULL, NULL, '2021-04-10 05:00:48', NULL, 0, '', 1),
(22, 'eli712', '[]', '$argon2id$v=19$m=65536,t=4,p=1$bWg3eDNBWENJMWs1VjQ1eA$EEAqPjS49Nz3P26TZUGsMG8+iTnJtzNketnghGA2J54', 'Elizabeth', 'Herrera Martinez', 0, NULL, NULL, NULL, '2021-04-10 05:02:00', NULL, 0, '', 1),
(23, 'Yoenmi', '[]', '$argon2id$v=19$m=65536,t=4,p=1$ZGlJR3c3RDYzbG9Ca1NqLg$wGdjYTcbCe/CDsWbpt5xExLL95MuQfqjRiF54njkLWw', 'Yoenmi', 'Peñaranda Rivero', 0, NULL, NULL, NULL, '2021-04-10 05:10:35', NULL, 0, '', 1),
(24, 'Richard', '[]', '$argon2id$v=19$m=65536,t=4,p=1$NERSaDRicjByd2pHT3RVTQ$bwjuVnJo1+R796+tRHnCeteBiPxbrD5pQ8oV34k96V0', 'Richard', 'González', 108, 'riyalesa@nauta.cu', 'Calle: 24. #°2505a. e/25 y 27. Caibarién. Villa Clara. Cuba.', '56282231', '2021-04-10 05:14:29', NULL, 0, '', 1),
(25, 'Yasmany88', '[]', '$argon2id$v=19$m=65536,t=4,p=1$VWplaWRkcVVqS3NMc3NIdw$HtE9aIODyG+RKo91+J4bXJCul6ao8ZCv6qAZQ1ZLcFY', 'Yasmany', 'Valdes', 0, NULL, NULL, NULL, '2021-04-10 05:55:21', NULL, 0, '', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cuadre`
--
ALTER TABLE `cuadre`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E205CED17D879E4F` (`negocio_id`),
  ADD KEY `IDX_E205CED18005CA7E` (`trabajador_saliente_id`),
  ADD KEY `IDX_E205CED1F5CB767C` (`trabajador_entrante_id`);

--
-- Indices de la tabla `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8F3F68C5DB38439E` (`usuario_id`);

--
-- Indices de la tabla `migration_versions`
--
ALTER TABLE `migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `negocio`
--
ALTER TABLE `negocio`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `negocio_user`
--
ALTER TABLE `negocio_user`
  ADD PRIMARY KEY (`negocio_id`,`user_id`),
  ADD KEY `IDX_84A850D17D879E4F` (`negocio_id`),
  ADD KEY `IDX_84A850D1A76ED395` (`user_id`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_C4EC16CE24FC919A` (`transporte_id`),
  ADD KEY `IDX_C4EC16CEDE734E51` (`cliente_id`),
  ADD KEY `IDX_C4EC16CEEC3656E` (`trabajador_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A7BB06153397707A` (`categoria_id`),
  ADD KEY `IDX_A7BB06157D879E4F` (`negocio_id`);

--
-- Indices de la tabla `producto_pedido`
--
ALTER TABLE `producto_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_69CBB9804854653A` (`pedido_id`);

--
-- Indices de la tabla `transporte`
--
ALTER TABLE `transporte`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `transporte_pedido`
--
ALTER TABLE `transporte_pedido`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`),
  ADD UNIQUE KEY `UNIQ_8D93D64977040BC9` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cuadre`
--
ALTER TABLE `cuadre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `negocio`
--
ALTER TABLE `negocio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `producto_pedido`
--
ALTER TABLE `producto_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `transporte`
--
ALTER TABLE `transporte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `transporte_pedido`
--
ALTER TABLE `transporte_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cuadre`
--
ALTER TABLE `cuadre`
  ADD CONSTRAINT `FK_E205CED17D879E4F` FOREIGN KEY (`negocio_id`) REFERENCES `negocio` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_E205CED18005CA7E` FOREIGN KEY (`trabajador_saliente_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_E205CED1F5CB767C` FOREIGN KEY (`trabajador_entrante_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `FK_8F3F68C5DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `negocio_user`
--
ALTER TABLE `negocio_user`
  ADD CONSTRAINT `FK_84A850D17D879E4F` FOREIGN KEY (`negocio_id`) REFERENCES `negocio` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_84A850D1A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `FK_C4EC16CE24FC919A` FOREIGN KEY (`transporte_id`) REFERENCES `transporte_pedido` (`id`),
  ADD CONSTRAINT `FK_C4EC16CEDE734E51` FOREIGN KEY (`cliente_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_C4EC16CEEC3656E` FOREIGN KEY (`trabajador_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `FK_A7BB06153397707A` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_A7BB06157D879E4F` FOREIGN KEY (`negocio_id`) REFERENCES `negocio` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_pedido`
--
ALTER TABLE `producto_pedido`
  ADD CONSTRAINT `FK_69CBB9804854653A` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
