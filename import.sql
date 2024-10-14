-- Create the database if it does not exist
CREATE DATABASE IF NOT EXISTS stoiccad

-- Use the ncic_db database
USE stoiccad;

-- Table: roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(50) NOT NULL,
  `role_name` varchar(255) NOT NULL,
  `permlevel` int(11) NOT NULL CHECK (`permlevel` >= 1 AND `permlevel` <= 3),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- Table: users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `discord_id` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `discord_id` (`discord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: warrants
CREATE TABLE IF NOT EXISTS `warrants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `crime_description` text DEFAULT NULL,
  `warrant_issued` date DEFAULT NULL,
  `officer_name` varchar(100) DEFAULT NULL,
  `case_number` varchar(50) DEFAULT NULL,
  `status` enum('active','served','expired') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;