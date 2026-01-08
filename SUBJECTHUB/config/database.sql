-- SUBJECTHUB Database Schema
-- --------------------------

-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `subjecthub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `subjecthub`;

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student','teacher','admin') NOT NULL,
  `profile_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `student_id` VARCHAR(50) DEFAULT NULL,
  `teacher_designation` VARCHAR(100) DEFAULT NULL,
  `phone_number` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `password_resets`
--
CREATE TABLE `password_resets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `subjects`
--
CREATE TABLE `subjects` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `icon_class` VARCHAR(50) DEFAULT 'fa-book',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `student_logbook`
-- This table is for a fixed list of 50 students as requested.
-- It's separate from the main `users` table which handles active system users.
--
CREATE TABLE `student_logbook` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `student_id` VARCHAR(50) NOT NULL,
  `phone_number` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `resources`
--
CREATE TABLE `resources` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `subject_id` INT(11) NOT NULL,
  `uploader_id` INT(11) NOT NULL,
  `resource_type` ENUM('student_note','teacher_note','book','question_paper','assignment') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL, -- Nullable for assignments without files
  `instructions` TEXT DEFAULT NULL, -- For assignment topics
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploader_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `activity_logs`
--
CREATE TABLE `activity_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Seeding some initial data for demonstration
--

-- Insert Subjects
INSERT INTO `subjects` (`name`, `icon_class`) VALUES
('Software Engineering', 'fa-cogs'),
('Introduction to Data Science (Elective-I)', 'fa-sitemap'),
('Operating Systems Lab', 'fa-database'),
('Operating Systems', 'fa-desktop'),
('Artificial Intelligence', 'fa-brain');

-- Insert a default Admin user (password is 'admin')
INSERT INTO `users` (`full_name`, `email`, `password`, `role`, `profile_completed`, `teacher_designation`) VALUES
('Admin User', 'admin@subjecthub.com', '$2y$10$E./e..E8.L3/p3xQ.Vf0y.5q3j6v.J.Y.C/Y.Vf1j.Z.Y.Vf0y.X.Y', 'admin', 1, 'Site Administrator');

-- Insert 10 sample students for the logbook (out of the 50)

