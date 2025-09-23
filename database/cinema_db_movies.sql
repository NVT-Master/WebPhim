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
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `duration_min` int NOT NULL,
  `rating` varchar(10) DEFAULT NULL,
  `poster_url` varchar(500) DEFAULT NULL,
  `trailer_url` varchar(500) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `genre` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movies`
--

LOCK TABLES `movies` WRITE;
/*!40000 ALTER TABLE `movies` DISABLE KEYS */;
INSERT INTO `movies` VALUES (1,'Avengers: Endgame','Bom tấn siêu anh hùng Marvel, kết thúc hành trình 22 phim của MCU',180,'C13','68d0f8f7eceb1_1.jpg','https://www.youtube.com/watch?v=TcMBFSGVi1c','2019-04-26','2025-09-04 12:06:34','Hành động'),(2,'Inception','Bộ phim khoa học viễn tưởng về giấc mơ và đánh cắp bí mật',148,'C16','68d1a854081b9_2.jpg','https://www.youtube.com/watch?v=YoHD9XEInc0','2010-07-16','2025-09-04 12:06:34','Khoa học viễn tưởng'),(3,'The Notebook','Một câu chuyện tình yêu lãng mạn vượt thời gian',123,'C16','68d1a859f228b_3.jpg','https://www.youtube.com/watch?v=FC6biTjEyZw','2004-06-25','2025-09-04 12:06:34','Tình cảm'),(4,'Get Out','Bộ phim kinh dị tâm lý về những bí mật đáng sợ',104,'C18','68d1a85f8bd52_4.jpg','https://www.youtube.com/watch?v=DzfpyUB60YY','2017-02-24','2025-09-04 12:06:34','Kinh dị'),(5,'Deadpool & Wolverine','Hài hành động với sự kết hợp của Deadpool và Wolverine',128,'C18','68d1a8655b725_5.jpg','https://www.youtube.com/watch?v=73_1biulkYk','2024-07-26','2025-09-04 12:06:34','Hài'),(6,'Inside Out 2','Phim hoạt hình khám phá cảm xúc của tuổi teen',96,'P','68d1a86bc9173_6.jpg','https://www.youtube.com/watch?v=LEjhY15eCx0','2024-06-14','2025-09-04 12:06:34','Hoạt hình'),(7,'Dune: Part Two','Hành trình phiêu lưu sử thi trên sa mạc Arrakis',166,'C13','68d1a8725ca0d_7.jpg','https://www.youtube.com/watch?v=Way9Dexny3w','2024-03-01','2025-09-04 12:06:34','Phiêu lưu'),(8,'Parasite','Phim tâm lý về bất bình đẳng xã hội',132,'C18','68d1a8785fcce_8.jpg','https://www.youtube.com/watch?v=5xH0HfJHsaY','2019-05-30','2025-09-04 12:06:34','Tâm lý');
/*!40000 ALTER TABLE `movies` ENABLE KEYS */;
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
