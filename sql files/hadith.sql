-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2024 at 08:33 AM
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
-- Table structure for table `hadith`
--

CREATE TABLE `hadith` (
  `id` int(11) NOT NULL,
  `arabic_text` text NOT NULL,
  `english_translation` text NOT NULL,
  `reference` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hadith`
--

INSERT INTO `hadith` (`id`, `arabic_text`, `english_translation`, `reference`) VALUES
(1, 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى.', 'Actions are but by intentions, and every man shall have only that which he intended.', 'Al-Bukhari and Muslim'),
(2, 'الدِّينُ النَّصِيحَةُ.', 'Religion is sincere advice.', 'Muslim'),
(3, 'مِنْ حُسْنِ إِسْلَامِ الْمَرْءِ تَرْكُهُ مَا لَا يَعْنِيهِ.', 'Part of the perfection of one’s Islam is his leaving that which does not concern him.', 'At-Tirmidhi'),
(4, 'لَا يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لِأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ.', 'None of you [truly] believes until he loves for his brother that which he loves for himself.', 'Al-Bukhari and Muslim'),
(5, 'مَنْ كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ.', 'Whoever believes in Allah and the Last Day, let him speak good or remain silent.', 'Al-Bukhari and Muslim'),
(6, 'لَا ضَرَرَ وَلَا ضِرَارَ.', 'There should be neither harming nor reciprocating harm.', 'Ibn Majah and others'),
(7, 'إِنَّ اللَّهَ كَتَبَ الإِحْسَانَ عَلَى كُلِّ شَيْءٍ.', 'Verily, Allah has prescribed excellence in all things.', 'Muslim'),
(8, 'إِنَّ اللَّهَ لَا يَنْظُرُ إِلَى صُوَرِكُمْ وَأَمْوَالِكُمْ، وَلَكِنْ يَنْظُرُ إِلَى قُلُوبِكُمْ وَأَعْمَالِكُمْ.', 'Allah does not look at your appearance or wealth, but rather He looks at your hearts and deeds.', 'Muslim'),
(9, 'الرَّاحِمُونَ يَرْحَمُهُمُ الرَّحْمَنُ.', 'Those who are merciful will be shown mercy by the Most Merciful.', 'At-Tirmidhi'),
(10, 'مَثَلُ الْمُؤْمِنِينَ فِي تَوَادِّهِمْ وَتَرَاحُمِهِمْ كَمَثَلِ الْجَسَدِ.', 'The believers are like a single body in their mutual love and compassion.', 'Al-Bukhari and Muslim');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hadith`
--
ALTER TABLE `hadith`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hadith`
--
ALTER TABLE `hadith`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