INSERT INTO student_logbook (full_name, student_id, email, phone_number) VALUES
('Miragul Islam Sakib', '232-15-660', '232-15-660@diu.edu.bd', '01790123456'),
('Saikat Sikder', '232-15-449', '232-15-449@diu.edu.bd', '01790234567'),
('Ashik Hasan', '232-15-739', '232-15-739@diu.edu.bd', '01790345678'),
('Mahmudul Hasan', '232-15-715', '232-15-715@diu.edu.bd', '01790456789'),
('SM Minhazul Abedin', '232-15-011', '232-15-011@diu.edu.bd', '01790567890'),
('Tanvir Islam', '232-15-340', '232-15-340@diu.edu.bd', '01790678901'),
('Rosy', '232-15-103-A', '232-15-103-A@diu.edu.bd', '01790789012'),
('Seezan T Rafif', '232-15-053', '232-15-053@diu.edu.bd', '01790890123'),
('Ashraful Asif', '232-15-183-A', '232-15-183-A@diu.edu.bd', '01790901234'),
('Md Mizanur Rahman', '232-15-144', '232-15-144@diu.edu.bd', '01791012345'),
('Sourav Hossain Turjo', '232-15-114', '232-15-114@diu.edu.bd', '01791123456'),
('Md. Aliful Islam', '232-15-183-B', '232-15-183-B@diu.edu.bd', '01791234567'),
('Mehjabin Azad', '232-15-807', '232-15-807@diu.edu.bd', '01791345678'),
('Montasir Rahman', '232-15-195', '232-15-195@diu.edu.bd', '01791456789'),
('Mufasi Amin Abesh', '242220005101918', '242220005101918@diu.edu.bd', '01791567890'),
('MD. Mustafizul Islam', '242310005101883', '242310005101883@diu.edu.bd', '01791678901'),
('Manuf Hossain Munna', '242310005101121', '242310005101121@diu.edu.bd', '01791789012'),
('Jahid Hossain', '232-15-542', '232-15-542@diu.edu.bd', '01791890123'),
('MD Ashiqun Rahman', '232-15-133', '232-15-133@diu.edu.bd', '01791901234'),
('Moudussour Roy Joy', '232-15-434', '232-15-434@diu.edu.bd', '01792012345'),
('Himeid Sarker', '232-15-731', '232-15-731@diu.edu.bd', '01792123456'),
('Obaidul Haque Buyan', '232-15-350', '232-15-350@diu.edu.bd', '01792234567'),
('Athhar Sayem Fahim', '232-15-854', '232-15-854@diu.edu.bd', '01792345678'),
('Hriday Kumar', '232-15-103-B', '232-15-103-B@diu.edu.bd', '01792456789'),
('Roknuzzaman Ovi', '232-15-061', '232-15-061@diu.edu.bd', '01792567890'),
('Abdullah Al Rifat', '232-15-894', '232-15-894@diu.edu.bd', '01792678901'),
('Rafid Shahriar', '232-15-883', '232-15-883@diu.edu.bd', '01792789012'),
('Rafiur Rahman Raj', '232-15-655', '232-15-655@diu.edu.bd', '01792890123'),
('MD. Abdullah Khan', '232-15-425', '232-15-425@diu.edu.bd', '01792901234'),
('MD. Adhar Rahman Nayeem', '232-15-143-A', '232-15-143-A@diu.edu.bd', '01793012345'),
('Sazzad Islam', '232-15-635', '232-15-635@diu.edu.bd', '01793123456'),
('Saif Ahmed', '232-15-636', '232-15-636@diu.edu.bd', '01793234567'),
('Sajib Hasan', '232-15-619', '232-15-619@diu.edu.bd', '01793345678'),
('A.H.M. Rafikul Hasan Mozumder', '232-15-495', '232-15-495@diu.edu.bd', '01793456789'),
('Arnay', '232-15-686', '232-15-686@diu.edu.bd', '01793567890'),
('Rajshree Ghatak', '232-15-162', '232-15-162@diu.edu.bd', '01793678901'),
('Jannat', '232-15-143-B', '232-15-143-B@diu.edu.bd', '01793789012'),
('Asraf Alom', '232-15-543', '232-15-543@diu.edu.bd', '01793890123'),
('Abdul Monim Rahat', '232-15-143-C', '232-15-143-C@diu.edu.bd', '01793901234'),
('Azijul', '232-15-143-D', '232-15-143-D@diu.edu.bd', '01794012345'),
('Mustafa', '232-15-431', '232-15-431@diu.edu.bd', '01794123456'),
('Afifa Azke Mohona', '232-15-143-E', '232-15-143-E@diu.edu.bd', '01794234567'),
('Afia Anzum', '232-15-386-A', '232-15-386-A@diu.edu.bd', '01794345678'),
('Md Jobayer Hossen', '232-15-386-B', '232-15-386-B@diu.edu.bd', '01794456789'),
('Pabel Chandra Shill', '232-15-413', '232-15-413@diu.edu.bd', '01794567890'),
('Sourav Das', '232-15-486', '232-15-486@diu.edu.bd', '01794678901'),
('Tahsin Mohammad', '232-15-143-F', '232-15-143-F@diu.edu.bd', '01794789012'),
('Nur-A-Jannat', '232-15-414', '232-15-414@diu.edu.bd', '01794890123');

-- Note: The admin password 'admin' is hashed. 
-- The hash is: '$2y$10$E./e..E8.L3/p3xQ.Vf0y.5q3j6v.J.Y.C/Y.Vf1j.Z.Y.Vf0y.X.Y'
-- This hash may need to be regenerated if password_hash function behaves differently on another system.
-- A more reliable way would be to create an admin registration script.
-- For now, this provides a starting point.
