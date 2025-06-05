-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: TiendaSuarez
-- ------------------------------------------------------
-- Server version	5.7.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Articulos`
--

DROP TABLE IF EXISTS `Articulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Articulos` (
  `idArticulo` int(11) NOT NULL AUTO_INCREMENT,
  `idCategoria` int(11) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `caracteristicas` varchar(100) DEFAULT NULL,
  `precio` float DEFAULT NULL,
  `imagen` blob,
  PRIMARY KEY (`idArticulo`),
  KEY `idCategoria` (`idCategoria`),
  CONSTRAINT `articulos_ibfk_1` FOREIGN KEY (`idCategoria`) REFERENCES `Categorias` (`idCategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Articulos`
--

LOCK TABLES `Articulos` WRITE;
/*!40000 ALTER TABLE `Articulos` DISABLE KEYS */;
INSERT INTO `Articulos` VALUES (1,4,'Alianza de oro amarillo','1.10mm * 2.30mm, talla 44-55.5',230,_binary 'Productos/SolitariosYAlianzas/al8018-0at2.jpg');
/*!40000 ALTER TABLE `Articulos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Categorias`
--

DROP TABLE IF EXISTS `Categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Categorias` (
  `idCategoria` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idCategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Categorias`
--

LOCK TABLES `Categorias` WRITE;
/*!40000 ALTER TABLE `Categorias` DISABLE KEYS */;
INSERT INTO `Categorias` VALUES (1,'Colecciones'),(2,'Joyas'),(3,'Relojeria'),(4,'Solitarios y Alianzas');
/*!40000 ALTER TABLE `Categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Clientes`
--

DROP TABLE IF EXISTS `Clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Clientes` (
  `idCliente` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(60) DEFAULT NULL,
  `colonia` varchar(50) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `pais` varchar(50) DEFAULT NULL,
  `codigo_postal` int(11) DEFAULT NULL,
  PRIMARY KEY (`idCliente`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Clientes`
--

LOCK TABLES `Clientes` WRITE;
/*!40000 ALTER TABLE `Clientes` DISABLE KEYS */;
INSERT INTO `Clientes` VALUES (1,'Abraham','Esquivel Salas','abraham@gmail.com','Priv. Guadalupe 7A','Tepeyac','Rio Grande','Zacatecas','Mexico',98422),(2,'Mara','Gonzlez','maria.gonzalez@email.com','Calle Principal 123','Centro','Ciudad de Mxico','CDMX','Mxico',6000),(3,'Juan','Prez','juan.perez@email.com','Avenida Revolucin 456','Del Valle','Guadalajara','Jalisco','Mxico',44100),(4,'Ana','Martnez','ana.martinez@email.com','Boulevard Lpez Mateos 789','La Estacin','Monterrey','Nuevo Len','Mxico',64000),(5,'Eduardo','Delfin','delfin@gmail.com','Mi casa w','Las Esperanzas','Rio Grande','Zacatuercas','Mexico',98400),(6,'Brian','Diaz','brian@gmail.com','Calle 13','Colonia','Rio Grande','Zacatecas','MÃ©xico',984200),(7,'Abel','Luna','abel@gmail.com','16 de septiembre #54','Salinas','Rio Grande','Zacatecas','Mexico',98403),(8,'Joselyn','Flores','josy@gmail.com','TenochtitlÃ¡n 10','Azteca','Rio Grande','Zacatecas','MÃ©xico',98422),(9,'Xitlalic','Flores','xitlalic@gmail.com','Tenoch','azteca','Rio Grande','Zacatecas','Mexico',98422);
/*!40000 ALTER TABLE `Clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Existencia`
--

DROP TABLE IF EXISTS `Existencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Existencia` (
  `idArticulo` int(11) DEFAULT NULL,
  `idTienda` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  KEY `idArticulo` (`idArticulo`),
  KEY `idTienda` (`idTienda`),
  CONSTRAINT `existencia_ibfk_1` FOREIGN KEY (`idArticulo`) REFERENCES `Articulos` (`idArticulo`),
  CONSTRAINT `existencia_ibfk_2` FOREIGN KEY (`idTienda`) REFERENCES `Tiendas` (`idTienda`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Existencia`
--

LOCK TABLES `Existencia` WRITE;
/*!40000 ALTER TABLE `Existencia` DISABLE KEYS */;
INSERT INTO `Existencia` VALUES (1,1,1);
/*!40000 ALTER TABLE `Existencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Tiendas`
--

DROP TABLE IF EXISTS `Tiendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Tiendas` (
  `idTienda` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_sucursal` varchar(50) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `direccion` varchar(50) DEFAULT NULL,
  `codigo_postal` int(11) DEFAULT NULL,
  `horario` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idTienda`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Tiendas`
--

LOCK TABLES `Tiendas` WRITE;
/*!40000 ALTER TABLE `Tiendas` DISABLE KEYS */;
INSERT INTO `Tiendas` VALUES (1,'Suarez Alicante','Alicante','C/Maisonnave, 43',30003,'Lunes a Sabado 10:00 - 14:00 y 16:30 - 20:30'),(2,'Suarez','Alicante','C/Maisonnave, 42',30002,'Lunes a viernes');
/*!40000 ALTER TABLE `Tiendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Usuarios`
--

DROP TABLE IF EXISTS `Usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Usuarios` (
  `idUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `idCliente` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `rol` enum('admin','cliente') DEFAULT 'cliente',
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `email` (`email`),
  KEY `idCliente` (`idCliente`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`idCliente`) REFERENCES `Clientes` (`idCliente`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Usuarios`
--

LOCK TABLES `Usuarios` WRITE;
/*!40000 ALTER TABLE `Usuarios` DISABLE KEYS */;
INSERT INTO `Usuarios` VALUES (1,2,'maria.gonzalez@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','2025-05-05 04:30:38',NULL,1,'cliente'),(4,3,'juan.perez@email.com','$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm','2025-05-05 04:39:55',NULL,1,'cliente'),(5,4,'ana.martinez@email.com','$2y$10$rDk5v5rXvGz5JYJf5lH9uOq5ZJlE6Yb5d5bW5c5n5v5b5N5v5b5N5','2025-05-05 04:41:58',NULL,1,'cliente'),(6,5,'delfin@gmail.com','$2y$10$vTe3dW6HcD0w/1T5C3bs9utIpTwuyJOtYhgbFnWjysTRZTIVmAvr.','2025-05-07 13:34:24','2025-05-22 02:53:43',1,'cliente'),(7,6,'brian@gmail.com','$2y$10$qIPkWLHHEWDMGiQsj2ugDemqzk3K5daPJwgSnZpWoMI6zO43jxT9W','2025-05-07 13:43:47','2025-05-07 18:56:04',1,'cliente'),(8,7,'abel@gmail.com','$2y$10$ez3Qcx8WhTPdXtYHkQMQzeVfzLQYxJGmN0nIt.9/LNCbLFfwZkmb2','2025-05-07 18:23:22','2025-05-07 18:23:36',1,'cliente'),(9,8,'josy@gmail.com','$2y$10$TepVnUA9ZlTsk2N7gao2k.clSv3igyRHS0lTG9GCpXBsm4akd5ksq','2025-05-07 19:00:52','2025-05-12 19:22:08',1,'cliente'),(10,9,'xitlalic.1403.flores@gmail.com','$2y$10$vsAt1LVrqZEGw0F0q397Xu5aeLFIMq4Dm52G/ROEQMeaoS.bhvfyW','2025-05-22 03:11:48','2025-05-22 03:22:15',1,'admin');
/*!40000 ALTER TABLE `Usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Ventas`
--

DROP TABLE IF EXISTS `Ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Ventas` (
  `folio` int(11) NOT NULL AUTO_INCREMENT,
  `idArticulo` int(11) DEFAULT NULL,
  `idCliente` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `idTienda` int(11) DEFAULT NULL,
  PRIMARY KEY (`folio`),
  KEY `idArticulo` (`idArticulo`),
  KEY `idCliente` (`idCliente`),
  KEY `idTienda` (`idTienda`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`idArticulo`) REFERENCES `Articulos` (`idArticulo`),
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`idCliente`) REFERENCES `Clientes` (`idCliente`),
  CONSTRAINT `ventas_ibfk_4` FOREIGN KEY (`idTienda`) REFERENCES `Tiendas` (`idTienda`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Ventas`
--

LOCK TABLES `Ventas` WRITE;
/*!40000 ALTER TABLE `Ventas` DISABLE KEYS */;
INSERT INTO `Ventas` VALUES (1,1,1,'2025-04-29',1),(2,1,5,'2025-05-22',1);
/*!40000 ALTER TABLE `Ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'TiendaSuarez'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-27 14:03:08
