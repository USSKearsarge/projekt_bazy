-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Cze 17, 2026 at 08:20 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `baza_testowa_g05`
--

DELIMITER $$
--
-- Procedury
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `emp_proc` (IN `plastname` VARCHAR(16) CHARSET utf8mb4)   BEGIN  
  DECLARE vok     INT DEFAULT FALSE;
  DECLARE vid     INT;
  DECLARE vname   VARCHAR(16);
  DECLARE vsalary DECIMAL(10,2);
  DECLARE cur1 CURSOR 
     FOR SELECT id, 
                last_name, 
                salary 
          FROM baza_testowa.emp
         WHERE last_name = plastname ;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET vok = TRUE;
  OPEN cur1;
  read_loop: LOOP
  	FETCH cur1 INTO vid, vname, vsalary;
    IF vok THEN
      LEAVE read_loop;
  	END IF;
  	SELECT vid, 
    vname, vsalary;
  END LOOP;  
  
  CLOSE cur1;
  
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `emp_proc1` (IN `plastname` VARCHAR(16) CHARSET utf8mb4, OUT `psalary` DECIMAL(10,2))   BEGIN  
  DECLARE vok     INT DEFAULT FALSE;
  DECLARE vid     INT;
  #DECLARE vname   VARCHAR(16);
  DECLARE vsalary DECIMAL(10,2);
  DECLARE cur1 CURSOR 
     FOR SELECT id, 
                salary
          FROM baza_testowa.emp
         WHERE last_name = plastname ;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET vok = TRUE;
  OPEN cur1;
 
  FETCH cur1 INTO vid, psalary;

  
  CLOSE cur1;
  
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `cena` (`id_czesci` INT UNSIGNED, `data` DATE) RETURNS DECIMAL(10,2)  RETURN (SELECT cennik.netto * (1 + cennik.vat/100)
          FROM cennik  
         WHERE (cennik.czesci_id, cennik.data) = (SELECT cn.czesci_id, MAX(cn.data)
                                                    FROM cennik AS cn
                                                   WHERE cn.czesci_id = id_czesci
                                                     AND cn.data <= data))$$

CREATE DEFINER=`root`@`localhost` FUNCTION `emp_fun` (`pid` INT) RETURNS DECIMAL(10,2) UNSIGNED  BEGIN  
  DECLARE vid     INT;
  DECLARE vsalary DECIMAL(10,2);
  DECLARE cur1 CURSOR 
     FOR SELECT salary
           FROM baza_testowa.emp
          WHERE id = pid;
  OPEN cur1;
  FETCH cur1 into vsalary;
  CLOSE cur1;
  RETURN vsalary;
  
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `customer`
--

