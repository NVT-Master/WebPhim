-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: cinema_db
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `theater_id` int NOT NULL,
  `name` varchar(60) NOT NULL,
  `seat_map_json` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `theater_id` (`theater_id`),
  CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`theater_id`) REFERENCES `theaters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,1,'Phòng 1','{\"rows\": [{\"seats\": [{\"type\": \"STANDARD\", \"number\": 1, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 2, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 3, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 4, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 5, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 6, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 7, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 8, \"status\": \"AVAILABLE\"}], \"row_label\": \"A\"}, {\"seats\": [{\"type\": \"VIP\", \"number\": 1, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 2, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 3, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 4, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 5, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 6, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 7, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 8, \"status\": \"AVAILABLE\"}], \"row_label\": \"B\"}]}'),(2,1,'Phòng 2','{\"rows\": [{\"seats\": [{\"type\": \"COUPLE\", \"number\": 1, \"status\": \"AVAILABLE\"}, {\"type\": \"COUPLE\", \"number\": 2, \"status\": \"AVAILABLE\"}, {\"type\": \"COUPLE\", \"number\": 3, \"status\": \"AVAILABLE\"}, {\"type\": \"COUPLE\", \"number\": 4, \"status\": \"AVAILABLE\"}], \"row_label\": \"A\"}]}'),(3,2,'Phòng 1','{\"rows\": [{\"seats\": [{\"type\": \"STANDARD\", \"number\": 1, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 2, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 3, \"status\": \"AVAILABLE\"}, {\"type\": \"STANDARD\", \"number\": 4, \"status\": \"AVAILABLE\"}], \"row_label\": \"A\"}]}'),(4,3,'Phòng 1','{\"rows\": [{\"seats\": [{\"type\": \"VIP\", \"number\": 1, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 2, \"status\": \"AVAILABLE\"}, {\"type\": \"VIP\", \"number\": 3, \"status\": \"AVAILABLE\"}], \"row_label\": \"A\"}]}');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-24  0:04:48
