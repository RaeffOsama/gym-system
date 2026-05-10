-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 08:17 PM
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
  `nutritionist_id` int(11) DEFAULT NULL,
  `goal` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Pending Assign','Planning','Active') NOT NULL DEFAULT 'Pending Assign'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_plans`
--

INSERT INTO `diet_plans` (`id`, `user_id`, `nutritionist_id`, `goal`, `description`, `status`) VALUES
(6, 1, 3, 'Weight Loss', 'High protein, low carb diet', 'Active');

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
(1,  'Treadmill',          'Commercial-grade cardio treadmill with incline settings',          5.00, 'available'),
(2,  'Bench Press Station','Flat/incline/decline bench with Olympic bar and safety catches',   4.00, 'available'),
(3,  'Squat Rack',         'Full power rack with adjustable J-hooks and pull-up bar',          5.00, 'available'),
(4,  'Cable Machine',      'Dual-stack multi-function cable station',                          4.00, 'available'),
(5,  'Rowing Machine',     'Concept2 Model D air-resistance rower',                            4.50, 'available'),
(6,  'Pull-up Bar',        'Wall-mounted multi-grip pull-up and dip station',                  2.00, 'available'),
(7,  'Dumbbell Set',       'Full rack of rubber hex dumbbells 5 kg – 50 kg',                   3.00, 'available'),
(8,  'Leg Press Machine',  'Plate-loaded 45-degree leg press with calf-raise platform',        4.00, 'available'),
(9,  'Battle Ropes',       'Heavy-duty 15 m nylon battle ropes anchored to the wall',          3.00, 'available'),
(10, 'Smith Machine',      'Counterbalanced guided barbell on fixed vertical track',           5.00, 'unavailable');

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
-- Treadmill (equip 1)
(1,  'Steady-State Run',         'Maintain a comfortable pace for 20–40 min',                    'Legs / Cardio',     1),
(2,  'Interval Sprint',          'Alternate 30 s sprints with 90 s recovery walks',               'Legs / Cardio',     1),
(3,  'Incline Walk',             'Walk at 10–15 % incline to target glutes and calves',           'Glutes / Calves',   1),
-- Bench Press Station (equip 2)
(4,  'Flat Bench Press',         'Compound chest press from a flat bench',                        'Chest',             2),
(5,  'Incline Bench Press',      'Press on 30–45 ° incline to hit upper chest',                   'Upper Chest',       2),
(6,  'Decline Bench Press',      'Press on a decline to target lower chest fibres',               'Lower Chest',       2),
-- Squat Rack (equip 3)
(7,  'Back Squat',               'Barbell on traps, squat to parallel or below',                  'Quads / Glutes',    3),
(8,  'Front Squat',              'Barbell on front delts for a quad-dominant squat',               'Quads',             3),
(9,  'Overhead Press',           'Press barbell from shoulder height to full lockout',             'Shoulders',         3),
(10, 'Romanian Deadlift',        'Hip-hinge movement keeping legs nearly straight',               'Hamstrings',        3),
-- Cable Machine (equip 4)
(11, 'Cable Fly',                'Chest isolation using high-to-low cable path',                  'Chest',             4),
(12, 'Tricep Pushdown',          'Push cable handle down to full tricep extension',               'Triceps',           4),
(13, 'Seated Cable Row',         'Pull cable handle to abdomen with neutral grip',                'Back',              4),
(14, 'Face Pull',                'Pull rope to face level to work rear delts and rotator cuff',   'Rear Delts',        4),
(15, 'Cable Lateral Raise',      'Single-arm raise to shoulder height for medial delt',           'Shoulders',         4),
-- Rowing Machine (equip 5)
(16, 'Steady Row',               'Consistent stroke rate for 20–30 min aerobic row',              'Back / Legs / Core',5),
(17, 'Power Row Intervals',      '10 maximum-effort strokes followed by easy recovery',            'Back / Arms',       5),
-- Pull-up Bar (equip 6)
(18, 'Pull-up',                  'Overhand-grip vertical pull until chin clears the bar',         'Back / Biceps',     6),
(19, 'Chin-up',                  'Underhand grip for greater bicep involvement',                  'Biceps / Back',     6),
(20, 'Hanging Leg Raise',        'Hang from bar and raise straight legs to 90 °',                 'Core / Abs',        6),
(21, 'L-Sit Hold',               'Hold hips flexed at 90 ° while hanging for core strength',     'Core',              6),
-- Dumbbell Set (equip 7)
(22, 'Dumbbell Curl',            'Supinated curl from full extension to peak contraction',        'Biceps',            7),
(23, 'Lateral Raise',            'Raise dumbbells to shoulder height for width',                  'Shoulders',         7),
(24, 'Single-Arm Dumbbell Row',  'Brace on bench and row dumbbell to hip',                        'Back',              7),
(25, 'Goblet Squat',             'Hold dumbbell at chest and squat deep',                         'Quads / Glutes',    7),
(26, 'Dumbbell Shoulder Press',  'Press dumbbells from ear height to lockout',                    'Shoulders',         7),
-- Leg Press Machine (equip 8)
(27, 'Leg Press',                'Drive platform away targeting quads and glutes',                'Quads / Glutes',    8),
(28, 'Narrow-Stance Leg Press',  'Feet close together to emphasise outer quads',                  'Quads',             8),
(29, 'Calf Raise on Leg Press',  'Push through ball of foot at bottom of platform travel',       'Calves',            8),
-- Battle Ropes (equip 9)
(30, 'Alternating Waves',        'Rapidly alternate arms to send continuous waves down ropes',    'Shoulders / Core',  9),
(31, 'Rope Slams',               'Raise both ropes overhead and slam to ground explosively',      'Full Body',         9),
(32, 'Lateral Rope Shuffle',     'Side-shuffle while maintaining rope waves for cardio',          'Legs / Core',       9),
-- Smith Machine (equip 10)
(33, 'Smith Machine Squat',      'Guided squat with fixed bar path for form practice',            'Quads / Glutes',   10),
(34, 'Smith Machine Bench Press','Fixed-path chest press ideal for solo training',                'Chest',            10),
(35, 'Smith Machine Hip Thrust', 'Bar across hips, drive glutes upward from floor',               'Glutes',           10);

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
-- Breakfast
(1,  'Oatmeal with Banana',         'Boil oats in milk for 5 min, top with sliced banana and a drizzle of honey',                          320, 250,  'Breakfast'),
(2,  'Greek Yogurt with Honey',     'Spoon yogurt into bowl, add honey, top with mixed berries and granola',                               180, 120,  'Breakfast'),
(3,  'Scrambled Eggs on Toast',     'Whisk 3 eggs, cook in butter over low heat; serve on 2 slices wholegrain toast',                      420, 250,  'Breakfast'),
(4,  'Banana Protein Smoothie',     'Blend 1 banana, 1 scoop whey protein, 300 ml skimmed milk and ice until smooth',                     380, 350,  'Breakfast'),
(5,  'Avocado & Egg Toast',         'Toast sourdough, mash half avocado on top, place a poached egg, season with chilli flakes',           360, 200,  'Breakfast'),
-- Lunch
(6,  'Grilled Chicken Salad',       'Season chicken breast, grill 15 min; slice and serve over mixed greens with olive oil dressing',     430, 380,  'Lunch'),
(7,  'Brown Rice & Salmon',         'Cook rice (20 min); pan-fry salmon fillet 4 min per side with lemon and dill',                       550, 420,  'Lunch'),
(8,  'Tuna Whole-Wheat Wrap',       'Mix canned tuna with light mayo and diced celery; roll in wrap with lettuce and tomato',              460, 310,  'Lunch'),
(9,  'Red Lentil Soup',             'Sauté onion and garlic, add lentils and broth, simmer 25 min, blend half, season with cumin',        340, 350,  'Lunch'),
(10, 'Turkey & Quinoa Bowl',        'Cook quinoa 15 min; sauté ground turkey with peppers; combine and top with salsa',                   510, 400,  'Lunch'),
-- Dinner
(11, 'Grilled Sirloin & Vegetables','Season steak, grill 4 min per side to medium; steam broccoli and carrots alongside',                 620, 450,  'Dinner'),
(12, 'Baked Chicken Breast',        'Marinate chicken in olive oil, garlic and herbs; bake at 200 °C for 25–30 min',                      400, 300,  'Dinner'),
(13, 'Spaghetti with Tomato Sauce', 'Boil pasta al dente; simmer crushed tomatoes with garlic, basil and olive oil for 20 min',           570, 380,  'Dinner'),
(14, 'Grilled Tilapia with Quinoa', 'Season tilapia, grill 3 min per side; serve with cooked quinoa and steamed spinach',                 460, 380,  'Dinner'),
(15, 'Beef & Vegetable Stir-Fry',   'Slice beef thin, stir-fry 3 min with mixed veg and soy-ginger sauce; serve over jasmine rice',      530, 420,  'Dinner'),
-- Snack
(16, 'Mixed Nuts',                  'Pre-portion 30 g of almonds, walnuts and cashews into a small container',                            190,  30,  'Snack'),
(17, 'Protein Bar',                 'No preparation needed — consume directly from wrapper',                                              220,  60,  'Snack'),
(18, 'Cottage Cheese & Fruit',      'Spoon cottage cheese into bowl, top with diced pineapple or berries',                                160, 170,  'Snack'),
(19, 'Rice Cakes with Peanut Butter','Spread 1 tbsp natural peanut butter evenly on 2 plain rice cakes',                                  200,  80,  'Snack'),
(20, 'Apple & Almond Butter',       'Core and slice apple; serve with 1 tbsp almond butter for dipping',                                  180, 170,  'Snack');

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
(6, 12, 5, '{\"text\":\"gym weights\"}', '{\"items\":null}'),
(7,  20, 6, '{\"text\":\"Certified personal trainer specialising in strength and conditioning\"}', '[\"NASM-CPT\",\"CrossFit Level 2\"]'),
(8,  21, 4, '{\"text\":\"Expert in HIIT and functional movement patterns\"}', '[\"ACE Personal Trainer\",\"TRX Certified\"]'),
(9,  22, 8, '{\"text\":\"Competitive powerlifter turned coach with decade-long coaching record\"}', '[\"NSCA-CSCS\",\"IPF National Judge\"]'),
(10, 23, 5, '{\"text\":\"Specialises in mobility training and corrective exercise\"}', '[\"FMS Level 2\",\"Yoga Alliance RYT-200\"]'),
(11, 24, 7, '{\"text\":\"Clinical nutritionist focused on body-composition and sports performance\"}', '[\"Registered Dietitian\",\"ISSN Sport Nutritionist\"]'),
(12, 25, 9, '{\"text\":\"Specialises in plant-based sports nutrition and gut health\"}', '[\"MSc Human Nutrition\",\"BDA Member\"]'),
(13, 26, 5, '{\"text\":\"Experienced in weight-management and therapeutic diets\"}', '[\"Registered Dietitian\",\"Diabetes Care Certificate\"]'),
(14, 27, 10, '{\"text\":\"Performance nutritionist working with elite athletes across multiple sports\"}', '[\"PhD Sports Science\",\"IOC Diploma in Sports Nutrition\"]');

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
  `trainer_id` int(11) DEFAULT NULL,
  `goal` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Pending Assign','Planning','Active') NOT NULL DEFAULT 'Pending Assign'
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
(15, 'saaed', 'saaed@outlook.com', 'alex', 25, 'male', '$2y$10$gWI66ZMaTnfh0txQO7UH8OGlVyMBDNEiBl1/XY5sc9NwCPrwp3bfG', 'admin', '...', 0.00),
(16, 'Alex Carter',        'user1@test.com',    'Cairo',      28, 'male',   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'user',         '01000000001', 100.00),
(17, 'Mia Torres',         'user2@test.com',    'Alexandria', 24, 'female', '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'user',         '01000000002', 200.00),
(18, 'Omar Nasser',        'user3@test.com',    'Giza',       30, 'male',   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'user',         '01000000003',  50.00),
(19, 'Layla Hassan',       'user4@test.com',    'Mansoura',   22, 'female', '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'user',         '01000000004',   0.00),
(20, 'Coach Mike',         'trainer1@test.com', NULL,         32, 'male',   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'trainer',      '01100000001',   0.00),
(21, 'Coach Sarah',        'trainer2@test.com', NULL,         29, 'female', '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'trainer',      '01100000002',   0.00),
(22, 'Coach James',        'trainer3@test.com', NULL,         35, 'male',   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'trainer',      '01100000003',   0.00),
(23, 'Coach Rania',        'trainer4@test.com', NULL,         27, 'female', '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'trainer',      '01100000004',   0.00),
(24, 'Dr. Amira Saad',     'nutri1@test.com',   NULL,         34, 'female', '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'nutritionist', '01200000001',   0.00),
(25, 'Dr. Youssef Karim',  'nutri2@test.com',   NULL,         38, 'male',   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'nutritionist', '01200000002',   0.00),
(26, 'Dr. Nada El-Sayed',  'nutri3@test.com',   NULL,         31, 'female', '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'nutritionist', '01200000003',   0.00),
(27, 'Dr. Khaled Omar',    'nutri4@test.com',   NULL,         40, 'male',   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'nutritionist', '01200000004',   0.00),
(28, 'Admin One',          'admin1@test.com',   NULL,         NULL, NULL,   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'admin',        NULL,            0.00),
(29, 'Admin Two',          'admin2@test.com',   NULL,         NULL, NULL,   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'admin',        NULL,            0.00),
(30, 'Admin Three',        'admin3@test.com',   NULL,         NULL, NULL,   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'admin',        NULL,            0.00),
(31, 'Admin Four',         'admin4@test.com',   NULL,         NULL, NULL,   '$2y$10$RrKtanOgnbSJlob8xkrbTumoRrDsR6Vfd5acJoX7gGVAmYoRueeL.', 'admin',        NULL,            0.00);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `specialist_profiles`
--
ALTER TABLE `specialist_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
  ADD CONSTRAINT `fk_diet_plans_nutritionist` FOREIGN KEY (`nutritionist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
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
  ADD CONSTRAINT `fk_training_plans_trainer` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
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