CREATE TABLE `customer` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `first_name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `street` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `city` varchar(30) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  `country` varchar(30) DEFAULT NULL,
  `zip_code` varchar(75) DEFAULT NULL,
  `credit_rating` enum('EXCELLENT','GOOD','POOR') DEFAULT NULL,
  `contact_id` int(11) UNSIGNED DEFAULT NULL,
  `region_id` int(11) UNSIGNED DEFAULT NULL,
  `nip` varchar(10) NOT NULL,
  `krs` char(10) DEFAULT NULL,
  `regon` char(9) DEFAULT NULL,
  `email` varchar(30) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('M','K','') NOT NULL,
  `pesel` char(11) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `agreement` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `agr_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id`, `name`, `first_name`, `phone`, `street`, `city`, `state`, `country`, `zip_code`, `credit_rating`, `contact_id`, `region_id`, `nip`, `krs`, `regon`, `email`, `birth_date`, `gender`, `pesel`, `comments`, `agreement`, `agr_date`) VALUES
(201, 'Alcatraz', '', '55-2066101', '72 Via Bahia', 'Sao Paolo', NULL, 'Brazil', '98-546', 'EXCELLENT', 12, 25, '0482132265', NULL, NULL, 'alcatraz_brazil@wp.pl', NULL, 'M', NULL, 'KOMENTARZ', NULL, NULL),
(202, 'OJ Atheletics', '', '81-20101', '6741 Takashi Blvd.', 'Osaka', NULL, 'Japan', '12-264', 'POOR', 14, 43, '1823004562', NULL, NULL, 'OJ_Atheletics@gmail.com', NULL, 'M', NULL, NULL, NULL, NULL),
(203, 'Delhi Sports', '', '91-10351', '11368 Chanakya', 'New Delhi', NULL, 'India', '29-485', 'GOOD', 14, 42, '9374956129', NULL, NULL, 'delhisports@gmail.com', NULL, 'M', NULL, NULL, NULL, NULL),
(204, 'Womansport', '', '1-206-104-0103', '281 King Street', 'Seattle', 'Washington', 'USA', '98101', 'EXCELLENT', 11, 11, '1029384756', NULL, NULL, 'womansport@example.com', NULL, 'M', NULL, 'Klient z USA', NULL, NULL),
(205, 'Kam\'s Sporting Goods', '', '852-3692888', '15 Henessey Road', 'Hong Kong', 'Hong Kong', NULL, '999077', 'EXCELLENT', 15, 42, '1092837465', NULL, NULL, 'kamgoods@example.hk', NULL, 'M', NULL, 'Sklep sportowy HK', NULL, NULL),
(206, 'Sportique', '', '33-2257201', '172 Rue de Rivoli', 'Cannes', 'Provence-Alpes-Côte ', 'France', '06400', 'EXCELLENT', 15, 52, '2837461920', NULL, NULL, 'sportique@example.fr', NULL, 'M', NULL, 'Francuski oddział', NULL, NULL),
(207, 'Sweet Rock Sports', '', '234-6036201', '6 Saint Antoine', 'Lagos', 'Lagos', 'Nigeria', '101212', 'GOOD', NULL, 35, '7461928374', NULL, NULL, 'sweetrock@example.ng', NULL, 'M', NULL, 'Afrykański partner', NULL, NULL),
(208, 'Muench Sports', '', '49-527454', '435 Gruenestrasse', 'Stuttgart', 'Baden-Württemberg', 'Germany', '70173', 'GOOD', 15, 55, '9182736450', NULL, NULL, 'beisboll@example.de', NULL, 'M', NULL, 'Niemiecki klient', NULL, NULL),
(209, 'Beisbol Si!', '', '809-352689', '792 Playa Del Mar', 'San Pedro de Macon\'s', 'La Romana', 'Dominican Republic', '', 'EXCELLENT', 11, 13, '8374651920', NULL, NULL, 'emp@example.do', NULL, 'M', NULL, 'Zainteresowany hurt', NULL, NULL),
(210, 'Festival', '', '52-404562', '3 Via Saguaro', 'Nogalesy', 'Sonora', 'Mexico', '84000', 'EXCELLENT', 12, 11, '1928374650', NULL, NULL, 'festivalmx@example.mx', NULL, 'M', NULL, 'Potrzebuje faktury', NULL, NULL),
(211, 'Kuhn\'s Sports', '', '42-111292', '7 Modrany', 'Prague', 'Hlavní město Praha', 'Czechoslovakia', '11000', 'EXCELLENT', 15, 55, '0192837465', NULL, NULL, 'kuhnshop@example.cz', NULL, 'M', NULL, 'Klient z Czech', NULL, NULL),
(212, 'Hamada Sports', '', '20-1209211', '57A Corniche', 'Alexandria', 'Alexandria', 'Egypt', '21532', 'EXCELLENT', 13, 31, '5647382910', NULL, NULL, 'hamada@example.eg', NULL, 'M', NULL, 'Dostawy co 2 tygodnie', NULL, NULL),
(213, 'Big John\'s Sports Emporium', '', '1-415-555-6281', '4783 18th Street', 'San Francisco', 'California', 'USA', '94118', 'EXCELLENT', 11, 11, '8372649102', NULL, NULL, 'bigjohn@example.com', NULL, 'M', NULL, 'VIP klient USA', NULL, NULL),
(214, 'Ojibway Retail', '', '1-716-555-7171', '415 Main Street', 'Buffalo', 'New York', 'USA', '14201', 'POOR', 11, 11, '5647382911', NULL, NULL, 'ojibwayretail@example.com', NULL, 'M', NULL, 'Zgłoszenie o rabat', NULL, NULL),
(215, 'Дядя Ваня и Девочки', '', '7-234234', 'Варшавское Шоссе ', 'Москва', 'Moscow', 'Russia', '121212', 'POOR', 15, 54, '9081726345', NULL, NULL, 'djadya@example.ru', NULL, 'M', NULL, 'Klient z Rosji', NULL, NULL),
(216, 'ACME Sp.z o.o.', '', '', 'ul. Przemysłowa 10', 'Niewachlów', 'Świętokrzyskie', 'Polska', '25-630', 'GOOD', 9, 55, '7326459180', NULL, NULL, 'acme_spzoo1@example.pl', NULL, 'M', NULL, 'Polski klient ACME', NULL, NULL),
(217, 'ACME Sp.z o.o.', '', '', 'ul. Główna 45', 'Niewachlów', 'Świętokrzyskie', 'Polska', '25-630', 'GOOD', 9, 55, '8372619450', NULL, NULL, 'acme_spzoo2@example.pl', NULL, 'M', NULL, 'Duplikat firmy ACME', NULL, NULL),
(218, 'Agro Harvest', '', '88-9012345', '101 Farm Road', 'Berlin', 'Berlin', 'Germany', '10115', 'EXCELLENT', 4, 55, '', NULL, NULL, 'info@agroharvest.de', NULL, 'M', NULL, 'Agricultural equipment supplier', NULL, NULL),
(219, 'Oceanic Seafoods', '', '99-5678901', '222 Portside', 'Sydney', 'NSW', 'Australia', '2000', 'GOOD', 5, 83, '', NULL, NULL, 'orders@oceanic.au', NULL, 'M', NULL, 'Importer of fresh seafood', NULL, NULL),
(220, 'Desert Bloom', '', '11-2345678', '333 Oasis Street', 'Dubai', 'Dubai', 'UAE', '00000', 'EXCELLENT', 6, 44, '', NULL, NULL, 'bookings@desertbloom.ae', NULL, 'M', NULL, 'Luxury desert tours', NULL, NULL),
(221, 'Maple Leaf Corp', '', '22-8901234', '444 Evergreen Lane', 'Toronto', 'ON', 'Canada', 'M5V 2J9', 'GOOD', 7, 11, '', NULL, NULL, 'office@mapleleaf.ca', NULL, 'M', NULL, 'Canadian distributor', NULL, NULL),
(222, 'Rio Carnival Supplies', '', '33-4567890', '555 Samba Street', 'Rio de Janeiro', 'RJ', 'Brazil', '20000-000', '', 8, 25, '', NULL, NULL, 'info@carnivalsupplies.br', NULL, 'M', NULL, 'Seasonal event supplies', NULL, NULL),
(223, 'Nordic Lights Trading', '', '44-0123456', '666 Fjord Road', 'Oslo', 'Oslo', 'Norway', '0101', 'EXCELLENT', 9, 51, '', NULL, NULL, 'sales@nordiclights.no', NULL, 'M', NULL, 'Specialty goods from Scandinavia', NULL, NULL),
(224, 'Eastern Bazaar', '', '55-6789012', '777 Spice Lane', 'New Delhi', 'Delhi', 'India', '110001', 'GOOD', 10, 42, '', NULL, NULL, 'contact@easternbazaar.in', NULL, 'M', NULL, 'Traditional handicrafts', NULL, NULL),
(225, 'Alpine Sports Gear', '', '66-2345678', '888 Mountain View', 'Zurich', 'Zurich', 'Switzerland', '8000', 'EXCELLENT', 11, 52, '', NULL, NULL, 'info@alpinesports.ch', NULL, 'M', NULL, 'Outdoor and sports equipment', NULL, NULL),
(226, 'Caribbean Delights', '', '77-8901234', '999 Beach Blvd', 'Kingston', 'Kingston Parish', 'Jamaica', '00000', 'POOR', 12, 13, '', NULL, NULL, 'orders@caribdelights.jm', NULL, 'M', NULL, 'Tropical food and beverages', NULL, NULL),
(227, 'Terra Nova Foods', '', '88-4567890', '111 Countryside', 'Madrid', 'Madrid', 'Spain', '28001', 'GOOD', 13, 52, '', NULL, NULL, 'contact@terranova.es', NULL, 'M', NULL, 'Organic produce supplier', NULL, NULL),
(228, 'Shanghai Silk Road', '', '99-0123456', '222 Grand Canal', 'Shanghai', 'Shanghai', 'China', '200000', 'EXCELLENT', 14, 45, '', NULL, NULL, 'sales@shanghaisilk.cn', NULL, 'M', NULL, 'Silk products and textiles', NULL, NULL),
(229, 'Sahara Expeditions', '', '11-6789012', '333 Dunes Road', 'Marrakech', 'Marrakech-Safi', 'Morocco', '40000', 'GOOD', 15, 31, '', NULL, NULL, 'info@saharaexp.ma', NULL, 'M', NULL, 'Desert tourism and safaris', NULL, NULL),
(230, 'Pacific Rim Imports', '', '22-2345678', '444 Ocean Front', 'Vancouver', 'BC', 'Canada', 'V6B 1P1', 'EXCELLENT', 16, 11, '', NULL, NULL, 'office@pacificrim.ca', NULL, 'M', NULL, 'Imports from Asia-Pacific', NULL, NULL),
(231, 'Baltic Sea Ventures', '', '33-8901234', '555 Harbour St', 'Copenhagen', 'Capital Region', 'Denmark', '1000', 'GOOD', 17, 51, '', NULL, NULL, 'info@balticsea.dk', NULL, 'M', NULL, 'Shipping and logistics', NULL, NULL),
(232, 'Amazonian Crafts', '', '44-4567890', '666 Rainforest Path', 'Manaus', 'Amazonas', 'Brazil', '69000-000', '', 18, 25, '', NULL, NULL, 'sales@amazoniancrafts.br', NULL, 'M', NULL, 'Indigenous crafts and art', NULL, NULL),
(233, 'Viking Heritage Co.', '', '55-0123456', '777 Ancient Trail', 'Stockholm', 'Stockholm', 'Sweden', '111 20', 'EXCELLENT', 19, 51, '', NULL, NULL, 'contact@vikingheritage.se', NULL, 'M', NULL, 'Historical reproductions', NULL, NULL),
(234, 'African Safari Ltd.', '', '66-6789012', '888 Savannah Way', 'Nairobi', 'Nairobi County', 'Kenya', '00100', 'GOOD', 20, 33, '', NULL, NULL, 'bookings@africansafari.ke', NULL, 'M', NULL, 'Safari and wildlife tours', NULL, NULL),
(235, 'Zenith Electronics', '', '77-3456789', '999 Circuit Dr', 'Tokyo', 'Tokyo', 'Japan', '100-0001', 'EXCELLENT', 1, 43, '', NULL, NULL, 'info@zenith.jp', NULL, 'M', NULL, 'Consumer electronics supplier', NULL, NULL),
(236, 'Evergreen Nurseries', '', '88-9012345', '111 Green Blvd', 'Melbourne', 'VIC', 'Australia', '3000', 'GOOD', 2, 83, '', NULL, NULL, 'sales@evergreen.au', NULL, 'M', NULL, 'Plant and garden supplies', NULL, NULL),
(237, 'Glacier Goods Co.', '', '99-5678901', '222 Ice Peak Rd', 'Reykjavik', 'Capital Region', 'Iceland', '101', 'EXCELLENT', 3, 51, '', NULL, NULL, 'contact@glaciergoods.is', NULL, 'M', NULL, 'Outdoor winter gear', NULL, NULL),
(238, 'Ancient Pottery House', '', '11-2345678', '333 Heritage St', 'Rome', 'Lazio', 'Italy', '00100', 'GOOD', 4, 53, '', NULL, NULL, 'orders@ancientpottery.it', NULL, 'M', NULL, 'Handmade ceramics importer', NULL, NULL),
(239, 'Lunar Tech Solutions', '', '22-8901234', '444 Innovation Way', 'Seoul', 'Seoul', 'South Korea', '03000', 'EXCELLENT', 5, 44, '', NULL, NULL, 'support@lunartech.kr', NULL, 'M', NULL, 'Software development firm', NULL, NULL),
(240, 'Golden Sands Resorts', '', '33-4567890', '555 Coastal Rd', 'Malaga', 'Andalusia', 'Spain', '29001', 'GOOD', 6, 52, '', NULL, NULL, 'bookings@goldensands.es', NULL, 'M', NULL, 'Luxury hotel chain', NULL, NULL),
(241, 'Polaris Logistics', '', '44-0123456', '666 Global Hub', 'Frankfurt', 'Hesse', 'Germany', '60311', 'EXCELLENT', 7, 55, '', NULL, NULL, 'info@polarislogistics.de', NULL, 'M', NULL, 'International freight forwarding', NULL, NULL),
(242, 'Delta Water Systems', '', '55-6789012', '777 Riverbank Rd', 'Amsterdam', 'North Holland', 'Netherlands', '1011', 'GOOD', 8, 52, '', NULL, NULL, 'sales@deltawater.nl', NULL, 'M', NULL, 'Water purification solutions', NULL, NULL),
(243, 'Crimson Art Supplies', '', '66-2345678', '888 Gallery Lane', 'London', 'England', 'UK', 'SW1A 0AA', 'EXCELLENT', 124, 52, '', '11', '', 'contact@crimsonart.co.uk', '0000-00-00', 'M', '', 'Fine art materials retailer', '', '0000-00-00'),
(244, 'Emerald Jewelry Co.', '', '77-8901234', '999 Gemstone Ave', 'Mumbai', 'Maharashtra', 'India', '400001', 'GOOD', 10, 42, '', NULL, NULL, 'orders@emeraldjewelry.in', NULL, 'M', NULL, 'Precious stone jewelry', NULL, NULL),
(245, 'Venture Capital Group', '', '88-4567890', '101 Investor Plaza', 'Zurich', 'Zurich', 'Switzerland', '8001', 'EXCELLENT', 11, 52, '', NULL, NULL, 'invest@venturecapital.ch', NULL, 'M', NULL, 'Investment and finance', NULL, NULL),
(246, 'Desert Rose Textiles', '', '99-0123456', '222 Fabric St', 'Cairo', 'Cairo Governorate', 'Egypt', '11511', 'GOOD', 12, 31, '', NULL, NULL, 'sales@desertrosetextiles.eg', NULL, 'M', NULL, 'Traditional textiles and fabrics', NULL, NULL),
(247, 'Urban Mobility Solutions', '', '11-6789012', '333 Transit Blvd', 'Singapore', 'Central Region', 'Singapore', '018906', 'EXCELLENT', 13, 42, '', NULL, NULL, 'info@urbanmobility.sg', NULL, 'M', NULL, 'Smart city transport', NULL, NULL),
(248, 'Pioneer Robotics', '', '22-2345678', '444 Automation Way', 'Boston', 'MA', 'USA', '02108', 'GOOD', 14, 11, '', NULL, NULL, 'contact@pioneerrobotics.com', NULL, 'M', NULL, 'Industrial automation and robotics', NULL, NULL),
(249, 'Azure Marine Supplies', '', '33-8901234', '555 Yacht Club Dr', 'Nice', 'Provence-Alpes-Côte ', 'France', '06000', 'EXCELLENT', 15, 52, '', NULL, NULL, 'sales@azuremarine.fr', NULL, 'M', NULL, 'Boating and marine equipment', NULL, NULL),
(250, 'Highland Distillery', '', '44-4567890', '666 Whiskey Rd', 'Edinburgh', 'Scotland', 'UK', 'EH1 1AA', 'GOOD', 16, 51, '', NULL, NULL, 'info@highlanddistillery.co.uk', NULL, 'M', NULL, 'Premium spirits producer', NULL, NULL),
(251, 'Silk Road Spices', '', '55-0123456', '777 Spice Market', 'Istanbul', 'Istanbul', 'Turkey', '34000', 'EXCELLENT', 17, 54, '', NULL, NULL, 'orders@silkroadspices.tr', NULL, 'M', NULL, 'Exotic spices and herbs', NULL, NULL),
(252, 'Outback Adventure Gear', '', '66-6789012', '888 Bushland Track', 'Perth', 'WA', 'Australia', '6000', 'GOOD', 18, 83, '', NULL, NULL, 'contact@outbackadventure.au', NULL, 'M', NULL, 'Camping and outdoor equipment', NULL, NULL),
(253, 'Nordic Design House', '', '77-2345678', '999 Modern Ave', 'Helsinki', 'Uusimaa', 'Finland', '00100', 'EXCELLENT', 19, 51, '', NULL, NULL, 'sales@nordicdesign.fi', NULL, 'M', NULL, 'Scandinavian furniture and decor', NULL, NULL),
(254, 'Canyon Vista Tours', '', '88-8901234', '101 Scenic Route', 'Las Vegas', 'NV', 'USA', '89101', 'GOOD', 20, 11, '', NULL, NULL, 'bookings@canyonvista.com', NULL, 'M', NULL, 'Sightseeing and adventure tours', NULL, NULL),
(255, 'Sunrise Dairy Farms', '', '99-4567890', '123 Meadow Lane', 'Dublin', 'Leinster', 'Ireland', 'D01 A1B2', 'GOOD', 1, 52, '', NULL, NULL, 'info@sunrisedairy.ie', NULL, 'M', NULL, 'Organic milk producer', NULL, NULL),
(256, 'Global Pharma Solutions', '', '11-0123456', '456 Lab Rd', 'Basel', 'Basel-Stadt', 'Switzerland', '4000', 'EXCELLENT', 2, 52, '', '', NULL, 'contact@globalpharma.ch', NULL, 'M', NULL, 'Pharmaceutical research', NULL, NULL),
(257, 'Crystal Clear Waters', '', '22-6789012', '789 Spring St', 'Wellington', 'Wellington', 'New Zealand', '6011', '', 3, 83, '', NULL, NULL, 'sales@crystalclear.nz', NULL, 'M', NULL, 'Bottled water distributor', NULL, NULL),
(258, 'Ironclad Security', '', '33-2345678', '101 Fortress Ave', 'Tel Aviv', 'Tel Aviv District', 'Israel', '61000', 'GOOD', 4, 44, '', NULL, NULL, 'support@ironcladsec.il', NULL, 'M', NULL, 'Cybersecurity services', NULL, NULL),
(259, 'Green Thumb Landscaping', '', '44-8901234', '222 Garden Path', 'Cologne', 'North Rhine-Westphal', 'Germany', '50667', 'POOR', 5, 55, '', NULL, NULL, 'info@greenthumb.de', NULL, 'M', NULL, 'Garden design and maintenance', NULL, NULL),
(260, 'Silver Lining Apparel', '', '55-4567890', '333 Fashion Row', 'Milan', 'Lombardy', 'Italy', '20121', 'GOOD', 6, 53, '', NULL, NULL, 'orders@silverlining.it', NULL, 'M', NULL, 'High-end clothing boutique', NULL, NULL),
(261, 'Quantum Computing Inc.', '', '66-0123456', '444 Data Center Dr', 'Cambridge', 'MA', 'USA', '02139', 'EXCELLENT', 7, 11, '', NULL, NULL, 'research@quantum.com', NULL, 'M', NULL, 'Advanced computing research', NULL, NULL),
(262, 'Redwood Timber Co.', '', '77-6789012', '555 Forest Rd', 'Vancouver', 'BC', 'Canada', 'V6G 2R3', '', 8, 11, '', NULL, NULL, 'sales@redwoodtimber.ca', NULL, 'M', NULL, 'Sustainable timber logging', NULL, NULL),
(263, 'Phoenix Aerospace', '', '88-2345678', '666 Airfield Way', 'Seattle', 'WA', 'USA', '98101', 'EXCELLENT', 9, 11, '', NULL, NULL, 'contact@phoenixaero.com', NULL, 'M', NULL, 'Aircraft manufacturing', NULL, NULL),
(264, 'Dragonfly Organics', '', '99-8901234', '777 Farm-to-Table', 'Portland', 'OR', 'USA', '97204', 'GOOD', 10, 11, '', NULL, NULL, 'info@dragonflyorganics.com', NULL, 'M', NULL, 'Organic food distributor', NULL, NULL),
(265, 'Blue Ocean Investments', '', '11-4567890', '888 Finance Tower', 'Dubai', 'Dubai', 'UAE', '00000', 'EXCELLENT', 11, 44, '', NULL, NULL, 'invest@blueocean.ae', NULL, 'M', NULL, 'Wealth management firm', NULL, NULL),
(266, 'Desert Bloom Cosmetics', '', '22-0123456', '999 Beauty Blvd', 'Abu Dhabi', 'Abu Dhabi', 'UAE', '00000', 'GOOD', 12, 44, '', NULL, NULL, 'sales@desertbloomcosmetics.ae', NULL, 'M', NULL, 'Natural beauty products', NULL, NULL),
(267, 'Vanguard Solutions', '', '33-6789012', '111 Tech Park', 'Bangalore', 'Karnataka', 'India', '560001', 'EXCELLENT', 13, 42, '', NULL, NULL, 'support@vanguardsolutions.in', NULL, 'M', NULL, 'IT and software services', NULL, NULL),
(268, 'Grand Tour Travel', '', '44-2345678', '222 Journey Lane', 'Barcelona', 'Catalonia', 'Spain', '08001', '', 14, 52, '', NULL, NULL, 'bookings@grandtour.es', NULL, 'M', NULL, 'Luxury travel agency', NULL, NULL),
(269, 'High Tide Fishery', '', '55-8901234', '333 Marina Blvd', 'Lisbon', 'Lisbon', 'Portugal', '1000-001', 'POOR', 15, 52, '', NULL, NULL, 'orders@hightide.pt', NULL, 'M', NULL, 'Fresh fish and seafood', NULL, NULL),
(270, 'Northern Lights Energy', '', '66-4567890', '444 Windmill Way', 'Oslo', 'Oslo', 'Norway', '0102', 'EXCELLENT', 16, 51, '', NULL, NULL, 'info@northernlights.no', NULL, 'M', NULL, 'Renewable energy solutions', NULL, NULL),
(271, 'Emerald Isle Tours', '', '77-0123456', '555 Castle Rd', 'Killarney', 'County Kerry', 'Ireland', 'V93 T999', 'GOOD', 17, 52, '', NULL, NULL, 'contact@emeraldisle.ie', NULL, 'M', NULL, 'Cultural and historical tours', NULL, NULL),
(272, 'Pacific Rim Logistics', '', '88-6789012', '666 Freight Terminal', 'Hong Kong', 'Hong Kong', 'China', '999077', 'EXCELLENT', 18, 42, '', NULL, NULL, 'cargo@pacificrimlogistics.hk', NULL, 'M', NULL, 'International cargo and shipping', NULL, NULL),
(273, 'Sunstone Gems', '', '99-2345678', '777 Jewelers Row', 'Bangkok', 'Bangkok', 'Thailand', '10110', 'GOOD', 19, 44, '', NULL, NULL, 'sales@sunstonegems.th', NULL, 'M', NULL, 'Precious and semi-precious stones', NULL, NULL),
(274, 'African Spice Trade', '', '11-8901234', '888 Market Rd', 'Accra', 'Greater Accra', 'Ghana', '00233', '', 20, 34, '', NULL, NULL, 'info@africanspice.gh', NULL, 'M', NULL, 'Exotic spices and produce', NULL, NULL),
(275, 'Polar Bear Apparel', '', '22-4567890', '999 Winter Lane', 'Montreal', 'QC', 'Canada', 'H2Y 2E3', 'EXCELLENT', 1, 11, '', NULL, NULL, 'contact@polarbear.ca', NULL, 'M', NULL, 'Winter clothing manufacturer', NULL, NULL),
(276, 'Sahara Oasis Springs', '', '33-0123456', '101 Desert Rd', 'Marrakech', 'Marrakech-Safi', 'Morocco', '40001', 'GOOD', 2, 31, '', NULL, NULL, 'sales@saharaoasis.ma', NULL, 'M', NULL, 'Mineral water bottling', NULL, NULL),
(277, 'Orion Aerospace', '', '44-6789012', '222 Launchpad Blvd', 'Houston', 'TX', 'USA', '77002', 'EXCELLENT', 3, 11, '', NULL, NULL, 'info@orionaero.com', NULL, 'M', NULL, 'Space technology development', NULL, NULL),
(278, 'Baltic Craft Brewers', '', '55-2345678', '333 Brewery St', 'Riga', 'Riga District', 'Latvia', 'LV-1001', '', 4, 51, '', NULL, NULL, 'orders@balticbrewers.lv', NULL, 'M', NULL, 'Artisan beer production', NULL, NULL),
(279, 'Andean Textiles Co.', '', '66-8901234', '444 Mountain Pass', 'Lima', 'Lima', 'Peru', '15001', 'POOR', 5, 23, '', NULL, NULL, 'sales@andeantextiles.pe', NULL, 'M', NULL, 'Handwoven fabrics from Andes', NULL, NULL),
(280, 'Danube Shipping Group', '', '77-4567890', '555 River Port Rd', 'Vienna', 'Vienna', 'Austria', '1010', 'EXCELLENT', 6, 55, '', NULL, NULL, 'contact@danubeshipping.at', NULL, 'M', NULL, 'River transport and logistics', NULL, NULL),
(281, 'Misty Fjords Tours', '', '88-0123456', '666 Glacial Way', 'Juneau', 'AK', 'USA', '99801', 'GOOD', 7, 11, '', NULL, NULL, 'bookings@mistyfjords.com', NULL, 'M', NULL, 'Alaskan wilderness tours', NULL, NULL),
(282, 'Golden Pagoda Antiques', '', '99-6789012', '777 Old Town Rd', 'Hanoi', 'Hanoi', 'Vietnam', '10000', '', 8, 42, '', NULL, NULL, 'info@goldenpagoda.vn', NULL, 'M', NULL, 'Southeast Asian antique dealer', NULL, NULL),
(283, 'Maple Syrup Producers', '', '11-2345678', '888 Sugar Bush', 'Quebec City', 'QC', 'Canada', 'G1R 0A7', 'GOOD', 9, 11, '', NULL, NULL, 'sales@maplesyrup.ca', NULL, 'M', NULL, 'Traditional maple syrup', NULL, NULL),
(284, 'Kalahari Safari Co.', '', '22-8901234', '999 Savannah Trail', 'Gaborone', 'South-East District', 'Botswana', '00000', 'EXCELLENT', 10, 35, '', NULL, NULL, 'bookings@kalaharisafari.bw', NULL, 'M', NULL, 'Wildlife safari expeditions', NULL, NULL),
(358, 'ACME sp.z.o.o.', '', NULL, 'ul. Partnerów 99', 'Niewachlów', 'Świętokrzyskie', 'Polska', '25-630', 'GOOD', 9, 55, '5647381920', NULL, NULL, 'acme12358@example.pl', NULL, 'M', NULL, 'Zarejestrowany partner', NULL, NULL),
(359, 'ACME Biuro Kielce', '', NULL, 'Białogońska 13', 'Kielce', 'Świętokrzyskie', 'Polska', '25-605', '', 9, 55, '0918273645', NULL, NULL, 'acme12359@example.pl', NULL, 'M', NULL, 'Potrzebna faktura VAT', NULL, NULL),
(360, 'ACME Biuro Mazowieckie', '', '', 'Białogońska 13', 'Kielce', 'Mazowieckie', 'Polska', '25-600', 'GOOD', 22, 54, '7362819471', '', '', 'acme12360@example.pl', '0000-00-00', 'M', '123', 'Niepoprawny kod pocztowy poprawiony', '', '0000-00-00'),
(361, 'ACME sp.z.o.o.', '', NULL, 'ul. Centralna 11', 'Niewachlów', 'Mazowieckie', 'Polska', '25-600', 'EXCELLENT', 110, 55, '8374659201', NULL, NULL, 'acme12361@example.pl', NULL, 'M', NULL, 'Poprawione dane adresowe', NULL, NULL),
(362, 'ACME sp.z.o.o.', '', '', 'ul. Testowa 88', 'Niewachlów', 'Świętokrzyskie', 'Polska', '25-630', '', 110, 54, '9182736400', '', '11', 'acme12362@example.pl', '0000-00-00', 'M', '', 'Wpis testowy', '', '0000-00-00'),
(364, 'ACME1 sp.z.o.o.', '', '111222333', 'ul. Handlowa 5', 'Niewachlów', 'Świętokrzyskie', 'Polska', '25-630', 'GOOD', 110, 55, '9245637810', NULL, NULL, 'acme1_odzial1@example.pl', NULL, 'M', NULL, 'Nowy oddział firmy ACME1', NULL, NULL),
(365, 'ACME1 sp.z.o.o.', '', '222333444', 'ul. Szeroka 8', 'Niewachlów', 'Świętokrzyskie', 'Polska', '25-630', '', 271, 55, '8154963720', NULL, NULL, 'acme1_odzial2@example.pl', NULL, 'M', NULL, 'Oddział testowy ACME1', NULL, NULL),
(366, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '', NULL, 'M', NULL, NULL, NULL, NULL),
(367, 'Jan Kowalski', '', '123456789', 'ul. Kwiatowa 10', 'Warszawa', 'Mazowieckie', 'Polska', '00-001', 'GOOD', 1, 55, '1234563218', NULL, NULL, 'jan.kowalski@example.com', NULL, 'M', NULL, 'Stały klient', NULL, NULL),
(368, 'Anna Nowak', '', '987654321', 'ul. Leśna 5', 'Kraków', 'Małopolskie', 'Polska', '30-002', 'EXCELLENT', 2, 55, '9876543210', NULL, NULL, 'anna.nowak@example.com', NULL, 'M', NULL, 'Nowy klient', NULL, NULL),
(369, 'Piotr Zieliński', '', '555444333', 'ul. Słoneczna 7', 'Gdańsk', 'Pomorskie', 'Polska', '80-003', 'POOR', 3, 55, '1112223334', NULL, NULL, 'piotr.zielinski@example.com', NULL, 'M', NULL, '', NULL, NULL),
(370, 'Katarzyna Wiśniewska', '', '666777888', 'ul. Polna 12', 'Poznań', 'Wielkopolskie', 'Polska', '60-004', 'GOOD', 2, 55, '9873216540', NULL, NULL, 'k.wisniewska@example.com', NULL, 'M', NULL, 'Zainteresowana nową ofertą', NULL, NULL),
(371, 'Tomasz Wójcik', '', '444333222', 'ul. Morska 8', 'Szczecin', 'Zachodniopomorskie', 'Polska', '70-005', 'EXCELLENT', 1, 55, '1239874561', NULL, NULL, 't.wojcik@example.com', NULL, 'M', NULL, '', NULL, NULL),
(372, 'Magdalena Lewandowska', '', '321654987', 'ul. Spacerowa 15', 'Lublin', 'Lubelskie', 'Polska', '20-006', 'POOR', 3, 55, '3216549870', NULL, NULL, 'm.lewandowska@example.com', NULL, 'M', NULL, 'Ma opóźnienia w płatnościach', NULL, NULL),
(373, 'Marek Kamiński', '', '111222333', 'ul. Lipowa 6', 'Katowice', 'Śląskie', 'Polska', '40-007', 'GOOD', 2, 55, '6543217890', NULL, NULL, 'm.kaminski@example.com', NULL, 'M', NULL, '', NULL, NULL),
(374, 'Agnieszka Kaczmarek', '', '888999000', 'ul. Zielona 3', 'Łódź', 'Łódzkie', 'Polska', '90-008', 'EXCELLENT', 1, 55, '7896541230', NULL, NULL, 'a.kaczmarek@example.com', NULL, 'M', NULL, 'Polecona przez znajomego', NULL, NULL),
(375, 'NovaTech Inc.', '', '321-555-0123', '1600 Solar Blvd', 'Orlando', 'Florida', 'USA', '32801', 'EXCELLENT', 12, 11, '8412398754', NULL, NULL, 'contact@novatech.com', NULL, 'M', NULL, 'Nowy klient IT', NULL, NULL),
(376, 'Müller GmbH', '', '030-12345678', 'Alexanderplatz 5', 'Berlin', 'Berlin', 'Germany', '10178', 'GOOD', 14, 55, '9988776655', NULL, NULL, 'kontakt@mueller.de', NULL, 'M', NULL, 'Potencjalny partner', NULL, NULL),
(377, 'Fromage Delicieux', '', '01 23 45 67 89', '12 Rue Lafayette', 'Paris', 'Ile-de-France', 'France', '75009', 'GOOD', 15, 52, '7788990011', NULL, NULL, 'info@fromagedelicieux.fr', NULL, 'M', NULL, 'Dystrybutor serów', NULL, NULL),
(378, 'Sakura Electronics', '', '03-1234-5678', '2-4-1 Shibuya', 'Tokyo', 'Tokyo', 'Japan', '150-0002', 'EXCELLENT', 14, 43, '1234567890', NULL, NULL, 'contact@sakura.jp', NULL, 'M', NULL, 'Współpraca technologiczna', NULL, NULL),
(379, 'Sol y Mar S.A.', '', '91 123 45 67', 'Calle Mayor 10', 'Madrid', 'Madrid', 'Spain', '28013', 'GOOD', 11, 52, '5566778899', NULL, NULL, 'ventas@solymar.es', NULL, 'M', NULL, 'Nowy rynek UE', NULL, NULL),
(380, 'Maple Leaf Goods', '', '416-555-7890', '89 Bloor Street', 'Toronto', 'Ontario', 'Canada', 'M5S 1W7', 'POOR', 9, 11, '1122334455', NULL, NULL, 'hello@mapleleaf.ca', NULL, 'M', NULL, 'Klient B2B', NULL, NULL),
(381, 'Pasta & Co.', '', '06-9876543', 'Via Roma 22', 'Rome', 'Lazio', 'Italy', '00184', 'EXCELLENT', 10, 53, '6677889900', NULL, NULL, 'info@pastaco.it', NULL, 'M', NULL, 'Partner gastronomiczny', NULL, NULL),
(382, 'Bharat Textiles', '', '011-23456789', 'MG Road 101', 'Mumbai', 'Maharashtra', 'India', '400001', 'GOOD', 8, 42, '9988776655', NULL, NULL, 'sales@bharattextiles.in', NULL, 'M', NULL, 'Producent tekstyliów', NULL, NULL),
(383, 'Koala Supplies', '', '02-9876-5432', '77 George St', 'Sydney', 'NSW', 'Australia', '2000', 'EXCELLENT', 13, 83, '2233445566', NULL, NULL, 'support@koalasupplies.au', NULL, 'M', NULL, 'Zamówienie cykliczne', NULL, NULL),
(384, 'Rio Imports Ltda.', '', '21 1234 5678', 'Av. Atlântica 3000', 'Rio de Janeiro', 'RJ', 'Brazil', '22070-000', 'GOOD', 15, 25, '8877665544', NULL, NULL, 'contato@rioimports.com.br', NULL, 'M', NULL, 'Nowy dostawca', NULL, NULL),
(389, 'Acme Ltd', '', '2134234', 'ul. Słoneczna 7', 'Nowa Południowa Walia', 'Podkarpackie', 'Polska', '25-255', 'GOOD', 10, 55, '9182736450', NULL, NULL, 'acme_ltd@example.pl', NULL, 'M', NULL, 'Nowy klient z południa', NULL, NULL),
(390, 'Wezuwiusz Ltd.', '', '7879-012', 'ul. Wulkaniczna 1', 'Radomska', 'Świętokrzyskie', 'Polska', '25-250', '', 12, 55, '2817364950', NULL, NULL, 'wezuwiusz@example.pl', NULL, 'M', NULL, 'Zamówienie specjalne', NULL, NULL),
(391, 'ACME sp.z.o.o.', '', NULL, 'ul. Fabryczna 8', 'Niewachlów', 'Świętokrzyskie', 'Polska', '25-630', 'GOOD', 9, 55, '7362819450', NULL, NULL, 'acme800@example.pl', NULL, 'M', NULL, 'ACME kontakt B2B', NULL, NULL);

--
-- Wyzwalacze `customer`
--
DELIMITER $$
CREATE TRIGGER `customer_ai_tr` AFTER INSERT ON `customer` FOR EACH ROW BEGIN
    INSERT INTO oper (kto, operacja, tabela, rekord, uwagi)
    VALUES (
        USER(),
        'insert',
        'customer',
        NEW.id,
        CONCAT(NEW.name, ';',
            NEW.city, ';',
            NEW.country, ';'
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `customer_au_tr` AFTER UPDATE ON `customer` FOR EACH ROW BEGIN
    SET @changes = CONCAT(
        IF(OLD.name <> NEW.name,
            CONCAT(OLD.name, '->', NEW.name, ';'), ''),
        IF(OLD.city <> NEW.city,
            CONCAT(OLD.city, '->', NEW.city, ';'), ''),
        IF(OLD.country <> NEW.country,
            CONCAT(OLD.country, '->', NEW.country, ';'), ''),
        IF(OLD.phone <> NEW.phone,
            CONCAT(OLD.phone, '->', NEW.phone, ';'), ''),
        IF(OLD.email <> NEW.email,
            CONCAT(OLD.email, '->', NEW.email, ';'), ''),
        IF(OLD.nip <> NEW.nip,
            CONCAT(OLD.nip, '->', NEW.nip, ';'), '')
    );

    IF @changes <> '' THEN
        INSERT INTO oper (kto, operacja, tabela, rekord, uwagi)
        VALUES (USER(), 'update', 'customer', NEW.id, CONCAT('', @changes));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `customer_bd_tr` BEFORE DELETE ON `customer` FOR EACH ROW BEGIN
    INSERT INTO oper (kto, kiedy, operacja, tabela, rekord, uwagi)
    VALUES (
        USER(),
        NOW(),
        'delete',
        'customer',
        OLD.id,
        CONCAT(OLD.name, ';', OLD.city, ';', OLD.nip)
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `dept`
--

CREATE TABLE `dept` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(25) NOT NULL,
  `region_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dept`
--

INSERT INTO `dept` (`id`, `name`, `region_id`) VALUES
(50, 'Administration', 52),
(83, 'Administration', 55),
(71, 'Badania i Rozwój', 55),
(121, 'Cleaning', 55),
(63, 'Customer Service', 55),
(85, 'Customer Service', 55),
(102, 'Customer Service', 55),
(69, 'Dział Prawny', 55),
(10, 'Finance', 52),
(57, 'Finance', 53),
(88, 'Finance', 53),
(39, 'Finance', 55),
(87, 'Finance', 55),
(89, 'Finance', 55),
(58, 'HR', 55),
(90, 'HR', 55),
(92, 'Human Resources', 55),
(60, 'IT Support', 55),
(91, 'IT Support', 55),
(123, 'IT Support', 55),
(68, 'Kadry', 55),
(76, 'Kontrola Jakości', 55),
(59, 'Legal', 55),
(94, 'Legal', 55),
(86, 'Legal Department', 55),
(74, 'logisitics', 55),
(64, 'Logistics', 55),
(95, 'Logistics', 55),
(96, 'Logistics', 55),
(125, 'Management', 55),
(62, 'Marketing', 55),
(72, 'Marketing', 55),
(97, 'Marketing', 55),
(98, 'Marketing', 55),
(11, 'Obsługa', 52),
(14, 'Obsługa', 52),
(12, 'Obsługa', 55),
(73, 'Obsługa Klienta', 55),
(77, 'Ochrona', 55),
(43, 'Operations', 52),
(44, 'Operations', 54),
(41, 'Operations', 55),
(42, 'Operations', 55),
(45, 'Operations', 55),
(104, 'Operations', 55),
(105, 'Operations', 55),
(106, 'Operations', 55),
(107, 'Operations', 55),
(108, 'Operations', 55),
(109, 'Planning', 55),
(79, 'Planowanie', 55),
(65, 'Procurement', 55),
(110, 'Procurement', 55),
(82, 'Public Administartion', 55),
(111, 'Public Administration', 55),
(124, 'Purchasing', 55),
(66, 'Quality Assurance', 55),
(112, 'Quality Assurance', 55),
(93, 'Quality Control', 55),
(61, 'R&D', 55),
(113, 'R&D', 55),
(84, 'Research and Development', 55),
(32, 'Sales', 52),
(35, 'Sales', 52),
(33, 'Sales', 54),
(31, 'Sales', 55),
(34, 'Sales', 55),
(47, 'Sales', 55),
(114, 'Sales', 55),
(115, 'Sales', 55),
(116, 'Sales', 55),
(117, 'Sales', 55),
(118, 'Sales', 55),
(119, 'Sales', 55),
(67, 'Security', 55),
(103, 'Security', 55),
(120, 'Security', 55),
(100, 'Service', 52),
(101, 'Service', 52),
(99, 'Service', 55),
(78, 'Sprzątanie', 55),
(81, 'Szkolenia', 55),
(122, 'Training', 55),
(70, 'Wsparcie IT', 55),
(75, 'Zakupy', 55),
(80, 'Zarząd', 55);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `emp`
--

CREATE TABLE `emp` (
  `id` int(11) UNSIGNED NOT NULL,
  `last_name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `first_name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `manager_id` int(11) UNSIGNED DEFAULT NULL,
  `title` varchar(25) DEFAULT NULL,
  `dept_id` int(11) UNSIGNED DEFAULT NULL,
  `salary` decimal(11,2) DEFAULT NULL,
  `commission_pct` decimal(4,2) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('M','K') NOT NULL,
  `pesel` char(11) DEFAULT NULL,
  `street` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `house_nr` varchar(10) DEFAULT NULL,
  `zip_code` varchar(12) DEFAULT NULL,
  `city` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `country` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `nationality` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `employment_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `education` varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `profession` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `username` varchar(12) DEFAULT NULL,
  `password_hash` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emp`
--

INSERT INTO `emp` (`id`, `last_name`, `first_name`, `start_date`, `end_date`, `comments`, `manager_id`, `title`, `dept_id`, `salary`, `commission_pct`, `birth_date`, `gender`, `pesel`, `street`, `house_nr`, `zip_code`, `city`, `country`, `nationality`, `employment_type`, `phone`, `email`, `education`, `profession`, `username`, `password_hash`) VALUES
(1, 'Velasquez', 'Carmen', '1990-03-03 00:00:00', '2099-12-31', 'Carmen-Velasquez-03-03-1990', NULL, 'President', 50, 2500.00, 19.69, '1980-01-01', 'M', '10559787274', 'Kurze', '13B', '39-731', 'Berlin', 'Niemcy', 'Lithuanian', 'Full-time', '546811639', 'cave@example.com', '', 'Employee', 'cvelasqu', 'hashed_password'),
(2, 'Ngao', 'LaDoris', '1990-03-08 00:00:00', '2099-12-31', 'LaDoris-Ngao-1990-03-08 00:00:00\r\nZnalazłem LaDoris Ngao', 1, 'VP, Operations', 12, 1595.00, 99.99, '0000-00-00', 'M', NULL, '89A', '78-623', '', 'Katowice', 'Polska', 'Full-time', '590465416', 'lang@example.co', 'Bachelor', '', 'autofill', 'lngao', 'autofill'),
(3, 'Nagayama', 'Midori', '1991-06-17 00:00:00', '2099-12-31', 'Midori-Nagayama-17-06-1991', 1, 'VP, Sales', 33, 1400.00, 99.99, '2007-06-14', 'M', '0321931212', 'main', '1132', '87-810', 'Kiev', 'Ukrainian', 'Full-time', '521426813', 'mina@example.co', 'Bachelor', '', 'autofill', 'mnagayam', 'autofill'),
(4, 'Quick-To-See', 'Mark', '1990-04-07 00:00:00', '2099-12-31', 'Mark-Quick-To-See-07-04-1990', 1, 'VP, Finance', 39, 1800.00, 99.99, '0000-00-00', 'M', '3213123121', 'wielka', '12', '79-312', 'Katowice', 'Polish', 'Full-time', '561878017', 'maqu@example.co', 'Bachelor', '', 'autofill', 'mquickto', 'autofill'),
(5, 'Ropeburn', 'Audry', '2000-10-17 10:01:11', '2099-12-31', 'Audry-Ropeburn-2000-10-17 10:01:11', 1, 'VP, Administration', 50, 1550.00, 99.99, '0000-00-00', 'M', '31203124', 'Rue de Lyon', '20B', '20-321', 'Paris', 'France', 'Full-time', '588539243', 'auro@example.co', '', '', 'autofill', 'aropebur', 'autofill'),
(6, 'Urguhart', 'Molly', '1991-01-18 00:00:00', '2099-12-31', 'Molly-Urguhart-18-01-1991', 2, 'Warehouse Manager', 44, 1540.00, 14.39, '1980-01-01', 'M', '95859263717', 'Słoneczna', '60B', '23-474', 'Wilno', 'Litwa', 'French', 'Full-time', '562658024', 'mour@example.com', '', 'Employee', 'murguhar', 'hashed_password'),
(7, 'Menchu', 'Roberta', '1990-05-14 00:00:00', '2099-12-31', 'Roberta-Menchu-14-05-1990', 2, 'Sales Representative', 35, 1375.00, 15.95, '1980-01-01', 'M', '90681001347', 'Nowa', '41', '59-308', 'Madryt', 'Hiszpania', 'Spanish', 'Full-time', '530897341', 'rome@example.com', '', 'Employee', 'rmenchu', 'hashed_password'),
(8, 'Biri', 'Ben', '1990-04-07 00:00:00', '2099-12-31', 'Ben-Biri-07-04-1990', 2, 'Warehouse Manager', 43, 1210.00, 10.06, '1980-01-01', 'M', '78154026515', 'Parkowa', '33B', '60-860', 'Hamburg', 'Niemcy', 'German', 'Full-time', '535054424', 'bebi@example.com', '', 'Employee', 'bbiri', 'hashed_password'),
(9, 'Catchpole', 'Antoinette', '1992-02-09 00:00:00', '2099-12-31', 'Antoinette-Catchpole-09-02-1992', 2, 'Warehouse Manager', 12, 1430.00, 18.91, '1980-01-01', 'K', '83277404462', 'Leśna', '81B', '10-249', 'Gdańsk', 'Polska', 'Ukrainian', 'Full-time', '541112289', 'anca@example.com', '', 'Employee', 'acatchpole', 'hashed_password'),
(10, 'Havel', 'Marta', '1991-02-27 00:00:00', '2099-12-31', 'Podwyżka', 2, 'Singer', 45, 1877.70, 11.26, '1980-01-01', 'M', '82768896861', 'Kwiatowa', '16B', '38-714', 'Bratysława', 'Słowacja', 'Slovak', 'Full-time', '543885794', 'maha@example.com', '', 'Employee', 'mhavel', 'hashed_password'),
(11, 'Nagayama', 'Colin', '1990-05-14 00:00:00', '2099-12-31', 'Colin-Nagayama-14-05-1990', 3, 'Sales Representative', 31, 1400.00, 10.00, '1980-01-01', 'M', '96970255173', 'Kwiatowa', '87A', '31-445', 'Bratysława', 'Słowacja', 'Italian', 'Full-time', '574425141', 'cona@example.com', '', 'Employee', 'cmagee', 'hashed_password'),
(12, 'Henry', 'Fonda', '1992-01-18 00:00:00', '2099-12-31', 'Henry-Giljum-18-01-1992', 3, 'Sales Representative', 32, 1490.00, 12.50, '1980-01-01', 'M', '41975512589', 'Krótka', '82B', '27-344', 'Barcelona', 'Hiszpania', 'Spanish', 'Full-time', '506308170', 'fohe@example.com', '', 'Employee', 'hfonda', 'hashed_password'),
(13, 'Sedeghi', 'Yasmina', '1991-02-08 00:00:00', '2099-12-31', 'Yasmin-Sedeghi-08-02-1991', 3, 'Sales Representative', 33, 1515.00, 10.00, '1980-01-01', 'M', '41291752622', 'Długa', '53A', '27-962', 'Lwów', 'Ukraina', 'Italian', 'Full-time', '586768639', 'yase@example.com', '', 'Employee', 'ysedeghi', 'hashed_password'),
(14, 'Nguyen', 'Mai', '1992-01-22 00:00:00', '2099-12-31', 'Mai-Nguyen-22-01-1992', 3, 'Sales Representative', 34, 1525.00, 15.00, '1980-01-01', 'M', '40237972980', 'Długa', '44B', '66-841', 'Koszyce', 'Słowacja', 'Lithuanian', 'Full-time', '555876173', 'mang@example.com', '', 'Employee', 'mnguyen', 'hashed_password'),
(15, 'Dumas', 'Andre', '1991-10-09 00:00:00', '2099-12-31', 'Andre-Dumas-09-10-1991', 3, 'Sales Representative', 35, 1450.00, 17.50, '1980-01-01', 'M', '82596504578', 'Kwiatowa', '51A', '68-661', 'Paryż', 'Francja', 'Polish', 'Full-time', '515537820', 'andu@example.com', '', 'Employee', 'adumas', 'hashed_password'),
(16, 'Maduro', 'Elena', '1992-02-07 00:00:00', '2099-12-31', 'Elena-Maduro-07-02-1992', 6, 'Stock Clerk', 10, 1540.00, 11.84, '1980-01-01', 'M', '62314916653', 'Parkowa', '25B', '39-627', 'Bruksela', 'Belgia', 'Polish', 'Full-time', '524447003', 'elma@example.com', '', 'Employee', 'emaduro', 'hashed_password'),
(17, 'Smith', 'George', '1990-03-08 00:00:00', '2099-12-31', 'George-Smith-08-03-1990', 6, 'Stock Clerk', 33, 1034.00, 19.65, '1980-01-01', 'M', '14750261673', 'Parkowa', '50', '68-304', 'Zagrzeb', 'Chorwacja', 'Ukrainian', 'Full-time', '527773413', 'gesm@example.com', '', 'Employee', 'gsmith', 'hashed_password'),
(18, 'Nozaki', 'Akira', '1991-02-09 00:00:00', '2099-12-31', 'Akira-Nozaki-09-02-1991', 7, 'Stock Clerk', 43, 1320.00, 12.91, '1980-01-01', 'M', '56531838911', 'Lipowa', '17A', '58-816', 'Paryż', 'Francja', 'Czech', 'Full-time', '512165861', 'akno@example.com', '', 'Employee', 'anozaki', 'hashed_password'),
(19, 'Patel', 'Vikram', '1991-08-06 00:00:00', '2099-12-31', 'Vikram-Patel-06-08-1991', 7, 'Stock Clerk', 10, 1100.00, 18.67, '1980-01-01', 'M', '74628105022', 'Parkowa', '33', '65-475', 'Paryż', 'Francja', 'Francja', 'Full-time', '531166263', 'vipa@example.com', '', 'Employee', 'vpatel', 'hashed_password'),
(20, 'Newman', 'Chad', '1991-07-21 00:00:00', '2099-12-31', 'Chad-Newman-21-07-1991', 8, 'Stock Clerk', 43, 825.00, 7.67, '1980-01-01', 'M', '15867546857', 'Kwiatowa', '98', '69-863', 'Berlin', 'Niemcy', 'Slovak', 'Full-time', '586011268', 'chne@example.com', '', 'Employee', 'cnewman', 'hashed_password'),
(21, 'Biri', 'Alexander', '1991-05-26 00:00:00', '2099-12-31', 'Alexander-Biri-26-05-1991', 8, 'Stock Clerk', 43, 935.00, 8.26, '1980-01-01', 'M', '76209072917', 'Polna', '37', '74-334', 'Madryt', 'Hiszpania', 'Ukrainian', 'Full-time', '514970926', 'albi@example.com', '', 'Employee', 'amarkari', 'hashed_password'),
(22, 'Chang', 'Eddie', '1990-11-30 00:00:00', '2099-12-31', 'Eddie-Chang-30-11-1990', 9, 'Stock Clerk', 44, 880.00, 11.82, '1980-01-01', 'M', '13022169680', 'Lipowa', '39', '40-226', 'Kijów', 'Ukraina', 'French', 'Full-time', '592113271', 'edch@example.com', '', 'Employee', 'echang', 'hashed_password'),
(23, 'Patel', 'Radha', '1990-10-17 00:00:00', '2099-12-31', 'Radha-Patel-17-10-1990', 9, 'Stock Clerk', 34, 874.50, 16.97, '1980-01-01', 'M', '94389886562', 'Szkolna', '34B', '20-473', 'Brno', 'Czechy', 'Spanish', 'Full-time', '582214853', 'rapa@example.com', '', 'Employee', 'rpatel', 'hashed_password'),
(24, 'Dancs', 'Bela', '1991-03-17 00:00:00', '2099-12-31', 'Bela-Dancs-17-03-1991', 10, 'Stock Clerk', 45, 946.00, 8.56, '1980-01-01', 'M', '62143632482', 'Lipowa', '66', '86-740', 'Praga', 'Czechy', 'Polish', 'Full-time', '592743833', 'beda@example.com', '', 'Employee', 'bdancs', 'hashed_password'),
(25, 'Schwartz', 'Sylvie', '1991-05-09 00:00:00', '2099-12-31', 'Sylvie-Schwartz-09-05-1991', 10, 'Stock Clerk', 45, 1210.00, 7.13, '1980-01-01', 'M', '28214837244', 'Kwiatowa', '71', '75-700', 'Bratysława', 'Słowacja', 'Italian', 'Full-time', '561961133', 'sysc@example.com', '', 'Employee', 'sschwart', 'hashed_password'),
(51, 'Schwarzenegger', 'Arnold', '1999-02-26 16:18:17', '2099-12-31', 'Arnold-Schwarzenegger-1999-02-26 16:18:17', 2, 'Actor', 12, 1894.20, 18.37, '1980-01-01', 'M', '57387194280', 'Leśna', '13B', '33-895', 'Kielce', 'Polska', 'Slovak', 'Full-time', '568909944', 'arsc@example.com', '', 'Employee', 'arsch', 'hashed_password'),
(52, 'Stallone', 'Milo', '2020-08-12 12:28:11', '2099-12-31', 'Milo-Stallone-2020-08-12 12:28:11', 10, 'Actor', 45, 2464.00, 18.09, '1980-01-01', 'M', '31188887487', 'Parkowa', '64', '90-670', 'Kraków', 'Polska', 'Italian', 'Full-time', '540405549', 'mist@example.com', '', 'Employee', 'milstall', 'hashed_password'),
(54, 'Radełko', 'Michał', '1999-01-12 00:00:00', '2099-12-31', 'Michał-Radełko-1999-01-12 00:00:00', 10, 'Singer', 45, 1100.00, 16.35, '1980-01-01', 'M', '96786361461', 'Słoneczna', '24', '58-759', 'Warszawa', 'Polska', 'Spanish', 'Full-time', '555929647', 'mira@example.com', '', 'Employee', 'mfrad', 'hashed_password'),
(84, 'Pazura', 'Cezary', '2017-11-15 08:02:26', '2099-12-31', 'Cezary-Pazura-2017-11-15 08:02:26', 10, 'Singer', 12, 3798.30, 17.38, '1980-01-01', 'M', '10816700750', 'Polna', '61', '37-480', 'Brno', 'Czechy', 'Slovak', 'Full-time', '598967680', 'cepa@example.com', '', 'Employee', 'cep', 'hashed_password'),
(85, 'Pazura', 'Adam', '2010-05-19 07:26:43', '2099-12-31', 'Adam-Pazura-2010-05-19 07:26:43', 10, 'Singer', 12, 3801.60, 15.79, '1980-01-01', 'M', '71106296721', 'Leśna', '85A', '92-666', 'Budapeszt', 'Węgry', 'Lithuanian', 'Full-time', '535644549', 'adpa@example.com', '', 'Employee', 'pazadam', 'hashed_password'),
(89, 'Nowakowski', 'Krzysztof', '2020-06-01 00:00:00', '2099-12-31', 'Krzysztof-Nowakowski-2020-06-01 00:00:00', 10, 'Singer', 10, 4950.00, 13.71, '1980-01-01', 'M', '47599959626', 'Szkolna', '93A', '83-237', 'Madryt', 'Hiszpania', 'Spanish', 'Full-time', '514047619', 'krno@example.com', '', 'Employee', 'knowa', 'hashed_password'),
(90, 'Krawczyk', 'Hipolit', '1998-12-16 09:53:20', '2099-12-31', 'Hipolit-Krawczyk-1998-12-16 09:53:20', 10, 'Actor', 12, 854.70, 11.07, '1980-01-01', 'M', '59055688938', 'Polna', '36', '36-450', 'Praga', 'Czechy', 'German', 'Full-time', '581788963', 'hikr@example.com', '', 'Employee', 'hipkra', 'hashed_password'),
(91, 'Nienowakowski', 'Jacenty', '2020-06-01 00:00:00', '2099-12-31', 'Jacenty-Nienowakowski-2020-06-01 00:00:00', 21, 'Singer', 10, 611.60, 16.88, '1980-01-01', 'M', '77433260117', 'Leśna', '7A', '43-954', 'Lyon', 'Francja', 'French', 'Full-time', '590548638', 'jani@example.com', '', 'Employee', 'janow', 'hashed_password'),
(92, 'Nienowak', 'Witold', '2020-06-02 00:00:00', '2099-12-31', 'Witold-Nienowak-2020-06-02 00:00:00', 10, 'Singer', 10, 855.78, 19.09, '1980-01-01', 'M', '73944676456', 'Szkolna', '94', '67-482', 'Berlin', 'Niemcy', 'Czech', 'Full-time', '512846948', 'wini@example.com', '', 'Employee', 'wnienow', 'hashed_password'),
(93, 'Nienowak', 'Witold', '2020-06-02 00:00:00', '2099-12-31', 'Witold-Nienowak-2020-06-02 00:00:00', 10, 'Singer', 10, 855.78, 7.86, '1980-01-01', 'M', '74181934427', 'Polna', '99B', '42-940', 'Kolonia', 'Niemcy', 'Spanish', 'Full-time', '593500752', 'wini@example.com', '', 'Employee', 'wnienowak', 'hashed_password'),
(94, 'Pazura', 'Maciej', '2019-07-17 06:31:14', '2099-12-31', 'Maciej-Pazura-2019-07-17 06:31:14', 10, 'Singer', 12, 3801.60, 10.27, '1980-01-01', 'M', '44484121765', 'Krótka', '3A', '46-587', 'Bratysława', 'Słowacja', 'Spanish', 'Full-time', '549209983', 'mapa@example.com', '', 'Employee', 'macpaz', 'hashed_password'),
(95, 'Pazura', 'Alfons', '2005-09-14 18:59:00', '2099-12-31', 'Alfons-Pazura-2005-09-14 18:59:00', 12, 'Actor', 10, 675.00, 14.86, '1980-01-01', 'M', '41037937060', 'Długa', '76A', '40-192', 'Lyon', 'Francja', 'Czech', 'Full-time', '598889922', 'alpa@example.com', '', 'Employee', 'jhgjhg', 'hashed_password'),
(110, 'Nowak', 'Jan', '1990-01-01 00:00:00', '2099-12-31', 'Jan-Nowak-1990-01-01 00:00:00', 5, 'Singer', 10, 1220.00, 14.91, '1980-01-01', 'M', '39645220430', 'Krótka', '62B', '31-870', 'Berlin', 'Niemcy', 'Italian', 'Full-time', '502192852', 'jano@example.com', '', 'Employee', 'janowak', 'hashed_password'),
(116, 'Nowak', 'Zenon', '1900-01-01 00:00:00', '2099-12-31', 'Zenon-Nowak-1900-01-01 00:00:00', 10, 'Actor', 12, 9788.90, 7.77, '1980-01-01', 'M', '81805464756', 'Krótka', '23A', '66-627', 'Lublin', 'Polska', 'Lithuanian', 'Full-time', '543594389', 'zeno@example.com', '', 'Employee', 'zenow', 'hashed_password'),
(122, 'Jankowski', 'Alain', '2023-07-11 09:00:00', '2099-12-31', 'Alain-Jankowski-2023-07-11 09:00:00', 17, 'Stock Clerk', 31, 0.00, 8.05, '1980-01-01', 'M', '20836284853', 'Leśna', '86A', '73-851', 'Kraków', 'Polska', 'Italian', 'Full-time', '596567915', 'alja@example.com', '', 'Employee', 'JanAlain', 'hashed_password'),
(124, 'Cielebąk', 'Jan', '2016-04-19 09:28:39', '2099-12-31', 'Jan-Cielebąk-2016-04-19 09:28:39', 10, 'Actor', 12, 1333.20, 12.44, '1980-01-01', 'M', '54650406112', 'Krótka', '68B', '18-391', 'Brno', 'Czechy', 'German', 'Full-time', '571607503', 'jaci@example.com', '', 'Employee', 'CieJa', 'hashed_password'),
(135, 'Nowacki', 'Zygmunt', '1999-12-31 00:00:00', '2099-12-31', 'Zygmunt-Nowacki-1999-12-31 00:00:00', 5, 'VP, Finance', 10, 123.00, 99.99, '0000-00-00', 'M', NULL, '99', '49-617', 'Bydgoszcz', 'Lizbona', 'Portugalia', 'Full-time', '538598087', 'zyno@example.co', 'Bachelor', '', 'autofill', 'nojac', 'autofill'),
(140, 'Nowak', 'Jozef', '1900-01-07 00:00:00', '2099-12-31', 'Jozef-Nowak-1900-01-07 00:00:00', 5, 'VP, Finance', 10, 1121.00, 99.99, '0000-00-00', 'M', '12312424534', '91', '33-745', 'Kraków', 'Paryż', 'Francja', 'Full-time', '589216625', 'jono@example.co', 'Bachelor', '', 'autofill', 'jonow', 'autofill'),
(153, 'Davies', 'Norman', '2010-07-21 09:19:00', '2099-12-31', 'Norman-Davies-2010-07-21 09:19:00', 230, 'Singer', 41, 1234.20, 11.10, '1980-01-01', 'M', '65980996559', 'Parkowa', '1', '10-787', 'Praga', 'Czechy', 'Polish', 'Full-time', '597279853', 'noda@example.com', '', 'Employee', 'Nordav', 'hashed_password'),
(154, 'Głowacki', 'Aleksander', '2005-06-16 18:00:30', '2099-12-31', 'Aleksander-Głowacki-2005-06-16 18:00:30', 9, 'Stock Clerk', 35, 3300.00, 7.74, '1980-01-01', 'M', '46201902727', 'Lipowa', '1B', '86-251', 'Hamburg', 'Niemcy', 'Czech', 'Full-time', '575358824', 'algł@example.com', '', 'Employee', 'gloolek', 'hashed_password'),
(220, 'Gęsiński', 'Męczysław', '1999-03-17 07:03:40', '2099-12-31', 'Męczysław-Gęsiński-1999-03-17 07:03:40', 2, 'Singer', 45, 0.00, 8.98, '1980-01-01', 'M', '46430843450', 'Słoneczna', '31A', '67-110', 'Bratysława', 'Słowacja', 'Norwegian', 'Full-time', '501060524', 'męgę@example.com', '', 'Employee', 'gęgę', 'hashed_password'),
(224, 'Braun', 'Włodzimierz', '2015-02-02 11:00:00', '2099-12-31', 'Włodzimierz-Braun-2015-02-02 11:00:00', 10, 'Actor', 45, 0.00, 18.20, '1980-01-01', 'M', '53865081942', 'Lipowa', '97', '28-590', 'Brno', 'Czechy', 'Norwegian', 'Full-time', '531636678', 'włbr@example.com', '', 'Employee', 'wlobran', 'hashed_password'),
(225, 'Stallone ', 'Sylwester', '1997-08-13 10:01:39', '2099-12-31', 'Sylwester-Stallone-1997-08-13 10:01:39', 10, 'Stock Clerk', 45, 0.00, 15.34, '1980-01-01', 'M', '42076140614', 'Nowa', '4B', '91-186', 'Praga', 'Czechy', 'Czech', 'Full-time', '542478990', 'syst@example.com', '', 'Employee', 'sylstall', 'hashed_password'),
(230, 'Kozieł', 'Grzegorz', '2011-09-15 17:02:27', '2099-12-31', 'Grzegorz-Kozieł-2011-09-15 17:02:27\r\nUwaga!!!!', 16, 'Warehouse Manager', 45, 1235.30, 19.26, '1980-01-01', 'M', '10391452371', 'Słoneczna', '61B', '29-364', 'Poznań', 'Polska', 'Italian', 'Full-time', '563242779', 'grko@example.com', '', 'Employee', 'gkoziel', 'hashed_password'),
(233, 'Nowak', 'Jacenty', '2008-09-17 07:04:16', '2099-12-31', 'Jacenty-Nowak-2008-09-17 07:04:16\r\nuwaga!', 2, 'VP, Operations', 45, 3300.00, 99.99, '0000-00-00', 'M', NULL, '75A', '40-848', 'Gdańsk', 'Kielce', 'Polska', 'Full-time', '562886845', 'jano@example.co', 'Bachelor', '', 'autofill', 'noja', 'autofill'),
(235, 'Makota', 'Aleksander', '1999-01-01 00:00:00', '2099-12-31', 'Aleksander-Makota-1999-01-01 00:00:00', 15, 'Actor', 32, 1234.00, 16.20, '1980-01-01', 'M', '45070171343', 'Kwiatowa', '29B', '25-429', 'Berlin', 'Niemcy', 'Polish', 'Full-time', '546796389', 'alma@example.com', '', 'Employee', 'alemakot', 'hashed_password'),
(265, 'Radełko', 'Michał', '1999-01-01 00:00:00', '2099-12-31', 'Michał-Radełko-1999-01-01 00:00:00\r\nNowy', 10, 'Singer', 45, 6230.40, 17.07, '1980-01-01', 'M', '77491205502', 'Krótka', '34', '26-519', 'Warszawa', 'Polska', 'Ukrainian', 'Full-time', '515012333', 'mira@example.com', '', 'Employee', 'mirad', 'hashed_password'),
(266, 'Ryśków', 'Krzysztof', '1999-01-02 00:00:00', '2099-12-31', 'Krzysztof-Ryśków-1999-01-02 00:00:00\r\nStary', 8, 'Singer', 45, 10985.70, 14.35, '1980-01-01', 'M', '28965220299', 'Parkowa', '79B', '28-465', 'Budapeszt', 'Węgry', 'Italian', 'Full-time', '536120743', 'krry@example.com', '', 'Employee', 'kirys', 'hashed_password'),
(267, 'Las', 'Ronald', '1999-01-03 00:00:00', '2099-12-31', 'Ronald-Las-1999-01-03 00:00:00', 7, 'Singer', 45, 2542.10, 19.93, '1980-01-01', 'M', '77900979088', 'Szkolna', '54A', '94-945', 'Bratysława', 'Słowacja', 'Lithuanian', 'Full-time', '546846251', 'rola@example.com', '', 'Employee', 'rolas', 'hashed_password'),
(268, 'Jonas', 'Dariusz', '1999-01-04 00:00:00', '2099-12-31', 'Dariusz-Jonas-1999-01-04 00:00:00', 51, 'Stock Clerk', 45, 7428.30, 15.13, '1980-01-01', 'M', '91036129528', 'Parkowa', '56B', '17-924', 'Kielce', 'Polska', 'Polish', 'Full-time', '515644329', 'dajo@example.com', '', 'Employee', 'darjo', 'hashed_password'),
(270, 'Assasin', 'Mark', '1999-01-07 00:00:00', '2099-12-31', 'Mark-Assasin-1999-01-07 00:00:00', 8, 'Sales Representative', 45, 1234.20, 15.13, '1980-01-01', 'M', '39287177293', 'Nowa', '34', '76-446', 'Brno', 'Czechy', 'Czech', 'Full-time', '553357395', 'maas@example.com', '', 'Employee', 'maas', 'hashed_password'),
(271, 'Nowak', 'Jan', '2006-06-21 08:04:51', '2099-12-31', 'Jan-Nowak-2006-06-21 08:04:51', 6, 'Actor', 12, 0.00, 9.90, '1980-01-01', 'M', '25406899046', 'Kwiatowa', '39B', '37-410', 'Kraków', 'Polska', 'German', 'Full-time', '511511772', 'jano@example.com', '', 'Employee', 'Janno', 'hashed_password'),
(302, 'Makota', 'Alicja', '2000-06-12 07:05:18', '2099-12-31', 'Alicja-Makota-2000-06-12 07:05:18', 5, 'Singer', 44, 0.00, 19.18, '1980-01-01', 'M', '99656994228', 'Krótka', '36', '44-611', 'Tirana', 'Albania', 'Czech', 'Full-time', '597177355', 'alma@example.com', '', 'Employee', 'alamakot', 'hashed_password'),
(318, 'Nowakowski', 'Maciek', '2015-02-10 18:06:04', '2099-12-31', 'Maciek-Nowakowski-2015-02-10 18:06:04', 2, 'Actor', 45, 10253.10, 11.68, '1980-01-01', 'M', '70799105415', 'Słoneczna', '54B', '34-619', 'Warszawa', 'Polska', 'Slovak', 'Full-time', '504271930', 'mano@example.com', '', 'Employee', 'macnow', 'hashed_password'),
(319, 'Stallone', 'Wacław', '2007-01-16 19:06:38', '2099-12-31', 'Wacław-Stallone-2007-01-16 19:06:38', 4, 'Actor', 12, 0.00, 15.74, '1980-01-01', 'M', '40761407218', 'Kwiatowa', '54', '29-766', 'Kielce', 'Polska', 'Czech', 'Full-time', '530679506', 'wast@example.com', '', 'Employee', 'wacstall', 'hashed_password');

--
-- Wyzwalacze `emp`
--
DELIMITER $$
CREATE TRIGGER `emp_ai_tr` AFTER INSERT ON `emp` FOR EACH ROW BEGIN
    INSERT INTO oper (kto, operacja, tabela, rekord, uwagi)
    VALUES (
        USER(),
        'insert',
        'emp',
        NEW.id,
        CONCAT(
            NEW.first_name, ';',
            NEW.last_name, ';',
            NEW.title, ';',
            NEW.city, ';',
            NEW.salary
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `emp_au_tr` AFTER UPDATE ON `emp` FOR EACH ROW BEGIN
    SET @changes = CONCAT(
        IF(OLD.first_name <> NEW.first_name,
            CONCAT(OLD.first_name, '->', NEW.first_name, ';'), ''),
        IF(OLD.last_name <> NEW.last_name,
            CONCAT(OLD.last_name, '->', NEW.last_name, ';'), ''),
        IF(OLD.title <> NEW.title,
            CONCAT(OLD.title, '->', NEW.title, ';'), ''),
        IF(OLD.city <> NEW.city,
            CONCAT(OLD.city, '->', NEW.city, ';'), ''),
        IF(OLD.salary <> NEW.salary,
            CONCAT(OLD.salary, '->', NEW.salary, ';'), ''),
        IF(OLD.email <> NEW.email,
            CONCAT(OLD.email, '->', NEW.email, ';'), '')
    );

    IF @changes <> '' THEN
        INSERT INTO oper (kto, operacja, tabela, rekord, uwagi)
        VALUES (USER(), 'update', 'emp', NEW.id, CONCAT('', @changes));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `emp_bd_tr` BEFORE UPDATE ON `emp` FOR EACH ROW BEGIN
    INSERT INTO oper (kto, kiedy, operacja, tabela, rekord, uwagi)
    VALUES (
        USER(),
        NOW(),
        'delete',
        'emp',
        OLD.id,
        CONCAT(
            OLD.first_name, ';', OLD.last_name, ';', OLD.title,';', OLD.city)
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `inventory`
--

CREATE TABLE `inventory` (
  `product_id` int(11) UNSIGNED NOT NULL,
  `warehouse_id` int(11) UNSIGNED NOT NULL,
  `amount_in_stock` int(11) NOT NULL,
  `reorder_point` int(11) DEFAULT NULL,
  `max_in_stock` int(11) DEFAULT NULL,
  `out_of_stock_explanation` varchar(255) DEFAULT NULL,
  `restock_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`product_id`, `warehouse_id`, `amount_in_stock`, `reorder_point`, `max_in_stock`, `out_of_stock_explanation`, `restock_date`) VALUES
