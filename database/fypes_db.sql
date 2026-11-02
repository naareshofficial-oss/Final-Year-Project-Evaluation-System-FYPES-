-- FYPES - Final Year Project Evaluation System
-- Universiti Selangor (UNISEL)
-- Full database export (schema + demo data), tested and verified live.
-- Assessment rubric matches official UNISEL FYP1/FYP2 marking scheme.
CREATE DATABASE IF NOT EXISTS fypes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fypes_db;
SET FOREIGN_KEY_CHECKS=0;
/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) DEFAULT NULL,
  `audience` enum('all','students','lecturers','coordinators') NOT NULL DEFAULT 'all',
  `subject` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admin_messages` WRITE;
/*!40000 ALTER TABLE `admin_messages` DISABLE KEYS */;
INSERT INTO `admin_messages` VALUES
(1,1,'all','Welcome to Semester 2, 2025/2026','Welcome back! Please check your dashboard for updated timelines and submission deadlines this semester.','2026-06-27 21:34:37'),
(2,1,'students','Test Announcement','This is a test broadcast message.','2026-07-09 06:23:44');
/*!40000 ALTER TABLE `admin_messages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `appointment_letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_letters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lecturer_id` int(11) NOT NULL,
  `role_type` enum('supervisor','presentation_panel','report_panel','assessor') NOT NULL,
  `programme_type` enum('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  `student_name` varchar(150) DEFAULT NULL,
  `letter_code` varchar(50) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `letter_code` (`letter_code`),
  KEY `lecturer_id` (`lecturer_id`),
  CONSTRAINT `appointment_letters_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `appointment_letters` WRITE;
/*!40000 ALTER TABLE `appointment_letters` DISABLE KEYS */;
INSERT INTO `appointment_letters` VALUES
(1,8,'supervisor','degree_fyp2','Ng Wei Xuan','FYPES-INV-20260001',NULL,1,'2026-07-02 21:34:37'),
(2,11,'supervisor','degree_fyp1','Yap Jia Ying','FYPES-INV-20260002',NULL,1,'2026-06-24 21:34:37'),
(3,12,'supervisor','degree_fyp2','Mohd Amirul bin Rosli','FYPES-INV-20260003',NULL,1,'2026-06-19 21:34:37'),
(4,15,'supervisor','degree_fyp1','Vikram a/l Suresh','FYPES-INV-20260004',NULL,1,'2026-06-26 21:34:37'),
(5,4,'supervisor','degree_fyp2','Faris Adnan bin Halim','FYPES-INV-20260005',NULL,1,'2026-07-02 21:34:37'),
(6,11,'supervisor','degree_fyp1','Haziq Danial bin Rahim','FYPES-INV-20260006',NULL,1,'2026-06-17 21:34:37'),
(7,12,'supervisor','degree_fyp2','Siti Aminah binti Rahman','FYPES-INV-20260007',NULL,1,'2026-06-17 21:34:37'),
(8,14,'supervisor','diploma_fyp2','Tan Wei Lin','FYPES-INV-20260008',NULL,1,'2026-06-29 21:34:37'),
(9,17,'supervisor','diploma_fyp1','Ong Mei Xin','FYPES-INV-20260009',NULL,1,'2026-07-05 21:34:37'),
(10,17,'supervisor','diploma_fyp1','Lim Jia Hui','FYPES-INV-20260010',NULL,1,'2026-07-01 21:34:37'),
(11,9,'supervisor','diploma_fyp1','Arjun a/l Rajan','FYPES-INV-20260011',NULL,1,'2026-06-19 21:34:37'),
(12,5,'supervisor','diploma_fyp1','Aina Batrisyia binti Zainal','FYPES-INV-20260012',NULL,1,'2026-06-24 21:34:37'),
(13,13,'supervisor','diploma_fyp1','Muhammad Hafiz bin Ismail','FYPES-INV-20260013',NULL,1,'2026-06-22 21:34:37'),
(14,5,'supervisor','diploma_fyp1','Danish Haziq bin Othman','FYPES-INV-20260014',NULL,1,'2026-07-01 21:34:37'),
(15,10,'supervisor','diploma_fyp2','Chong Yee Wei','FYPES-INV-20260015',NULL,1,'2026-07-06 21:34:37'),
(16,9,'supervisor','diploma_fyp1','Divya a/p Naidu','FYPES-INV-20260016',NULL,1,'2026-07-05 21:34:37'),
(17,11,'supervisor','degree_fyp1','Dr. Aina Sofea binti Zainal','FYPES-INV-2026-6C423E40',NULL,3,'2026-07-09 06:21:30');
/*!40000 ALTER TABLE `appointment_letters` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(200) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES
(1,1,'login','Admin login','127.0.0.1','2026-07-09 06:19:51'),
(2,3,'login','Lecturer login','127.0.0.1','2026-07-09 06:20:04'),
(3,23,'login','Student login','127.0.0.1','2026-07-09 06:20:18'),
(4,13,'login','Lecturer login','127.0.0.1','2026-07-09 06:20:51'),
(5,13,'respond_supervisor_request','Request #1 -> accepted','127.0.0.1','2026-07-09 06:20:51'),
(6,5,'login','Lecturer login','127.0.0.1','2026-07-09 06:21:13'),
(7,5,'confirm_supervision','Request #1 confirmed','127.0.0.1','2026-07-09 06:21:13'),
(8,5,'login','Lecturer login','127.0.0.1','2026-07-09 06:21:30'),
(9,5,'assign_panel','Student #2','127.0.0.1','2026-07-09 06:21:30'),
(10,13,'login','Lecturer login','127.0.0.1','2026-07-09 06:21:44'),
(11,28,'login','Student login','127.0.0.1','2026-07-09 06:22:17'),
(12,23,'login','Student login','127.0.0.1','2026-07-09 06:22:40'),
(13,1,'login','Admin login','127.0.0.1','2026-07-09 06:22:41'),
(14,1,'generate_certificate','For user #23: Certificate of Completion - Final Year Project','127.0.0.1','2026-07-09 06:22:41'),
(15,23,'login','Student login','127.0.0.1','2026-07-09 06:22:58'),
(16,23,'upload_submission','Category #14, google doc link','127.0.0.1','2026-07-09 06:22:58'),
(17,23,'delete_submission','Submission #85','127.0.0.1','2026-07-09 06:22:58'),
(18,1,'login','Admin login','127.0.0.1','2026-07-09 06:23:14'),
(19,1,'update_semester_dates','','127.0.0.1','2026-07-09 06:23:14'),
(20,1,'login','Admin login','127.0.0.1','2026-07-09 06:23:44'),
(21,1,'send_message','To students: Test Announcement','127.0.0.1','2026-07-09 06:23:44'),
(22,3,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:44'),
(23,4,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:44'),
(24,5,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:44'),
(25,6,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:44'),
(26,7,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:44'),
(27,8,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(28,9,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(29,10,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(30,11,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(31,12,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(32,13,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(33,14,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(34,15,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(35,16,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(36,17,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(37,18,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(38,19,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:45'),
(39,20,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:46'),
(40,21,'login','Lecturer login','127.0.0.1','2026-07-09 06:23:46'),
(41,1,'login','Admin login','127.0.0.1','2026-07-17 01:36:37'),
(42,3,'login','Lecturer login','127.0.0.1','2026-07-17 01:36:51'),
(43,24,'login','Student login','127.0.0.1','2026-07-17 01:37:04'),
(44,1,'login','Admin login','127.0.0.1','2026-07-17 01:37:21'),
(45,3,'login','Lecturer login','127.0.0.1','2026-07-17 06:50:47'),
(46,3,'assign_assessor','Student #21','127.0.0.1','2026-07-17 06:50:47'),
(47,7,'login','Lecturer login','127.0.0.1','2026-07-17 06:51:12'),
(48,7,'submit_evaluation','Evaluation #86 completed, score=42','127.0.0.1','2026-07-17 06:51:12'),
(49,3,'login','Lecturer login','127.0.0.1','2026-07-17 06:51:26'),
(50,13,'login','Lecturer login','127.0.0.1','2026-07-17 06:51:50'),
(51,13,'review_report','Report #1','127.0.0.1','2026-07-17 06:51:50'),
(52,3,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:06'),
(53,4,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:06'),
(54,5,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:06'),
(55,6,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(56,7,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(57,8,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(58,9,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(59,10,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(60,11,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(61,12,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(62,13,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(63,14,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(64,15,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(65,16,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(66,17,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:07'),
(67,18,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:08'),
(68,19,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:08'),
(69,20,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:08'),
(70,21,'login','Lecturer login','127.0.0.1','2026-07-17 06:52:08'),
(71,23,'login','Student login','127.0.0.1','2026-07-17 06:52:08'),
(72,24,'login','Student login','127.0.0.1','2026-07-17 06:52:08'),
(73,25,'login','Student login','127.0.0.1','2026-07-17 06:52:08'),
(74,26,'login','Student login','127.0.0.1','2026-07-17 06:52:08'),
(75,27,'login','Student login','127.0.0.1','2026-07-17 06:52:08'),
(76,28,'login','Student login','127.0.0.1','2026-07-17 06:52:08'),
(77,29,'login','Student login','127.0.0.1','2026-07-17 06:52:08'),
(78,30,'login','Student login','127.0.0.1','2026-07-17 06:52:08'),
(79,31,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(80,32,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(81,33,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(82,34,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(83,35,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(84,36,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(85,37,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(86,38,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(87,39,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(88,40,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(89,41,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(90,42,'login','Student login','127.0.0.1','2026-07-17 06:52:09'),
(91,43,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(92,44,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(93,45,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(94,46,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(95,47,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(96,48,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(97,49,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(98,50,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(99,51,'login','Student login','127.0.0.1','2026-07-17 06:52:10'),
(100,52,'login','Student login','127.0.0.1','2026-07-17 06:52:10');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cert_type` enum('completion','appreciation') NOT NULL,
  `cert_code` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `grade` enum('A','B','C') DEFAULT NULL COMMENT 'Grade A, B, or C for student certificates',
  `template` varchar(50) NOT NULL DEFAULT 'completion',
  `file_path` varchar(255) DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `issued_by_role` enum('admin','coordinator') DEFAULT 'admin',
  `issued_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cert_code` (`cert_code`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
INSERT INTO `certificates` VALUES
(1,3,'appreciation','FYPES-APR-20260001','Certificate of Appreciation - FYP Evaluation Duty',NULL,'appreciation',NULL,1,'admin','2026-06-22 21:34:37'),
(2,4,'appreciation','FYPES-APR-20260002','Certificate of Appreciation - FYP Evaluation Duty',NULL,'appreciation',NULL,1,'admin','2026-06-21 21:34:37'),
(3,5,'appreciation','FYPES-APR-20260003','Certificate of Appreciation - FYP Evaluation Duty',NULL,'appreciation',NULL,1,'admin','2026-06-30 21:34:37'),
(4,6,'appreciation','FYPES-APR-20260004','Certificate of Appreciation - FYP Evaluation Duty',NULL,'appreciation',NULL,1,'admin','2026-07-03 21:34:37'),
(5,7,'appreciation','FYPES-APR-20260005','Certificate of Appreciation - FYP Evaluation Duty',NULL,'appreciation',NULL,1,'admin','2026-06-19 21:34:37'),
(6,8,'appreciation','FYPES-APR-20260006','Certificate of Appreciation - FYP Evaluation Duty',NULL,'appreciation',NULL,1,'admin','2026-06-24 21:34:37'),
(7,9,'appreciation','FYPES-APR-20260007','Certificate of Appreciation - FYP Evaluation Duty',NULL,'appreciation',NULL,1,'admin','2026-07-01 21:34:37'),
(8,10,'appreciation','FYPES-APR-20260008','Certificate of Appreciation - FYP Evaluation Duty',NULL,'appreciation',NULL,1,'admin','2026-07-03 21:34:37'),
(9,28,'completion','FYPES-CERT-20260009','Certificate of Completion - Final Year Project','A','completion',NULL,1,'admin','2026-06-28 21:34:37'),
(10,38,'completion','FYPES-CERT-20260010','Certificate of Completion - Final Year Project','A','completion',NULL,1,'admin','2026-07-06 21:34:37'),
(11,23,'completion','FYPES-CERT-2026-2DE12BC8','Certificate of Completion - Final Year Project','A','completion',NULL,1,'admin','2026-07-09 06:22:41');
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chatbot_faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chatbot_faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT 'general',
  `audience` enum('all','student','lecturer','admin') NOT NULL DEFAULT 'all',
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chatbot_faqs` WRITE;
/*!40000 ALTER TABLE `chatbot_faqs` DISABLE KEYS */;
INSERT INTO `chatbot_faqs` VALUES
(1,'Submission','student','How do I submit my FYP documents?','Go to your Student Dashboard, click on \'Project Submission\', select the required chapter, then upload your file or paste a Google Doc link. You can re-upload before the deadline.','submit,upload,document,chapter',1,'2026-07-07 21:34:37'),
(2,'Submission','student','What file formats are accepted for submission?','Most categories accept PDF, DOCX, and PPTX files up to 50MB. Google Doc links are also accepted.','file format,pdf,size,google doc',1,'2026-07-07 21:34:37'),
(3,'Supervisor','student','How do I request a supervisor?','Navigate to \'My Supervisor\', browse available supervisors, then click \'Send Request\'. You\'ll be notified once the supervisor and coordinator respond.','supervisor,request,select',1,'2026-07-07 21:34:37'),
(4,'Supervisor','student','What happens if my supervisor rejects my request?','If rejected, you\'ll see the reason given and can browse the list again to request a different supervisor.','rejected,reject,supervisor,note',1,'2026-07-07 21:34:37'),
(5,'Panel','student','Who are my evaluation panels?','Check \'My Panel\' in your dashboard. Your Report Panel and Presentation Panel (plus your presentation date) appear there once your coordinator assigns them.','panel,report panel,presentation panel,date',1,'2026-07-07 21:34:37'),
(6,'Evaluation','student','How is my FYP evaluated?','Your supervisor, report panel, and presentation panel each evaluate you independently using their own rubric covering methodology, documentation, and presentation skills.','evaluation,marks,rubric',1,'2026-07-07 21:34:37'),
(7,'Evaluation','student','When will I see my evaluation results?','Results are released according to the Timeline set by your Programme Coordinator. Check \'Evaluation Results\' or the Timeline page.','results,marks,release',1,'2026-07-07 21:34:37'),
(8,'Progress','student','What is the weekly progress form?','Your coordinator uploads a progress report template under \'Progress Reports\'. Download it, fill it in, and submit it back before the deadline.','progress,weekly,form',1,'2026-07-07 21:34:37'),
(9,'Certificates','student','How do I download my completion certificate?','Once your FYP evaluation is finalized, go to \'Certificates\' and click Download. It includes your final grade and a verification code.','certificate,download,completion,grade',1,'2026-07-07 21:34:37'),
(10,'Account','student','Why can\'t I register with my Gmail address?','Only official UNISEL student emails (MatricNumber@student.unisel.edu.my) are accepted to verify enrollment.','register,email,gmail',1,'2026-07-07 21:34:37'),
(11,'Evaluation','lecturer','How do I evaluate a student?','Go to \'Evaluate\' under Supervision (for your supervisees) or under Panel Duties (for assigned panel students) and score against the rubric provided by the coordinator.','evaluate,rubric,score,mark',1,'2026-07-07 21:34:37'),
(12,'Rubrics','lecturer','Where can I see the marking rubric?','Rubrics set by your coordinator appear under \'Rubric Criteria\' in your dashboard before you evaluate any student.','rubric,criteria,marking',1,'2026-07-07 21:34:37'),
(13,'Requests','lecturer','How do I respond to a supervision request?','Go to \'Pending Requests\', accept or decline, and add a short note explaining your decision â€” the student and coordinator will both see it.','request,accept,decline,note',1,'2026-07-07 21:34:37'),
(14,'Coordination','lecturer','How do I generate an invitation letter?','Coordinators can go to \'Invitation Letters\', pick the lecturer and role, and generate â€” it appears in that lecturer\'s Certificates page.','invitation,letter,appointment,generate',1,'2026-07-07 21:34:37'),
(15,'General','all','What is FYPES?','FYPES (Final Year Project Evaluation System) is UNISEL\'s platform for managing FYP submissions, supervision, evaluation, and result processing for Diploma and Degree students.','fypes,about,system',1,'2026-07-07 21:34:37'),
(16,'Account','all','I forgot my password, what do I do?','Click \'Forgot Password\' on the login page and follow the instructions sent to your registered email.','password,forgot,reset',1,'2026-07-07 21:34:37');
/*!40000 ALTER TABLE `chatbot_faqs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chatbot_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chatbot_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `question` text NOT NULL,
  `matched_faq_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chatbot_logs` WRITE;
/*!40000 ALTER TABLE `chatbot_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatbot_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `evaluation_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluation_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(11) NOT NULL,
  `criteria_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `percentage_score` decimal(5,2) DEFAULT NULL COMMENT 'Calculated percentage based on rubric marks',
  PRIMARY KEY (`id`),
  KEY `evaluation_id` (`evaluation_id`),
  KEY `criteria_id` (`criteria_id`),
  CONSTRAINT `evaluation_scores_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluation_scores_ibfk_2` FOREIGN KEY (`criteria_id`) REFERENCES `rubric_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `evaluation_scores` WRITE;
/*!40000 ALTER TABLE `evaluation_scores` DISABLE KEYS */;
INSERT INTO `evaluation_scores` (`id`,`evaluation_id`,`criteria_id`,`score`) VALUES
(109,31,67,8.00),
(110,31,68,24.00),
(111,32,78,16.00),
(112,32,79,16.00),
(113,32,80,8.00),
(114,33,67,8.00),
(115,33,68,24.00),
(116,34,78,16.00),
(117,34,79,16.00),
(118,34,80,8.00),
(119,35,67,8.00),
(120,35,68,24.00),
(121,36,78,16.00),
(122,36,79,16.00),
(123,36,80,8.00),
(124,37,67,8.00),
(125,37,68,24.00),
(126,38,78,16.00),
(127,38,79,16.00),
(128,38,80,8.00),
(129,86,69,17.00),
(130,86,70,25.00);
/*!40000 ALTER TABLE `evaluation_scores` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `evaluator_id` int(11) NOT NULL,
  `evaluator_role` enum('supervisor','assessor','presentation_panel','report_panel','coordinator') NOT NULL,
  `rubric_id` int(11) NOT NULL,
  `total_score` decimal(5,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `evaluated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `rubric_id` (`rubric_id`),
  CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `evaluations` WRITE;
/*!40000 ALTER TABLE `evaluations` DISABLE KEYS */;
INSERT INTO `evaluations` VALUES
(31,2,11,'supervisor',16,32.00,'Good progress overall.','completed','2026-07-12 01:17:24','2026-07-17 01:17:01'),
(32,6,8,'supervisor',22,40.00,'Good progress overall.','completed','2026-07-12 01:17:24','2026-07-17 01:17:01'),
(33,7,11,'supervisor',16,32.00,'Good progress overall.','completed','2026-07-12 01:17:24','2026-07-17 01:17:01'),
(34,8,12,'supervisor',22,40.00,'Good progress overall.','completed','2026-07-12 01:17:24','2026-07-17 01:17:01'),
(35,11,15,'supervisor',16,32.00,'Good progress overall.','completed','2026-07-12 01:17:24','2026-07-17 01:17:01'),
(36,12,4,'supervisor',22,40.00,'Good progress overall.','completed','2026-07-12 01:17:24','2026-07-17 01:17:01'),
(37,13,11,'supervisor',16,32.00,'Good progress overall.','completed','2026-07-12 01:17:24','2026-07-17 01:17:01'),
(38,15,12,'supervisor',22,40.00,'Good progress overall.','completed','2026-07-12 01:17:24','2026-07-17 01:17:01'),
(39,16,14,'supervisor',19,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(40,17,17,'supervisor',13,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(41,19,17,'supervisor',13,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(42,20,9,'supervisor',13,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(43,21,5,'supervisor',13,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(44,22,13,'supervisor',13,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(45,23,5,'supervisor',13,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(46,27,10,'supervisor',19,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(47,29,9,'supervisor',13,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(62,2,1,'assessor',17,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(63,6,8,'assessor',23,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(64,8,8,'assessor',23,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(65,15,8,'assessor',23,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(66,12,12,'assessor',23,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(67,16,14,'assessor',20,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(68,27,14,'assessor',20,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(69,17,1,'coordinator',15,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(70,19,1,'coordinator',15,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(71,20,1,'coordinator',15,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(72,21,1,'coordinator',15,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(73,22,1,'coordinator',15,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(74,23,1,'coordinator',15,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(75,29,1,'coordinator',15,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(76,16,2,'coordinator',21,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(77,27,2,'coordinator',21,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(78,2,3,'coordinator',18,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(79,7,3,'coordinator',18,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(80,11,3,'coordinator',18,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(81,13,3,'coordinator',18,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(82,6,4,'coordinator',24,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(83,8,4,'coordinator',24,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(84,12,4,'coordinator',24,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(85,15,4,'coordinator',24,NULL,NULL,'pending',NULL,'2026-07-17 01:17:01'),
(86,21,5,'assessor',14,42.00,'Solid presentation','completed','2026-07-17 06:51:12','2026-07-17 06:50:47');
/*!40000 ALTER TABLE `evaluations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `lecturer_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lecturer_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lecturer_id` int(11) NOT NULL,
  `role_type` enum('coordinator','supervisor','assessor') NOT NULL,
  `programme_type` enum('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_role` (`lecturer_id`,`role_type`,`programme_type`),
  CONSTRAINT `lecturer_roles_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `lecturer_roles` WRITE;
/*!40000 ALTER TABLE `lecturer_roles` DISABLE KEYS */;
INSERT INTO `lecturer_roles` VALUES
(1,18,'supervisor','diploma_fyp2',1,'2026-07-07 21:34:37'),
(4,1,'coordinator','diploma_fyp1',1,'2026-07-07 21:34:37'),
(5,5,'supervisor','diploma_fyp1',1,'2026-07-07 21:34:37'),
(8,14,'supervisor','diploma_fyp2',1,'2026-07-07 21:34:37'),
(10,2,'supervisor','diploma_fyp2',1,'2026-07-07 21:34:37'),
(11,16,'supervisor','degree_fyp2',1,'2026-07-07 21:34:37'),
(12,1,'supervisor','diploma_fyp1',1,'2026-07-07 21:34:37'),
(13,9,'supervisor','diploma_fyp1',1,'2026-07-07 21:34:37'),
(14,10,'supervisor','diploma_fyp2',1,'2026-07-07 21:34:37'),
(15,15,'supervisor','degree_fyp1',1,'2026-07-07 21:34:37'),
(16,12,'supervisor','degree_fyp2',1,'2026-07-07 21:34:37'),
(17,11,'supervisor','degree_fyp1',1,'2026-07-07 21:34:37'),
(21,3,'coordinator','degree_fyp1',1,'2026-07-07 21:34:37'),
(22,8,'supervisor','degree_fyp2',1,'2026-07-07 21:34:37'),
(24,7,'supervisor','degree_fyp1',1,'2026-07-07 21:34:37'),
(25,6,'supervisor','diploma_fyp2',1,'2026-07-07 21:34:37'),
(28,17,'supervisor','diploma_fyp1',1,'2026-07-07 21:34:37'),
(29,19,'supervisor','degree_fyp1',1,'2026-07-07 21:34:37'),
(30,3,'supervisor','degree_fyp1',1,'2026-07-07 21:34:37'),
(31,13,'supervisor','diploma_fyp1',1,'2026-07-07 21:34:37'),
(32,4,'coordinator','degree_fyp2',1,'2026-07-07 21:34:37'),
(37,2,'coordinator','diploma_fyp2',1,'2026-07-07 21:34:37'),
(38,4,'supervisor','degree_fyp2',1,'2026-07-07 21:34:37'),
(39,10,'assessor','diploma_fyp2',1,'2026-07-16 19:09:43'),
(40,17,'assessor','diploma_fyp1',1,'2026-07-16 19:09:43'),
(41,7,'assessor','degree_fyp1',1,'2026-07-16 19:09:43'),
(42,6,'assessor','diploma_fyp2',1,'2026-07-16 19:09:43'),
(43,18,'assessor','diploma_fyp2',1,'2026-07-16 19:09:43'),
(44,1,'assessor','degree_fyp1',1,'2026-07-16 19:09:43'),
(45,5,'assessor','diploma_fyp1',1,'2026-07-16 19:09:43'),
(46,14,'assessor','diploma_fyp2',1,'2026-07-16 19:09:43'),
(47,12,'assessor','degree_fyp2',1,'2026-07-16 19:09:43'),
(48,11,'assessor','degree_fyp1',1,'2026-07-16 19:09:43'),
(49,2,'assessor','degree_fyp2',1,'2026-07-16 19:09:43'),
(50,8,'assessor','degree_fyp2',1,'2026-07-16 19:09:43');
/*!40000 ALTER TABLE `lecturer_roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `lecturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lecturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `faculty` varchar(150) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `research_interests` text DEFAULT NULL,
  `max_supervisees` int(11) DEFAULT 8,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `lecturers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `lecturers` WRITE;
/*!40000 ALTER TABLE `lecturers` DISABLE KEYS */;
INSERT INTO `lecturers` VALUES
(1,3,'Faculty of Computing, Visual Arts and Communication','Department of Cybersecurity','Block D, Level 1, Room 197','Mobile Application Development',6),
(2,4,'Faculty of Computing, Visual Arts and Communication','Department of Software Engineering','Block A, Level 1, Room 513','Creative Multimedia Technology',6),
(3,5,'Faculty of Computing, Visual Arts and Communication','Department of Computer Graphics & Animation','Block D, Level 4, Room 104','Human-Computer Interaction',6),
(4,6,'Faculty of Computing, Visual Arts and Communication','Department of Multimedia Computing','Block A, Level 2, Room 312','Game Development & Interactive Media',6),
(5,7,'Faculty of Computing, Visual Arts and Communication','Department of Creative Technology','Block C, Level 4, Room 356','Computer Graphics & Animation',10),
(6,8,'Faculty of Computing, Visual Arts and Communication','Department of Data Science','Block B, Level 4, Room 582','Mobile Application Development',6),
(7,9,'Faculty of Computing, Visual Arts and Communication','Department of Networking','Block A, Level 2, Room 333','Game Development & Interactive Media',10),
(8,10,'Faculty of Computing, Visual Arts and Communication','Department of Computer Science','Block B, Level 1, Room 384','Computer Graphics & Animation',6),
(9,11,'Faculty of Computing, Visual Arts and Communication','Department of Cybersecurity','Block A, Level 4, Room 595','Artificial Intelligence & Machine Learning',10),
(10,12,'Faculty of Computing, Visual Arts and Communication','Department of Software Engineering','Block C, Level 5, Room 422','Internet of Things (IoT)',10),
(11,13,'Faculty of Computing, Visual Arts and Communication','Department of Computer Graphics & Animation','Block D, Level 4, Room 389','Game Development & Interactive Media',8),
(12,14,'Faculty of Computing, Visual Arts and Communication','Department of Multimedia Computing','Block C, Level 2, Room 228','Computer Graphics & Animation',6),
(13,15,'Faculty of Computing, Visual Arts and Communication','Department of Creative Technology','Block D, Level 4, Room 297','Computer Vision',10),
(14,16,'Faculty of Computing, Visual Arts and Communication','Department of Data Science','Block A, Level 3, Room 521','Computer Graphics & Animation',10),
(15,17,'Faculty of Computing, Visual Arts and Communication','Department of Networking','Block C, Level 4, Room 381','Cybersecurity & Network Defense',10),
(16,18,'Faculty of Computing, Visual Arts and Communication','Department of Computer Science','Block C, Level 2, Room 289','Mobile Application Development',8),
(17,19,'Faculty of Computing, Visual Arts and Communication','Department of Cybersecurity','Block D, Level 2, Room 127','Mobile Application Development',10),
(18,20,'Faculty of Computing, Visual Arts and Communication','Department of Software Engineering','Block B, Level 4, Room 275','Human-Computer Interaction',6),
(19,21,'Faculty of Computing, Visual Arts and Communication','Department of Computer Graphics & Animation','Block B, Level 2, Room 143','Human-Computer Interaction',10),
(20,22,'Faculty of Computing, Visual Arts and Communication','Department of Multimedia Computing','Block C, Level 2, Room 517','Computer Graphics & Animation',8);
/*!40000 ALTER TABLE `lecturers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'general',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES
(1,23,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-04 21:34:37'),
(2,24,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(3,24,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-04 21:34:37'),
(4,25,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(5,25,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-03 21:34:37'),
(6,26,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(7,26,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-02 21:34:37'),
(8,27,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(9,27,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-06 21:34:37'),
(10,28,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(11,28,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-02 21:34:37'),
(12,29,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',0,'2026-06-19 21:34:37'),
(13,29,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-02 21:34:37'),
(14,30,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(15,30,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-06 21:34:37'),
(16,31,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-04 21:34:37'),
(17,32,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(18,32,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-05 21:34:37'),
(19,33,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',0,'2026-06-19 21:34:37'),
(20,33,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-03 21:34:37'),
(21,34,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(22,34,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-03 21:34:37'),
(23,35,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(24,35,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-04 21:34:37'),
(25,36,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(26,36,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-04 21:34:37'),
(27,37,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',0,'2026-06-19 21:34:37'),
(28,37,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-03 21:34:37'),
(29,38,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(30,38,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-04 21:34:37'),
(31,39,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(32,39,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-05 21:34:37'),
(33,40,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-02 21:34:37'),
(34,41,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(35,41,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-06 21:34:37'),
(36,42,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(37,42,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-06 21:34:37'),
(38,43,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(39,43,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-02 21:34:37'),
(40,44,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',0,'2026-06-19 21:34:37'),
(41,44,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-06 21:34:37'),
(42,45,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(43,45,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-04 21:34:37'),
(44,46,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(45,46,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-05 21:34:37'),
(46,47,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(47,47,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-03 21:34:37'),
(48,48,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-04 21:34:37'),
(49,49,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',1,'2026-06-19 21:34:37'),
(50,49,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',0,'2026-07-05 21:34:37'),
(51,50,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(52,50,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-04 21:34:37'),
(53,51,'Supervisor Request Accepted','Your supervisor request has been accepted and confirmed by the coordinator.','supervisor','my-supervisor',0,'2026-06-19 21:34:37'),
(54,51,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-06 21:34:37'),
(55,52,'Supervisor Request Sent','Your supervisor request has been submitted and is pending review.','supervisor','my-supervisor',1,'2026-07-05 21:34:37'),
(56,52,'Deadline Reminder','A submission deadline is approaching. Please check your project submission page.','deadline','submissions',1,'2026-07-06 21:34:37'),
(57,3,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(58,4,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(59,5,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(60,6,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(61,7,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(62,8,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(63,9,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(64,10,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(65,11,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(66,12,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(67,13,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(68,14,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(69,15,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(70,16,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(71,17,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(72,18,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(73,19,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(74,20,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(75,21,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(76,22,'System Announcement','Welcome to FYPES - Final Year Project Evaluation System for Session 2025/2026.','system',NULL,1,'2026-06-12 21:34:37'),
(77,5,'Supervision Awaiting Confirmation','Dr. Aina Sofea binti Zainal accepted a student and needs your confirmation.','supervision','supervision-oversight',0,'2026-07-09 06:20:51'),
(78,24,'Supervisor Request Accepted','Dr. Aina Sofea binti Zainal has accepted your supervision request.','supervisor','my-supervisor',0,'2026-07-09 06:20:51'),
(79,24,'Evaluation Panel Assigned','Your report/presentation panel has been assigned. Check My Panel for details.','panel','my-panel',0,'2026-07-09 06:21:30'),
(80,13,'Invitation Letter Ready','Your invitation letter (FYPES-INV-2026-6C423E40) is now available in your Certificates page.','letter','certificates',0,'2026-07-09 06:21:30'),
(81,23,'New Certificate Issued','You have received a new certificate: Certificate of Completion - Final Year Project','certificate','certificates',0,'2026-07-09 06:22:41'),
(82,23,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(83,24,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(84,25,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(85,26,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(86,27,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(87,28,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(88,29,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(89,30,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(90,31,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(91,32,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(92,33,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(93,34,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(94,35,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(95,36,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(96,37,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(97,38,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(98,39,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(99,40,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(100,41,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(101,42,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(102,43,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(103,44,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(104,45,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(105,46,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(106,47,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(107,48,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(108,49,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(109,50,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(110,51,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(111,52,'Test Announcement','This is a test broadcast message.','announcement',NULL,0,'2026-07-09 06:23:44'),
(112,7,'New Assessment Assigned','You have been assigned as Assessor for a student.','assessor','assigned-students',0,'2026-07-17 06:50:47'),
(113,43,'Assessor Assigned','Your Assessor has been assigned. Check My Assessor for details.','assessor','my-assessor',0,'2026-07-17 06:50:47'),
(114,43,'Evaluation Marks Released','One of your evaluations has been completed. Check your Evaluation Results.','evaluation','results',0,'2026-07-17 06:51:12'),
(115,24,'Report Reviewed','Dr. Aina Sofea binti Zainal has reviewed your report.','report','my-reports',0,'2026-07-17 06:51:50');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `progress_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `progress_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `programme_type` enum('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `progress_forms` WRITE;
/*!40000 ALTER TABLE `progress_forms` DISABLE KEYS */;
INSERT INTO `progress_forms` VALUES
(1,'diploma_fyp1','Week 3 Progress Report','Fill in your weekly progress: tasks completed, issues faced, and next steps.',NULL,3,'2026-07-15',1,'2026-07-07 21:34:37'),
(2,'diploma_fyp2','Week 4 Progress Report','Fill in your weekly progress: tasks completed, issues faced, and next steps.',NULL,4,'2026-07-16',2,'2026-07-07 21:34:37'),
(3,'degree_fyp1','Week 5 Progress Report','Fill in your weekly progress: tasks completed, issues faced, and next steps.',NULL,5,'2026-07-17',3,'2026-07-07 21:34:37'),
(4,'degree_fyp2','Week 6 Progress Report','Fill in your weekly progress: tasks completed, issues faced, and next steps.',NULL,6,'2026-07-18',4,'2026-07-07 21:34:37');
/*!40000 ALTER TABLE `progress_forms` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `progress_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `progress_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `progress_form_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `progress_form_id` (`progress_form_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `progress_submissions_ibfk_1` FOREIGN KEY (`progress_form_id`) REFERENCES `progress_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progress_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `progress_submissions` WRITE;
/*!40000 ALTER TABLE `progress_submissions` DISABLE KEYS */;
INSERT INTO `progress_submissions` VALUES
(1,4,6,'uploads/submissions/2023100006_progress.pdf','2023100006_progress.pdf','2026-07-02 21:34:37'),
(2,3,7,'uploads/submissions/2023100007_progress.pdf','2023100007_progress.pdf','2026-06-28 21:34:37'),
(3,3,10,'uploads/submissions/2023100010_progress.pdf','2023100010_progress.pdf','2026-07-01 21:34:37'),
(4,4,15,'uploads/submissions/2023100015_progress.pdf','2023100015_progress.pdf','2026-07-02 21:34:37'),
(5,1,17,'uploads/submissions/2023200017_progress.pdf','2023200017_progress.pdf','2026-07-06 21:34:37'),
(6,1,20,'uploads/submissions/2023200020_progress.pdf','2023200020_progress.pdf','2026-07-03 21:34:37'),
(7,1,26,'uploads/submissions/2023200026_progress.pdf','2023200026_progress.pdf','2026-07-03 21:34:37'),
(8,2,27,'uploads/submissions/2023200027_progress.pdf','2023200027_progress.pdf','2026-07-02 21:34:37'),
(9,1,28,'uploads/submissions/2023200028_progress.pdf','2023200028_progress.pdf','2026-06-27 21:34:37'),
(10,1,29,'uploads/submissions/2023200029_progress.pdf','2023200029_progress.pdf','2026-07-01 21:34:37');
/*!40000 ALTER TABLE `progress_submissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `rubric_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rubric_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rubric_id` int(11) NOT NULL,
  `criteria_name` varchar(200) NOT NULL,
  `weight` int(11) NOT NULL,
  `max_marks` int(11) DEFAULT 10 COMMENT 'Max marks for this criterion',
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `rubric_id` (`rubric_id`),
  CONSTRAINT `rubric_criteria_ibfk_1` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `rubric_criteria` WRITE;
/*!40000 ALTER TABLE `rubric_criteria` DISABLE KEYS */;
INSERT INTO `rubric_criteria` (`id`,`rubric_id`,`criteria_name`,`weight`,`sort_order`) VALUES
(65,13,'Project Progress',10,1),
(66,13,'Project Report',30,2),
(67,16,'Project Progress',10,1),
(68,16,'Project Report',30,2),
(69,14,'Project Presentation',20,1),
(70,14,'Project Report',30,2),
(71,17,'Project Presentation',20,1),
(72,17,'Project Report',30,2),
(73,15,'Project Progress',10,1),
(74,18,'Project Progress',10,1),
(75,19,'Project Report',20,1),
(76,19,'Project Product & Presentation',20,2),
(77,19,'Progress Report',10,3),
(78,22,'Project Report',20,1),
(79,22,'Project Product & Presentation',20,2),
(80,22,'Progress Report',10,3),
(81,20,'Project Report',10,1),
(82,20,'Project Product & Presentation',30,2),
(83,23,'Project Report',10,1),
(84,23,'Project Product & Presentation',30,2),
(85,21,'Progress Report',10,1),
(86,24,'Progress Report',10,1);
/*!40000 ALTER TABLE `rubric_criteria` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `rubrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rubrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `programme_type` enum('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  `evaluator_role` enum('supervisor','assessor','presentation_panel','report_panel','coordinator') NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `rubrics` WRITE;
/*!40000 ALTER TABLE `rubrics` DISABLE KEYS */;
INSERT INTO `rubrics` VALUES
(13,'diploma_fyp1','supervisor','FYP1 Assessment - Supervisor (40%)',1,'2026-07-16 19:10:05'),
(14,'diploma_fyp1','assessor','FYP1 Assessment - Assessor (50%)',1,'2026-07-16 19:10:05'),
(15,'diploma_fyp1','coordinator','FYP1 Assessment - Coordinator (10%)',1,'2026-07-16 19:10:05'),
(16,'degree_fyp1','supervisor','FYP1 Assessment - Supervisor (40%)',3,'2026-07-16 19:10:05'),
(17,'degree_fyp1','assessor','FYP1 Assessment - Assessor (50%)',3,'2026-07-16 19:10:05'),
(18,'degree_fyp1','coordinator','FYP1 Assessment - Coordinator (10%)',3,'2026-07-16 19:10:05'),
(19,'diploma_fyp2','supervisor','FYP2 Assessment - Supervisor (50%)',2,'2026-07-16 19:10:05'),
(20,'diploma_fyp2','assessor','FYP2 Assessment - Assessor (40%)',2,'2026-07-16 19:10:05'),
(21,'diploma_fyp2','coordinator','FYP2 Assessment - Coordinator (10%)',2,'2026-07-16 19:10:05'),
(22,'degree_fyp2','supervisor','FYP2 Assessment - Supervisor (50%)',4,'2026-07-16 19:10:05'),
(23,'degree_fyp2','assessor','FYP2 Assessment - Assessor (40%)',4,'2026-07-16 19:10:05'),
(24,'degree_fyp2','coordinator','FYP2 Assessment - Coordinator (10%)',4,'2026-07-16 19:10:05');
/*!40000 ALTER TABLE `rubrics` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `semesters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `semesters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `semesters` WRITE;
/*!40000 ALTER TABLE `semesters` DISABLE KEYS */;
INSERT INTO `semesters` VALUES
(1,'Semester 1, 2025/2026','2026-01-05','2026-05-15',0,'2026-07-07 21:34:37'),
(2,'Semester 2, 2025/2026','2026-06-01','2026-09-30',1,'2026-07-07 21:34:37');
/*!40000 ALTER TABLE `semesters` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `student_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `target_role` enum('supervisor','assessor') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `marks` decimal(5,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('submitted','reviewed') NOT NULL DEFAULT 'submitted',
  `reviewed_by` int(11) DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_reports_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `student_reports` WRITE;
/*!40000 ALTER TABLE `student_reports` DISABLE KEYS */;
INSERT INTO `student_reports` VALUES
(1,2,'supervisor','2023100002_report.pdf','uploads/submissions/2023100002_report.pdf',85.00,'Well organized report','reviewed',11,'2026-07-14 01:17:31','2026-07-17 06:51:50'),
(2,12,'assessor','2023100012_report.pdf','uploads/submissions/2023100012_report.pdf',NULL,NULL,'submitted',NULL,'2026-07-15 01:17:31',NULL);
/*!40000 ALTER TABLE `student_reports` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `matric_number` varchar(30) NOT NULL,
  `level` enum('diploma','degree') NOT NULL,
  `programme` varchar(150) DEFAULT NULL,
  `faculty` varchar(150) DEFAULT NULL,
  `programme_type` enum('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  `project_title` varchar(255) DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `assessor_id` int(11) DEFAULT NULL,
  `presentation_date` datetime DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matric_number` (`matric_number`),
  KEY `user_id` (`user_id`),
  KEY `supervisor_id` (`supervisor_id`),
  KEY `fk_students_assessor` (`assessor_id`),
  CONSTRAINT `fk_students_assessor` FOREIGN KEY (`assessor_id`) REFERENCES `lecturers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES
(1,23,'2023100001','degree','Bachelor of Multimedia Computing (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp2','AI-Powered Student Performance Prediction System',NULL,NULL,NULL,22,'male'),
(2,24,'2023100002','degree','Bachelor of Multimedia Computing (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','IoT-Based Smart Classroom Monitoring System',11,1,'2026-08-10 14:00:00',22,'male'),
(3,25,'2023100003','degree','Bachelor of Software Engineering (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','Blockchain-Based Secure Voting System',NULL,NULL,NULL,24,'female'),
(4,26,'2023100004','degree','Bachelor of Computer Science (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp2','NLP-Based Sentiment Analysis for Customer Reviews',NULL,NULL,NULL,21,'male'),
(5,27,'2023100005','degree','Bachelor of Multimedia Computing (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','Cloud-Based Learning Management System',NULL,NULL,NULL,21,'female'),
(6,28,'2023100006','degree','Bachelor of Computer Science (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp2','Mobile Health Tracking Application',8,8,'2026-08-09 14:00:00',23,'male'),
(7,29,'2023100007','degree','Bachelor of Computer Science (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','E-Commerce Recommendation Engine Using Machine Learning',11,NULL,NULL,23,'female'),
(8,30,'2023100008','degree','Bachelor of Multimedia Computing (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp2','Smart Parking Management System',12,8,'2026-08-08 14:00:00',23,'male'),
(9,31,'2023100009','degree','Bachelor of Multimedia Computing (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','Facial Recognition Attendance System',NULL,NULL,NULL,22,'female'),
(10,32,'2023100010','degree','Bachelor of Computer Science (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','Chatbot for University Enquiry System',NULL,NULL,NULL,22,'female'),
(11,33,'2023100011','degree','Bachelor of Software Engineering (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','Inventory Management System for SMEs',15,NULL,NULL,22,'male'),
(12,34,'2023100012','degree','Bachelor of Computer Science (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp2','Online Food Ordering & Delivery Platform',4,12,'2026-08-06 14:00:00',24,'male'),
(13,35,'2023100013','degree','Bachelor of Software Engineering (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','Predictive Maintenance System Using IoT Sensors',11,NULL,NULL,21,'male'),
(14,36,'2023100014','degree','Bachelor of Computer Science (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp1','Digital Financial Literacy Mobile App',NULL,NULL,NULL,21,'female'),
(15,37,'2023100015','degree','Bachelor of Multimedia Computing (Hons)','Faculty of Computing, Visual Arts and Communication','degree_fyp2','Automated Resume Screening System Using AI',12,8,'2026-08-10 11:00:00',21,'female'),
(16,38,'2023200016','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp2','Smart Waste Management System',14,14,'2026-08-11 09:00:00',21,'female'),
(17,39,'2023200017','diploma','Diploma in Creative Multimedia','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Virtual Reality Campus Tour Application',17,NULL,NULL,22,'female'),
(18,40,'2023200018','diploma','Diploma in Computer Science','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Fake News Detection Using Machine Learning',NULL,NULL,NULL,21,'female'),
(19,41,'2023200019','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Employee Performance Management System',17,NULL,NULL,20,'female'),
(20,42,'2023200020','diploma','Diploma in Computer Science','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Online Examination Proctoring System',9,NULL,NULL,19,'male'),
(21,43,'2023200021','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Personal Finance Budgeting Mobile App',5,5,'2026-08-15 10:00:00',22,'female'),
(22,44,'2023200022','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Hospital Queue Management System',13,NULL,NULL,19,'male'),
(23,45,'2023200023','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Crop Disease Detection Using Deep Learning',5,NULL,NULL,22,'male'),
(24,46,'2023200024','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Ride-Sharing Application for Campus',NULL,NULL,NULL,21,'male'),
(25,47,'2023200025','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp2','Digital Library Management System',NULL,NULL,NULL,20,'male'),
(26,48,'2023200026','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Real-Time Traffic Prediction System',NULL,NULL,NULL,21,'female'),
(27,49,'2023200027','diploma','Diploma in Computer Science','Faculty of Computing, Visual Arts and Communication','diploma_fyp2','Smart Agriculture Monitoring System',10,14,'2026-08-10 14:00:00',21,'female'),
(28,50,'2023200028','diploma','Diploma in Information Technology','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','Customer Churn Prediction System',NULL,NULL,NULL,22,'female'),
(29,51,'2023200029','diploma','Diploma in Creative Multimedia','Faculty of Computing, Visual Arts and Communication','diploma_fyp1','AI-Based Resume & Job Matching Platform',9,NULL,NULL,21,'female'),
(30,52,'2023200030','diploma','Diploma in Computer Science','Faculty of Computing, Visual Arts and Communication','diploma_fyp2','Home Automation System Using IoT',NULL,NULL,NULL,20,'female');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `submission_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `submission_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `programme_type` enum('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `info_file_path` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `submission_categories` WRITE;
/*!40000 ALTER TABLE `submission_categories` DISABLE KEYS */;
INSERT INTO `submission_categories` VALUES
(1,'diploma_fyp1','Chapter 1 Introduction','Background, problem statement, objectives, scope',2,'2026-07-10',NULL,1,'2026-07-07 21:34:37'),
(2,'diploma_fyp1','Chap 2 Literature Review','Review of related works and existing systems',3,'2026-08-13',NULL,1,'2026-07-07 21:34:37'),
(3,'diploma_fyp1','Chapter 3 Methodology','Research design and development methodology',4,'2026-07-16',NULL,1,'2026-07-07 21:34:37'),
(4,'diploma_fyp1','Chapter 4 Analysis','Requirements and system analysis',5,'2026-08-19',NULL,1,'2026-07-07 21:34:37'),
(5,'diploma_fyp1','Chapter 5 Design','System and architecture design',6,'2026-07-22',NULL,1,'2026-07-07 21:34:37'),
(6,'diploma_fyp2','Chap 6 Development and Analysis','System development, implementation and analysis of results',1,'2026-07-10',NULL,2,'2026-07-07 21:34:37'),
(7,'diploma_fyp2','Chapter 7 Testing','Testing strategy, test cases and results',2,'2026-08-13',NULL,2,'2026-07-07 21:34:37'),
(8,'diploma_fyp2','Chapter 8 Conclusion','Conclusion, limitations and future work',3,'2026-07-16',NULL,2,'2026-07-07 21:34:37'),
(9,'degree_fyp1','Chapter 1 Introduction','Background, problem statement, objectives, scope',2,'2026-07-10',NULL,3,'2026-07-07 21:34:37'),
(10,'degree_fyp1','Chap 2 Literature Review','Review of related works and existing systems',3,'2026-08-13',NULL,3,'2026-07-07 21:34:37'),
(11,'degree_fyp1','Chapter 3 Methodology','Research design and development methodology',4,'2026-07-16',NULL,3,'2026-07-07 21:34:37'),
(12,'degree_fyp1','Chapter 4 Analysis','Requirements and system analysis',5,'2026-08-19',NULL,3,'2026-07-07 21:34:37'),
(13,'degree_fyp1','Chapter 5 Design','System and architecture design',6,'2026-07-22',NULL,3,'2026-07-07 21:34:37'),
(14,'degree_fyp2','Chap 6 Development and Analysis','System development, implementation and analysis of results',1,'2026-07-10',NULL,4,'2026-07-07 21:34:37'),
(15,'degree_fyp2','Chapter 7 Testing','Testing strategy, test cases and results',2,'2026-08-13',NULL,4,'2026-07-07 21:34:37'),
(16,'degree_fyp2','Chapter 8 Conclusion','Conclusion, limitations and future work',3,'2026-07-16',NULL,4,'2026-07-07 21:34:37'),
(17,'diploma_fyp1','Proposal','Initial project proposal outlining problem statement and objectives',1,'2026-06-20',NULL,1,'2026-07-17 01:16:27'),
(18,'degree_fyp1','Proposal','Initial project proposal outlining problem statement and objectives',1,'2026-06-20',NULL,3,'2026-07-17 01:16:27');
/*!40000 ALTER TABLE `submission_categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `google_doc_link` varchar(500) DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `status` enum('submitted','under_review','approved','rejected') NOT NULL DEFAULT 'submitted',
  `remarks` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `submission_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
INSERT INTO `submissions` VALUES
(1,1,14,'2023100001_1.pdf','uploads/submissions/2023100001_1.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-19 21:34:37'),
(2,2,9,'2023100002_1.pdf','uploads/submissions/2023100002_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-30 21:34:37'),
(3,2,10,'2023100002_2.pdf','uploads/submissions/2023100002_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-30 21:34:37'),
(4,3,9,'2023100003_1.pdf','uploads/submissions/2023100003_1.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-25 21:34:37'),
(5,3,10,'2023100003_2.pdf','uploads/submissions/2023100003_2.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-05 21:34:37'),
(6,3,11,'2023100003_3.pdf','uploads/submissions/2023100003_3.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-19 21:34:37'),
(7,3,12,'2023100003_4.pdf','uploads/submissions/2023100003_4.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-28 21:34:37'),
(8,3,13,'2023100003_5.pdf','uploads/submissions/2023100003_5.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-22 21:34:37'),
(9,4,14,'2023100004_1.pdf','uploads/submissions/2023100004_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-04 21:34:37'),
(10,4,15,'2023100004_2.pdf','uploads/submissions/2023100004_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-07-05 21:34:37'),
(11,5,9,'2023100005_1.pdf','uploads/submissions/2023100005_1.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-18 21:34:37'),
(12,5,10,'2023100005_2.pdf','uploads/submissions/2023100005_2.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-21 21:34:37'),
(13,5,11,'2023100005_3.pdf','uploads/submissions/2023100005_3.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-14 21:34:37'),
(14,5,12,'2023100005_4.pdf','uploads/submissions/2023100005_4.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-25 21:34:37'),
(15,5,13,'2023100005_5.pdf','uploads/submissions/2023100005_5.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-29 21:34:37'),
(16,6,14,'2023100006_1.pdf','uploads/submissions/2023100006_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-22 21:34:37'),
(17,6,15,'2023100006_2.pdf','uploads/submissions/2023100006_2.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-14 21:34:37'),
(18,7,9,'2023100007_1.pdf','uploads/submissions/2023100007_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-02 21:34:37'),
(19,7,10,'2023100007_2.pdf','uploads/submissions/2023100007_2.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-15 21:34:37'),
(20,7,11,'2023100007_3.pdf','uploads/submissions/2023100007_3.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-10 21:34:37'),
(21,7,12,'2023100007_4.pdf','uploads/submissions/2023100007_4.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-10 21:34:37'),
(22,7,13,'2023100007_5.pdf','uploads/submissions/2023100007_5.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-15 21:34:37'),
(23,8,14,'2023100008_1.pdf','uploads/submissions/2023100008_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-16 21:34:37'),
(24,8,15,'2023100008_2.pdf','uploads/submissions/2023100008_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-07-01 21:34:37'),
(25,8,16,'2023100008_3.pdf','uploads/submissions/2023100008_3.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-25 21:34:37'),
(26,9,9,'2023100009_1.pdf','uploads/submissions/2023100009_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-10 21:34:37'),
(27,9,10,'2023100009_2.pdf','uploads/submissions/2023100009_2.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-13 21:34:37'),
(28,9,11,'2023100009_3.pdf','uploads/submissions/2023100009_3.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-04 21:34:37'),
(29,10,9,'2023100010_1.pdf','uploads/submissions/2023100010_1.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-11 21:34:37'),
(30,11,9,'2023100011_1.pdf','uploads/submissions/2023100011_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-30 21:34:37'),
(31,12,14,'2023100012_1.pdf','uploads/submissions/2023100012_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-25 21:34:37'),
(32,12,15,'2023100012_2.pdf','uploads/submissions/2023100012_2.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-21 21:34:37'),
(33,12,16,'2023100012_3.pdf','uploads/submissions/2023100012_3.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-07-05 21:34:37'),
(34,13,9,'2023100013_1.pdf','uploads/submissions/2023100013_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-27 21:34:37'),
(35,14,9,'2023100014_1.pdf','uploads/submissions/2023100014_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-23 21:34:37'),
(36,14,10,'2023100014_2.pdf','uploads/submissions/2023100014_2.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-27 21:34:37'),
(37,15,14,'2023100015_1.pdf','uploads/submissions/2023100015_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-19 21:34:37'),
(38,15,15,'2023100015_2.pdf','uploads/submissions/2023100015_2.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-07-01 21:34:37'),
(39,15,16,'2023100015_3.pdf','uploads/submissions/2023100015_3.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-23 21:34:37'),
(40,16,6,'2023200016_1.pdf','uploads/submissions/2023200016_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-23 21:34:37'),
(41,16,7,'2023200016_2.pdf','uploads/submissions/2023200016_2.pdf',NULL,1,'submitted',NULL,NULL,'2026-07-03 21:34:37'),
(42,16,8,'2023200016_3.pdf','uploads/submissions/2023200016_3.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-03 21:34:37'),
(43,17,1,'2023200017_1.pdf','uploads/submissions/2023200017_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-25 21:34:37'),
(44,17,2,'2023200017_2.pdf','uploads/submissions/2023200017_2.pdf',NULL,1,'submitted',NULL,NULL,'2026-07-01 21:34:37'),
(45,18,1,'2023200018_1.pdf','uploads/submissions/2023200018_1.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-26 21:34:37'),
(46,18,2,'2023200018_2.pdf','uploads/submissions/2023200018_2.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-02 21:34:37'),
(47,18,3,'2023200018_3.pdf','uploads/submissions/2023200018_3.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-24 21:34:37'),
(48,18,4,'2023200018_4.pdf','uploads/submissions/2023200018_4.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-03 21:34:37'),
(49,19,1,'2023200019_1.pdf','uploads/submissions/2023200019_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-07-03 21:34:37'),
(50,19,2,'2023200019_2.pdf','uploads/submissions/2023200019_2.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-08 21:34:37'),
(51,19,3,'2023200019_3.pdf','uploads/submissions/2023200019_3.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-09 21:34:37'),
(52,19,4,'2023200019_4.pdf','uploads/submissions/2023200019_4.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-02 21:34:37'),
(53,20,1,'2023200020_1.pdf','uploads/submissions/2023200020_1.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-08 21:34:37'),
(54,20,2,'2023200020_2.pdf','uploads/submissions/2023200020_2.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-23 21:34:37'),
(55,20,3,'2023200020_3.pdf','uploads/submissions/2023200020_3.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-08 21:34:37'),
(56,21,1,'2023200021_1.pdf','uploads/submissions/2023200021_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-05-09 21:34:37'),
(57,21,2,'2023200021_2.pdf','uploads/submissions/2023200021_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-01 21:34:37'),
(58,21,3,'2023200021_3.pdf','uploads/submissions/2023200021_3.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-01 21:34:37'),
(59,21,4,'2023200021_4.pdf','uploads/submissions/2023200021_4.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-19 21:34:37'),
(60,21,5,'2023200021_5.pdf','uploads/submissions/2023200021_5.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-26 21:34:37'),
(61,22,1,'2023200022_1.pdf','uploads/submissions/2023200022_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-25 21:34:37'),
(62,22,2,'2023200022_2.pdf','uploads/submissions/2023200022_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-25 21:34:37'),
(63,22,3,'2023200022_3.pdf','uploads/submissions/2023200022_3.pdf',NULL,1,'submitted',NULL,NULL,'2026-06-12 21:34:37'),
(64,23,1,'2023200023_1.pdf','uploads/submissions/2023200023_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-07-03 21:34:37'),
(65,23,2,'2023200023_2.pdf','uploads/submissions/2023200023_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-21 21:34:37'),
(66,23,3,'2023200023_3.pdf','uploads/submissions/2023200023_3.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-24 21:34:37'),
(67,23,4,'2023200023_4.pdf','uploads/submissions/2023200023_4.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-03 21:34:37'),
(68,24,1,'2023200024_1.pdf','uploads/submissions/2023200024_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-19 21:34:37'),
(69,24,2,'2023200024_2.pdf','uploads/submissions/2023200024_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-01 21:34:37'),
(70,25,6,'2023200025_1.pdf','uploads/submissions/2023200025_1.pdf',NULL,1,'submitted',NULL,NULL,'2026-07-04 21:34:37'),
(71,26,1,'2023200026_1.pdf','uploads/submissions/2023200026_1.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-25 21:34:37'),
(72,27,6,'2023200027_1.pdf','uploads/submissions/2023200027_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-09 21:34:37'),
(73,27,7,'2023200027_2.pdf','uploads/submissions/2023200027_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-16 21:34:37'),
(74,28,1,'2023200028_1.pdf','uploads/submissions/2023200028_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-16 21:34:37'),
(75,28,2,'2023200028_2.pdf','uploads/submissions/2023200028_2.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-09 21:34:37'),
(76,29,1,'2023200029_1.pdf','uploads/submissions/2023200029_1.pdf',NULL,1,'approved','Good work, well structured.',NULL,'2026-06-10 21:34:37'),
(77,29,2,'2023200029_2.pdf','uploads/submissions/2023200029_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-28 21:34:37'),
(78,29,3,'2023200029_3.pdf','uploads/submissions/2023200029_3.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-24 21:34:37'),
(79,30,6,'2023200030_1.pdf','uploads/submissions/2023200030_1.pdf',NULL,1,'submitted',NULL,NULL,'2026-05-21 21:34:37'),
(80,30,7,'2023200030_2.pdf','uploads/submissions/2023200030_2.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-06-03 21:34:37'),
(81,30,8,'2023200030_3.pdf','uploads/submissions/2023200030_3.pdf',NULL,1,'under_review','Please revise section 2.3',NULL,'2026-05-20 21:34:37'),
(82,1,6,'2023100001_ch1.pdf','uploads/submissions/2023100001_ch1.pdf',NULL,1,'approved','Well done, approved from last semester.',NULL,'2026-03-10 19:38:36'),
(83,1,7,'2023100001_ch2.pdf','uploads/submissions/2023100001_ch2.pdf',NULL,1,'approved','Good literature coverage.',NULL,'2026-03-20 19:38:36'),
(84,4,6,'2023100004_ch1.pdf','uploads/submissions/2023100004_ch1.pdf',NULL,1,'approved','Approved.',NULL,'2026-03-15 19:38:36');
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `supervisor_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supervisor_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `response_note` text DEFAULT NULL,
  `coordinator_status` enum('pending','confirmed','overridden') NOT NULL DEFAULT 'pending',
  `coordinator_note` text DEFAULT NULL,
  `coordinator_decided_by` int(11) DEFAULT NULL,
  `coordinator_decided_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `supervisor_id` (`supervisor_id`),
  CONSTRAINT `supervisor_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supervisor_requests_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `supervisor_requests` WRITE;
/*!40000 ALTER TABLE `supervisor_requests` DISABLE KEYS */;
INSERT INTO `supervisor_requests` VALUES
(1,2,11,'accepted','I would like to request your supervision for my FYP.','Great fit for my research area','confirmed',NULL,3,'2026-07-09 06:21:13','2026-07-05 21:34:37','2026-07-09 06:20:51'),
(2,3,7,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL),
(3,4,12,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL),
(4,5,19,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL),
(5,6,8,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(6,7,11,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(7,8,12,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(8,10,7,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL),
(9,11,15,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(10,12,4,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(11,13,11,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(12,14,19,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL),
(13,15,12,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(14,16,14,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(15,17,17,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(16,19,17,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(17,20,9,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(18,21,5,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(19,22,13,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(20,23,5,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(21,24,1,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL),
(22,25,2,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL),
(23,27,10,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(24,28,1,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL),
(25,29,9,'accepted','I would like to request your supervision for my FYP.','Happy to supervise this project.','confirmed','Approved - good fit for supervisor expertise.',1,'2026-06-20 21:34:37','2026-06-17 21:34:37','2026-06-19 21:34:37'),
(26,30,18,'pending','I would like to request your supervision for my FYP.',NULL,'pending',NULL,NULL,NULL,'2026-07-05 21:34:37',NULL);
/*!40000 ALTER TABLE `supervisor_requests` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES
('academic_session','2025/2026 Semester 2'),
('chatbot_enabled','1'),
('institution_name','Universiti Selangor (UNISEL)'),
('max_file_size_mb','50'),
('semester_end_date','2026-09-30'),
('semester_name','Semester 2, 2025/2026'),
('semester_start_date','2026-06-01'),
('system_name','FYPES - Final Year Project Evaluation System');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `timelines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `timelines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `programme_type` enum('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  `event_name` varchar(150) NOT NULL,
  `event_type` enum('submission','evaluation','result_release','presentation') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `timelines` WRITE;
/*!40000 ALTER TABLE `timelines` DISABLE KEYS */;
INSERT INTO `timelines` VALUES
(1,'diploma_fyp1','Proposal Submission Period','submission','2026-06-01','2026-07-10',1,'2026-07-07 21:34:37'),
(2,'diploma_fyp1','Proposal Defense Evaluation','evaluation','2026-07-11','2026-07-25',1,'2026-07-07 21:34:37'),
(3,'diploma_fyp1','Proposal Results Release','result_release','2026-07-30','2026-07-30',1,'2026-07-07 21:34:37'),
(4,'diploma_fyp2','Final Report Submission Period','submission','2026-06-15','2026-08-01',2,'2026-07-07 21:34:37'),
(5,'diploma_fyp2','Final Presentation Week','presentation','2026-08-05','2026-08-12',2,'2026-07-07 21:34:37'),
(6,'diploma_fyp2','Final Evaluation Period','evaluation','2026-08-05','2026-08-20',2,'2026-07-07 21:34:37'),
(7,'diploma_fyp2','Final Results Release','result_release','2026-08-28','2026-08-28',2,'2026-07-07 21:34:37'),
(8,'degree_fyp1','Proposal Submission Period','submission','2026-06-01','2026-07-10',3,'2026-07-07 21:34:37'),
(9,'degree_fyp1','Proposal Defense Evaluation','evaluation','2026-07-11','2026-07-25',3,'2026-07-07 21:34:37'),
(10,'degree_fyp1','Proposal Results Release','result_release','2026-07-30','2026-07-30',3,'2026-07-07 21:34:37'),
(11,'degree_fyp2','Final Report Submission Period','submission','2026-06-15','2026-08-01',4,'2026-07-07 21:34:37'),
(12,'degree_fyp2','Final Presentation Week','presentation','2026-08-05','2026-08-12',4,'2026-07-07 21:34:37'),
(13,'degree_fyp2','Final Evaluation Period','evaluation','2026-08-05','2026-08-20',4,'2026-07-07 21:34:37'),
(14,'degree_fyp2','Final Results Release','result_release','2026-08-28','2026-08-28',4,'2026-07-07 21:34:37');
/*!40000 ALTER TABLE `timelines` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('admin','lecturer','student') NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `email_verified` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','System Administrator','admin1@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','012-3456789',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(2,'admin','Deputy Administrator','admin2@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','012-3456790',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(3,'lecturer','Dr. Farah Nadia binti Zulkifli','farahn1@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','017-8867467',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(4,'lecturer','Dr. Kamal Hassan bin Ahmad','kamalh2@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','017-4323355',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(5,'lecturer','Prof. Dr. Siti Aminah binti Yusof','sitia3@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','014-7677378',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(6,'lecturer','Dr. Wei Ling Tan','weil4@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','011-8176405',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(7,'lecturer','Assoc. Prof. Dr. Rajesh Kumar','rajeshk5@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','018-8571418',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(8,'lecturer','Dr. Hawani binti Ismail','hawanib6@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','011-9854780',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(9,'lecturer','Dr. Azman bin Rahman','azmanb7@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','016-4381501',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(10,'lecturer','Dr. Nur Syafiqah binti Mohd Salleh','nurs8@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','014-9292407',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(11,'lecturer','Prof. Dr. Lim Chee Keong','limc9@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','019-4225110',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(12,'lecturer','Dr. Farid bin Othman','faridb10@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','017-6482447',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(13,'lecturer','Dr. Aina Sofea binti Zainal','ainas11@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','018-3154360',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(14,'lecturer','Dr. Vincent Raj a/l Anthony','vincentr12@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','017-8800516',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(15,'lecturer','Dr. Rosnah binti Ibrahim','rosnahb13@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','011-9763498',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(16,'lecturer','Assoc. Prof. Dr. Chong Wei Sheng','chongw14@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','014-5267196',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(17,'lecturer','Dr. Nurul Huda binti Kamarudin','nurulh15@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','018-8911794',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(18,'lecturer','Dr. Suresh a/l Muniandy','suresha16@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','018-5545103',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(19,'lecturer','Dr. Amirul Hakim bin Zolkefli','amirulh17@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','011-3973394',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(20,'lecturer','Dr. Christine Wong Mei Fong','christinew18@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','012-1649073',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(21,'lecturer','Dr. Zulaikha binti Hashim','zulaikhab19@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','017-5374000',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(22,'lecturer','Prof. Dr. Bala Krishnan a/l Muthu','balak20@unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','011-3966115',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(23,'student','Lee Zhi Xuan','2023100001@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','014-4383470',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(24,'student','Ahmad Rizal bin Zulkifli','2023100002@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','012-7003773',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(25,'student','Iman Syazwani binti Firdaus','2023100003@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','013-4701806',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(26,'student','Chan Kar Wei','2023100004@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','014-8310196',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(27,'student','Nurul Aisyah binti Hassan','2023100005@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','014-6169983',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(28,'student','Ng Wei Xuan','2023100006@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','016-4219156',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(29,'student','Yap Jia Ying','2023100007@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','015-5899881',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(30,'student','Mohd Amirul bin Rosli','2023100008@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','016-8717455',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(31,'student','Nithya a/p Ramesh','2023100009@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','014-6863567',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(32,'student','Nur Ain binti Shahrin','2023100010@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','011-4264776',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(33,'student','Vikram a/l Suresh','2023100011@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','017-7568805',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(34,'student','Faris Adnan bin Halim','2023100012@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','019-6808054',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(35,'student','Haziq Danial bin Rahim','2023100013@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','016-7868007',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(36,'student','Priya a/p Kumar','2023100014@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','013-5036710',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(37,'student','Siti Aminah binti Rahman','2023100015@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','019-6853848',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(38,'student','Tan Wei Lin','2023200016@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','016-2863636',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(39,'student','Ong Mei Xin','2023200017@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','014-6442073',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(40,'student','Wong Kah Yan','2023200018@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','011-2357421',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(41,'student','Lim Jia Hui','2023200019@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','019-7541735',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(42,'student','Arjun a/l Rajan','2023200020@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','016-2762211',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(43,'student','Aina Batrisyia binti Zainal','2023200021@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','015-3833137',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(44,'student','Muhammad Hafiz bin Ismail','2023200022@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','015-9379233',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(45,'student','Danish Haziq bin Othman','2023200023@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','015-3161723',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(46,'student','Teoh Wei Jian','2023200024@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','019-4105531',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(47,'student','Adam Fitri bin Yusof','2023200025@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','019-8831131',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(48,'student','Farah Izzati binti Salleh','2023200026@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','013-8377130',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(49,'student','Chong Yee Wei','2023200027@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','011-1260434',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(50,'student','Amirah Sofea binti Kamarudin','2023200028@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','013-6821030',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(51,'student','Divya a/p Naidu','2023200029@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','018-8815797',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37'),
(52,'student','Anis Nabilah binti Zulkarnain','2023200030@student.unisel.edu.my','$2b$10$/kMFbhyf2dvkQdsY8Z87c.PTK1gIaA.sg.jm1AUVfU9FoN2bj1fri','013-1442394',NULL,NULL,'active',1,NULL,'2026-07-07 21:34:37','2026-07-07 21:34:37');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================================
-- FYPES LOGIC CORRECTION MIGRATION (two independent panels + workflow controls)
-- Applied after the original verified demo-data import.
-- ============================================================================
SET FOREIGN_KEY_CHECKS=0;

-- One current semester is the source of truth.
UPDATE semesters SET is_current=0;
UPDATE semesters SET is_current=1 WHERE id=2;

-- New administrator-created accounts must replace their temporary password.
ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER email_verified;
UPDATE users SET must_change_password=1;

-- Stronger user/profile identity constraints.
ALTER TABLE lecturers ADD UNIQUE KEY uniq_lecturer_user (user_id);
ALTER TABLE students ADD UNIQUE KEY uniq_student_user (user_id);

-- Split the old assessor capability into Report Panel and Presentation Panel.
ALTER TABLE lecturer_roles MODIFY role_type ENUM('coordinator','supervisor','assessor','report_panel','presentation_panel') NOT NULL;
INSERT IGNORE INTO lecturer_roles (lecturer_id,role_type,programme_type,assigned_by,assigned_at)
SELECT lecturer_id,'presentation_panel',programme_type,assigned_by,assigned_at FROM lecturer_roles WHERE role_type='assessor';
UPDATE lecturer_roles SET role_type='report_panel' WHERE role_type='assessor';
-- Extra independent panel for Diploma FYP1 demo assignments.
INSERT IGNORE INTO lecturer_roles (lecturer_id,role_type,programme_type,assigned_by) VALUES (13,'presentation_panel','diploma_fyp1',1);
ALTER TABLE lecturer_roles MODIFY role_type ENUM('coordinator','supervisor','report_panel','presentation_panel') NOT NULL;

ALTER TABLE appointment_letters MODIFY role_type ENUM('supervisor','assessor','report_panel','presentation_panel') NOT NULL;
UPDATE appointment_letters SET role_type='report_panel' WHERE role_type='assessor';
ALTER TABLE appointment_letters MODIFY role_type ENUM('supervisor','report_panel','presentation_panel') NOT NULL;

-- Students now have two independent panel assignments.
ALTER TABLE students
  ADD COLUMN report_panel_id INT NULL AFTER supervisor_id,
  ADD COLUMN presentation_panel_id INT NULL AFTER report_panel_id,
  ADD COLUMN presentation_venue VARCHAR(150) NULL AFTER presentation_date,
  ADD COLUMN presentation_status ENUM('not_scheduled','scheduled','completed') NOT NULL DEFAULT 'not_scheduled' AFTER presentation_venue,
  ADD COLUMN semester_id INT NULL AFTER presentation_status;
UPDATE students SET report_panel_id=assessor_id, semester_id=2, presentation_status=IF(presentation_date IS NULL,'not_scheduled','scheduled');
-- Resolve old supervisor/assessor conflicts and assign distinct presentation panels.
UPDATE students SET report_panel_id=12, presentation_panel_id=2 WHERE id=6;
UPDATE students SET presentation_panel_id=7 WHERE id=2;
UPDATE students SET presentation_panel_id=2 WHERE id IN (8,12,15);
UPDATE students SET report_panel_id=10, presentation_panel_id=6 WHERE id=16;
UPDATE students SET report_panel_id=17, presentation_panel_id=13 WHERE id=21;
UPDATE students SET presentation_panel_id=6 WHERE id=27;
ALTER TABLE students DROP FOREIGN KEY fk_students_assessor;
ALTER TABLE students DROP INDEX fk_students_assessor;
ALTER TABLE students DROP COLUMN assessor_id;
ALTER TABLE students
  ADD KEY idx_students_report_panel (report_panel_id),
  ADD KEY idx_students_presentation_panel (presentation_panel_id),
  ADD KEY idx_students_semester (semester_id),
  ADD CONSTRAINT fk_students_supervisor FOREIGN KEY (supervisor_id) REFERENCES lecturers(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_students_report_panel FOREIGN KEY (report_panel_id) REFERENCES lecturers(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_students_presentation_panel FOREIGN KEY (presentation_panel_id) REFERENCES lecturers(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_students_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;

-- Split each old combined assessor rubric while preserving the official 100-mark total.
ALTER TABLE rubrics
  MODIFY evaluator_role ENUM('supervisor','assessor','report_panel','presentation_panel','coordinator') NOT NULL,
  ADD COLUMN version INT NOT NULL DEFAULT 1 AFTER name,
  ADD COLUMN is_locked TINYINT(1) NOT NULL DEFAULT 1 AFTER version,
  ADD COLUMN published_at DATETIME NULL AFTER is_locked;
INSERT INTO rubrics (id,programme_type,evaluator_role,name,version,is_locked,created_by,created_at) VALUES
(25,'diploma_fyp1','presentation_panel','FYP1 Assessment - Presentation Panel (20%)',1,1,1,'2026-07-16 19:10:05'),
(26,'degree_fyp1','presentation_panel','FYP1 Assessment - Presentation Panel (20%)',1,1,3,'2026-07-16 19:10:05'),
(27,'diploma_fyp2','presentation_panel','FYP2 Assessment - Presentation Panel (30%)',1,1,2,'2026-07-16 19:10:05'),
(28,'degree_fyp2','presentation_panel','FYP2 Assessment - Presentation Panel (30%)',1,1,4,'2026-07-16 19:10:05');
UPDATE rubrics SET evaluator_role='report_panel', name='FYP1 Assessment - Report Panel (30%)' WHERE id IN (14,17);
UPDATE rubrics SET evaluator_role='report_panel', name='FYP2 Assessment - Report Panel (10%)' WHERE id IN (20,23);
UPDATE rubric_criteria SET rubric_id=25 WHERE id=69;
UPDATE rubric_criteria SET rubric_id=26 WHERE id=71;
UPDATE rubric_criteria SET rubric_id=27 WHERE id=82;
UPDATE rubric_criteria SET rubric_id=28 WHERE id=84;
ALTER TABLE rubrics MODIFY evaluator_role ENUM('supervisor','report_panel','presentation_panel','coordinator') NOT NULL;
UPDATE rubrics SET published_at=created_at WHERE is_locked=1 AND published_at IS NULL;
ALTER TABLE rubrics ADD UNIQUE KEY uniq_rubric_version (programme_type,evaluator_role,version);

-- Evaluation lifecycle, ownership and one-current-evaluation constraints.
ALTER TABLE evaluations
  MODIFY evaluator_role ENUM('supervisor','assessor','report_panel','presentation_panel','coordinator') NOT NULL,
  MODIFY status ENUM('pending','completed','reopened') NOT NULL DEFAULT 'pending',
  ADD COLUMN locked_at DATETIME NULL AFTER evaluated_at,
  ADD COLUMN verified_by INT NULL AFTER locked_at,
  ADD COLUMN verified_at DATETIME NULL AFTER verified_by,
  ADD COLUMN semester_id INT NULL AFTER created_at;
UPDATE evaluations SET evaluator_role='report_panel' WHERE evaluator_role='assessor';
UPDATE evaluations SET rubric_id=14 WHERE evaluator_role='report_panel' AND student_id IN (17,19,20,21,22,23,29);
UPDATE evaluations SET rubric_id=17 WHERE evaluator_role='report_panel' AND student_id IN (2,7,11,13);
UPDATE evaluations SET rubric_id=20 WHERE evaluator_role='report_panel' AND student_id IN (16,27);
UPDATE evaluations SET rubric_id=23 WHERE evaluator_role='report_panel' AND student_id IN (6,8,12,15);
UPDATE evaluations SET evaluator_id=12 WHERE student_id=6 AND evaluator_role='report_panel';
UPDATE evaluations SET evaluator_id=10 WHERE student_id=16 AND evaluator_role='report_panel';
UPDATE evaluations SET evaluator_id=17 WHERE student_id=21 AND evaluator_role='report_panel';
UPDATE evaluations SET semester_id=2, locked_at=evaluated_at WHERE status='completed';

-- Create the second independent panel evaluation for every existing panel assignment.
INSERT INTO evaluations (student_id,evaluator_id,evaluator_role,rubric_id,total_score,comments,status,evaluated_at,locked_at,created_at,semester_id) VALUES
(2,7,'presentation_panel',26,NULL,NULL,'pending',NULL,NULL,'2026-07-17 01:17:01',2),
(6,2,'presentation_panel',28,NULL,NULL,'pending',NULL,NULL,'2026-07-17 01:17:01',2),
(8,2,'presentation_panel',28,NULL,NULL,'pending',NULL,NULL,'2026-07-17 01:17:01',2),
(12,2,'presentation_panel',28,NULL,NULL,'pending',NULL,NULL,'2026-07-17 01:17:01',2),
(15,2,'presentation_panel',28,NULL,NULL,'pending',NULL,NULL,'2026-07-17 01:17:01',2),
(16,6,'presentation_panel',27,NULL,NULL,'pending',NULL,NULL,'2026-07-17 01:17:01',2),
(21,13,'presentation_panel',25,NULL,NULL,'pending',NULL,NULL,'2026-07-17 06:50:47',2),
(27,6,'presentation_panel',27,NULL,NULL,'pending',NULL,NULL,'2026-07-17 01:17:01',2);
-- The original combined demo mark was recorded before the scheduled presentation; discard that presentation criterion and require a fresh panel evaluation.
DELETE FROM evaluation_scores WHERE id=129;
UPDATE evaluations SET total_score=25.00, comments='Solid report assessment' WHERE id=86;
ALTER TABLE evaluations MODIFY evaluator_role ENUM('supervisor','report_panel','presentation_panel','coordinator') NOT NULL;
ALTER TABLE evaluations
  ADD KEY idx_evaluations_evaluator (evaluator_id),
  ADD KEY idx_evaluations_semester (semester_id),
  ADD CONSTRAINT fk_evaluations_evaluator FOREIGN KEY (evaluator_id) REFERENCES lecturers(id) ON DELETE RESTRICT,
  ADD CONSTRAINT fk_evaluations_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
  ADD UNIQUE KEY uniq_student_semester_role (student_id,semester_id,evaluator_role);
ALTER TABLE evaluation_scores ADD UNIQUE KEY uniq_evaluation_criteria (evaluation_id,criteria_id);
UPDATE rubric_criteria SET max_marks=weight;
UPDATE evaluation_scores es JOIN rubric_criteria rc ON rc.id=es.criteria_id SET es.percentage_score=ROUND((es.score/NULLIF(rc.weight,0))*100,2);

-- Supervision requests are tied to the student's semester/stage.
ALTER TABLE supervisor_requests ADD COLUMN semester_id INT NULL AFTER responded_at;
UPDATE supervisor_requests SET semester_id=2;
ALTER TABLE supervisor_requests
  ADD KEY idx_supervisor_requests_semester (semester_id),
  ADD CONSTRAINT fk_supervisor_requests_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;

-- Reports go to the supervisor or Report Panel only.
ALTER TABLE student_reports MODIFY target_role ENUM('supervisor','assessor','report_panel') NOT NULL;
UPDATE student_reports SET target_role='report_panel' WHERE target_role='assessor';
ALTER TABLE student_reports MODIFY target_role ENUM('supervisor','report_panel') NOT NULL;
ALTER TABLE student_reports
  ADD COLUMN semester_id INT NULL AFTER reviewed_at,
  ADD KEY idx_student_reports_semester (semester_id),
  ADD CONSTRAINT fk_student_reports_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;
UPDATE student_reports SET semester_id=2;

-- Separate reviews prevent supervisor/panel feedback from overwriting each other.
DROP TABLE IF EXISTS submission_reviews;
CREATE TABLE submission_reviews (
  id INT NOT NULL AUTO_INCREMENT,
  submission_id INT NOT NULL,
  reviewer_id INT NOT NULL,
  reviewer_role ENUM('supervisor','report_panel') NOT NULL,
  status ENUM('under_review','approved','rejected') NOT NULL,
  remarks TEXT NULL,
  reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_submission_reviewer_role (submission_id,reviewer_role),
  KEY idx_submission_review_reviewer (reviewer_id),
  CONSTRAINT fk_submission_reviews_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
  CONSTRAINT fk_submission_reviews_reviewer FOREIGN KEY (reviewer_id) REFERENCES lecturers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO submission_reviews (submission_id,reviewer_id,reviewer_role,status,remarks,reviewed_at)
SELECT sub.id,sub.reviewed_by,
  CASE WHEN sub.reviewed_by=s.report_panel_id THEN 'report_panel' ELSE 'supervisor' END,
  CASE WHEN sub.status IN ('approved','rejected','under_review') THEN sub.status ELSE 'under_review' END,
  sub.remarks,sub.uploaded_at
FROM submissions sub JOIN students s ON s.id=sub.student_id WHERE sub.reviewed_by IS NOT NULL;
ALTER TABLE submissions ADD COLUMN semester_id INT NULL AFTER uploaded_at;
UPDATE submissions SET semester_id=2;
ALTER TABLE submissions ADD KEY idx_submissions_semester (semester_id), ADD CONSTRAINT fk_submissions_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;

-- Only one progress submission per student/form; replacements update the same record.
DELETE ps1 FROM progress_submissions ps1 JOIN progress_submissions ps2
  ON ps1.progress_form_id=ps2.progress_form_id AND ps1.student_id=ps2.student_id AND ps1.id<ps2.id;
ALTER TABLE progress_submissions ADD UNIQUE KEY uniq_progress_student_form (progress_form_id,student_id);

-- Immutable final-result snapshot and controlled release.
DROP TABLE IF EXISTS final_results;
CREATE TABLE final_results (
  id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  semester_id INT NULL,
  final_score DECIMAL(5,2) NOT NULL,
  grade ENUM('A','B','C','F') NOT NULL,
  status ENUM('finalized','released') NOT NULL DEFAULT 'finalized',
  finalized_by INT NOT NULL,
  finalized_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  released_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_final_result_student_semester (student_id,semester_id),
  CONSTRAINT fk_final_results_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_final_results_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
  CONSTRAINT fk_final_results_finalizer FOREIGN KEY (finalized_by) REFERENCES lecturers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certificates are student completion documents only; lecturers receive appointment or invitation letters.
DELETE FROM certificates;
ALTER TABLE certificates MODIFY cert_type ENUM('completion') NOT NULL DEFAULT 'completion';
ALTER TABLE certificates ADD COLUMN final_result_id INT NULL AFTER user_id, ADD COLUMN semester_id INT NULL AFTER issued_at;
UPDATE certificates SET semester_id=2;
ALTER TABLE certificates
  ADD KEY idx_cert_final_result (final_result_id),
  ADD KEY idx_cert_semester (semester_id),
  ADD CONSTRAINT fk_cert_final_result FOREIGN KEY (final_result_id) REFERENCES final_results(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_cert_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;

-- Semester ownership for academic configuration records.
ALTER TABLE submission_categories ADD COLUMN semester_id INT NULL AFTER created_at;
ALTER TABLE progress_forms ADD COLUMN semester_id INT NULL AFTER created_at;
ALTER TABLE timelines ADD COLUMN semester_id INT NULL AFTER created_at;
UPDATE submission_categories SET semester_id=2;
UPDATE progress_forms SET semester_id=2;
UPDATE timelines SET semester_id=2;
ALTER TABLE submission_categories
  ADD KEY idx_submission_categories_semester (semester_id),
  ADD CONSTRAINT fk_submission_categories_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;
ALTER TABLE progress_forms
  ADD KEY idx_progress_forms_semester (semester_id),
  ADD CONSTRAINT fk_progress_forms_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;
ALTER TABLE timelines
  ADD KEY idx_timelines_semester (semester_id),
  ADD CONSTRAINT fk_timelines_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;


-- Student stage history keeps past semester records separate from the current stage.
DROP TABLE IF EXISTS student_stage_history;
CREATE TABLE student_stage_history (
  id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  semester_id INT NOT NULL,
  programme_type ENUM('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  project_title VARCHAR(255) NULL,
  supervisor_id INT NULL,
  report_panel_id INT NULL,
  presentation_panel_id INT NULL,
  status ENUM('active','completed','promoted','withdrawn') NOT NULL DEFAULT 'active',
  started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ended_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_student_stage_semester (student_id,semester_id),
  KEY idx_stage_semester (semester_id),
  CONSTRAINT fk_stage_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_stage_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE RESTRICT,
  CONSTRAINT fk_stage_supervisor FOREIGN KEY (supervisor_id) REFERENCES lecturers(id) ON DELETE SET NULL,
  CONSTRAINT fk_stage_report_panel FOREIGN KEY (report_panel_id) REFERENCES lecturers(id) ON DELETE SET NULL,
  CONSTRAINT fk_stage_presentation_panel FOREIGN KEY (presentation_panel_id) REFERENCES lecturers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO student_stage_history (student_id,semester_id,programme_type,project_title,supervisor_id,report_panel_id,presentation_panel_id,status,started_at)
SELECT id,semester_id,programme_type,project_title,supervisor_id,report_panel_id,presentation_panel_id,'active','2026-06-01 00:00:00'
FROM students WHERE semester_id IS NOT NULL;

-- Lecturer letters record the generating user consistently (admin or coordinator user account).
ALTER TABLE appointment_letters
  ADD KEY idx_appointment_generated_by (generated_by),
  ADD CONSTRAINT fk_appointment_generated_by FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL;

-- Remove stale legacy wording from demonstration notifications and FAQ content.
UPDATE notifications SET title='New Report Panel Assignment',message='You have been assigned as Report Panel for a student.',type='panel',link='report-panel-students' WHERE id=112;
UPDATE notifications SET title='Panels Assigned',message='Your Report Panel and Presentation Panel have been assigned. Check My Panels for details.',type='panel',link='my-panels' WHERE id=113;
UPDATE notifications SET title='Panels Assigned',message='Your Report Panel and Presentation Panel have been assigned. Check My Panels for details.',link='my-panels' WHERE type='panel' AND link IN ('my-panel','assigned-students');
UPDATE notifications SET message='Your academic role letter is available on the Letters page.',link='invitation-letters' WHERE type='letter';
UPDATE audit_logs SET action='assign_panels',details=REPLACE(details,'Assessor','Report Panel') WHERE action='assign_assessor';
UPDATE chatbot_faqs SET question='Who are my evaluation panels?',answer='Open My Panels to see your independently assigned Report Panel, Presentation Panel, presentation date, venue, and status.',keywords='panel,report panel,presentation panel,date,venue' WHERE id=5;
UPDATE chatbot_faqs SET question='How do I generate a lecturer letter?',answer='Open Letters. Supervisors receive Appointment Letters, while Report Panel and Presentation Panel members receive Invitation Letters. The issued PDF appears on the recipient lecturer account.' WHERE id=14;
UPDATE chatbot_faqs SET answer='Email password reset is not enabled in this local installation. Contact the system administrator to reset your account securely.' WHERE id=16;

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================================
-- FYPES COMPLETE WORKFLOW EXTENSION (Google sign-in, global UI, email and
-- detailed non-grading rubrics). This section is applied on a fresh import.
-- ============================================================================
SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE users
  ADD COLUMN google_sub VARCHAR(255) NULL AFTER remember_token,
  ADD COLUMN last_google_login_at DATETIME NULL AFTER google_sub,
  ADD COLUMN temp_password_emailed_at DATETIME NULL AFTER last_google_login_at,
  ADD UNIQUE KEY uniq_users_google_sub (google_sub);

UPDATE lecturers SET max_supervisees=10;
ALTER TABLE lecturers MODIFY max_supervisees INT NOT NULL DEFAULT 10;

-- Academic role letters: Admin issues Coordinator letters; Coordinators may issue
-- Supervisor, Report Panel and Presentation Panel letters for programmes they manage.
DELETE FROM appointment_letters;
ALTER TABLE appointment_letters MODIFY role_type ENUM('coordinator','supervisor','report_panel','presentation_panel') NOT NULL;
ALTER TABLE appointment_letters ADD UNIQUE KEY uniq_appointment_role (lecturer_id,role_type,programme_type);
-- Coordinator appointment letters are issued explicitly by an administrator from the Letters page.

-- Lecturer deletion can preserve finalized academic snapshots.
ALTER TABLE final_results DROP FOREIGN KEY fk_final_results_finalizer;
ALTER TABLE final_results MODIFY finalized_by INT NULL;
ALTER TABLE final_results ADD CONSTRAINT fk_final_results_finalizer FOREIGN KEY (finalized_by) REFERENCES lecturers(id) ON DELETE SET NULL;

DROP TABLE IF EXISTS email_delivery_logs;
CREATE TABLE email_delivery_logs (
  id INT NOT NULL AUTO_INCREMENT,
  recipient VARCHAR(255) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  provider ENUM('smtp','php_mail') NOT NULL,
  status ENUM('sent','failed') NOT NULL,
  error_message TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_email_delivery_recipient (recipient),
  KEY idx_email_delivery_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS detailed_rubric_criteria;
DROP TABLE IF EXISTS detailed_rubrics;
CREATE TABLE detailed_rubrics (
  id INT NOT NULL AUTO_INCREMENT,
  programme_type ENUM('diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2') NOT NULL,
  evaluator_role ENUM('supervisor','coordinator','report_panel','presentation_panel') NOT NULL,
  title VARCHAR(200) NOT NULL,
  version VARCHAR(30) NOT NULL,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  created_by INT NULL,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_detailed_rubric_version (programme_type,evaluator_role,version),
  KEY idx_detailed_rubric_creator (created_by),
  CONSTRAINT fk_detailed_rubric_creator FOREIGN KEY (created_by) REFERENCES lecturers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detailed_rubric_criteria (
  id INT NOT NULL AUTO_INCREMENT,
  detailed_rubric_id INT NOT NULL,
  criterion_name VARCHAR(200) NOT NULL,
  description TEXT NULL,
  max_marks DECIMAL(5,2) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_detailed_criteria_rubric (detailed_rubric_id),
  CONSTRAINT fk_detailed_criteria_rubric FOREIGN KEY (detailed_rubric_id) REFERENCES detailed_rubrics(id) ON DELETE CASCADE,
  CONSTRAINT chk_detailed_criterion_marks CHECK (max_marks>=0 AND max_marks<=10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff-only student scoring against the published detailed rubric. These marks
-- are deliberately separate from the official weighted evaluation shown to students.
DROP TABLE IF EXISTS detailed_assessment_scores;
DROP TABLE IF EXISTS detailed_assessments;
CREATE TABLE detailed_assessments (
  id INT NOT NULL AUTO_INCREMENT,
  evaluation_id INT NOT NULL,
  detailed_rubric_id INT NOT NULL,
  total_score DECIMAL(7,2) NOT NULL DEFAULT 0,
  comments TEXT NULL,
  status ENUM('draft','completed') NOT NULL DEFAULT 'draft',
  assessed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_detailed_assessment_evaluation (evaluation_id),
  KEY idx_detailed_assessment_rubric (detailed_rubric_id),
  CONSTRAINT fk_detailed_assessment_evaluation FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
  CONSTRAINT fk_detailed_assessment_rubric FOREIGN KEY (detailed_rubric_id) REFERENCES detailed_rubrics(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detailed_assessment_scores (
  id INT NOT NULL AUTO_INCREMENT,
  detailed_assessment_id INT NOT NULL,
  detailed_criterion_id INT NOT NULL,
  score DECIMAL(5,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_detailed_assessment_criterion (detailed_assessment_id,detailed_criterion_id),
  KEY idx_detailed_score_criterion (detailed_criterion_id),
  CONSTRAINT fk_detailed_score_assessment FOREIGN KEY (detailed_assessment_id) REFERENCES detailed_assessments(id) ON DELETE CASCADE,
  CONSTRAINT fk_detailed_score_criterion FOREIGN KEY (detailed_criterion_id) REFERENCES detailed_rubric_criteria(id) ON DELETE RESTRICT,
  CONSTRAINT chk_detailed_assessment_score CHECK (score>=0 AND score<=10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO detailed_rubrics (id,programme_type,evaluator_role,title,version,status,created_by,published_at) VALUES
(1,'diploma_fyp1','supervisor','Diploma FYP 1 Supervisor Detailed Rubric','1.0','published',1,NOW()),
(2,'diploma_fyp1','coordinator','Diploma FYP 1 Coordinator Detailed Rubric','1.0','published',1,NOW()),
(3,'diploma_fyp1','report_panel','Diploma FYP 1 Report Panel Detailed Rubric','1.0','published',1,NOW()),
(4,'diploma_fyp1','presentation_panel','Diploma FYP 1 Presentation Panel Detailed Rubric','1.0','published',1,NOW()),
(5,'diploma_fyp2','supervisor','Diploma FYP 2 Supervisor Detailed Rubric','1.0','published',2,NOW()),
(6,'diploma_fyp2','coordinator','Diploma FYP 2 Coordinator Detailed Rubric','1.0','published',2,NOW()),
(7,'diploma_fyp2','report_panel','Diploma FYP 2 Report Panel Detailed Rubric','1.0','published',2,NOW()),
(8,'diploma_fyp2','presentation_panel','Diploma FYP 2 Presentation Panel Detailed Rubric','1.0','published',2,NOW()),
(9,'degree_fyp1','supervisor','Degree FYP 1 Supervisor Detailed Rubric','1.0','published',3,NOW()),
(10,'degree_fyp1','coordinator','Degree FYP 1 Coordinator Detailed Rubric','1.0','published',3,NOW()),
(11,'degree_fyp1','report_panel','Degree FYP 1 Report Panel Detailed Rubric','1.0','published',3,NOW()),
(12,'degree_fyp1','presentation_panel','Degree FYP 1 Presentation Panel Detailed Rubric','1.0','published',3,NOW()),
(13,'degree_fyp2','supervisor','Degree FYP 2 Supervisor Detailed Rubric','1.0','published',4,NOW()),
(14,'degree_fyp2','coordinator','Degree FYP 2 Coordinator Detailed Rubric','1.0','published',4,NOW()),
(15,'degree_fyp2','report_panel','Degree FYP 2 Report Panel Detailed Rubric','1.0','published',4,NOW()),
(16,'degree_fyp2','presentation_panel','Degree FYP 2 Presentation Panel Detailed Rubric','1.0','published',4,NOW());

INSERT INTO detailed_rubric_criteria (detailed_rubric_id,criterion_name,description,max_marks,sort_order)
SELECT id,'Understanding and preparation','Evidence of understanding, planning and preparation.',10,1 FROM detailed_rubrics
UNION ALL SELECT id,'Technical quality','Correctness, depth and suitability of the technical work.',10,2 FROM detailed_rubrics
UNION ALL SELECT id,'Documentation and communication','Clarity, organization and professional communication.',10,3 FROM detailed_rubrics
UNION ALL SELECT id,'Independent effort','Initiative, problem solving and response to feedback.',10,4 FROM detailed_rubrics
UNION ALL SELECT id,'Overall achievement','Overall quality against the expected FYP outcomes.',10,5 FROM detailed_rubrics;

ALTER TABLE system_settings ADD COLUMN description VARCHAR(255) NULL AFTER setting_value;
INSERT INTO system_settings (setting_key,setting_value,description) VALUES
('dark_mode','0','Global dashboard dark mode for every user'),
('google_client_id','','Google OAuth Web client ID'),
('google_client_secret','','Google OAuth Web client secret'),
('google_redirect_uri','http://localhost/fypes/auth/google-login.php','Google OAuth authorized redirect URI'),
('smtp_host','','SMTP server hostname'),
('smtp_port','587','SMTP port'),
('smtp_username','','SMTP login username'),
('smtp_password','','SMTP password or app password'),
('smtp_encryption','tls','SMTP encryption: tls, ssl or none'),
('mail_from_email','','Email sender address'),
('mail_from_name','FYPES','Email sender display name')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),description=VALUES(description);

-- Expanded role-aware assistant knowledge.
INSERT INTO chatbot_faqs (category,audience,question,answer,keywords,created_by) VALUES
('Account','student','How can I change a forgotten password?','On the login page choose Student, click Forgot Password, verify your institutional email and matric number, then set a new password.','forgot password reset student matric',1),
('Account','lecturer','How can I change a forgotten password?','On the login page choose Lecturer, click Forgot Password, verify your institutional email and registered phone number, then set a new password.','forgot password reset lecturer phone',1),
('Account','admin','How can I change a forgotten password?','On the login page choose Admin, click Forgot Password, verify your administrator email and registered phone number, then set a new password.','forgot password reset admin phone',1),
('Rubrics','lecturer','Where can I view my rubric criteria?','Open Rubric Criteria under the relevant Supervision, Report Panel, Presentation Panel, or Coordination sidebar section. It shows that role only, including the published evaluation rubric and detailed rubric version.','rubric detailed version supervisor coordinator panel',1),
('Supervision','student','How many students can a supervisor accept?','Every supervisor has a maximum of 10 supervisees. The available-supervisor list shows the current load, for example 8/10.','supervisor load capacity 10 supervisees',1),
('Deadlines','lecturer','How can a coordinator reopen an expired item?','Open Submission Categories or Progress Forms, choose Reopen on an expired item, and set a new future deadline. Existing student submissions are preserved.','reopen deadline category progress form coordinator',1),
('Google Login','all','Who can use Google login?','Existing verified UNISEL accounts can use Google sign-in. Student-domain accounts sign in only as students; staff-domain accounts sign in only as lecturers. Administrators use email and password.','google login unisel student lecturer domain',1);

SET FOREIGN_KEY_CHECKS=1;

-- Current deployment normalization.
UPDATE lecturers SET department='Department of Computing';

-- ============================================================================
-- SEMESTER PROGRESSION, RETAKE AND SEMESTER-SCOPED ROLE EXTENSION
-- ============================================================================
SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE semesters
  ADD COLUMN source_semester_id INT NULL AFTER is_current,
  ADD COLUMN rollover_prepared_at DATETIME NULL AFTER source_semester_id,
  ADD COLUMN activated_at DATETIME NULL AFTER rollover_prepared_at,
  ADD KEY idx_semesters_source (source_semester_id),
  ADD CONSTRAINT fk_semesters_source FOREIGN KEY (source_semester_id) REFERENCES semesters(id) ON DELETE SET NULL;
UPDATE semesters SET activated_at=start_date WHERE is_current=1 AND activated_at IS NULL;

-- Preserve a dedicated index for the lecturer_id foreign key before replacing the old unique key.
ALTER TABLE lecturer_roles ADD KEY idx_lecturer_roles_lecturer (lecturer_id);
ALTER TABLE lecturer_roles DROP INDEX uniq_role;
ALTER TABLE lecturer_roles ADD COLUMN semester_id INT NULL AFTER programme_type;
UPDATE lecturer_roles SET semester_id=2 WHERE semester_id IS NULL;
ALTER TABLE lecturer_roles MODIFY semester_id INT NOT NULL;
ALTER TABLE lecturer_roles
  ADD KEY idx_lecturer_roles_semester (semester_id),
  ADD CONSTRAINT fk_lecturer_roles_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
  ADD UNIQUE KEY uniq_role_semester (lecturer_id,role_type,programme_type,semester_id);

ALTER TABLE appointment_letters DROP INDEX uniq_appointment_role;
ALTER TABLE appointment_letters ADD COLUMN semester_id INT NULL AFTER programme_type;
UPDATE appointment_letters SET semester_id=2 WHERE semester_id IS NULL;
ALTER TABLE appointment_letters MODIFY semester_id INT NOT NULL;
ALTER TABLE appointment_letters
  ADD KEY idx_appointment_semester (semester_id),
  ADD CONSTRAINT fk_appointment_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
  ADD UNIQUE KEY uniq_appointment_role_semester (lecturer_id,role_type,programme_type,semester_id);

ALTER TABLE students
  ADD COLUMN academic_status ENUM('active','inactive','completed') NOT NULL DEFAULT 'active' AFTER semester_id,
  ADD COLUMN attempt_number INT NOT NULL DEFAULT 1 AFTER academic_status;
UPDATE students SET academic_status=IF(semester_id IS NULL,'inactive','active'),attempt_number=1;

ALTER TABLE student_stage_history
  MODIFY status ENUM('planned','active','completed','promoted','failed','incomplete','withdrawn') NOT NULL DEFAULT 'active',
  ADD COLUMN outcome ENUM('pending','pass','fail') NOT NULL DEFAULT 'pending' AFTER status,
  ADD COLUMN attempt_number INT NOT NULL DEFAULT 1 AFTER outcome,
  ADD COLUMN enrolment_type ENUM('new','promoted','retake','manual') NOT NULL DEFAULT 'new' AFTER attempt_number,
  ADD COLUMN source_semester_id INT NULL AFTER enrolment_type,
  ADD KEY idx_stage_source_semester (source_semester_id),
  ADD CONSTRAINT fk_stage_source_semester FOREIGN KEY (source_semester_id) REFERENCES semesters(id) ON DELETE SET NULL;
UPDATE student_stage_history SET outcome='pending',attempt_number=1,enrolment_type='new';



-- Student access closure is disabled until both dates are set by an administrator.
INSERT INTO system_settings (setting_key,setting_value,description) VALUES
('student_access_block_start','','Inclusive date when student access closes'),
('student_access_block_end','','Inclusive date when student access reopens after this date')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),description=VALUES(description);

SET FOREIGN_KEY_CHECKS=1;
