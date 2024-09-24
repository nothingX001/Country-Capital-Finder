-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 24, 2024 at 03:51 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `country_searches`
--

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `country_name` varchar(255) NOT NULL,
  `capital_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `country_name`, `capital_name`) VALUES
(1, 'Afghanistan', 'Kabul'),
(2, 'Albania', 'Tirana'),
(3, 'Algeria', 'Algiers'),
(4, 'Andorra', 'Andorra la Vella'),
(5, 'Angola', 'Luanda'),
(6, 'Antigua and Barbuda', 'Saint John\'s'),
(7, 'Argentina', 'Buenos Aires'),
(8, 'Armenia', 'Yerevan'),
(9, 'Australia', 'Canberra'),
(10, 'Austria', 'Vienna'),
(11, 'Azerbaijan', 'Baku'),
(12, 'Bahamas', 'Nassau'),
(13, 'Bahrain', 'Manama'),
(14, 'Bangladesh', 'Dhaka'),
(15, 'Barbados', 'Bridgetown'),
(16, 'Belarus', 'Minsk'),
(17, 'Belgium', 'Brussels'),
(18, 'Belize', 'Belmopan'),
(19, 'Benin', 'Porto-Novo'),
(20, 'Bhutan', 'Thimphu'),
(21, 'Bolivia', 'Sucre'),
(22, 'Bosnia and Herzegovina', 'Sarajevo'),
(23, 'Botswana', 'Gaborone'),
(24, 'Brazil', 'Brasilia'),
(25, 'Brunei', 'Bandar Seri Begawan'),
(26, 'Bulgaria', 'Sofia'),
(27, 'Burkina Faso', 'Ouagadougou'),
(28, 'Burundi', 'Gitega'),
(29, 'Cabo Verde', 'Praia'),
(30, 'Cambodia', 'Phnom Penh'),
(31, 'Cameroon', 'Yaoundé'),
(32, 'Canada', 'Ottawa'),
(33, 'Central African Republic', 'Bangui'),
(34, 'Chad', 'N\'Djamena'),
(35, 'Chile', 'Santiago'),
(36, 'China', 'Beijing'),
(37, 'Colombia', 'Bogotá'),
(38, 'Comoros', 'Moroni'),
(39, 'Democratic Republic of the Congo', 'Kinshasa'),
(40, 'Republic of the Congo', 'Brazzaville'),
(41, 'Costa Rica', 'San José'),
(42, 'Croatia', 'Zagreb'),
(43, 'Cuba', 'Havana'),
(44, 'Cyprus', 'Nicosia'),
(45, 'Czech Republic', 'Prague'),
(46, 'Denmark', 'Copenhagen'),
(47, 'Djibouti', 'Djibouti'),
(48, 'Dominica', 'Roseau'),
(49, 'Dominican Republic', 'Santo Domingo'),
(50, 'East Timor (Timor-Leste)', 'Dili'),
(51, 'Ecuador', 'Quito'),
(52, 'Egypt', 'Cairo'),
(53, 'El Salvador', 'San Salvador'),
(54, 'Equatorial Guinea', 'Malabo'),
(55, 'Eritrea', 'Asmara'),
(56, 'Estonia', 'Tallinn'),
(57, 'Eswatini', 'Mbabane'),
(58, 'Ethiopia', 'Addis Ababa'),
(59, 'Fiji', 'Suva'),
(60, 'Finland', 'Helsinki'),
(61, 'France', 'Paris'),
(62, 'Gabon', 'Libreville'),
(63, 'Gambia', 'Banjul'),
(64, 'Georgia', 'Tbilisi'),
(65, 'Germany', 'Berlin'),
(66, 'Ghana', 'Accra'),
(67, 'Greece', 'Athens'),
(68, 'Grenada', 'Saint George\'s'),
(69, 'Guatemala', 'Guatemala City'),
(70, 'Guinea', 'Conakry'),
(71, 'Guinea-Bissau', 'Bissau'),
(72, 'Guyana', 'Georgetown'),
(73, 'Haiti', 'Port-au-Prince'),
(74, 'Honduras', 'Tegucigalpa'),
(75, 'Hungary', 'Budapest'),
(76, 'Iceland', 'Reykjavik'),
(77, 'India', 'New Delhi'),
(78, 'Indonesia', 'Jakarta'),
(79, 'Iran', 'Tehran'),
(80, 'Iraq', 'Baghdad'),
(81, 'Ireland', 'Dublin'),
(82, 'Israel', 'Jerusalem'),
(83, 'Italy', 'Rome'),
(84, 'Jamaica', 'Kingston'),
(85, 'Japan', 'Tokyo'),
(86, 'Jordan', 'Amman'),
(87, 'Kazakhstan', 'Astana'),
(88, 'Kenya', 'Nairobi'),
(89, 'Kiribati', 'South Tarawa'),
(90, 'North Korea', 'Pyongyang'),
(91, 'South Korea', 'Seoul'),
(92, 'Kosovo', 'Pristina'),
(93, 'Kuwait', 'Kuwait City'),
(94, 'Kyrgyzstan', 'Bishkek'),
(95, 'Laos', 'Vientiane'),
(96, 'Latvia', 'Riga'),
(97, 'Lebanon', 'Beirut'),
(98, 'Lesotho', 'Maseru'),
(99, 'Liberia', 'Monrovia'),
(100, 'Libya', 'Tripoli'),
(101, 'Liechtenstein', 'Vaduz'),
(102, 'Lithuania', 'Vilnius'),
(103, 'Luxembourg', 'Luxembourg'),
(104, 'Madagascar', 'Antananarivo'),
(105, 'Malawi', 'Lilongwe'),
(106, 'Malaysia', 'Kuala Lumpur'),
(107, 'Maldives', 'Malé'),
(108, 'Mali', 'Bamako'),
(109, 'Malta', 'Valletta'),
(110, 'Marshall Islands', 'Majuro'),
(111, 'Mauritania', 'Nouakchott'),
(112, 'Mauritius', 'Port Louis'),
(113, 'Mexico', 'Mexico City'),
(114, 'Micronesia', 'Palikir'),
(115, 'Moldova', 'Chisinau'),
(116, 'Monaco', 'Monaco'),
(117, 'Mongolia', 'Ulaanbaatar'),
(118, 'Montenegro', 'Podgorica'),
(119, 'Morocco', 'Rabat'),
(120, 'Mozambique', 'Maputo'),
(121, 'Myanmar', 'Naypyidaw'),
(122, 'Namibia', 'Windhoek'),
(123, 'Nauru', 'Yaren'),
(124, 'Nepal', 'Kathmandu'),
(125, 'Netherlands', 'Amsterdam'),
(126, 'New Zealand', 'Wellington'),
(127, 'Nicaragua', 'Managua'),
(128, 'Niger', 'Niamey'),
(129, 'Nigeria', 'Abuja'),
(130, 'North Macedonia', 'Skopje'),
(131, 'Norway', 'Oslo'),
(132, 'Oman', 'Muscat'),
(133, 'Pakistan', 'Islamabad'),
(134, 'Palau', 'Ngerulmud'),
(135, 'Panama', 'Panama City'),
(136, 'Papua New Guinea', 'Port Moresby'),
(137, 'Paraguay', 'Asunción'),
(138, 'Peru', 'Lima'),
(139, 'Philippines', 'Manila'),
(140, 'Poland', 'Warsaw'),
(141, 'Portugal', 'Lisbon'),
(142, 'Qatar', 'Doha'),
(143, 'Romania', 'Bucharest'),
(144, 'Russia', 'Moscow'),
(145, 'Rwanda', 'Kigali'),
(146, 'Saint Kitts and Nevis', 'Basseterre'),
(147, 'Saint Lucia', 'Castries'),
(148, 'Saint Vincent and the Grenadines', 'Kingstown'),
(149, 'Samoa', 'Apia'),
(150, 'San Marino', 'San Marino'),
(151, 'Sao Tome and Principe', 'São Tomé'),
(152, 'Saudi Arabia', 'Riyadh'),
(153, 'Senegal', 'Dakar'),
(154, 'Serbia', 'Belgrade'),
(155, 'Seychelles', 'Victoria'),
(156, 'Sierra Leone', 'Freetown'),
(157, 'Singapore', 'Singapore'),
(158, 'Slovakia', 'Bratislava'),
(159, 'Slovenia', 'Ljubljana'),
(160, 'Solomon Islands', 'Honiara'),
(161, 'Somalia', 'Mogadishu'),
(162, 'South Africa', 'Pretoria'),
(163, 'South Sudan', 'Juba'),
(164, 'Spain', 'Madrid'),
(165, 'Sri Lanka', 'Sri Jayawardenepura Kotte'),
(166, 'Sudan', 'Khartoum'),
(167, 'Suriname', 'Paramaribo'),
(168, 'Sweden', 'Stockholm'),
(169, 'Switzerland', 'Bern'),
(170, 'Syria', 'Damascus'),
(171, 'Taiwan', 'Taipei'),
(172, 'Tajikistan', 'Dushanbe'),
(173, 'Tanzania', 'Dodoma'),
(174, 'Thailand', 'Bangkok'),
(175, 'Togo', 'Lomé'),
(176, 'Tonga', 'Nukuʻalofa'),
(177, 'Trinidad and Tobago', 'Port of Spain'),
(178, 'Tunisia', 'Tunis'),
(179, 'Turkey', 'Ankara'),
(180, 'Turkmenistan', 'Ashgabat'),
(181, 'Tuvalu', 'Funafuti'),
(182, 'Uganda', 'Kampala'),
(183, 'Ukraine', 'Kyiv'),
(184, 'United Arab Emirates', 'Abu Dhabi'),
(185, 'United Kingdom', 'London'),
(186, 'United States', 'Washington, D.C'),
(187, 'Uruguay', 'Montevideo'),
(188, 'Uzbekistan', 'Tashkent'),
(189, 'Vanuatu', 'Port Vila'),
(190, 'Vatican City', 'Vatican City'),
(191, 'Venezuela', 'Caracas'),
(192, 'Vietnam', 'Hanoi'),
(193, 'Yemen', 'Sana\'a'),
(194, 'Zambia', 'Lusaka'),
(195, 'Zimbabwe', 'Harare');

-- --------------------------------------------------------

--
-- Table structure for table `search_tracking`
--

CREATE TABLE `search_tracking` (
  `country_id` int(11) DEFAULT NULL,
  `search_count` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `search_tracking`
--

INSERT INTO `search_tracking` (`country_id`, `search_count`) VALUES
(186, 14),
(182, 1),
(91, 1),
(73, 1),
(174, 5),
(141, 2),
(83, 1),
(78, 1),
(170, 1),
(77, 1),
(184, 1),
(86, 1),
(32, 1),
(21, 1),
(125, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `search_tracking`
--
ALTER TABLE `search_tracking`
  ADD KEY `country_id` (`country_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `search_tracking`
--
ALTER TABLE `search_tracking`
  ADD CONSTRAINT `search_tracking_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
