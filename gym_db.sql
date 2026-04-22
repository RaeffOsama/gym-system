-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2026 at 11:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diet_meals`
--

CREATE TABLE `diet_meals` (
  `id` int(11) NOT NULL,
  `diet_plan_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `day_number` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_meals`
--

INSERT INTO `diet_meals` (`id`, `diet_plan_id`, `meal_id`, `day_number`) VALUES
(6, 6, 1, 1),
(7, 6, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `diet_plans`
--

CREATE TABLE `diet_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nutritionist_id` int(11) NOT NULL,
  `goal` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_plans`
--

INSERT INTO `diet_plans` (`id`, `user_id`, `nutritionist_id`, `goal`, `description`) VALUES
(6, 1, 3, 'Weight Loss', 'High protein, low carb diet');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `booking_price` decimal(10,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `description`, `booking_price`, `status`) VALUES
(1, 'Treadmill', 'Pro Series', 5.00, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE `exercises` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `muscle_name` varchar(255) DEFAULT NULL,
  `equipment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`id`, `name`, `description`, `muscle_name`, `equipment_id`) VALUES
(1, 'Treadmill', 'leg workout', 'Leg', 1),
(2, 'Bench Press', 'Chest workout', 'Chest', 1);

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `preparation_steps` text DEFAULT NULL,
  `calories` int(11) DEFAULT NULL,
  `serving_size` int(11) DEFAULT NULL,
  `meal_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`id`, `name`, `preparation_steps`, `calories`, `serving_size`, `meal_type`) VALUES
(1, 'Oatmeal', 'Boil oats in milk', 300, 200, 'Breakfast'),
(2, 'Greek Yogurt with Honey', 'Add honey and fruits to yogurt', 180, 120, 'Breakfast');

-- --------------------------------------------------------

--
-- Table structure for table `specialist_profiles`
--

CREATE TABLE `specialist_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `bio` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bio`)),
  `achievements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`achievements`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialist_profiles`
--

INSERT INTO `specialist_profiles` (`id`, `user_id`, `experience_years`, `bio`, `achievements`) VALUES
(1, 3, 3, '{\"text\":\"Focused on functional training and injury recovery\"}', '[\"Physiotherapy Diploma\"]'),
(2, 4, 7, '{\"text\":\"Specialized in strength training and bodybuilding\"}', '{\"items\":null}'),
(3, 5, 5, '{\"text\":\"asdasda\"}', '[\"6klas asd\"]'),
(4, 6, 5, '{\"text\":\"...\"}', '{\"items\":null}'),
(5, 10, 3, '{\"text\":\"specialist in swimming events\"}', '{\"items\":null}'),
(6, 12, 5, '{\"text\":\"gym weights\"}', '{\"items\":null}');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `plan_type` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `description`, `plan_type`, `price`) VALUES
(1, 'diet', 'diet', 'diet', 12.00),
(2, 'gym', 'gym', 'gym', 13.00),
(3, 'both', 'both', 'both', 25.00);

-- --------------------------------------------------------

--
-- Table structure for table `trainer_sessions`
--