(10011, 4, 657, 40, 700, NULL, NULL),
(10011, 101, 650, 625, 1100, NULL, NULL),
(10011, 10502, 34, 20, 100, NULL, NULL),
(10011, 10503, 110, 15, 255, NULL, NULL),
(10011, 10504, 243, 43, 508, NULL, NULL),
(10012, 4, 54, 50, 100, NULL, NULL),
(10012, 5, 98, 40, 1000, NULL, NULL),
(10012, 101, 600, 560, 1000, NULL, NULL),
(10012, 10501, 300, 300, 525, NULL, NULL),
(10012, 10502, 87, 80, 900, NULL, NULL),
(10012, 10503, 100, 20, 200, NULL, NULL),
(10012, 10504, 359, 25, 400, NULL, NULL),
(10013, 101, 400, 400, 700, NULL, NULL),
(10013, 10501, 314, 300, 525, NULL, NULL),
(10021, 4, 433, 20, 600, NULL, NULL),
(10021, 101, 500, 425, 740, NULL, NULL),
(10021, 10502, 86, 25, 100, NULL, NULL),
(10021, 10503, 432, 40, 500, NULL, NULL),
(10022, 4, 22, 15, 100, NULL, NULL),
(10022, 5, 566, 60, 600, NULL, NULL),
(10022, 101, 300, 200, 350, NULL, NULL),
(10022, 10501, 502, 300, 525, NULL, NULL),
(10022, 10502, 65, 50, 100, NULL, NULL),
(10022, 10503, 200, 20, 400, NULL, NULL),
(10022, 10504, 478, 77, 2000, NULL, NULL),
(10023, 101, 400, 300, 525, NULL, NULL),
(10023, 10501, 500, 300, 525, NULL, NULL),
(20106, 101, 993, 625, 1000, NULL, NULL),
(20106, 201, 220, 150, 260, NULL, NULL),
(20106, 10501, 150, 100, 175, NULL, NULL),
(20106, 10502, 632, 70, 800, NULL, NULL),
(20108, 101, 700, 700, 1225, NULL, NULL),
(20108, 201, 166, 150, 260, NULL, NULL),
(20108, 10501, 222, 200, 350, NULL, NULL),
(20108, 10502, 78, 70, 100, NULL, NULL),
(20201, 101, 802, 800, 1400, NULL, NULL),
(20201, 201, 320, 200, 350, NULL, NULL),
(20201, 10501, 275, 200, 350, NULL, NULL),
(20201, 10502, 446, 75, 500, NULL, NULL),
(20510, 4, 908, 85, 1000, NULL, NULL),
(20510, 5, 999, 800, 1000, NULL, NULL),
(20510, 101, 1389, 850, 1400, NULL, NULL),
(20510, 201, 175, 100, 175, NULL, NULL),
(20510, 301, 69, 40, 100, NULL, NULL),
(20510, 401, 88, 50, 100, NULL, NULL),
(20510, 10501, 57, 50, 87, NULL, NULL),
(20510, 10503, 260, 30, 300, NULL, NULL),
(20510, 10504, 171, 20, 180, NULL, NULL),
(20512, 4, 65, 40, 400, NULL, NULL),
(20512, 5, 889, 20, 4000, NULL, NULL),
(20512, 101, 850, 850, 1450, NULL, NULL),
(20512, 201, 162, 100, 175, NULL, NULL),
(20512, 301, 28, 20, 50, NULL, NULL),
(20512, 401, 75, 75, 140, NULL, NULL),
(20512, 10501, 62, 50, 87, NULL, NULL),
(20512, 10503, 700, 50, 900, NULL, NULL),
(20512, 10504, 800, 99, 900, NULL, NULL),
(30321, 4, 67, 44, 500, NULL, NULL),
(30321, 101, 2000, 1500, 2500, NULL, NULL),
(30321, 201, 96, 80, 140, NULL, NULL),
(30321, 301, 85, 80, 140, NULL, NULL),
(30321, 401, 102, 80, 140, NULL, NULL),
(30321, 10501, 194, 150, 275, NULL, NULL),
(30326, 101, 2100, 2000, 3500, NULL, NULL),
(30326, 201, 147, 120, 210, NULL, NULL),
(30326, 401, 113, 80, 140, NULL, NULL),
(30326, 10501, 277, 250, 440, NULL, NULL),
(30326, 10503, 21, 20, 300, NULL, NULL),
(30326, 10504, 679, 80, 700, NULL, NULL),
(30421, 4, 457, 70, 500, NULL, NULL),
(30421, 101, 1822, 1800, 3150, NULL, NULL),
(30421, 201, 102, 80, 140, NULL, NULL),
(30421, 301, 102, 80, 140, NULL, NULL),
(30421, 401, 85, 80, 140, NULL, NULL),
(30421, 10501, 190, 150, 275, NULL, NULL),
(30426, 101, 2250, 2000, 3500, NULL, NULL),
(30426, 201, 200, 120, 210, NULL, NULL),
(30426, 401, 135, 80, 140, NULL, NULL),
(30426, 10501, 423, 250, 450, NULL, NULL),
(30426, 10503, 923, 100, 1000, NULL, NULL),
(30433, 101, 650, 600, 1050, NULL, NULL),
(30433, 201, 130, 130, 230, NULL, NULL),
(30433, 301, 35, 20, 35, NULL, NULL),
(30433, 401, 0, 100, 175, 'A defective shipment was sent to Hong Kong and needed to be returned.\r\nThe soonest ACME can turn this around is early February.', '1992-08-07 00:00:00'),
(30433, 10501, 273, 200, 350, NULL, NULL),
(32779, 101, 2120, 1250, 2200, NULL, NULL),
(32779, 201, 180, 150, 260, NULL, NULL),
(32779, 301, 102, 95, 175, NULL, NULL),
(32779, 401, 135, 100, 175, NULL, NULL),
(32779, 10501, 280, 200, 350, NULL, NULL),
(32779, 10502, 980, 60, 1200, NULL, NULL),
(32861, 101, 505, 500, 875, NULL, NULL),
(32861, 201, 132, 80, 140, NULL, NULL),
(32861, 301, 57, 50, 100, NULL, NULL),
(32861, 401, 250, 150, 250, NULL, NULL),
(32861, 10501, 288, 200, 350, NULL, NULL),
(40421, 4, 33, 20, 100, NULL, NULL),
(40421, 5, 78, 45, 200, NULL, NULL),
(40421, 101, 578, 350, 600, NULL, NULL),
(40421, 301, 70, 40, 70, NULL, NULL),
(40421, 401, 47, 40, 70, NULL, NULL),
(40421, 10501, 97, 80, 140, NULL, NULL),
(40421, 10502, 67, 50, 100, NULL, NULL),
(40421, 10503, 520, 90, 600, NULL, NULL),
(40421, 10504, 999, 200, 1000, NULL, NULL),
(40422, 101, 0, 350, 600, 'Phenomenal sales...', '1993-02-08 00:00:00'),
(40422, 301, 65, 40, 70, NULL, NULL),
(40422, 401, 50, 40, 70, NULL, NULL),
(40422, 10501, 90, 80, 140, NULL, NULL),
(41010, 101, 250, 250, 437, NULL, NULL),
(41010, 301, 59, 40, 70, NULL, NULL),
(41010, 401, 80, 70, 220, NULL, NULL),
(41010, 10501, 151, 140, 245, NULL, NULL),
(41010, 10502, 62, 50, 100, NULL, NULL),
(41020, 101, 471, 450, 750, NULL, NULL),
(41020, 301, 61, 40, 70, NULL, NULL),
(41020, 401, 91, 70, 220, NULL, NULL),
(41020, 10501, 224, 140, 245, NULL, NULL),
(41020, 10502, 90, 20, 100, NULL, NULL),
(41050, 101, 501, 450, 750, NULL, NULL),
(41050, 301, 49, 40, 70, NULL, NULL),
(41050, 401, 169, 70, 220, NULL, NULL),
(41050, 10501, 157, 140, 245, NULL, NULL),
(41050, 10502, 100, 25, 100, NULL, NULL),
(41080, 101, 400, 400, 700, NULL, NULL),
(41080, 301, 50, 40, 70, NULL, NULL),
(41080, 401, 100, 70, 220, NULL, NULL),
(41080, 10501, 159, 140, 245, NULL, NULL),
(41080, 10502, 231, 200, 10000, NULL, NULL),
(41100, 101, 350, 350, 600, NULL, NULL),
(41100, 301, 42, 40, 70, NULL, NULL),
(41100, 401, 75, 70, 220, NULL, NULL),
(41100, 10501, 141, 140, 245, NULL, NULL),
(41100, 10502, 1000, 200, 1000, NULL, NULL),
(50169, 101, 2530, 1500, 2600, NULL, NULL),
(50169, 201, 225, 220, 385, NULL, NULL),
(50169, 401, 240, 200, 350, NULL, NULL),
(50169, 10502, 195, 25, 200, NULL, NULL),
(50169, 10504, 260, 20, 300, NULL, NULL),
(50273, 4, 768, 90, 860, NULL, NULL),
(50273, 101, 233, 200, 350, NULL, NULL),
(50273, 201, 75, 60, 100, NULL, NULL),
(50273, 401, 224, 150, 280, NULL, NULL),
(50273, 10504, 50, 5, 100, NULL, NULL),
(50417, 4, 54, 15, 300, NULL, NULL),
(50417, 101, 518, 500, 875, NULL, NULL),
(50417, 201, 82, 60, 100, NULL, NULL),
(50417, 401, 130, 120, 210, NULL, NULL),
(50417, 10504, 600, 1, 1000, NULL, NULL),
(50418, 4, 56, 5, 900, NULL, NULL),
(50418, 5, 34, 20, 760, NULL, NULL),
(50418, 101, 244, 100, 275, NULL, NULL),
(50418, 201, 98, 60, 100, NULL, NULL),
(50418, 401, 156, 100, 175, NULL, NULL),
(50418, 10502, 76, 50, 200, NULL, NULL),
(50418, 10503, 1000, 10, 1000, NULL, NULL),
(50418, 10504, 300, 20, 700, NULL, NULL),
(50419, 101, 230, 120, 310, NULL, NULL),
(50419, 201, 77, 60, 100, NULL, NULL),
(50419, 401, 151, 150, 280, NULL, NULL),
(50419, 10502, 67, 45, 100, NULL, NULL),
(50419, 10504, 300, 50, 350, NULL, NULL),
(50530, 4, 857, 90, 900, NULL, NULL),
(50530, 101, 669, 400, 700, NULL, NULL),
(50530, 201, 62, 60, 100, NULL, NULL),
(50530, 401, 119, 100, 175, NULL, NULL),
(50530, 10502, 50, 30, 100, NULL, NULL),
(50530, 10504, 200, 20, 300, NULL, NULL),
(50532, 101, 0, 100, 175, 'Wait for Spring.', '1993-04-12 00:00:00'),
(50532, 201, 67, 60, 100, NULL, NULL),
(50532, 401, 233, 200, 350, NULL, NULL),
(50532, 10504, 100, 23, 200, NULL, NULL),
(50536, 101, 173, 100, 175, NULL, NULL),
(50536, 201, 97, 60, 100, NULL, NULL),
(50536, 401, 138, 100, 175, NULL, NULL),
(50536, 10504, 200, 20, 200, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `item`
--

CREATE TABLE `item` (
  `ord_id` int(11) UNSIGNED NOT NULL,
  `item_id` int(11) NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `gross` decimal(11,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_shipped` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`ord_id`, `item_id`, `product_id`, `price`, `gross`, `quantity`, `quantity_shipped`) VALUES
(97, 1, 20106, 1.00, 0.00, 1000, 1000),
(97, 2, 30321, 5.00, 0.00, 1000, 1000),
(97, 3, 10012, 10.00, 0.00, 10, 10),
(98, 1, 50273, 22.89, 0.00, 17, 17),
(99, 1, 20510, 12.00, 0.00, 20, 18),
(99, 2, 20512, 12.00, 0.00, 25, 25),
(99, 3, 50417, 12.00, 0.00, 53, 53),
(99, 400, 50530, 12.00, 0.00, 69, 69),
(100, 1, 50417, 9876.00, 10666.08, 401, 401),
(100, 3, 10021, 14.00, 0.00, 500, 500),
(100, 4, 10023, 36.00, 0.00, 400, 400),
(100, 5, 30326, 582.00, 0.00, 600, 600),
(100, 7, 41010, 8.00, 0.00, 251, 250),
(100, 8, 50418, 9876.00, 12147.48, 123, 123),
(100, 9, 10012, 12.00, 0.00, 11, NULL),
(100, 11, 10013, 12.00, 0.00, 12, NULL),
(100, 15, 20201, 70.00, 86.10, 0, 0),
(100, 16, 50532, 47.00, 57.81, 1, NULL),
(100, 17, 20106, 80.00, 98.40, 1, NULL),
(100, 18, 50530, 43.00, 52.89, 1, NULL),
(100, 19, 32779, 120.00, 147.60, 11, 11),
(100, 20, 10011, 783.00, 806.49, 3, 3),
(101, 1, 30421, 16.00, 0.00, 15, 15),
(101, 2, 40422, 50.00, 0.00, 30, 30),
(101, 3, 41010, 8.00, 0.00, 20, 20),
(101, 4, 41100, 45.00, 0.00, 35, 35),
(101, 5, 50169, 4.29, 0.00, 40, 40),
(101, 6, 50417, 80.00, 0.00, 27, 27),
(101, 7, 50530, 45.00, 0.00, 50, 50),
(102, 1, 20108, 28.00, 0.00, 100, 100),
(102, 2, 20201, 123.00, 0.00, 45, 45),
(103, 1, 30433, 20.00, 0.00, 15, 15),
(103, 2, 32779, 7.00, 0.00, 11, 11),
(104, 1, 20510, 9.00, 0.00, 7, 7),
(104, 2, 20512, 8.00, 0.00, 12, 12),
(104, 3, 30321, 1669.00, 0.00, 19, 19),
(104, 4, 30421, 16.00, 0.00, 35, 35),
(105, 1, 50273, 22.89, 0.00, 16, 16),
(105, 2, 50419, 80.00, 0.00, 13, 13),
(105, 3, 50532, 47.00, 0.00, 28, 28),
(106, 1, 20108, 28.00, 0.00, 46, 46),
(106, 2, 20201, 123.00, 0.00, 21, 21),
(106, 3, 50169, 4.29, 0.00, 125, 125),
(106, 4, 50273, 22.89, 0.00, 75, 75),
(106, 5, 50418, 75.00, 0.00, 98, 98),
(106, 6, 50419, 80.00, 0.00, 27, 27),
(107, 1, 20106, 11.00, 0.00, 50, 50),
(107, 2, 20108, 28.00, 0.00, 22, 22),
(107, 3, 20201, 115.00, 0.00, 130, 130),
(107, 4, 30321, 1669.00, 0.00, 75, 75),
(107, 5, 30421, 16.00, 0.00, 55, 55),
(108, 1, 20510, 9.00, 0.00, 9, 9),
(108, 2, 20512, 8.00, 0.00, 18, 18),
(108, 3, 30321, 1669.00, 0.00, 85, 85),
(108, 4, 32779, 7.00, 0.00, 60, 60),
(108, 5, 32861, 60.00, 0.00, 57, 57),
(108, 6, 41080, 35.00, 0.00, 50, 50),
(108, 7, 41100, 45.00, 0.00, 42, 42),
(109, 1, 10011, 140.00, 0.00, 150, 150),
(109, 2, 10012, 175.00, 0.00, 600, 600),
(109, 3, 10022, 21.95, 0.00, 300, 300),
(109, 4, 30326, 582.00, 0.00, 1500, 1500),
(109, 5, 30426, 18.25, 0.00, 500, 500),
(109, 6, 32861, 60.00, 0.00, 50, 50),
(109, 7, 50418, 75.00, 0.00, 43, 43),
(111, 1, 40421, 65.00, 0.00, 27, 27),
(111, 2, 41080, 35.00, 0.00, 29, 29),
(114, 1, 10012, 10.00, 0.00, 5, NULL),
(114, 2, 10013, 5.00, 0.00, 7, NULL),
(115, 1, 10023, 200.00, 246.00, 1, 1),
(116, 1, 10011, 783.00, 806.49, 3, 3),
(117, 1, 10023, 200.00, 246.00, 1, 1),
(118, 1, 10011, 783.00, 806.49, 5, 5),
(119, 1, 10011, 783.00, 806.49, 3, 3);

--
-- Wyzwalacze `item`
--
DELIMITER $$
CREATE TRIGGER `item_ai_tr` AFTER INSERT ON `item` FOR EACH ROW BEGIN
    INSERT INTO oper (kto, kiedy, operacja, tabela, rekord, Uwagi)
    VALUES (
        CURRENT_USER(),
        NOW(),
        'INSERT',
        'item',
        CONCAT('ord_id=', NEW.ord_id, ', item_id=', NEW.item_id),
        CONCAT(
            'Dodano: product_id=', NEW.product_id,
            ', price=', NEW.price,
            ', quantity=', NEW.quantity,
            ', quantity_shipped=', NEW.quantity_shipped
        )
    );

    UPDATE ord
       SET total = (SELECT SUM(item.price * item.quantity)
                      FROM item
                     WHERE item.ord_id = NEW.ord_id)
     WHERE id = NEW.ord_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `item_au_tr` AFTER UPDATE ON `item` FOR EACH ROW BEGIN
   DECLARE msg VARCHAR(255) DEFAULT '';
   UPDATE ord 
     SET ord.total = (SELECT SUM(item.price * item.quantity)
                        FROM item                       
                       WHERE item.ord_id = OLD.ord_id)
    WHERE ord.id = OLD.ord_id;

    IF OLD.product_id <> NEW.product_id THEN
        SET msg = CONCAT(msg, 'product_id: ', OLD.product_id, '', NEW.product_id, '; ');
    END IF;

    IF OLD.price <> NEW.price THEN
        SET msg = CONCAT(msg, 'price: ', OLD.price, ' -> ', NEW.price, '; ');
    END IF;

    IF OLD.quantity <> NEW.quantity THEN
        SET msg = CONCAT(msg, 'quantity: ', OLD.quantity, '', NEW.quantity, '; ');
    END IF;

    IF OLD.quantity_shipped <> NEW.quantity_shipped THEN
        SET msg = CONCAT(msg, 'shipped: ', OLD.quantity_shipped, '', NEW.quantity_shipped, '; ');
    END IF;

    IF msg <> '' THEN
        INSERT INTO oper (kto, kiedy, operacja, tabela, rekord, Uwagi)
        VALUES (
            CURRENT_USER(),
            NOW(),
            'UPDATE',
            'item',
            CONCAT('ord_id=', OLD.ord_id, ', item_id=', OLD.item_id),
            msg
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `item_bd_tr` BEFORE DELETE ON `item` FOR EACH ROW BEGIN
  
    INSERT INTO oper (kto, kiedy, operacja, tabela, rekord, Uwagi)
    VALUES (
        CURRENT_USER(),
        NOW(),
        'DELETE',
        'item',
        CONCAT('ord_id=', OLD.ord_id, ', item_id=', OLD.item_id),
        CONCAT(
            'Usunięto: product_id=', OLD.product_id,
            ', price=', OLD.price,
            ', quantity=', OLD.quantity,
            ', shipped=', OLD.quantity_shipped
        )
    );
    
    UPDATE ord 
       SET ord.total = (SELECT SUM(item.price * item.quantity) 
                          FROM item                        
                         WHERE item.ord_id = OLD.ord_id)
     WHERE ord.id = OLD.ord_id;
     
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `job`
--

CREATE TABLE `job` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `salary_min` decimal(11,2) DEFAULT NULL,
  `salary_max` decimal(11,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job`
--

INSERT INTO `job` (`id`, `name`, `salary_min`, `salary_max`) VALUES
(1, 'President', 2000.00, 4000.00),
(2, 'Stock Clerk', 700.00, 1200.00),
(3, 'Sales Representative', 1200.00, 1400.00),
(4, 'Warehouse Manager', 1000.00, 1500.00),
(5, 'General worker', 0.00, 1000.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `oper`
--

CREATE TABLE `oper` (
  `id` int(10) UNSIGNED NOT NULL,
  `kto` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `kiedy` timestamp NOT NULL DEFAULT current_timestamp(),
  `operacja` varchar(20) NOT NULL,
  `tabela` varchar(30) NOT NULL,
  `rekord` varchar(25) DEFAULT NULL,
  `Uwagi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `oper`
--

INSERT INTO `oper` (`id`, `kto`, `kiedy`, `operacja`, `tabela`, `rekord`, `Uwagi`) VALUES
(12, 'root@localho', '2025-11-19 13:01:37', 'delete', 'emp', '2', 'LaDoris;Ngao;VP, Operations;'),
(13, 'root@localho', '2025-12-03 12:44:21', 'update', 'customer', '369', '->Polska;'),
(14, 'root@localho', '2025-12-03 13:00:22', 'update', 'customer', '212', '   ->Alexandria;'),
(15, 'root@localho', '2025-12-03 13:28:52', 'delete', 'emp', '1', 'Carmen;Velasquez;President;'),
(16, 'root@localho', '2025-12-03 13:28:52', 'update', 'emp', '1', '->Berlin;'),
(17, 'root@localho', '2025-12-03 13:28:52', 'delete', 'emp', '5', 'Audry;Ropeburn;VP, Administration;Gdańsk'),
(18, 'root@localho', '2025-12-03 13:28:52', 'update', 'emp', '5', 'Bachelor->;'),
(19, 'root@localho', '2025-12-03 13:29:41', 'delete', 'emp', '2', 'LaDoris;Ngao;VP, Operations;'),
(20, 'root@localho', '2025-12-03 13:29:41', 'update', 'emp', '2', '->Katowice;'),
(21, 'root@localho', '2025-12-08 12:52:17', 'delete', 'emp', '3', 'Midori;Nagayama;VP, Sales;Bdgosz'),
(22, 'root@localho', '2025-12-08 12:52:17', 'update', 'emp', '3', 'Bdgosz->Kiev;'),
(23, 'root@localho', '2025-12-08 12:53:56', 'delete', 'emp', '4', 'Mark;Quick-To-See;VP, Finance;Katowice'),
(24, 'root@localho', '2025-12-08 12:55:17', 'delete', 'emp', '5', 'Audry;Ropeburn;VP, Administration;Gdańsk'),
(25, 'root@localho', '2025-12-08 12:55:17', 'update', 'emp', '5', 'Gdańsk->Paris;'),
(26, 'root@localho', '2025-12-08 13:36:41', 'delete', 'emp', '6', 'Molly;Urguhart;Warehouse Manager;'),
(27, 'root@localho', '2025-12-08 13:36:41', 'update', 'emp', '6', '->Wilno;'),
(28, 'root@localho', '2025-12-08 13:37:33', 'delete', 'emp', '7', 'Roberta;Menchu;Sales Representative;'),
(29, 'root@localho', '2025-12-08 13:37:33', 'update', 'emp', '7', '->Madryt;'),
(30, 'root@localho', '2025-12-08 13:37:55', 'delete', 'emp', '8', 'Ben;Biri;Warehouse Manager;'),
(31, 'root@localho', '2025-12-08 13:37:55', 'update', 'emp', '8', '->Hamburg;'),
(32, 'root@localho', '2025-12-08 13:38:26', 'delete', 'emp', '9', 'Antoinette;Catchpole;Warehouse Manager;'),
(33, 'root@localho', '2025-12-08 13:38:26', 'update', 'emp', '9', '->Gdańsk;'),
(34, 'root@localho', '2025-12-08 13:39:07', 'delete', 'emp', '10', 'Marta;Havel;Singer;'),
(35, 'root@localho', '2025-12-08 13:39:07', 'update', 'emp', '10', '->Bratysława;'),
(36, 'root@localho', '2025-12-08 13:39:22', 'delete', 'emp', '11', 'Colin;Nagayama;Sales Representative;'),
(37, 'root@localho', '2025-12-08 13:39:22', 'update', 'emp', '11', '->Bratysława;'),
(38, 'root@localho', '2025-12-08 13:39:50', 'delete', 'emp', '12', 'Fonda;Henry;Sales Representative;'),
(39, 'root@localho', '2025-12-08 13:39:50', 'update', 'emp', '12', '->Barcelona;'),
(40, 'root@localho', '2025-12-08 13:40:08', 'delete', 'emp', '13', 'Yasmina;Sedeghi;Sales Representative;'),
(41, 'root@localho', '2025-12-08 13:40:08', 'update', 'emp', '13', '->Lwów;'),
(42, 'root@localho', '2025-12-08 13:40:44', 'delete', 'emp', '14', 'Mai;Nguyen;Sales Representative;'),
(43, 'root@localho', '2025-12-08 13:40:44', 'update', 'emp', '14', '->Koszyce;'),
(44, 'root@localho', '2025-12-08 13:41:07', 'delete', 'emp', '15', 'Andre;Dumas;Sales Representative;'),
(45, 'root@localho', '2025-12-08 13:41:07', 'update', 'emp', '15', '->Paryż;'),
(46, 'root@localho', '2025-12-08 13:41:44', 'delete', 'emp', '16', 'Elena;Maduro;Stock Clerk;'),
(47, 'root@localho', '2025-12-08 13:41:44', 'update', 'emp', '16', '->Bruksela;'),
(48, 'root@localho', '2025-12-08 13:42:24', 'delete', 'emp', '17', 'George;Smith;Stock Clerk;'),
(49, 'root@localho', '2025-12-08 13:42:24', 'update', 'emp', '17', '->Zagrzeb;'),
(50, 'root@localho', '2025-12-08 13:43:03', 'delete', 'emp', '18', 'Akira;Nozaki;Stock Clerk;'),
(51, 'root@localho', '2025-12-08 13:43:03', 'update', 'emp', '18', '->Paryż;'),
(52, 'root@localho', '2025-12-08 13:43:26', 'delete', 'emp', '19', 'Vikram;Patel;Stock Clerk;'),
(53, 'root@localho', '2025-12-08 13:43:56', 'delete', 'emp', '19', 'Vikram;Patel;Stock Clerk;'),
(54, 'root@localho', '2025-12-08 13:43:56', 'update', 'emp', '19', '->Paryż;'),
(55, 'root@localho', '2025-12-08 13:44:16', 'delete', 'emp', '20', 'Chad;Newman;Stock Clerk;'),
(56, 'root@localho', '2025-12-08 13:44:16', 'update', 'emp', '20', '->Berlin;'),
(57, 'root@localho', '2025-12-08 13:44:38', 'delete', 'emp', '21', 'Alexander;Biri;Stock Clerk;'),
(58, 'root@localho', '2025-12-08 13:44:38', 'update', 'emp', '21', '->Madryt;'),
(59, 'root@localho', '2025-12-08 13:45:17', 'delete', 'emp', '22', 'Eddie;Chang;Stock Clerk;'),
(60, 'root@localho', '2025-12-08 13:45:17', 'update', 'emp', '22', '->Kijów;'),
(61, 'root@localho', '2025-12-08 13:46:15', 'delete', 'emp', '23', 'Radha;Patel;Stock Clerk;'),
(62, 'root@localho', '2025-12-08 13:46:15', 'update', 'emp', '23', '->Brno;'),
(63, 'root@localho', '2025-12-08 13:47:12', 'delete', 'emp', '24', 'Bela;Dancs;Stock Clerk;'),
(64, 'root@localho', '2025-12-08 13:47:12', 'update', 'emp', '24', '->Praga;'),
(65, 'root@localho', '2025-12-08 13:47:42', 'delete', 'emp', '25', 'Sylvie;Schwartz;Stock Clerk;'),
(66, 'root@localho', '2025-12-08 13:47:42', 'update', 'emp', '25', '->Bratysława;'),
(67, 'root@localho', '2025-12-08 13:49:20', 'delete', 'emp', '51', 'Arnold;Schwarzenegger;Actor;'),
(68, 'root@localho', '2025-12-08 13:49:20', 'update', 'emp', '51', '->Kielce;'),
(69, 'root@localho', '2025-12-08 13:49:41', 'delete', 'emp', '52', 'Milo;Stallone;Actor;'),
(70, 'root@localho', '2025-12-08 13:49:41', 'update', 'emp', '52', '->Kraków;'),
(71, 'root@localho', '2025-12-08 13:50:00', 'delete', 'emp', '54', 'Michał;Radełko;Singer;'),
(72, 'root@localho', '2025-12-08 13:50:00', 'update', 'emp', '54', '->Warszawa;'),
(73, 'root@localho', '2025-12-08 13:51:01', 'delete', 'emp', '84', 'Cezary;Pazura;Singer;'),
(74, 'root@localho', '2025-12-08 13:51:01', 'update', 'emp', '84', '->Brno;'),
(75, 'root@localho', '2025-12-08 13:51:23', 'delete', 'emp', '85', 'Adam;Pazura;Singer;'),
(76, 'root@localho', '2025-12-08 13:51:23', 'update', 'emp', '85', '->Budapeszt;'),
(77, 'root@localho', '2025-12-08 13:54:14', 'delete', 'emp', '89', 'Krzysztof;Nowakowski;Singer;'),
(78, 'root@localho', '2025-12-08 13:54:14', 'update', 'emp', '89', '->Madryt;'),
(79, 'root@localho', '2025-12-08 13:54:32', 'delete', 'emp', '90', 'Hipolit;Krawczyk;Actor;'),
(80, 'root@localho', '2025-12-08 13:54:32', 'update', 'emp', '90', '->Praga;'),
(81, 'root@localho', '2025-12-08 13:55:04', 'delete', 'emp', '91', 'Jacenty;Nienowakowski;Singer;'),
(82, 'root@localho', '2025-12-08 13:55:04', 'update', 'emp', '91', '->Lyon;'),
(83, 'root@localho', '2025-12-08 13:55:33', 'delete', 'emp', '92', 'Witold;Nienowak;Singer;'),
(84, 'root@localho', '2025-12-08 13:55:33', 'update', 'emp', '92', '->Berlin;'),
(85, 'root@localho', '2025-12-08 13:56:04', 'delete', 'emp', '93', 'Witold;Nienowak;Singer;'),
(86, 'root@localho', '2025-12-08 13:56:04', 'update', 'emp', '93', '->Kolonia;'),
(87, 'root@localho', '2025-12-08 13:56:44', 'delete', 'emp', '94', 'Maciej;Pazura;Singer;'),
(88, 'root@localho', '2025-12-08 13:56:44', 'update', 'emp', '94', '->Bratysława;'),
(89, 'root@localho', '2025-12-08 13:57:01', 'delete', 'emp', '95', 'Alfons;Pazura;Actor;'),
(90, 'root@localho', '2025-12-08 13:57:01', 'update', 'emp', '95', '->Lyon;'),
(91, 'root@localho', '2025-12-08 13:57:35', 'delete', 'emp', '116', 'Zenon;Nowak;Actor;'),
(92, 'root@localho', '2025-12-08 13:57:35', 'update', 'emp', '116', '->Lublin;'),
(93, 'root@localho', '2025-12-08 13:58:07', 'delete', 'emp', '110', 'Jan;Nowak;Singer;'),
(94, 'root@localho', '2025-12-08 13:58:07', 'update', 'emp', '110', '->Berlin;'),
(95, 'root@localho', '2025-12-08 14:00:22', 'delete', 'emp', '122', 'Alain;Jankowski;Stock Clerk;'),
(96, 'root@localho', '2025-12-08 14:00:22', 'update', 'emp', '122', '->Kraków;'),
(97, 'root@localho', '2025-12-08 14:00:44', 'delete', 'emp', '124', 'Jan;Cielebąk;Actor;'),
(98, 'root@localho', '2025-12-08 14:00:44', 'update', 'emp', '124', '->Brno;'),
(99, 'root@localho', '2025-12-08 14:01:12', 'delete', 'emp', '135', 'Zygmunt;Nowacki;VP, Finance;'),
(100, 'root@localho', '2025-12-08 14:01:12', 'update', 'emp', '135', '->Lizbona;'),
(101, 'root@localho', '2025-12-08 14:02:12', 'delete', 'emp', '140', 'Jozef;Nowak;VP, Finance;'),
(102, 'root@localho', '2025-12-08 14:02:12', 'update', 'emp', '140', '->Paryż;'),
(103, 'root@localho', '2025-12-08 14:02:44', 'delete', 'emp', '153', 'Norman;Davies;Singer;'),
(104, 'root@localho', '2025-12-08 14:02:44', 'update', 'emp', '153', '->Praga;'),
(105, 'root@localho', '2025-12-08 14:03:01', 'delete', 'emp', '154', 'Aleksander;Głowacki;Stock Clerk;'),
(106, 'root@localho', '2025-12-08 14:03:01', 'update', 'emp', '154', '->Hamburg;'),
(107, 'root@localho', '2025-12-08 14:03:24', 'delete', 'emp', '220', 'Męczysław;Gęsiński;Singer;'),
(108, 'root@localho', '2025-12-08 14:03:24', 'update', 'emp', '220', '->Bratysława;'),
(109, 'root@localho', '2025-12-08 14:04:38', 'delete', 'emp', '224', 'Włodzimierz;Braun;Actor;'),
(110, 'root@localho', '2025-12-08 14:04:38', 'update', 'emp', '224', '->Brno;'),
(111, 'root@localho', '2025-12-08 14:04:53', 'delete', 'emp', '225', 'Sylwester;Stallone ;Stock Clerk;'),
(112, 'root@localho', '2025-12-08 14:04:53', 'update', 'emp', '225', '->Praga;'),
(113, 'root@localho', '2025-12-08 14:05:17', 'delete', 'emp', '230', 'Grzegorz;Kozieł;Warehouse Manager;'),
(114, 'root@localho', '2025-12-08 14:05:17', 'update', 'emp', '230', '->Poznań;'),
(115, 'root@localho', '2025-12-08 14:05:38', 'delete', 'emp', '233', 'Jacenty;Nowak;VP, Operations;'),
(116, 'root@localho', '2025-12-08 14:05:38', 'update', 'emp', '233', '->Kielce;'),
(117, 'root@localho', '2025-12-08 14:05:58', 'delete', 'emp', '235', 'Aleksander;Makota;Actor;'),
(118, 'root@localho', '2025-12-08 14:05:58', 'update', 'emp', '235', '->Berlin;'),
(119, 'root@localho', '2025-12-08 14:06:33', 'delete', 'emp', '265', 'Michał;Radełko;Singer;'),
(120, 'root@localho', '2025-12-08 14:06:33', 'update', 'emp', '265', '->Warszawa;'),
(121, 'root@localho', '2025-12-08 14:06:51', 'delete', 'emp', '266', 'Krzysztof;Ryśków;Singer;'),
(122, 'root@localho', '2025-12-08 14:07:15', 'delete', 'emp', '266', 'Krzysztof;Ryśków;Singer;'),
(123, 'root@localho', '2025-12-08 14:07:15', 'update', 'emp', '266', '->Budapeszt;'),
(124, 'root@localho', '2025-12-08 14:07:42', 'delete', 'emp', '267', 'Ronald;Las;Singer;'),
(125, 'root@localho', '2025-12-08 14:07:42', 'update', 'emp', '267', '->Bratysława;'),
(126, 'root@localho', '2025-12-08 14:08:02', 'delete', 'emp', '268', 'Dariusz;Jonas;Stock Clerk;'),
(127, 'root@localho', '2025-12-08 14:08:02', 'update', 'emp', '268', '->Kielce;'),
(128, 'root@localho', '2025-12-08 14:08:23', 'delete', 'emp', '270', 'Mark;Assasin;Sales Representative;'),
(129, 'root@localho', '2025-12-08 14:08:23', 'update', 'emp', '270', '->Brno;'),
(130, 'root@localho', '2025-12-08 14:08:48', 'delete', 'emp', '271', 'Jan;Nowak;Actor;'),
(131, 'root@localho', '2025-12-08 14:08:48', 'update', 'emp', '271', '->Kraków;'),
(132, 'root@localho', '2025-12-08 14:09:36', 'delete', 'emp', '302', 'Alicja;Makota;Singer;'),
(133, 'root@localho', '2025-12-08 14:09:36', 'update', 'emp', '302', '->Tirana;'),
(134, 'root@localho', '2025-12-08 14:10:05', 'delete', 'emp', '318', 'Maciek;Nowakowski;Actor;'),
(135, 'root@localho', '2025-12-08 14:10:05', 'update', 'emp', '318', '->Warszawa;'),
(136, 'root@localho', '2025-12-08 14:10:24', 'delete', 'emp', '319', 'Wacław;Stallone;Actor;'),
(137, 'root@localho', '2025-12-08 14:10:24', 'update', 'emp', '319', '->Kielce;'),
(138, 'root@localho', '2026-02-24 11:35:55', 'delete', 'emp', '10', 'Marta;Havel;Singer;Bratysława'),
(139, 'root@localho', '2026-02-24 11:35:55', 'update', 'emp', '10', '1307.00->1407.00;'),
(140, 'root@localho', '2026-02-24 11:36:10', 'delete', 'emp', '10', 'Marta;Havel;Singer;Bratysława'),
(141, 'root@localho', '2026-02-24 11:36:10', 'update', 'emp', '10', '1407.00->1507.00;'),
(142, 'root@localho', '2026-02-24 11:36:22', 'delete', 'emp', '10', 'Marta;Havel;Singer;Bratysława'),
(143, 'root@localho', '2026-02-24 11:36:22', 'update', 'emp', '10', '1507.00->1607.00;'),
(144, 'root@localho', '2026-02-24 12:46:54', 'delete', 'emp', '140', 'Jozef;Nowak;VP, Finance;Paryż'),
(145, 'root@localho', '2026-02-24 12:47:09', 'delete', 'emp', '140', 'Jozef;Nowak;VP, Finance;Paryż'),
(146, 'root@localho', '2026-02-24 12:48:29', 'delete', 'emp', '2', 'LaDoris;Ngao;VP, Operations;Katowice'),
(147, 'root@localho', '2026-02-24 12:48:38', 'delete', 'emp', '135', 'Zygmunt;Nowacki;VP, Finance;Lizbona'),
(148, 'root@localho', '2026-02-24 12:48:45', 'delete', 'emp', '233', 'Jacenty;Nowak;VP, Operations;Kielce'),
(151, 'root@localho', '2026-02-24 13:20:50', 'delete', 'emp', '10', 'Marta;Havel;Singer;Bratysława'),
(152, 'root@localho', '2026-02-24 13:20:50', 'update', 'emp', '10', '1607.00->1707.00;'),
(153, 'root@localho', '2026-03-24 08:24:31', 'delete', 'emp', '4', 'Mark;Quick-To-See;VP, Finance;Katowice'),
(154, 'root@localho', '2026-03-24 09:16:00', 'delete', 'emp', '4', 'Mark;Quick-To-See;VP, Finance;Katowice'),
(155, 'root@localho', '2026-04-21 10:48:13', 'UPDATE', 'ord', '100', '601100.00->601571.00; '),
(156, 'root@localho', '2026-04-21 10:48:13', 'UPDATE', 'item', 'ord_id=100, item_id=6', 'product_id: 3043310021; price: 20.00 -> 21.00; quantity: 450451; '),
(157, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '2', 'LaDoris;Ngao;VP, Operations;Katowice'),
(158, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '2', '1450.00->1595.00;'),
(159, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '6', 'Molly;Urguhart;Warehouse Manager;Wilno'),
(160, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '6', '1400.00->1540.00;'),
(161, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '7', 'Roberta;Menchu;Sales Representative;Madryt'),
(162, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '7', '1250.00->1375.00;'),
(163, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '8', 'Ben;Biri;Warehouse Manager;Hamburg'),
(164, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '8', '1100.00->1210.00;'),
(165, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '9', 'Antoinette;Catchpole;Warehouse Manager;Gdańsk'),
(166, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '9', '1300.00->1430.00;'),
(167, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '10', 'Marta;Havel;Singer;Bratysława'),
(168, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '10', '1707.00->1877.70;'),
(169, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '16', 'Elena;Maduro;Stock Clerk;Bruksela'),
(170, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '16', '1400.00->1540.00;'),
(171, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '17', 'George;Smith;Stock Clerk;Zagrzeb'),
(172, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '17', '940.00->1034.00;'),
(173, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '18', 'Akira;Nozaki;Stock Clerk;Paryż'),
(174, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '18', '1200.00->1320.00;'),
(175, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '19', 'Vikram;Patel;Stock Clerk;Paryż'),
(176, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '19', '1000.00->1100.00;'),
(177, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '20', 'Chad;Newman;Stock Clerk;Berlin'),
(178, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '20', '750.00->825.00;'),
(179, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '21', 'Alexander;Biri;Stock Clerk;Madryt'),
(180, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '21', '850.00->935.00;'),
(181, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '22', 'Eddie;Chang;Stock Clerk;Kijów'),
(182, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '22', '800.00->880.00;'),
(183, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '23', 'Radha;Patel;Stock Clerk;Brno'),
(184, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '23', '795.00->874.50;'),
(185, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '24', 'Bela;Dancs;Stock Clerk;Praga'),
(186, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '24', '860.00->946.00;'),
(187, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '25', 'Sylvie;Schwartz;Stock Clerk;Bratysława'),
(188, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '25', '1100.00->1210.00;'),
(189, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '51', 'Arnold;Schwarzenegger;Actor;Kielce'),
(190, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '51', '1722.00->1894.20;'),
(191, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '52', 'Milo;Stallone;Actor;Kraków'),
(192, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '52', '2240.00->2464.00;'),
(193, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '54', 'Michał;Radełko;Singer;Warszawa'),
(194, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '54', '1000.00->1100.00;'),
(195, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '84', 'Cezary;Pazura;Singer;Brno'),
(196, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '84', '3453.00->3798.30;'),
(197, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '85', 'Adam;Pazura;Singer;Budapeszt'),
(198, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '85', '3456.00->3801.60;'),
(199, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '89', 'Krzysztof;Nowakowski;Singer;Madryt'),
(200, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '89', '4500.00->4950.00;'),
(201, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '90', 'Hipolit;Krawczyk;Actor;Praga'),
(202, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '90', '777.00->854.70;'),
(203, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '91', 'Jacenty;Nienowakowski;Singer;Lyon'),
(204, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '91', '556.00->611.60;'),
(205, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '92', 'Witold;Nienowak;Singer;Berlin'),
(206, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '92', '777.98->855.78;'),
(207, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '93', 'Witold;Nienowak;Singer;Kolonia'),
(208, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '93', '777.98->855.78;'),
(209, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '94', 'Maciej;Pazura;Singer;Bratysława'),
(210, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '94', '3456.00->3801.60;'),
(211, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '116', 'Zenon;Nowak;Actor;Lublin'),
(212, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '116', '8899.00->9788.90;'),
(213, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '122', 'Alain;Jankowski;Stock Clerk;Kraków'),
(214, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '124', 'Jan;Cielebąk;Actor;Brno'),
(215, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '124', '1212.00->1333.20;'),
(216, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '153', 'Norman;Davies;Singer;Praga'),
(217, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '153', '1122.00->1234.20;'),
(218, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '154', 'Aleksander;Głowacki;Stock Clerk;Hamburg'),
(219, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '154', '3000.00->3300.00;'),
(220, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '220', 'Męczysław;Gęsiński;Singer;Bratysława'),
(221, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '224', 'Włodzimierz;Braun;Actor;Brno'),
(222, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '225', 'Sylwester;Stallone ;Stock Clerk;Praga'),
(223, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '230', 'Grzegorz;Kozieł;Warehouse Manager;Poznań'),
(224, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '230', '1123.00->1235.30;'),
(225, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '233', 'Jacenty;Nowak;VP, Operations;Kielce'),
(226, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '233', '3000.00->3300.00;'),
(227, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '265', 'Michał;Radełko;Singer;Warszawa'),
(228, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '265', '5664.00->6230.40;'),
(229, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '266', 'Krzysztof;Ryśków;Singer;Budapeszt'),
(230, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '266', '9987.00->10985.70;'),
(231, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '267', 'Ronald;Las;Singer;Bratysława'),
(232, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '267', '2311.00->2542.10;'),
(233, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '268', 'Dariusz;Jonas;Stock Clerk;Kielce'),
(234, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '268', '6753.00->7428.30;'),
(235, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '270', 'Mark;Assasin;Sales Representative;Brno'),
(236, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '270', '1122.00->1234.20;'),
(237, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '271', 'Jan;Nowak;Actor;Kraków'),
(238, 'root@localho', '2026-04-23 10:52:20', 'delete', 'emp', '318', 'Maciek;Nowakowski;Actor;Warszawa'),
(239, 'root@localho', '2026-04-23 10:52:20', 'update', 'emp', '318', '9321.00->10253.10;'),
(240, 'root@localho', '2026-05-15 18:27:53', 'delete', 'emp', '8', 'Ben;Biri;Warehouse Manager;Hamburg'),
(241, 'root@localho', '2026-05-17 14:33:14', 'insert', 'prices', '10023', '2026-05-17 16:32:19120.0023.00'),
(242, 'root@localho', '2026-05-17 14:35:22', 'update', 'prices', '10023', '120.00->120.67;'),
(243, 'root@localho', '2026-05-17 14:43:19', 'insert', 'prices', '10023', '2026-05-17 16:32:19120.6723.00'),
(244, 'root@localho', '2026-05-20 18:26:31', 'delete', 'emp', '9', 'Antoinette;Catchpole;Warehouse Manager;Gdańsk'),
(245, 'root@localho', '2026-06-07 15:19:26', 'INS', 'ord', '113', NULL),
(246, 'root@localho', '2026-06-07 15:21:03', 'INS', 'ord', '114', NULL),
(247, 'root@localho', '2026-06-07 16:01:32', 'INSERT', 'item', 'ord_id=100, item_id=8', 'Dodano: product_id=10012, price=123.00, quantity=123, quantity_shipped=0'),
(248, 'root@localho', '2026-06-07 16:01:32', 'UPDATE', 'ord', '100', '601571.00->616700.00; '),
(249, 'root@localho', '2026-06-07 16:50:43', 'INSERT', 'item', 'ord_id=100, item_id=9', NULL),
(250, 'root@localho', '2026-06-07 16:50:43', 'UPDATE', 'ord', '100', '616700.00->616844.00; '),
(251, 'root@localho', '2026-06-08 19:02:28', 'UPDATE', 'ord', '100', '616844.00->616979.00; '),
(252, 'root@localho', '2026-06-08 19:02:28', 'UPDATE', 'item', 'ord_id=100, item_id=1', 'quantity: 500501; '),
(253, 'root@localho', '2026-06-08 19:02:49', 'UPDATE', 'ord', '100', '616979.00->616844.00; '),
(254, 'root@localho', '2026-06-08 19:02:49', 'UPDATE', 'item', 'ord_id=100, item_id=1', 'quantity: 501500; '),
(255, 'root@localho', '2026-06-08 19:44:21', 'INSERT', 'item', 'ord_id=114, item_id=1', NULL),
(256, 'root@localho', '2026-06-08 19:45:23', 'INSERT', 'item', 'ord_id=114, item_id=2', NULL),
(257, 'root@localho', '2026-06-08 19:45:23', 'UPDATE', 'ord', '114', '50.00->85.00; '),
(258, 'root@localho', '2026-06-08 20:00:24', 'UPDATE', 'ord', '100', '616844.00->616820.00; '),
(259, 'root@localho', '2026-06-08 20:00:24', 'UPDATE', 'item', 'ord_id=100, item_id=9', 'quantity: 1210; '),
(260, 'root@localho', '2026-06-08 20:01:13', 'UPDATE', 'ord', '100', '616820.00->616828.00; '),
(261, 'root@localho', '2026-06-08 20:01:13', 'UPDATE', 'item', 'ord_id=100, item_id=7', 'quantity: 250251; '),
(262, 'root@localho', '2026-06-12 18:43:33', 'INS', 'ord', '115', NULL),
(263, 'root@localho', '2026-06-12 18:47:49', 'INS', 'ord', '116', NULL),
(264, 'root@localho', '2026-06-12 18:55:48', 'INS', 'ord', '117', NULL),
(265, 'root@localho', '2026-06-13 09:53:21', 'UPDATE', 'ord', '100', '1992-08-31 00:00:00->2026-06-13 11:53:21; CREDIT->CASH; '),
(266, 'root@localho', '2026-06-13 11:05:20', 'INSERT', 'item', 'ord_id=100, item_id=10', NULL),
(267, 'root@localho', '2026-06-13 11:05:50', 'UPDATE', 'ord', '100', '616828.00->616840.00; '),
(268, 'root@localho', '2026-06-13 11:05:50', 'UPDATE', 'item', 'ord_id=100, item_id=9', 'quantity: 1011; '),
(269, 'root@localho', '2026-06-13 11:06:12', 'INSERT', 'item', 'ord_id=100, item_id=11', NULL),
(270, 'root@localho', '2026-06-13 11:06:12', 'UPDATE', 'ord', '100', '616840.00->616984.00; '),
(271, 'root@localho', '2026-06-13 11:07:03', 'INSERT', 'item', 'ord_id=100, item_id=12', NULL),
(272, 'root@localho', '2026-06-13 11:07:03', 'UPDATE', 'ord', '100', '616984.00->617128.00; '),
(273, 'root@localho', '2026-06-13 11:26:57', 'INSERT', 'item', 'ord_id=100, item_id=13', NULL),
(274, 'root@localho', '2026-06-13 11:26:57', 'UPDATE', 'ord', '100', '617128.00->617272.00; '),
(275, 'root@localho', '2026-06-13 12:11:18', 'INSERT', 'item', 'ord_id=100, item_id=14', NULL),
(276, 'root@localho', '2026-06-13 12:11:18', 'UPDATE', 'ord', '100', '617272.00->617273.00; '),
(277, 'root@localho', '2026-06-13 12:11:33', 'UPDATE', 'ord', '100', '617273.00->617276.00; '),
(278, 'root@localho', '2026-06-13 12:11:33', 'UPDATE', 'item', 'ord_id=100, item_id=14', 'price: 1.00 -> 2.00; quantity: 12; '),
(279, 'root@localho', '2026-06-13 12:12:34', 'UPDATE', 'ord', '100', '617276.00->617278.00; '),
(280, 'root@localho', '2026-06-13 12:12:34', 'UPDATE', 'item', 'ord_id=100, item_id=14', 'quantity: 23; '),
(281, 'root@localho', '2026-06-13 12:13:01', 'UPDATE', 'ord', '100', '617278.00->617272.00; '),
(282, 'root@localho', '2026-06-13 12:13:01', 'UPDATE', 'item', 'ord_id=100, item_id=14', 'quantity: 30; '),
(283, 'root@localho', '2026-06-13 12:13:46', 'UPDATE', 'ord', '100', '617272.00->617276.00; '),
(284, 'root@localho', '2026-06-13 12:13:46', 'UPDATE', 'item', 'ord_id=100, item_id=14', 'product_id: 5053610013; quantity: 02; '),
(285, 'root@localho', '2026-06-13 12:21:07', 'UPDATE', 'ord', '100', '617276.00->617656.00; '),
(286, 'root@localho', '2026-06-13 12:21:07', 'UPDATE', 'item', 'ord_id=100, item_id=2', 'quantity: 400401; '),
(287, 'root@localho', '2026-06-13 12:30:18', 'UPDATE', 'item', 'ord_id=100, item_id=10', 'quantity: 0-12; '),
(288, 'root@localho', '2026-06-13 12:34:07', 'UPDATE', 'item', 'ord_id=100, item_id=10', 'quantity: -121; '),
(289, 'root@localho', '2026-06-13 12:34:38', 'UPDATE', 'ord', '100', '617656.00->617512.00; '),
(290, 'root@localho', '2026-06-13 12:34:38', 'UPDATE', 'item', 'ord_id=100, item_id=12', 'quantity: 120; '),
(291, 'root@localho', '2026-06-14 14:21:42', 'UPDATE', 'ord', '100', '617512.00->608041.00; '),
(292, 'root@localho', '2026-06-14 14:21:42', 'UPDATE', 'item', 'ord_id=100, item_id=6', 'quantity: 4510; '),
(293, 'root@localho', '2026-06-14 15:15:19', 'INSERT', 'item', 'ord_id=100, item_id=15', NULL),
(294, 'root@localho', '2026-06-14 15:15:19', 'UPDATE', 'ord', '100', '608041.00->608181.00; '),
(295, 'root@localho', '2026-06-14 15:28:17', 'INSERT', 'item', 'ord_id=100, item_id=16', NULL),
(296, 'root@localho', '2026-06-14 15:28:17', 'UPDATE', 'ord', '100', '608181.00->608228.00; '),
(297, 'root@localho', '2026-06-14 16:19:38', 'INSERT', 'item', 'ord_id=100, item_id=17', NULL),
(298, 'root@localho', '2026-06-14 16:19:38', 'UPDATE', 'ord', '100', '608228.00->608308.00; '),
(299, 'root@localho', '2026-06-14 16:36:47', 'INSERT', 'item', 'ord_id=100, item_id=18', NULL),
(300, 'root@localho', '2026-06-14 16:36:47', 'UPDATE', 'ord', '100', '608308.00->608351.00; '),
(301, 'root@localho', '2026-06-15 13:53:08', 'UPDATE', 'ord', '100', '608351.00->466820.98; '),
(302, 'root@localho', '2026-06-15 13:53:08', 'UPDATE', 'item', 'ord_id=100, item_id=2', 'product_id: 1001310022; price: 380.00 -> 26.99; quantity: 401402; shipped: 400402; '),
(303, 'root@localho', '2026-06-15 13:54:41', 'UPDATE', 'ord', '100', '466820.98->490141.00; '),
(304, 'root@localho', '2026-06-15 13:54:41', 'UPDATE', 'item', 'ord_id=100, item_id=2', 'product_id: 1002250419; price: 26.99 -> 85.00; '),
(305, 'root@localho', '2026-06-15 13:54:59', 'UPDATE', 'ord', '100', '490141.00->465141.00; '),
(306, 'root@localho', '2026-06-15 13:54:59', 'UPDATE', 'item', 'ord_id=100, item_id=1', 'product_id: 1001150419; price: 135.00 -> 85.00; '),
(307, 'root@localho', '2026-06-15 13:55:17', 'UPDATE', 'ord', '100', '465141.00->432866.00; '),
(308, 'root@localho', '2026-06-15 13:55:17', 'UPDATE', 'item', 'ord_id=100, item_id=1', 'product_id: 5041910021; price: 85.00 -> 20.45; '),
(309, 'root@localho', '2026-06-15 14:24:12', 'UPDATE', 'ord', '100', '432866.00->4358972.00; '),
(310, 'root@localho', '2026-06-15 14:24:12', 'UPDATE', 'item', 'ord_id=100, item_id=2', 'product_id: 5041950417; price: 85.00 -> 9876.00; quantity: 402401; shipped: 402401; '),
(311, 'root@localho', '2026-06-15 14:29:59', 'insert', 'prices', '32779', '2026-06-01 16:29:30;120.00;23.00'),
(312, 'root@localho', '2026-06-15 14:31:54', 'INSERT', 'item', 'ord_id=100, item_id=19', 'Dodano: product_id=32779, price=120.00, quantity=11, quantity_shipped=11'),
(313, 'root@localho', '2026-06-15 14:31:54', 'UPDATE', 'ord', '100', '4358972.00->4360292.00; '),
(314, 'root@localho', '2026-06-15 14:57:29', 'UPDATE', 'ord', '100', '4360292.00->5559911.00; '),
(315, 'root@localho', '2026-06-15 14:57:29', 'UPDATE', 'item', 'ord_id=100, item_id=8', 'product_id: 1001250418; price: 123.00 -> 9876.00; shipped: 0123; '),
(316, 'root@localho', '2026-06-15 14:57:59', 'INSERT', 'item', 'ord_id=100, item_id=20', 'Dodano: product_id=10011, price=783.00, quantity=3, quantity_shipped=3'),
(317, 'root@localho', '2026-06-15 14:57:59', 'UPDATE', 'ord', '100', '5559911.00->5562260.00; '),
(318, 'root@localho', '2026-06-16 14:51:04', 'INS', 'ord', '118', NULL),
(319, 'root@localho', '2026-06-16 14:51:28', 'UPDATE', 'ord', '100', '2026-06-13 11:53:21->2026-06-16 16:51:28; '),
(320, 'root@localho', '2026-06-16 14:51:41', 'INS', 'ord', '119', NULL),
(321, 'root@localho', '2026-06-16 16:44:04', 'INSERT', 'item', 'ord_id=115, item_id=1', 'Dodano: product_id=10023, price=200.00, quantity=1, quantity_shipped=1'),
(322, 'root@localho', '2026-06-16 16:53:17', 'INSERT', 'item', 'ord_id=119, item_id=1', 'Dodano: product_id=10011, price=783.00, quantity=3, quantity_shipped=3'),
(323, 'root@localho', '2026-06-16 16:54:59', 'INSERT', 'item', 'ord_id=118, item_id=1', 'Dodano: product_id=10011, price=783.00, quantity=5, quantity_shipped=5'),
(324, 'root@localho', '2026-06-16 17:00:07', 'INSERT', 'item', 'ord_id=117, item_id=1', 'Dodano: product_id=10023, price=200.00, quantity=1, quantity_shipped=1'),
(325, 'root@localho', '2026-06-16 17:01:07', 'INSERT', 'item', 'ord_id=116, item_id=1', 'Dodano: product_id=10011, price=783.00, quantity=3, quantity_shipped=3'),
(326, 'root@localho', '2026-06-17 14:00:16', 'DELETE', 'item', 'ord_id=100, item_id=1', 'Usunięto: product_id=10021, price=20.45, quantity=500, shipped=500'),
(327, 'root@localho', '2026-06-17 14:00:42', 'DELETE', 'item', 'ord_id=100, item_id=6', 'Usunięto: product_id=10021, price=21.00, quantity=0, shipped=450'),
(328, 'root@localho', '2026-06-17 14:00:42', 'UPDATE', 'ord', '100', '5562260.00->5552035.00; '),
(329, 'root@localho', '2026-06-17 14:01:43', 'DELETE', 'item', 'ord_id=100, item_id=10', NULL),
(330, 'root@localho', '2026-06-17 14:02:32', 'DELETE', 'item', 'ord_id=100, item_id=12', NULL),
(331, 'root@localho', '2026-06-17 14:02:32', 'DELETE', 'item', 'ord_id=100, item_id=13', NULL),
(332, 'root@localho', '2026-06-17 14:02:32', 'DELETE', 'item', 'ord_id=100, item_id=14', NULL),
(333, 'root@localho', '2026-06-17 14:02:32', 'UPDATE', 'ord', '100', '5552035.00->5551891.00; '),
(334, 'root@localho', '2026-06-17 14:03:27', 'UPDATE', 'ord', '100', '5551891.00->5551747.00; '),
(335, 'root@localho', '2026-06-17 14:03:27', 'UPDATE', 'item', 'ord_id=100, item_id=15', 'quantity: 20; '),
(336, 'root@localho', '2026-06-17 14:10:01', 'UPDATE', 'ord', '100', '2026-06-16 16:51:28->2026-06-17 16:10:01; Cash->Credit card; '),
(337, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '97', '->Cash; '),
(338, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '99', '->Cash; '),
(339, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '100', 'Credit card->Cash; '),
(340, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '101', '->Cash; '),
(341, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '102', '->Cash; '),
(342, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '104', '->Cash; '),
(343, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '105', '->Cash; '),
(344, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '106', '->Cash; '),
(345, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '107', '->Cash; '),
(346, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '108', '->Cash; '),
(347, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '109', '->Cash; '),
(348, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '112', '->Cash; '),
(349, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '113', '->Cash; '),
(350, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '114', '->Cash; '),
(351, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '115', '->Cash; '),
(352, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '116', '->Cash; '),
(353, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '117', '->Cash; '),
(354, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '118', '->Cash; '),
(355, 'root@localho', '2026-06-17 15:11:04', 'UPDATE', 'ord', '119', '->Cash; ');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ord`
--

CREATE TABLE `ord` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) UNSIGNED NOT NULL,
  `date_ordered` datetime NOT NULL,
  `date_shipped` datetime DEFAULT NULL,
  `sales_rep_id` int(11) UNSIGNED DEFAULT NULL,
  `total` decimal(11,2) DEFAULT NULL,
  `payment_type` enum('Cash','Credit card','Blik','Bank transfer') DEFAULT NULL,
  `status` enum('Nowe','Zatwierdzone','w Realizacji','Wysłane','Zamknięte') DEFAULT 'Nowe'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ord`
--

INSERT INTO `ord` (`id`, `customer_id`, `date_ordered`, `date_shipped`, `sales_rep_id`, `total`, `payment_type`, `status`) VALUES
(97, 201, '0000-00-00 00:00:00', '1992-09-17 00:00:00', 12, 6100.00, 'Cash', 'Zamknięte'),
(98, 202, '1992-08-31 00:00:00', '1992-09-10 00:00:00', 14, 595.00, 'Cash', 'Zamknięte'),
(99, 218, '1992-08-31 00:00:00', '1992-09-18 00:00:00', 14, 2004.00, 'Cash', 'Zamknięte'),
(100, 204, '2026-06-17 16:10:01', NULL, NULL, 5551747.00, 'Cash', 'Nowe'),
(101, 205, '1992-08-31 00:00:00', '1992-09-15 00:00:00', 14, 8056.60, 'Cash', 'Zamknięte'),
(102, 206, '1992-09-01 00:00:00', '1992-09-08 00:00:00', 15, 8335.00, 'Cash', 'Zamknięte'),
(103, 208, '1992-09-02 00:00:00', '1992-09-22 00:00:00', 15, 377.00, 'Cash', 'Zamknięte'),
(104, 208, '1992-09-03 00:00:00', '1992-09-23 00:00:00', 15, 32430.00, 'Cash', 'Zamknięte'),
(105, 209, '1992-09-04 00:00:00', '1992-09-18 00:00:00', 11, 2722.24, 'Cash', 'Zamknięte'),
(106, 210, '1992-09-07 00:00:00', '1992-09-15 00:00:00', 12, 15634.00, 'Cash', 'Zamknięte'),
(107, 211, '1992-09-07 00:00:00', '1992-09-21 00:00:00', 15, 142171.00, 'Cash', 'Zamknięte'),
(108, 212, '1992-09-07 00:00:00', '1992-09-12 00:00:00', 13, 149570.59, 'Cash', 'Zamknięte'),
(109, 213, '1992-09-08 00:00:00', '1992-09-28 00:00:00', 11, 1020935.00, 'Cash', 'Zamknięte'),
(110, 214, '1992-09-09 00:00:00', '1992-09-21 00:00:00', 11, 1539.13, 'Cash', 'Zamknięte'),
(111, 204, '1992-09-09 00:00:00', '1992-09-21 00:00:00', 11, 2770.00, 'Cash', 'Zamknięte'),
(112, 210, '1992-08-31 00:00:00', '1992-09-10 00:00:00', 12, 550.00, 'Cash', 'Zamknięte'),
(113, 389, '2026-06-06 00:00:00', NULL, NULL, NULL, 'Cash', 'Nowe'),
(114, 216, '2026-09-09 00:00:00', NULL, NULL, 85.00, 'Cash', 'Zatwierdzone'),
(115, 217, '2026-06-12 20:43:33', NULL, NULL, 200.00, 'Cash', 'Nowe'),
(116, 216, '2026-06-12 20:47:49', NULL, NULL, 2349.00, 'Cash', 'Zatwierdzone'),
(117, 217, '2026-06-12 20:55:48', NULL, NULL, 200.00, 'Cash', 'Nowe'),
(118, 359, '2026-06-16 16:51:04', NULL, NULL, 3915.00, 'Cash', 'Zatwierdzone'),
(119, 358, '2026-06-16 16:51:41', NULL, NULL, 2349.00, 'Cash', 'Zatwierdzone');

--
-- Wyzwalacze `ord`
--
DELIMITER $$
CREATE TRIGGER `ord_ai_tr` AFTER INSERT ON `ord` FOR EACH ROW BEGIN
    INSERT INTO oper (kto, kiedy, operacja, tabela, rekord, Uwagi)
    VALUES (CURRENT_USER(), NOW(), 'INS', 'ord', NEW.id, null);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `ord_au_tr` AFTER UPDATE ON `ord` FOR EACH ROW BEGIN
DECLARE changed_fields TEXT DEFAULT '';
IF OLD.date_ordered != NEW.date_ordered THEN
        SET changed_fields = CONCAT(changed_fields, OLD.date_ordered, '->', NEW.date_ordered, '; ');
    END IF;
    
    IF OLD.date_shipped != NEW.date_shipped THEN
        SET changed_fields = CONCAT(changed_fields, OLD.date_shipped, '->', NEW.date_shipped, '; ');
    END IF;
    
    IF OLD.total != NEW.total THEN
        SET changed_fields = CONCAT(changed_fields, OLD.total, '->', NEW.total, '; ');
    END IF;
    
    IF OLD.payment_type != NEW.payment_type THEN
        SET changed_fields = CONCAT(changed_fields, OLD.payment_type, '->', NEW.payment_type, '; ');
    END IF;

    IF changed_fields != '' THEN
        INSERT INTO oper (kto, kiedy, operacja, tabela, rekord, Uwagi)
        VALUES (CURRENT_USER(), NOW(), 'UPDATE', 'ord', OLD.id, 
                changed_fields);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `ord_bd_tr` BEFORE DELETE ON `ord` FOR EACH ROW BEGIN
    INSERT INTO oper (kto, kiedy, operacja, tabela, rekord, Uwagi)
    VALUES (CURRENT_USER(), NOW(), 'DELETE', 'ord', OLD.id, null);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `permissions`
--

CREATE TABLE `permissions` (
  `username` varchar(12) NOT NULL,
  `menu` varchar(255) NOT NULL,
  `type` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `prices`
--

CREATE TABLE `prices` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `date` datetime NOT NULL,
  `info` varchar(25) NOT NULL,
  `netto` decimal(10,2) NOT NULL,
  `vat` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prices`
--

INSERT INTO `prices` (`product_id`, `date`, `info`, `netto`, `vat`) VALUES
(10011, '2026-01-05 12:00:12', '', 785.00, 23.00),
(10011, '2026-01-13 00:00:00', '', 902.00, 5.00),
(10011, '2026-02-01 11:00:00', '', 772.00, 0.00),
(10011, '2026-02-01 12:12:12', '', 904.00, 3.00),
(10011, '2026-02-11 19:25:00', '', 781.00, 0.00),
(10011, '2026-03-01 15:14:12', '', 785.00, 5.00),
(10011, '2026-03-05 12:14:12', '', 783.00, 3.00),
(10011, '2026-07-05 12:14:12', '', 780.00, 23.00),
(10011, '2026-09-01 15:00:00', '', 774.00, 0.00),
(10011, '2026-11-01 15:30:00', '', 782.00, 5.00),
(10012, '2026-01-10 13:00:00', '', 1069.00, 23.00),
(10012, '2026-01-11 13:00:00', '', 1069.00, 23.00),
(10012, '2026-01-12 13:00:00', '', 1069.00, 23.00),
(10012, '2026-01-13 13:00:00', '', 1069.00, 23.00),
(10012, '2026-01-13 13:24:00', '', 1069.00, 23.00),
(10013, '2026-05-12 12:26:12', '', 199.99, 23.00),
(10013, '2026-05-12 13:26:11', '', 199.00, 23.00),
(10013, '2026-05-12 14:26:11', '', 195.99, 0.00),
(10021, '2026-01-14 13:35:39', '', 20.45, 23.00),
(10022, '2026-01-21 00:00:00', '', 19.99, 5.00),
(10022, '2026-02-01 00:00:00', '', 21.00, 5.00),
(10022, '2026-03-11 12:00:00', '', 23.00, 5.00),
(10022, '2026-03-20 00:00:00', '', 19.50, 5.00),
(10022, '2026-03-21 00:00:00', '', 25.00, 5.00),
(10022, '2026-03-23 12:00:00', '', 24.90, 5.00),
(10022, '2026-05-01 00:00:00', '', 20.99, 5.00),
(10022, '2026-05-12 12:04:52', '', 20.00, 5.00),
(10022, '2026-05-12 12:11:33', '', 21.95, 5.00),
(10022, '2026-05-21 00:00:00', '', 26.99, 5.00),
(10023, '2026-01-13 12:12:12', '', 40.95, 23.00),
(10023, '2026-02-01 12:30:00', '', 765.00, 23.00),
(10023, '2026-05-12 12:10:37', '', 200.00, 23.00),
(10023, '2026-05-12 12:12:08', '', 200.00, 23.00),
(10023, '2026-05-12 12:16:23', '', 200.00, 23.00),
(10023, '2026-05-12 12:26:11', '', 200.00, 23.00),
(20106, '2026-05-10 00:00:01', '', 80.00, 23.00),
(20106, '2026-05-10 00:00:02', '', 76.99, 23.00),
(20106, '2026-05-10 00:00:03', '', 78.99, 23.00),
(20106, '2026-05-10 00:00:04', '', 75.99, 23.00),
(20106, '2026-05-10 00:00:05', '', 79.99, 23.00),
(20106, '2026-05-10 00:00:06', '', 74.00, 23.00),
(20106, '2026-05-10 00:00:07', '', 73.00, 23.00),
(20106, '2026-05-10 00:00:08', '', 81.00, 23.00),
(20106, '2026-05-10 00:00:09', '', 84.00, 23.00),
(20106, '2026-05-10 00:00:10', '', 85.00, 23.00),
(20106, '2026-05-12 12:17:50', '', 80.00, 23.00),
(20108, '2026-02-02 12:20:52', '', 28.00, 23.00),
(20108, '2026-05-12 12:15:43', '', 67.00, 23.00),
(20108, '2026-05-15 12:33:08', '', 67.67, 23.00),
(20108, '2026-05-20 19:33:08', '', 69.00, 23.00),
(20108, '2026-05-29 19:34:35', '', 69.67, 23.00),
(20108, '2026-05-30 23:34:35', '', 69.69, 23.00),
(20201, '2026-05-10 12:09:34', '', 70.00, 23.00),
(20510, '2026-01-05 00:00:00', '', 9876.00, 5.00),
(20512, '2026-05-01 13:26:12', '', 9876.00, 23.00),
(20512, '2026-05-08 13:26:12', '', 9876.00, 23.00),
(20512, '2026-05-10 00:00:00', '', 1000.00, 23.00),
(20512, '2026-05-15 13:26:12', '', 9876.00, 23.00),
(20512, '2026-05-22 13:26:12', '', 9876.00, 23.00),
(20512, '2026-05-29 13:26:12', '', 9876.00, 23.00),
(20512, '2026-06-05 13:26:12', '', 9876.00, 23.00),
(20512, '2026-06-12 13:26:12', '', 9876.00, 23.00),
(20512, '2026-06-19 13:26:12', '', 9876.00, 23.00),
(20512, '2026-06-26 13:26:12', '', 9876.00, 23.00),
(20512, '2026-07-03 13:26:12', '', 9876.00, 23.00),
(30321, '2026-01-12 20:34:35', '', 1665.00, 23.00),
(30321, '2026-02-01 09:12:43', '', 1676.00, 23.00),
(30321, '2026-02-18 10:37:43', '', 1669.00, 23.00),
(30321, '2026-02-28 09:28:18', '', 1600.00, 23.00),
(30321, '2026-03-01 12:28:18', '', 1599.00, 23.00),
(30321, '2026-03-17 16:23:27', '', 1667.00, 23.00),
(30321, '2026-05-23 12:23:35', '', 1699.00, 23.00),
(30326, '2026-05-10 00:00:00', '', 582.00, 23.00),
(30326, '2026-05-17 08:14:00', '', 596.55, 23.00),
(30326, '2026-05-24 13:47:00', '', 581.64, 23.00),
(30326, '2026-05-31 19:05:00', '', 596.18, 23.00),
(30326, '2026-06-07 09:32:00', '', 581.28, 23.00),
(30326, '2026-06-14 16:21:00', '', 566.75, 23.00),
(30326, '2026-06-21 11:58:00', '', 580.92, 23.00),
(30326, '2026-06-28 21:16:00', '', 595.44, 23.00),
(30326, '2026-07-05 07:43:00', '', 580.55, 23.00),
(30326, '2026-07-12 14:27:00', '', 565.99, 23.00),
(30326, '2026-07-19 18:54:00', '', 580.14, 23.00),
(30421, '2026-03-09 07:00:00', '', 16.00, 23.00),
(30421, '2026-03-10 10:30:00', '', 17.00, 23.00),
(30421, '2026-03-11 09:30:00', '', 16.50, 23.00),
(30421, '2026-03-12 11:30:00', '', 15.00, 23.00),
(30421, '2026-03-13 13:00:00', '', 18.00, 23.00),
(30421, '2026-03-14 12:00:00', '', 15.50, 23.00),
(30421, '2026-03-15 11:00:00', '', 14.50, 23.00),
(30421, '2026-03-16 10:00:00', '', 17.50, 23.00),
(30421, '2026-03-17 09:00:00', '', 16.20, 23.00),
(30421, '2026-03-18 08:00:00', '', 15.70, 23.00),
(30426, '2025-08-13 00:00:00', '', 662.99, 23.00),
(30426, '2025-08-20 10:11:00', '', 679.56, 23.00),
(30426, '2025-08-27 15:38:00', '', 662.57, 23.00),
(30426, '2025-09-03 08:49:00', '', 646.01, 23.00),
(30426, '2025-09-10 19:26:00', '', 662.16, 23.00),
(30426, '2025-09-17 12:07:00', '', 678.71, 23.00),
(30426, '2025-09-24 17:55:00', '', 661.74, 23.00),
(30426, '2025-10-01 09:14:00', '', 645.20, 23.00),
(30426, '2025-10-08 21:03:00', '', 661.33, 23.00),
(30426, '2025-10-15 13:41:00', '', 677.86, 23.00),
(30426, '2025-10-22 16:28:00', '', 660.91, 23.00),
(32779, '2026-06-01 16:29:30', '', 120.00, 23.00),
(40421, '2026-02-03 12:20:00', '', 743.00, 23.00),
(40421, '2026-02-03 12:20:01', '', 742.00, 5.00),
(40421, '2026-02-03 12:20:02', '', 741.00, 8.00),
(40421, '2026-02-03 12:20:53', '', 750.00, 5.00),
(40421, '2026-02-03 12:20:54', '', 749.00, 8.00),
(40421, '2026-02-03 12:20:55', '', 748.00, 23.00),
(40421, '2026-02-03 12:20:56', '', 747.00, 8.00),
(40421, '2026-02-03 12:20:57', '', 746.00, 5.00),
(40421, '2026-02-03 12:20:58', '', 745.00, 5.00),
(40421, '2026-02-03 12:20:59', '', 744.00, 8.00),
(40422, '2026-05-20 13:38:12', '', 50.00, 23.00),
(40422, '2026-06-01 13:38:12', '', 50.00, 23.00),
(40422, '2026-06-12 13:38:12', '', 50.00, 23.00),
(40422, '2026-06-22 13:38:12', '', 50.00, 23.00),
(40422, '2026-07-12 13:38:12', '', 50.00, 23.00),
(40422, '2026-07-22 13:38:12', '', 50.00, 23.00),
(40422, '2026-07-31 13:38:12', '', 50.00, 23.00),
(40422, '2026-08-12 13:38:12', '', 50.00, 23.00),
(40422, '2026-08-24 13:38:12', '', 50.00, 23.00),
(41010, '2026-01-04 13:12:12', '', 8.00, 23.00),
(41010, '2026-01-12 10:12:08', '', 7.00, 23.00),
(41010, '2026-02-20 01:49:01', '', 8.00, 5.00),
(41010, '2026-02-21 21:37:07', '', 9.00, 0.00),
(41010, '2026-02-25 08:32:00', '', 9.23, 23.00),
(41010, '2026-03-03 09:12:12', '', 7.00, 5.00),
(41010, '2026-03-09 07:12:12', '', 7.00, 5.00),
(41010, '2026-03-12 10:15:12', '', 9.00, 8.00),
(41010, '2026-04-10 08:12:01', '', 9.00, 23.00),
(41010, '2026-05-01 04:32:59', '', 8.00, 8.00),
(41010, '2026-05-01 09:24:10', '', 8.00, 5.00),
(41020, '2026-01-14 13:30:00', '', 13.89, 23.00),
(41020, '2026-01-30 00:00:00', '', 12.00, 8.00),
(41020, '2026-02-10 00:00:00', '', 12.20, 8.00),
(41020, '2026-02-14 00:00:00', '', 12.40, 8.00),
(41020, '2026-02-27 00:00:00', '', 13.70, 8.00),
(41020, '2026-03-15 00:00:00', '', 15.30, 8.00),
(41020, '2026-03-20 00:00:00', '', 14.70, 8.00),
(41020, '2026-03-22 00:00:00', '', 14.20, 8.00),
(41020, '2026-03-26 00:00:00', '', 15.10, 8.00),
(41020, '2026-04-17 00:00:00', '', 16.05, 8.00),
(41020, '2026-04-19 00:00:00', '', 14.90, 8.00),
(41020, '2026-06-21 00:00:00', '', 14.15, 8.00),
(41080, '2026-02-26 00:00:00', '', 35.00, 23.00),
(41100, '2026-01-27 21:37:00', '', 50.00, 23.00),
(41100, '2026-01-30 21:30:00', '', 47.00, 23.00),
(41100, '2026-04-27 12:21:00', '', 48.00, 23.00),
(41100, '2026-05-12 17:05:02', '', 45.00, 5.00),
(41100, '2026-05-21 14:51:00', '', 49.00, 23.00),
(50169, '2026-01-30 14:00:50', '', 22.89, 0.00),
(50169, '2026-05-02 12:24:32', '', 4.49, 0.00),
(50169, '2026-05-03 12:23:40', '', 4.99, 0.00),
(50169, '2026-05-04 12:22:33', '', 4.29, 0.00),
(50169, '2026-05-05 12:22:15', '', 4.25, 0.00),
(50169, '2026-05-06 12:21:42', '', 4.22, 0.00),
(50169, '2026-05-07 12:20:12', '', 4.44, 0.00),
(50169, '2026-05-08 12:19:48', '', 4.60, 0.00),
(50169, '2026-05-09 12:19:10', '', 4.40, 0.00),
(50169, '2026-05-10 12:18:53', '', 4.20, 0.00),
(50169, '2026-05-11 12:17:48', '', 4.50, 0.00),
(50169, '2026-05-12 12:21:24', '', 4.33, 0.00),
(50273, '2026-01-20 11:00:41', '', 22.89, 5.00),
(50417, '2026-04-12 00:00:00', '', 9876.00, 8.00),
(50417, '2026-05-05 13:32:05', '', 9876.00, 8.00),
(50417, '2026-05-12 13:32:05', '', 9876.00, 8.00),
(50417, '2026-05-12 13:32:15', 'Griffey Glove', 9875.00, 8.00),
(50417, '2026-05-12 13:32:52', '', 9876.00, 8.00),
(50417, '2026-05-13 13:32:06', '', 9876.00, 8.00),
(50417, '2026-05-13 13:32:25', 'Griffey Glove', 9874.00, 8.00),
(50417, '2026-05-14 13:32:07', '', 9876.00, 8.00),
(50417, '2026-05-14 13:32:35', 'Griffey Glove', 9876.00, 8.00),
(50417, '2026-05-15 13:32:08', '', 9876.00, 8.00),
(50417, '2026-05-15 13:32:45', 'Griffey Glove', 9846.00, 8.00),
(50417, '2026-05-19 13:32:05', '', 9876.00, 8.00),
(50417, '2026-05-21 13:32:03', '', 9876.00, 8.00),
(50417, '2026-05-21 13:32:05', '', 9876.00, 8.00),
(50417, '2026-05-21 13:32:09', 'Griffey Glove', 9806.00, 8.00),
(50417, '2026-05-26 13:32:05', '', 9876.00, 8.00),
(50417, '2026-06-02 13:32:05', '', 9876.00, 8.00),
(50417, '2026-06-09 13:32:05', '', 9876.00, 8.00),
(50417, '2026-06-16 13:32:05', '', 9876.00, 8.00),
(50417, '2026-06-16 13:32:15', '', 9876.00, 8.00),
(50417, '2026-06-16 13:32:55', 'Griffey Glove', 9866.00, 8.00),
(50417, '2026-06-17 13:32:05', 'Griffey Glove', 9816.00, 8.00),
(50417, '2026-06-17 13:32:45', '', 9876.00, 8.00),
(50417, '2026-06-18 13:32:06', 'Griffey Glove', 9896.00, 8.00),
(50417, '2026-06-18 13:32:55', '', 9876.00, 8.00),
(50417, '2026-06-19 13:32:01', '', 9876.00, 8.00),
(50417, '2026-06-19 13:32:07', 'Griffey Glove', 9976.00, 8.00),
(50417, '2026-06-20 13:32:02', '', 9876.00, 8.00),
(50417, '2026-06-20 13:32:08', 'Griffey Glove', 9816.00, 8.00),
(50417, '2026-06-23 13:32:05', '', 9876.00, 8.00),
(50417, '2026-06-30 13:32:05', '', 0.00, 8.00),
(50418, '2026-02-27 00:00:00', '', 9876.00, 23.00),
(50418, '2026-05-05 13:34:09', '', 9876.00, 23.00),
(50419, '2026-05-28 12:02:06', '', 85.00, 8.00),
(50530, '2023-08-25 21:00:00', '', 47.00, 23.00),
(50530, '2026-02-01 14:00:00', '', 43.00, 23.00),
(50530, '2026-02-16 15:00:00', '', 41.00, 23.00),
(50530, '2026-04-20 17:00:00', '', 45.00, 23.00),
(50530, '2026-05-13 12:24:00', '', 45.00, 23.00),
(50530, '2026-05-27 14:00:00', '', 43.00, 23.00),
(50530, '2026-06-23 19:00:00', '', 45.77, 23.00),
(50530, '2026-06-29 18:00:00', '', 42.00, 23.00),
(50530, '2026-10-20 10:00:00', '', 49.00, 23.00),
(50530, '2026-10-23 10:00:00', '', 45.00, 23.00),
(50532, '2026-01-20 00:00:00', '', 47.00, 23.00),
(50536, '2026-03-03 18:31:05', '', 9762.00, 23.00),
(50536, '2026-03-15 12:22:52', '', 9800.00, 23.00),
(50536, '2026-03-16 14:23:57', '', 9466.00, 23.00),
(50536, '2026-03-30 12:23:57', '', 9850.00, 23.00),
(50536, '2026-04-22 15:23:57', '', 9766.00, 23.00),
(50536, '2026-05-12 12:23:44', '', 8976.00, 23.00),
(50536, '2026-05-18 22:23:57', '', 9902.00, 23.00),
(50536, '2026-06-03 12:23:57', '', 9567.00, 23.00),
(50536, '2026-06-25 12:23:57', '', 9506.00, 23.00),
(50536, '2026-06-30 23:23:57', '', 9999.00, 23.00);

--
-- Wyzwalacze `prices`
--
DELIMITER $$
CREATE TRIGGER `prices_ai_tr` AFTER INSERT ON `prices` FOR EACH ROW BEGIN
    SET @info = CONCAT(NEW.date, ';', NEW.netto, ';', NEW.vat);
    INSERT INTO oper (kto, operacja, tabela, rekord, uwagi)
              VALUES (USER(), 'insert', 'prices', NEW.product_id, @info);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prices_au_tr` BEFORE UPDATE ON `prices` FOR EACH ROW BEGIN
    SET @changes = CONCAT(
        IF(OLD.product_id <> NEW.product_id,
            CONCAT(OLD.product_id, '->', NEW.product_id, ';'), ''),
        IF(OLD.date <> NEW.date,
            CONCAT(OLD.date, '->', NEW.date, ';'), ''),
        IF(OLD.netto <> NEW.netto,
            CONCAT(OLD.netto, '->', NEW.netto, ';'), ''),
        IF(OLD.vat <> NEW.vat,
            CONCAT(OLD.vat, '->', NEW.vat, ';'), '')
      );

    IF @changes <> '' THEN
        INSERT INTO oper (kto, operacja, tabela, rekord, uwagi)
        VALUES (USER(), 'update', 'prices', NEW.product_id, CONCAT('', @changes));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prices_bd_tr` BEFORE DELETE ON `prices` FOR EACH ROW BEGIN
    SET @info = CONCAT(OLD.date, ';', OLD.netto, ';', OLD.vat);
    INSERT INTO oper (kto, operacja, tabela, rekord, uwagi)
              VALUES (USER(), 'insert', 'prices', OLD.product_id, @info);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `product`
--

CREATE TABLE `product` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `short_desc` varchar(255) NOT NULL,
  `suggested_price` decimal(11,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `short_desc`, `suggested_price`) VALUES
(10011, 'Bunny Boot', 'Beginner\'s ski boot', 765.00),
(10012, 'Ace Ski Boot', 'Intermediate ski boot', 765.00),
(10013, 'Pro Ski Boot', 'Advanced ski boot', 765.00),
(10021, 'Bunny Ski Pole', 'Beginner\'s ski pole', 16.25),
(10022, 'Ace Ski Pole', 'Intermediate ski pole', 21.95),
(10023, 'Pro Ski Pole', 'Advanced ski pole', 200.00),
(20106, 'Junior Soccer Ball', 'Junior soccer ball', 11.00),
(20108, 'World Cup Soccer Ball', 'World cup soccer ball', 28.00),
(20201, 'World Cup Net', 'World cup net', 123.00),
(20510, 'Black Hawk Knee Pads', 'Knee pads, pair', 9876.00),
(20512, 'Black Hawk Elbow Pads', 'Elbow pads, pair', 9876.00),
(30321, 'Grand Prix Bicycle', 'Road bicycle', 1669.00),
(30326, 'Himalaya Bicycle', 'Mountain bicycle', 582.00),
(30421, 'Grand Prix Bicycle Tires', 'Road bicycle tires', 16.00),
(30426, 'Himalaya Tires', 'Mountain bicycle tires', 9876.00),
(30433, 'New Air Pump', 'Tire pump', 20.00),
(32779, 'Slaker Water Bottle', 'Water bottle', 7.00),
(32861, 'Safe-T Helmet', 'Bicycle helmet', 9876.00),
(40421, 'Alexeyer Pro Lifting Bar', 'Straight bar', 65.00),
(40422, 'Pro Curling Bar', 'Curling bar', 50.00),
(41010, 'Prostar 10 Pound Weight', 'Ten pound weight', 8.00),
(41020, 'Prostar 20 Pound Weight', 'Twenty pound weight', 12.00),
(41050, 'Prostar 50 Pound Weight', 'Fifty pound weight', 25.00),
(41080, 'Prostar 80 Pound Weight', 'Eighty pound weight', 35.00),
(41100, 'Prostar 100 Pound Weight', 'One hundred pound weight', 45.00),
(50169, 'Major League Baseball', 'Baseball', 4.29),
(50273, 'Chapman Helmet', 'Batting helmet', 22.89),
(50417, 'Griffey Glove', 'Outfielder\'s glove', 9876.00),
(50418, 'Alomar Glove', 'Infielder\'s glove', 9876.00),
(50419, 'Steinbach Glove', 'Catcher\'s glove', 80.00),
(50530, 'Cabrera Bat', 'Thirty inch bat', 45.00),
(50532, 'Puckett Bat', 'Thirty-two inch bat', 47.00),
(50536, 'Winfield Bat', 'Thirty-six inch bat', 9876.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pvlink`
--

CREATE TABLE `pvlink` (
  `pcid` int(11) UNSIGNED NOT NULL,
  `vin` char(17) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pvlink`
--

INSERT INTO `pvlink` (`pcid`, `vin`) VALUES
(10, 'VW20061135737D457'),
(22, 'TY20121155737ET33'),
(24, 'WOLOMFF74693PYC85'),
(153, 'MCB0FR113573ZG758');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `region`
--

CREATE TABLE `region` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `region`
--

INSERT INTO `region` (`id`, `name`) VALUES
(35, 'Africa/Central'),
(34, 'Africa/Middle/West'),
(33, 'Africa/MiddleEast'),
(31, 'Africa/North'),
(32, 'Africa/South'),
(7, 'Antarctida'),
(45, 'Asia/China'),
(43, 'Asia/Japan'),
(44, 'Asia/Middle East'),
(42, 'Asia/South-East'),
(81, 'Australia/North'),
(83, 'Australia/South-East'),
(84, 'Australia/South-West'),
(55, 'Europe/Central'),
(54, 'Europe/East'),
(51, 'Europe/North'),
(53, 'Europe/South'),
(52, 'Europe/West'),
(20, 'Middle America'),
(15, 'North America/Central'),
(13, 'North America/East'),
(11, 'North America/North'),
(12, 'North America/South'),
(14, 'North America/West'),
(25, 'South America/Brasil'),
(21, 'South America/North'),
(22, 'South America/South'),
(23, 'South America/West');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `roles`
--

CREATE TABLE `roles` (
  `nazwa` varchar(25) NOT NULL,
  `menu` varchar(15) NOT NULL,
  `typ_uprawnien` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`nazwa`, `menu`, `typ_uprawnien`) VALUES
('Actor', 'hr', 'R'),
('President', 'hr', 'W'),
('Sales Representative', 'hr', 'W'),
('Sales Representative', 'magazyn', 'W'),
('VP, Administration', 'hr', 'W'),
('VP, Finance', 'finance', 'W'),
('Warehouse Manager', 'magazyn', 'R'),
('Warehouse Manager', 'warehouse', 'W');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `title`
--

CREATE TABLE `title` (
  `name` varchar(25) NOT NULL,
  `salary_min` decimal(10,2) NOT NULL,
  `salary_max` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `title`
--

INSERT INTO `title` (`name`, `salary_min`, `salary_max`) VALUES
('Actor', 1000.00, 5000.00),
('President', 2000.00, 4000.00),
('Sales Representative', 1400.00, 1800.00),
('Singer', 750.00, 5000.00),
('Stock Clerk', 0.00, 0.00),
('VP, Administration', 800.00, 1200.00),
('VP, Finance', 1400.00, 1600.00),
('VP, Operations', 1400.00, 1600.00),
('VP, Sales', 1400.00, 1600.00),
('Warehouse Manager', 1200.00, 1400.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `vehicle`
--

CREATE TABLE `vehicle` (
  `vin` char(17) NOT NULL,
  `brand` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `model` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `type` varchar(15) NOT NULL,
  `mileage` int(10) UNSIGNED DEFAULT NULL,
  `mdate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle`
--

INSERT INTO `vehicle` (`vin`, `brand`, `model`, `type`, `mileage`, `mdate`) VALUES
('', '', '', '', NULL, NULL),
('14151515341', 'ferrari', 'turbo', 'sport', 555555, '2007-02-05'),
('1FTFW1ET1DKF12345', 'Ford', 'F-150', 'Pickup', 123000, '2013-11-30'),
('1FTFW1ET1EKF12345', 'Ford', 'F-150', 'Pickup', 134000, '2014-06-11'),
('1HGCM82633A004352', 'Honda', 'Accord', 'Sedan', 150000, '2010-05-15'),
('1HTMMAAM27H123456', 'International', 'DuraStar', 'Medium Truck', 390000, '2007-01-03'),
('23486932614KHA', 'BMW', 'M4', 'Sedan', 340000, '2019-06-28'),
('2HGFG11889H501234', 'Honda', 'Civic', 'Coupe', 68000, '2014-06-12'),
('2T1BURHE0JC078692', 'Toyota', 'Corolla', 'Sedan', 95000, '2018-03-22'),
('33356273584864894', 'YAMAHA', 'WR', 'sport', 12421, '2017-06-25'),
('3335627489489', 'YAMAHA', 'WR', 'sport', 12421, '2017-06-25'),
('3D7MX48C25G123456', 'Dodge', 'Ram 3500', 'Pickup', 245000, '2005-03-12'),
('3FA6P0H73DR265789', 'Ford', 'Fusion', 'Sedan', 87000, '2013-08-09'),
('3VW2K7AJ5EM123456', 'Volkswagen', 'Jetta', 'Sedan', 88000, '2015-03-09'),
('4T1BF1FK0GU123456', 'Toyota', 'Camry', 'Sedan', 67000, '2016-01-15'),
('5YJ3E1EA7KF123456', 'Tesla', 'Model 3', 'Sedan', 30000, '2019-09-05'),
('5YJSA1E26FF087432', 'Tesla', 'Model S', 'Electric', 78000, '2015-09-30'),
('676745775357753', 'KTM', 'duke', 'sport', 1234, '2025-06-02'),
('79792GAJAASJKT', 'Skoda', 'Fabia', 'Sedan', 340000, '2015-06-19'),
('878732JHJKS865JGB', 'Honda', 'CBR', 'Sport', 234, '2025-06-18'),
('D-HDRY20200345XYZ', 'Airbus Helicopters', 'H145', 'Helicopter', 1300, '2020-11-05'),
('G-OBMX20191234XYZ', 'Robinson', 'R44 Raven II', 'Helicopter', 900, '2019-08-23'),
('GUGS674C545', 'BMW', 'M5', 'Sedan', 321000, '2017-03-22'),
('HFSJJS52332', 'Toyota', 'Yaris', 'Sedan', 2100000, '2009-06-19'),
('JA123A19980678XYZ', 'Mitsubishi', 'MU-2', 'Airplane', 4900, '1998-02-17'),
('JH4KA9650MC123456', 'Acura', 'Legend', 'Sedan', 202000, '1991-06-28'),
('JHMFA16586S000456', 'Honda', 'Civic Hybrid', 'Sedan', 142000, '2006-04-19'),
('JN8AS5MT3CW123456', 'Nissan', 'Rogue', 'SUV', 112000, '2012-12-21'),
('JN8AZ2KR0CT123456', 'Nissan', 'NV3500', 'Van', 112000, '2012-02-05'),
('MCB0FR113573ZG758', 'Mercedes', 'S-Class', 'Sedan', 150450, '2022-11-16'),
('N123AB20200001XYZ', 'Cessna', '172 Skyhawk', 'Airplane', 3500, '2000-07-12'),
('N159GH20170893XYZ', 'Bell', '206 JetRanger', 'Helicopter', 2200, '2017-06-29'),
('N456CD20150678XYZ', 'Piper', 'PA-28 Cherokee', 'Airplane', 2800, '2005-09-25'),
('N789EF20120123XYZ', 'Beechcraft', 'Baron 58', 'Airplane', 4100, '2012-03-14'),
('RA-820452014899XY', 'Antonov', 'An-124 Ruslan', 'Cargo Aircraft', 8700, '2014-04-01'),
('TY20121155737ET33', 'Toyota', 'Avensis', 'Kombi', 280000, '2022-11-21'),
('VF624AEA000123456', 'Renault', 'Master', 'Van', 198000, '2013-07-14'),
('VW20061135737D457', 'Volkswagen', 'Golf', 'Hatchback', 12500, '2022-11-30'),
('WAUZZZ8V1JA123456', 'Audi', 'A4', 'Sedan', 85000, '2017-05-10'),
('WAUZZZ8V2KA234567', 'Audi', 'Q5', 'SUV', 60000, '2019-08-23'),
('WAUZZZ8V3KA345678', 'Audi', 'A6', 'Sedan', 81000, '2016-01-17'),
('WAUZZZ8V7JA000456', 'Audi', 'A3', 'Hatchback', 54000, '2018-04-10'),
('WBA3A5C53CF234567', 'BMW', 'X3', 'SUV', 54000, '2018-11-05'),
('WBA3A9C50FF123456', 'BMW', '320i', 'Sedan', 73000, '2015-03-15'),
('WBA3B5G53JN123456', 'BMW', '530i', 'Sedan', 92000, '2014-07-21'),
('WBA5A7C59FD567890', 'BMW', 'X5', 'SUV', 58000, '2017-04-10'),
('WDB9302331L123456', 'Mercedes-Benz', 'Actros', 'Truck', 450000, '2015-11-20'),
('WDBUF56X78B123456', 'Mercedes-Benz', 'E-Class', 'Sedan', 102000, '2008-07-25'),
('WDDGF4HB2DR234567', 'Mercedes-Benz', 'C-Class', 'Sedan', 67000, '2017-12-12'),
('WDDGF4HB3FR456789', 'Mercedes-Benz', 'E-Class', 'Sedan', 74000, '2018-06-29'),
('WDDGF8AB7DR123456', 'Mercedes-Benz', 'GLA', 'SUV', 49000, '2016-09-30'),
('WMA06XZZ9FM123456', 'MAN', 'TGX', 'Truck', 720000, '2015-05-18'),
('WOLOMFF74693PYC85', 'Opel', 'Astra', 'Sedan', 250432, '2022-11-21'),
('WVWZZZ3BZWE689725', 'Volkswagen', 'Golf', 'Hatchback', 123000, '2012-10-10'),
('YS2P4X20005312345', 'Scania', 'R450', 'Truck', 670000, '2018-09-25'),
('YS3DF78K6X7056789', 'Saab', '9-3', 'Convertible', 99000, '2007-05-19'),
('ZCFC65A8001234567', 'Iveco', 'Daily', 'Light Truck', 168000, '2010-12-01'),
('ZFAAXX00E0P123456', 'Fiat', 'Panda', 'Hatchback', 78000, '2011-02-14');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `warehouse`
--

CREATE TABLE `warehouse` (
  `id` int(11) UNSIGNED NOT NULL,
  `region_id` int(11) UNSIGNED NOT NULL,
  `address` longtext DEFAULT NULL,
  `city` varchar(30) NOT NULL,
  `state` varchar(20) DEFAULT NULL,
  `country` varchar(30) NOT NULL,
  `zip_code` varchar(75) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `manager_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouse`
--

INSERT INTO `warehouse` (`id`, `region_id`, `address`, `city`, `state`, `country`, `zip_code`, `phone`, `manager_id`) VALUES
(4, 55, 'Warszawska 23', 'Radom', NULL, 'Polska', '25-631', '+48 765 156 416', 122),
(5, 55, 'Miła 96', 'Poznań', NULL, 'Polska', '25-631', '+48 165 354 564', 122),
(101, 14, '283 King Street', 'Seattle', 'WA', 'USA', '5654', '+1 212 555 1234', 6),
(201, 23, 'Ruta 5', 'Paposo', NULL, 'Chile', '12345', '+56 9 8765 4321', 7),
(301, 35, '6921 King Way', 'Abuda', NULL, 'Nigeria', '543', '+234 803 123 4567', 8),
(401, 42, '86 Chu Street', 'Hong Kong', NULL, 'Hongkong', '654', '+852 1234 5678', 9),
(777, 55, 'Jajeczna 232', 'Masłów', NULL, 'Polska', '22-222', '+48 654 543 754', 122),
(10501, 55, 'Jihlava 1', 'Bratislava', NULL, 'Czechia', '564975', '+420 123 456 789', 18),
(10502, 55, 'Białogońska 13', 'Kielce', NULL, 'Polska', '25-631', '+48 534 654 854', 9),
(10503, 55, 'Radomska 213', 'Kielce', NULL, 'Polska', '25-631', '+48 734 876 654', 9),
(10504, 55, 'Szczecińska 35', 'Kielce', NULL, 'Polska', '25-635', '+48 763 765 535', 2);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_region_id_fk` (`region_id`),
  ADD KEY `customer_sales_rep_id_fk` (`contact_id`),
  ADD KEY `name` (`name`);

--
-- Indeksy dla tabeli `dept`
--
ALTER TABLE `dept`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dept_name_region_id_uk` (`name`,`region_id`),
  ADD KEY `dept_region_id_fk` (`region_id`);

--
-- Indeksy dla tabeli `emp`
--
ALTER TABLE `emp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `pesel` (`pesel`),
  ADD KEY `emp_dept_id_fk` (`dept_id`),
  ADD KEY `emp_manager_id_fk` (`manager_id`),
  ADD KEY `emp_title_fk` (`title`),
  ADD KEY `last_name` (`last_name`),
  ADD KEY `birth_date` (`birth_date`);

--
-- Indeksy dla tabeli `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`product_id`,`warehouse_id`),
  ADD KEY `inventory_warehouse_id_fk` (`warehouse_id`);

--
-- Indeksy dla tabeli `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`ord_id`,`item_id`),
  ADD KEY `item_ordid_prodid_uk` (`ord_id`,`product_id`),
  ADD KEY `item_product_id_fk` (`product_id`);

--
-- Indeksy dla tabeli `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `oper`
--
ALTER TABLE `oper`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `ord`
--
ALTER TABLE `ord`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ord_customer_id_fk` (`customer_id`),
  ADD KEY `ord_sales_rep_id_fk` (`sales_rep_id`);

--
-- Indeksy dla tabeli `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`username`,`menu`);

--
-- Indeksy dla tabeli `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`product_id`,`date`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeksy dla tabeli `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_name_uk` (`name`);

--
-- Indeksy dla tabeli `pvlink`
--
ALTER TABLE `pvlink`
  ADD PRIMARY KEY (`pcid`,`vin`),
  ADD KEY `pcveh2` (`vin`);

--
-- Indeksy dla tabeli `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_name_uk` (`name`);

--
-- Indeksy dla tabeli `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`nazwa`,`menu`);

--
-- Indeksy dla tabeli `title`
--
ALTER TABLE `title`
  ADD PRIMARY KEY (`name`);

--
-- Indeksy dla tabeli `vehicle`
--
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`vin`);

--
-- Indeksy dla tabeli `warehouse`
--
ALTER TABLE `warehouse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_manager_id_fk` (`manager_id`),
  ADD KEY `warehouse_region_id_fk` (`region_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12367;

--
-- AUTO_INCREMENT for table `dept`
--
ALTER TABLE `dept`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `emp`
--
ALTER TABLE `emp`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=320;

--
-- AUTO_INCREMENT for table `oper`
--
ALTER TABLE `oper`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=356;

--
-- AUTO_INCREMENT for table `ord`
--
ALTER TABLE `ord`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50537;

--
-- AUTO_INCREMENT for table `region`
--
ALTER TABLE `region`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `warehouse`
--
ALTER TABLE `warehouse`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10505;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_region_id_fk` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`),
  ADD CONSTRAINT `customer_sales_rep_id_fk` FOREIGN KEY (`contact_id`) REFERENCES `emp` (`id`);

--
-- Constraints for table `dept`
--
ALTER TABLE `dept`
  ADD CONSTRAINT `dept_region_id_fk` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`);

--
-- Constraints for table `emp`
--
ALTER TABLE `emp`
  ADD CONSTRAINT `emp_dept_id_fk` FOREIGN KEY (`dept_id`) REFERENCES `dept` (`id`),
  ADD CONSTRAINT `emp_manager_id_fk` FOREIGN KEY (`manager_id`) REFERENCES `emp` (`id`),
  ADD CONSTRAINT `emp_title_fk` FOREIGN KEY (`title`) REFERENCES `title` (`name`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_product_id_fk` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `inventory_warehouse_id_fk` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`);

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_product_id_fk` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `ord_item_fk` FOREIGN KEY (`ord_id`) REFERENCES `ord` (`id`);

--
-- Constraints for table `ord`
--
ALTER TABLE `ord`
  ADD CONSTRAINT `ord_customer_id_fk` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `ord_sales_rep_id_fk` FOREIGN KEY (`sales_rep_id`) REFERENCES `emp` (`id`);

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`username`) REFERENCES `emp` (`username`);

--
-- Constraints for table `prices`
--
ALTER TABLE `prices`
  ADD CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `pvlink`
--
ALTER TABLE `pvlink`
  ADD CONSTRAINT `pcveh1` FOREIGN KEY (`pcid`) REFERENCES `emp` (`id`),
  ADD CONSTRAINT `pcveh2` FOREIGN KEY (`vin`) REFERENCES `vehicle` (`vin`);

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `fk_roles_title` FOREIGN KEY (`nazwa`) REFERENCES `title` (`name`);

--
-- Constraints for table `warehouse`
--
ALTER TABLE `warehouse`
  ADD CONSTRAINT `warehouse_manager_id_fk` FOREIGN KEY (`manager_id`) REFERENCES `emp` (`id`),
  ADD CONSTRAINT `warehouse_region_id_fk` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
