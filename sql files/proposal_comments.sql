-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2024 at 02:12 PM
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
-- Database: `qalamiquest`
--

-- --------------------------------------------------------

--
-- Table structure for table `proposal_comments`
--

CREATE TABLE `proposal_comments` (
  `id` int(11) NOT NULL,
  `proposal_id` int(11) NOT NULL,
  `section_name` varchar(255) NOT NULL,
  `subsection` varchar(255) DEFAULT NULL,
  `comment` text NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposal_comments`
--

INSERT INTO `proposal_comments` (`id`, `proposal_id`, `section_name`, `subsection`, `comment`, `lecturer_id`, `created_at`) VALUES
(18, 81, 'methodologies', 'research_design', 'awak', 13, '2024-11-17 17:24:19'),
(19, 81, 'methodologies', 'data_collection', 'ayu', 13, '2024-11-17 17:24:19'),
(20, 81, 'methodologies', 'data_analysis', 'dan', 13, '2024-11-17 17:24:19'),
(21, 81, 'methodologies', 'sampling_method', 'sopan', 13, '2024-11-17 17:24:19'),
(22, 81, 'methodologies', 'ethical_considerations', 'bijak', 13, '2024-11-17 17:24:19'),
(23, 81, 'references', NULL, 'bagus', 13, '2024-11-17 17:24:19'),
(24, 80, 'introduction', NULL, 'apa d', 13, '2024-11-18 09:44:54'),
(25, 80, 'problem_statement', NULL, 'assalamualaikum', 13, '2024-11-18 09:44:54'),
(26, 80, 'objectives', NULL, 'buat balik', 13, '2024-11-18 09:44:54'),
(27, 80, 'central_research_question', NULL, 'soalan apa ni', 13, '2024-11-18 09:44:54'),
(28, 80, 'research_questions', NULL, 'kenapa semua sama soalannya?', 13, '2024-11-18 09:44:54'),
(29, 80, 'preliminary_review', NULL, 'bagi review ', 13, '2024-11-18 09:44:54'),
(30, 80, 'methodologies', 'research_design', 'yapping', 13, '2024-11-18 09:44:54'),
(31, 80, 'methodologies', 'data_collection', 'more yapping', 13, '2024-11-18 09:44:54'),
(32, 80, 'methodologies', 'data_analysis', 'more more yapping', 13, '2024-11-18 09:44:54'),
(33, 80, 'methodologies', 'sampling_method', 'sigh', 13, '2024-11-18 09:44:54'),
(34, 80, 'methodologies', 'ethical_considerations', 'hmm', 13, '2024-11-18 09:44:54'),
(35, 80, 'references', NULL, 'nice', 13, '2024-11-18 09:44:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `proposal_comments`
--
ALTER TABLE `proposal_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_id` (`proposal_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `proposal_comments`
--
ALTER TABLE `proposal_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `proposal_comments`
--
ALTER TABLE `proposal_comments`
  ADD CONSTRAINT `proposal_comments_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`),
  ADD CONSTRAINT `proposal_comments_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `supervisors` (`supervisor_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
