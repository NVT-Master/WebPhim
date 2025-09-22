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
-- Table structure for table `seats`
--

DROP TABLE IF EXISTS `seats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_id` int NOT NULL,
  `row_label` varchar(5) NOT NULL,
  `seat_number` int NOT NULL,
  `seat_type` enum('STANDARD','VIP','COUPLE') DEFAULT 'STANDARD',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_seat` (`room_id`,`row_label`,`seat_number`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seats`
--

LOCK TABLES `seats` WRITE;
/*!40000 ALTER TABLE `seats` DISABLE KEYS */;
INSERT INTO `seats` VALUES (1,1,'A',1,'STANDARD'),(2,1,'A',2,'STANDARD'),(3,1,'A',3,'STANDARD'),(4,1,'A',4,'STANDARD'),(5,1,'A',5,'STANDARD'),(6,1,'A',6,'STANDARD'),(7,1,'A',7,'STANDARD'),(8,1,'A',8,'STANDARD'),(9,1,'B',1,'VIP'),(10,1,'B',2,'VIP'),(11,1,'B',3,'VIP'),(12,1,'B',4,'VIP'),(13,1,'B',5,'VIP'),(14,1,'B',6,'VIP'),(15,1,'B',7,'VIP'),(16,1,'B',8,'VIP'),(17,2,'A',1,'COUPLE'),(18,2,'A',2,'COUPLE'),(19,2,'A',3,'COUPLE'),(20,2,'A',4,'COUPLE'),(21,3,'A',1,'STANDARD'),(22,3,'A',2,'STANDARD'),(23,3,'A',3,'STANDARD'),(24,3,'A',4,'STANDARD'),(25,4,'A',1,'VIP'),(26,4,'A',2,'VIP'),(27,4,'A',3,'VIP');
/*!40000 ALTER TABLE `seats` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-23  5:09:18