CREATE TABLE `trainer_sessions` (
  `id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_plans`
--

CREATE TABLE `training_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `goal` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_type` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `transaction_type`, `amount`, `created_at`) VALUES
(1, 9, 'Deposit / Top-up', 50.00, '2026-04-20 13:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_name` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `address`, `age`, `gender`, `password_hash`, `role_name`, `phone`, `balance`) VALUES
(1, 'Jane Doe', 'jane@example.com', '123 Main St', 25, 'female', '$2y$10$cZ2aNoHjw1Qz2Ea40vOKNuwETSvW3Ju0NXkNUmrwAFcm5wqF04I4G', 'user', '1234567890', 0.00),
(2, 'test', 'ahmedtttest@gmail.copm', '12312312', 25, 'male', '$2y$10$HR39HvOpzioHuOfPB7.kQ.7kKH2.HwOTLkpn0rgbOILZukR/3A5gC', 'user', '012312321323232', 0.00),
(3, 'samy', 'samy@gmail.com', '123sadas', 25, 'male', '$2y$10$21l0HeRLXt7WJvzq8bnTTunmgXnT9XTW3GXil3KHH4IaveByksaTC', 'user', '010090121200000', 0.00),
(4, 'Ahmed Hassan', 'ahmed@gym.com', NULL, NULL, NULL, '$2y$10$4AIZBuBtOCmY2EnkxOwq0eb/BbD.shZtP41qbEh953Zsp3Wufro/.', 'trainer', NULL, 0.00),
(5, 'sada', 'dasdsa@asda.com', 'asd12asd', 25, 'male', '$2y$10$EDG63yD2MZgJL3y.jEEalu0bdbVflNayL9hj43s/dCr9xuT5rOLTO', 'user', '0121212121211', 0.00),
(6, 'John Doe', 'john@gym.com', NULL, NULL, NULL, '$2y$10$xajpbhSpvnYfh4TWukNQ/usEdq20XjZoMRg/cd5FSkCwESR9.EipG', 'trainer', NULL, 0.00),
(7, 'Admin', 'admin@gym.com', NULL, NULL, NULL, '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'admin', NULL, 0.00),
(8, 'mariam', 'maskd@as.com', '.123123', 25, 'female', '$2y$10$L9sbKbFTOAEteWqFErKq5.KYjC5zdY/G0GSzFaRBnuvUJgI1ynPHu', 'user', '012121212120', 0.00),
(9, 'mohamed admin', 'moadmin@as.com', '.123123', 25, 'female', '$2y$10$udRKXouFqtxrRITUPYVhbOFWyGECBD9q3o9u/p.PoEEy8lRfaE3RO', 'admin', '012121212120', 50.00),
(10, 'sara', 'sara@mi.com', NULL, NULL, NULL, '$2y$10$CXwf8D.c6xla1IgUWlxsVO.m0J.Mn8HOTvL/Vgvpm/tw5nC9xcKVy', 'nutritionist', NULL, 0.00),
(11, 'kamal', 'kamal@lsak.com', 'asd12asd', 25, 'male', '$2y$10$00o/5SrBpAzfelqPoBg32esWdJfAGGMbQGjVNMEnWm7q9qMbZHN3m', 'user', '01212121111111', 0.00),
(12, 'nancy', 'nancy@gym.com', NULL, NULL, NULL, '$2y$10$A1e1hHH7M7HkNoLMt9XdFeb/Cp9yiwFoE9J1ceB6P38uPMY8LxFCa', 'trainer', NULL, 0.00),
(13, 'Lamin', 'saasd@asda.com', 'xcasd12', 25, 'male', '$2y$10$uvQUnLKIb6fpIkx8ctZN2u9.Z4gWHi.p0tkUEgCd2F4nv5l16G0gS', 'specilist', '...', 0.00),
(14, 'Lamin', 'saasad@asda.com', 'xcasd12', 25, 'male', '$2y$10$DbxNymEP5tbbZ2b7fYHJku57mThYmxtPCNqRLQ3JHyh.spvW32RwW', 'Specialist', '...', 0.00),
(15, 'saaed', 'saaed@outlook.com', 'alex', 25, 'male', '$2y$10$gWI66ZMaTnfh0txQO7UH8OGlVyMBDNEiBl1/XY5sc9NwCPrwp3bfG', 'admin', '...', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_plan_id` int(11) NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workout_exercises`
--

CREATE TABLE `workout_exercises` (
  `id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  `training_plan_id` int(11) NOT NULL,
  `day_number` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `sets` int(11) DEFAULT NULL,
  `reps` int(11) DEFAULT NULL,
  `rest_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bookings_equipment` (`equipment_id`),
  ADD KEY `fk_bookings_user` (`user_id`);

--
-- Indexes for table `diet_meals`
--
ALTER TABLE `diet_meals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_diet_meals_diet_plan` (`diet_plan_id`),
  ADD KEY `fk_diet_meals_meal` (`meal_id`);

--
-- Indexes for table `diet_plans`
--
ALTER TABLE `diet_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_diet_plans_user` (`user_id`),
  ADD KEY `fk_diet_plans_nutritionist` (`nutritionist_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exercises_equipment` (`equipment_id`);

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `specialist_profiles`
--
ALTER TABLE `specialist_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_specialist_profiles_user` (`user_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trainer_sessions`
--
ALTER TABLE `trainer_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `training_plans`
--
ALTER TABLE `training_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_training_plans_user` (`user_id`),
  ADD KEY `fk_training_plans_trainer` (`trainer_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transactions_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_subscriptions_user` (`user_id`),
  ADD KEY `fk_user_subscriptions_plan` (`subscription_plan_id`);

--
-- Indexes for table `workout_exercises`
--
ALTER TABLE `workout_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_workout_exercises_exercise` (`exercise_id`),
  ADD KEY `fk_workout_exercises_training_plan` (`training_plan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diet_meals`
--
ALTER TABLE `diet_meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `diet_plans`
--
ALTER TABLE `diet_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `specialist_profiles`
--
ALTER TABLE `specialist_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `trainer_sessions`
--
ALTER TABLE `trainer_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_plans`
--
ALTER TABLE `training_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workout_exercises`
--
ALTER TABLE `workout_exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diet_meals`
--
ALTER TABLE `diet_meals`
  ADD CONSTRAINT `fk_diet_meals_diet_plan` FOREIGN KEY (`diet_plan_id`) REFERENCES `diet_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_diet_meals_meal` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diet_plans`
--
ALTER TABLE `diet_plans`
  ADD CONSTRAINT `fk_diet_plans_nutritionist` FOREIGN KEY (`nutritionist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_diet_plans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exercises`
--
ALTER TABLE `exercises`
  ADD CONSTRAINT `fk_exercises_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `specialist_profiles`
--
ALTER TABLE `specialist_profiles`
  ADD CONSTRAINT `fk_specialist_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trainer_sessions`
--
ALTER TABLE `trainer_sessions`
  ADD CONSTRAINT `trainer_sessions_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainer_sessions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_plans`
--
ALTER TABLE `training_plans`
  ADD CONSTRAINT `fk_training_plans_trainer` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_training_plans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `fk_user_subscriptions_plan` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_subscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workout_exercises`
--
ALTER TABLE `workout_exercises`
  ADD CONSTRAINT `fk_workout_exercises_exercise` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_workout_exercises_training_plan` FOREIGN KEY (`training_plan_id`) REFERENCES `training_plans` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
