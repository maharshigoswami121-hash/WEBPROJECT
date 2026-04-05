-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2026 at 05:34 AM
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
-- Database: `webproject_db`
--

-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS webproject_db

  DEFAULT CHARACTER SET utf8mb4

  COLLATE utf8mb4_general_ci;

USE webproject_db;
--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quality` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `payment_method`, `shipping_address`, `notes`, `created_at`, `updated_at`) VALUES
(6, 1, 'ORD-1775271970', 1620.00, 'pending', NULL, '23 holder, brantford, ON n3t0w1', NULL, '2026-04-04 03:06:10', '2026-04-04 03:06:10'),
(7, 1, 'ORD-1775272090', 1620.00, 'pending', NULL, '23 holder drive, brantford, ON n3t0w1', NULL, '2026-04-04 03:08:10', '2026-04-04 03:08:10'),
(8, 3, 'ORD-1775348529', 1649.46, 'processing', NULL, '23 holder, brantford, ON n3t0w1', NULL, '2026-04-05 00:22:09', '2026-04-05 00:22:09'),
(9, 4, 'ORD-1775349999', 1648.73, 'shipped', NULL, '23 HOLDER, BRANTFORD, ON N3T0W1', NULL, '2026-04-05 00:46:39', '2026-04-05 00:46:39');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `product_name` varchar(255) DEFAULT NULL,
  `product_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `total_price`, `product_name`, `product_price`, `subtotal`, `created_at`) VALUES
(1, 6, 1, 1, 1500.00, 0.00, NULL, NULL, NULL, '2026-04-04 03:06:10'),
(2, 7, 1, 1, 1500.00, 0.00, NULL, NULL, NULL, '2026-04-04 03:08:10'),
(3, 8, 23, 1, 27.28, 0.00, NULL, NULL, NULL, '2026-04-05 00:22:09'),
(4, 8, 1, 1, 1500.00, 0.00, NULL, NULL, NULL, '2026-04-05 00:22:09'),
(5, 9, 26, 1, 26.60, 0.00, NULL, NULL, NULL, '2026-04-05 00:46:39'),
(6, 9, 1, 1, 1500.00, 0.00, NULL, NULL, NULL, '2026-04-05 00:46:39');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount` int(11) NOT NULL DEFAULT 0,
  `original_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `description`, `price`, `stock`, `image_url`, `created_at`, `updated_at`, `discount`, `original_price`) VALUES
(1, 'Laptop Windows 11', 'Laptops', 'Compatable', 1500.00, 10, 'https://budgetlaptops.ca/cdn/shop/files/17143992.jpg?v=1707677916', '2026-04-04 00:59:42', '2026-04-04 00:59:42', 0, NULL),
(2, 'HP Elite Desktop Computer, Intel Core i5 3.2 GHz, 8 GB RAM, 500 GB HDD, Keyboard & Mouse, Wi-Fi, 19inch LCD Monitor, DVD-ROM, Windows 10, (Renewed)', 'Desktops', 'This product has been professionally inspected and tested to work and look like new.', 319.00, 5, 'https://m.media-amazon.com/images/I/61vlIlUbiaL._AC_SY300_SX300_QL70_ML2_.jpg', '2026-04-04 04:33:29', '2026-04-04 04:33:29', 10, NULL),
(3, 'HP Desktop SFF Computer PC, Intel i5 3.20 GHz, 16GB RAM, 256GB SSD, 24 Inch Monitor, Windows 11 Pro', 'Desktops', 'HP Compaq 8300 SFF Computer PC, 3.20 GHz Intel i5 Quad Core, 16GB DDR3 RAM, 256 GB SSD, HDMI, 24 inch Monitor, Windows 10 Professional 64 bit .16 GB DDR RAM |256 GB SSD | Win 10 Pro , USB WiFi Adapter | Free - Keyboard & Mouse .', 379.99, 4, 'https://tse4.mm.bing.net/th/id/OIP.dzvua_lz_idjhxo0H1CvRwHaHa?pid=Api&P=0&h=180', '2026-04-04 18:33:16', '2026-04-04 18:33:16', 0, NULL),
(4, 'Lenovo Thinkcentre SFF Desktop PC Computer Inte Core i5 16GB RAM 256GB SSD 1TB HDD Windows 11 Pro WiFi Bluetooth', 'Desktops', 'Lenovo Thinkcentre SFF Desktop PC Computer Inte Core i5 6400 16GB  256GB SSD 1TB HDD Windows 11 Home NEW Keyboard,mouse,Power cord,USB WiFi . TPM 2.0 is recommended for Windows 11, yet this PC only has TPM 1.2. This PC may not support all security features and newest updates.', 319.99, 20, 'https://tse3.mm.bing.net/th/id/OIP.9JKJFv3ZpO83fYWx5wgODgHaHa?pid=Api&P=0&h=180', '2026-04-04 18:35:20', '2026-04-04 18:35:20', 0, NULL),
(5, 'Acer Aspire C22-1610-UA91 AIO Desktop 21.5\\\" Intel Core i3-N305 Intel UHD Graphics 8GB RAM 512GB SSD Windows11 Home - with USB Keyboard and Mouse', 'Desktops', 'One slice of metal to do it all. Finished in black and gold, the 7.5 mm, ultra-slim all-in-one promises space-saving features and cable-tidy management, keeping the family home clutter-free and looking sharp.\\r\\nEnjoy the crisp colors that this cool, 21.5\\\" Full HD PC delivers. It features an Intel Core i3 processor, Intel UHD Graphics and fast Intel Wireless Wi-Fi 6 AX201 connectivity.\\r\\nThe 21.5\\\" FHD IPS display is framed between a narrow bezel delivering a screen-to-body ratio of an incredible 90.71%.\\r\\n21.5\\\" Non-Touch, 1 year warranty', 649.99, 40, 'https://i5.walmartimages.com/asr/4d02064c-aa4a-419b-a8d7-90d401967cba.e8cbb9608b3f39107d4dc8048ad7467f.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 18:50:49', '2026-04-04 18:50:49', 0, NULL),
(6, 'Gaming Desktop SFF PC - HP ProDesk Computer | Core i5 Up to 3.6GHz | 22\\\" Inch FHD Monitor | 32GB RAM 1TB SSD | AMD RX 550 4G GDDR5 (HDMI) | Gaming Keyboard and Mouse| Win 10 Pro 64bit', 'Desktops', 'MODEL: HP 600 G3 Small Form Factor,SFF (Excellent Refurbished)\\r\\nProcessor : Intel Core i5-(6500)32GB DDR4 RAM 1TB SSD.(Windows 10 Pro)\\r\\nGraphics : AMD Radeon RX 550 4GB GDDR5.', 799.99, 15, 'https://i5.walmartimages.com/asr/e1431a62-2928-417c-a86b-31f1991fa252.81da107b72775d9b1219cc316cca16ae.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 18:52:12', '2026-04-04 18:52:12', 0, NULL),
(7, 'Acer Aspire Lite 15.6\\\" FHD IPS Laptop Intel Celeron N4500 4 GB RAM 128 GB eMMC Windows 11S', 'Laptops', 'Webcam\\r\\nIntel® Celeron® dual-core processor N4500 15.6\\\"\\\" display with IPS (In-Plane Switching) technology, Full HD 1920 x 1080, Acer ComfyView™ LED-backlit TFT LCD                      \\r\\nIntel® UHD Graphics                                                            \\r\\n4GB LPDDR4X Memory and 128GB eMMC   \\r\\n802.11ac WiFi 5 wireless LAN Dual Band (2.4 GHz and 5 GHz)                                                              \\r\\nSupports Bluetooth® 5.0 or above                                                                                       \\r\\n1 - USB Type-C™ Port\\r\\n3 - USB 3.2 Gen 1 Ports and 1 - Ethernet (RJ-45) Port\\r\\n1 - HDMI™ 1.4 Port                                                                             \\r\\nHD camera                                                                   \\r\\nTwo Built-in Stereo Speakers and Dual Built-in Microphones                                                                  \\r\\nBattery life: up to 8 hours', 299.98, 10, 'https://i5.walmartimages.com/asr/bff755a3-06da-4215-88d4-6ca6729c6474.2fadcf4d1d3b65a4171dd49947e58f45.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 18:53:55', '2026-04-04 18:53:55', 0, NULL),
(8, 'Acer Aspire 3 A315-24P-R8ZP 15.6\\\" FHD Laptop AMD Ryzen 5 7520u 8GB RAM 512GB SSD', 'Laptops', 'Numeric Keypad\\r\\nAMD Ryzen 5 7520u Processor\\r\\n802.11ac Wi-Fi 6 (Dual-Band 2.4GHz and 5GHz)\\r\\nAMD Radeon™ Graphics\\r\\n15.6\\\" Full HD Acer Comfy View™ Widescreen LED-backlit Display\\r\\nTechnology with AI Noise Reduction\\r\\n8GB LPDDR5 Memory and 512GB NVMe SSD, HD Webcam (1280 x 720) with Blue Glass Lens supporting 720 H, Two Built-in Stereo Speakers and Built-in Digital Microphone', 649.98, 40, 'https://i5.walmartimages.com/asr/fc6f5a32-6976-4a8f-b12c-e79c5aecbd11.9e02ad70ac207b40d6b262175f01ac4e.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 18:55:28', '2026-04-04 18:55:28', 20, NULL),
(9, 'Auusda 15.6\\\" Laptop Computer, 32GB RAM 1TB SSD, Windows 11 Pro Computers, Intel Alder CPU, Pink', 'Laptops', 'UP TO 3.4 GHz CPU: Fast and smooth performance for work and play, with Intel Alder Lake-N processor with cooling fan, 4 cores, 4 threads, 6MB Cache, and Intel UHD Graphics\\r\\n32GB RAM 1TB SSD: Enjoy laptop speed and performance, store files and media easily, and upgrade storage up to 1TB microSD\\r\\n15.6\\\" 1920x1080 IPS LCD screen: Very suitable for video editing and entertainment viewing\\r\\nMultiple interfaces (2*USB 3.0, Audio 3.5mm jack port, Mini HDMI interface, Micro SD card interface and DC charging port) for connectivity', 727.00, 2, 'https://i5.walmartimages.com/asr/8916b2a3-c271-45b4-9580-2178dbb56133.af6e6fe4c17fa0401040d043f14c76fc.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:01:17', '2026-04-04 19:01:17', 0, NULL),
(11, 'Dell Latitude 5420 14\\\" FHD Business Laptop Computer, Intel Quad-Core i5-1135G7, 16GB DDR4 RAM, 512GB SSD, Backlit Keyboard, HDMI, Windows 11 Pro', 'Laptops', 'Dell Latitude 5420 14\\\" FHD Business Laptop Computer, Intel Quad-Core i5-1135G7, 16GB DDR4 RAM, 512GB SSD, Backlit Keyboard, HDMI, Windows 11 Pro', 420.00, 5, 'https://i5.walmartimages.com/asr/e6ff1d89-c116-47c4-9f4b-f444dd9c1347.dccd94532e4ebbfe6f2b64c953a3d131.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:02:42', '2026-04-04 19:02:42', 0, NULL),
(12, 'PlayStation Portal™ Remote Player', 'Gaming', 'PlayStation Portal™ Remote Player Play your PS5® console over Wi-Fi with console quality controls using PlayStation Portal™ Remote Player. Put Your PS5 in the Palm of Your Hand PlayStation Portal™ Remote Player gives you access to the games on your PS5® console over a Wi-Fi network,1 letting you jump into gaming on a gorgeous 8” LCD screen capable of 1080p resolution play at 60fps, all without needing to play on a TV. Play Your Game Collection with Remote Play PlayStation Portal™ Remote Player can play compatible games you have installed on your PS5® console, including your favorite games for PS5 and PS4. Enjoy Cloud Streaming for PS5® Games with PlayStation Plus®Premium Stream select titles from the Game Catalog, Classics Catalog, and even select digital PS5® games in Your Library from PlayStation™Store, without needing to wait for downloads. Experience Breathtaking Immersion with DualSense® Wireless Controller Features and 3D Audio Feel the incredible immersion of haptic feedback and adaptive triggers in supported games, and surround yourself with sound via Tempest 3D AudioTech in supported games when a compatible audio device is connected.', 269.96, 100, 'https://i5.walmartimages.com/asr/32b9b4c2-3f4e-4ec0-b25c-af44cbba3ca7.3bd8ba539c9376f2d387fae6bd61e1c7.png?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:04:42', '2026-04-04 19:04:42', 0, NULL),
(13, 'WINGOMART X3 Gaming Headset Over-ear 3.5mm LED Light Stereo Bass Game Headphones with Mic for PC Laptop Gamer Xbox One, PS5, PS4, PC, Nintendo Switch playstation 5 Gaming headset - Black', 'Gaming', 'WINGOMART X3 Gaming Headset Over-ear 3.5mm LED Light Stereo Bass Game Headphones with Mic for PC Laptop Gamer Xbox One, PS5, PS4, PC, Nintendo Switch PlayStation 5 Gaming Headset - Black\\r\\n\\r\\nLightweight design for maximum comfort.\\r\\nAllows you to focus on playing games without being disturbed.\\r\\nHigh sensitivity microphone, very convenient to adjust the angle of the microphone.\\r\\nErgonomic wearing design, let you experience the ultimate comfortable feeling.\\r\\nGreat for office, classroom, or home use, gaming, business, entertainment, and more!', 109.99, 80, 'https://i5.walmartimages.com/asr/34666d3e-df8d-49c7-8775-855cd879a960.b6d6b4d593d5c5887cd700c99f4e1946.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:06:23', '2026-04-04 19:06:23', 70, NULL),
(14, 'Nintendo Switch™ 2 Pro Controller', 'Gaming', 'Take your gaming to the next level with the Nintendo Switch™ 2 Pro Controller.\\r\\n\\r\\nFeatures include:\\r\\n· HD Rumble 2\\r\\n· Motion controls\\r\\n· Built-in amiibo™ functionality*\\r\\n· Capture Button\\r\\n· C Button for GameChat**\\r\\n· Programmable GL/GR buttons\\r\\n· Audio jack', 109.96, 20, 'https://i5.walmartimages.com/asr/6e44a9b3-f8fc-4d7e-a7fb-b9bde47164f2.c34e11d4c0d578f2509e37cfd771427c.png?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:07:22', '2026-04-04 19:07:22', 0, NULL),
(15, 'Xbox Series X - 1TB SSD - Carbon Black (with Disk)', 'Gaming', 'Introducing Xbox Series X, the fastest, most powerful Xbox ever. Play thousands of titles from four generations of consoles—all games look and play best on Xbox Series X\\r\\nExperience next-gen speed and performance with the Xbox Velocity Architecture, powered by a custom SSD and integrated software\\r\\nPlay thousands of games from four generations of Xbox with Backward Compatibility, including optimized titles at launch\\r\\nDownload and play over 100 high-quality games, including all new Xbox Game Studios titles like Halo Infinite the day they release, with Xbox Game Pass Ultimate (membership sold separately)\\r\\nXbox Smart Delivery ensures you play the best available version of your game no matter which console you\\\'re playing on', 825.00, 20, 'https://i5.walmartimages.com/asr/09315781-8fc2-4549-b288-12532761100d.7a1914c8fe5219d84ef2bd91f3c61594.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:08:47', '2026-04-04 19:08:47', 0, NULL),
(16, 'Xbox Series X – Diablo IV Bundle', 'Gaming', 'Xbox Series X console\\r\\nDiablo® IV\\r\\nBonus in-game items\\r\\nXbox Wireless Controller\\r\\nUltra high-speed HDMI cable\\r\\nPower cable', 929.99, 10, 'https://i5.walmartimages.com/asr/1bed057d-6efd-4690-8e99-cfa4a926d9ac.1999de1da0ee2c4cffbb62a80cd84ceb.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:09:56', '2026-04-04 19:09:56', 0, NULL),
(17, 'Nintendo Switch AC Adapter', 'Accessories', 'The AC adapter also allows you to recharge the battery, even while you play\\r\\nNintendo Switch AC Adapter\\r\\nPlug in the AC adapter and power your Nintendo Switch system from any 120-volt outlet', 29.99, 200, 'https://i5.walmartimages.com/asr/7a460f30-8c63-424a-a653-f19181ff93cc.07021e53df183b88a5269cd16aa73c99.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:11:44', '2026-04-04 19:11:44', 0, NULL),
(18, 'USB c HUB, BENFEI USB Type-c to HDMI VgA Adapter, USB c to USB Adapter, compatible for MacBook Pro 201920182017,Surface Book 2, Dell XPS 1315, Pixelbo', 'Accessories', 'USB c HUB, BENFEI USB Type-c to HDMI VgA Adapter, USB c to USB Adapter, compatible for MacBook Pro 201920182017,Surface Book 2, Dell XPS 1315, Pixelbook and More\\r\\nUSB c HUB, BENFEI USB Type-c to HDMI VgA Adapter, USB c to USB Adapter, compatible for MacBook Pro 201920182017,Surface Book 2, Dell XPS 1315, Pixelbo', 16.14, 300, 'https://i5.walmartimages.com/asr/f6028ec3-49b4-4c07-a4f8-395b99dc6987.3880afc3f03870426d02cfabd8eb4870.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:12:58', '2026-04-04 19:12:58', 0, NULL),
(19, 'Apple 20W USB-C Power Adapter - iPhone Charger with Fast Charging Capability, Type C Wall Charger', 'Accessories', 'The Apple 20W USB‑C Power Adapter offers fast, efficient charging at home, in the office, or on the go.||Pair it with iPhone 8 or later for fast charging - 50 percent battery in around 30 minutes.||Or pair it with the iPad Pro and iPad Air for optimal charging performance.||Works with all iPhone, AirPods, iPad and Apple Watch models||USB-C wall charger only, charging cable sold separately.\\r\\nCable is Required & Not Included\\r\\n1 x USB Type-C Charging Port\\r\\nWhat’s in the Box Apple 20W USB-C Power Adapter\\r\\nPlugs Into an Available Wall Socket\\r\\nProvides Up to 20W of Power\\r\\nCompatibility: iPhone Models (iPhone 12 Pro, iPhone 12 Pro Max, iPhone 12 mini, iPhone 12, iPhone 11 Pro. iPhone 11 Pro Max, iPhone 11, iPhone SE (2nd generation), iPhone XS, iPhone XS Max, iPhone XR, iPhone X, iPhone 8, iPhone 8 Plus), iPad Models(iPad Pro 12.9-inch (4th generation), iPad Pro 12.9-inch (3rd generation), iPad Pro 12.9-inch (2nd generation), iPad Pro 12.9-inch (1st generation), iPad Pro 11-inch (2nd generation), iPad Pro 11-inch (1st generation), iPad Pro 10.5-inch, iPad Air (4th generation), iPad Air (3rd generation), iPad (8th generation), iPad (7th generation), iPad mini (5th generation), AirPods Models (AirPods Max, AirPods Pro, AirPods with Wireless Charging Case (2nd generation), AirPods with Charging Case (2nd generation), AirPods (1st generation), Wireless Charging Case for AirPods', 25.00, 400, 'https://i5.walmartimages.com/asr/aa298aab-6fff-4cc1-a6a8-5b04e24542c4.fa6d0d5566332c023e352051a1b5025e.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:13:55', '2026-04-04 19:13:55', 0, NULL),
(20, 'HFLRZZ 45W Samsung Super Fast Charger Type C, USB C Charger with 6.6FT Cable for Samsung Galaxy S23 Ultra/S23/S23+/S22 Ultra/S22+/S22, Galaxy Tab S7/S8, Black', 'Accessories', 'High Speed Charging: Samsung super fast charger with maximum power of 45 Watt Super Fast Charge capability uses Power Delivery technology to provide the most efficient charge possible. Super fast charge for Samsung Galaxy S22/S23 Ultra from 0% to 50% in just 20 min, more higher than 15W or 25W charger.\\r\\nUSB C to USB C Cable: The 45 watt usb c charger block kit come with a detachable USB-C to USB-C cable, so you can charge your phone from any other USB-C power source such as your phones, tablets, and computers. Use the cable to sync and transfer files at blazing speeds with your Galaxy or any other compatible smartphone or laptop.\\r\\nCompatible Models: Super Fast Charging 2.0 maximum 45W compatible with Samsung Galaxy S23, S23 Ultra. S23 plus, S22, S22+, S22 Ultra, S20 Ultra, Note 10+, Note 10+ 5G, A91 and future compatible devices. Super Fast Charging max. Super Fast Charging maximum 25W compatible with Samsung Galaxy S21/S21 Ultra/ S21+ 5G, S20/ S20+/ S20 FE, Note 10/ Note 10 5G, Note 20/ 20 Ultra 5G/ S10 5G, Galaxy Z Fold 3 5g, A90 5G, A80, A70, Book S, Tab S7/S8 (Ultra).\\r\\nPortable Wall Charger: The Samsung Galaxy charger itself is compact which makes it highly portable. Ideal for taking with you on holidays or short trips. Easy to store and carry around with you for quick and convenient charging.\\r\\nWhat You Get: 45W USB C Wall Charger Block, 6.6 foot type c to type c fast charging cable. Our worry-free 12-month warranty, and friendly customer service.', 13.20, 100, 'https://i5.walmartimages.com/asr/35f3ac6f-1ba4-41af-819d-3fa8cc502092.554431381cd0088df0cba139ec0b7b49.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:14:56', '2026-04-04 19:14:56', 10, NULL),
(21, 'Oefntac Data Cable 240W Data Charging Cable,Type C Fast Charge Port,Integrated Stand,Universal Device,Durable Tough,Portable Phone Accessory', 'Accessories', 'Oefntac Data Cable 240W Data Charging Cable,Type C Fast Charge Port,Integrated Stand,Universal Device,Durable Tough,Portable Phone Accessory\\r\\nOur products are made from high-grade materials and undergo strict quality checks to ensure they withstand daily use over time. You can count on their reliability and lasting performance\\r\\nWe’re here for you whenever you need help! Our customer service is Fast to respond and dedicated to solving any issues you have—before, during, or after your purchase. Your satisfaction is our priority.\\r\\nWe offer competitive prices without cutting corners on quality. Each product is crafted to deliver great performance and functionality\\r\\nWish you a wonderful shopping experience.', 26.58, 20, 'https://i5.walmartimages.com/asr/33614de0-cd8f-47f8-bf24-5611f657d47f.0c081587d262bf34ebdb7e9775c11d09.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:16:31', '2026-04-04 19:16:31', 30, NULL),
(22, 'Ccdes USB2.0 with MIC 16MP HD Webcam Web Camera Cam 360° for Computer PC Laptop for Skype / MSN, Web Camera,USB Camera', 'Webcams', 'USB2.0 Web Camera - Specially designed for both laptop and desktop.\\r\\nRotation of horizontal 360 degree and vertical 60° allows you to adjust the angle freely.\\r\\nSupports all kinds of video conferencing softwares, namely Netmeeting, for MSN / Yahoo / Skype, etc.\\r\\nAuto White Balance & Auto Color Correction & Manual Focus, no need to adjust the lens to get clear pictures or videos.\\r\\nHigh Image Transmission & High Definition & High Resolution - Can catch the pictures and videos without any blurs or distortion.\\r\\nImported high-quality optical lens with high accuracy, therefore no distortion pictures.\\r\\nBuilt-in Microphone - Sounds within 10 meters can be heard.\\r\\nUSB 2.0 Interface - Plug & Play.', 37.96, 40, 'https://i5.walmartimages.com/asr/4763af40-e6d9-414c-be53-e3cf38df6011.82cafe89dc830fbee7703d97c2a830a5.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:17:37', '2026-04-04 19:17:37', 40, NULL),
(23, '1080p Desktop Webcam with Microphone, Webcam, Computer Camera, USB Streaming Webcam for PC, Laptop, Desktop-', 'Webcams', '1.Natural imaging: In order to achieve the best picture effect, this USB camera has a wide viewing angle to avoid fish-eye effect. Auto white balance can make the image closer to natural imaging, avoiding serious color distortion and overexposure.\\r\\n2.Manual focus is adjustable: After professional testing, determine the most suitable focal length for high-definition cameras. You can manually focus on your different needs to obtain the clearest and most ideal recording conditions.\\r\\n3.1080p computer camera: This game camera has 2 million pixels, 1920*1080P resolution. Capture clear pictures at 30 frames per second, and set up smooth videos for chats, meetings, online courses, live broadcasts, etc. $ Built-in microphone, plug and play: PC webcam is equipped with a built-in microphone, allowing you to obtain clear and natural sound quality and reduce noise within 5 meters.\\r\\n4.Achieve clear sound quality and dialogue effects. Simply plug the USB cable into a USB port on your computer, and you can connect without a driver.\\r\\n5.Universal compatibility: The webcam is suitable for different devices. This applies to Windows 7, 8, and 10, Mac OS, Linux, Android, etc. Suitable for Smart TV, Android TV Box, Skype, MSN, FaceTime, Facebook Messenger, Youtube, Yahoo Messenger, etc.', 54.55, 400, 'https://i5.walmartimages.com/asr/d51d8237-5ab3-4109-9b1e-67d8e1b5867f.63438c764fe3c8dc51bc93c7d7c4e8ca.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:18:42', '2026-04-04 19:18:42', 50, NULL),
(24, 'Webcam 1080P Full HD USB Web Camera With Microphone USB And Play Video Call Webcam For Pc Computer Desktop Gamer Streaming ,Easy to Install', 'Webcams', 'Related Products:webcam,webcam for pc,web cam,4k webcam,laptop camera,webcam for streaming,gaming camera,web camera,pc camera,computer camera for desktop,webcam with microphone,computer camera,streaming camera\\r\\n\\r\\n1. Built-in stereo microphone: this video webcam built-in noise reduction microphone, can automatically eliminate background noise and pick up audio sound within 5 meters, and achieve studio-quality sound, you don\\\'t need an external microphone/headset at all,and it is easy to set up without any blur or jerking.\\r\\n2. High definition: As everyone is working from home, this webcam can meet many needs working at home, if you are using it at your desk the picture quality is great. with 30fps recording-streaming media and record vivid,high-resolution video, providing incredible decent picture and mic quality in clear visuals,clear and concise audio.\\r\\n3. Smooth livestream: advanced compression technology enables fast uploads and streaming on social media and gaming. for streaming,video conferencing,video chatting,gaming,online classes and more.\\r\\n4. Rotatable: 360 degrees rotatable, you can adjust the angle as you like.', 58.38, 40, 'https://i5.walmartimages.com/asr/0bd44013-2de8-42fc-9515-2eb2cc169247.60f2887f7693950acde37edaf19d4c77.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:19:34', '2026-04-04 19:19:34', 0, NULL),
(25, 'Webcam with Microphone, Webcam 1080P Full HD, USB Webcam with Fixed Focus, Computer, PC, Desktop, for Live Streaming, Video Call, Conference, Online Teaching, Game', 'Webcams', 'Colour:black\\r\\nSensor: 4 million high-resolution image sensor\\r\\nFocus distance: 0.3 to 5 mm (fixed focus)\\r\\nSystem requirements: Windows XP / Mac OS 10.6 / Android 5.0 / Linux or higher\\r\\nProtocol: standard UVC / UVA, driver-free, plug and play.', 27.24, 80, 'https://i5.walmartimages.com/asr/fd81b7ac-aa65-4f2b-8b2c-a5201bcd64d9.a7efa717a0b6a35fd76f8b657b5f0a9e.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:20:33', '2026-04-04 19:20:33', 0, NULL),
(26, 'EGNMCR Webcam Webcam With LED Light 1080P HD Web Camera Auto Focus 4MP Microphone LED Webcam', 'Webcams', 'Perfect Gifts: It is a great gift for friends.It can be given as a daily gift among friends.\\\\r\\\\nOccasion: Ideal for the dorm, RV and home office.\\\\r\\\\nAfter Sales Service: If you have any dissatisfaction after purchasing our products, you can contact us at any time.We have professional customer service, which will serve you until you are satisfied.\\\\r\\\\nHigh quality products that are durable, this is the best choice to save you money.\\\\r\\\\nEnvironmental protection and health, they are made of environmentally friendly materials to reduce environmental pollution.', 26.60, 10, 'https://i5.walmartimages.com/asr/bd5e457a-bf13-4cb1-a16e-68bd9b5e408f.a026b057570f090a91ef8220b5ac8e5c.jpeg?odnHeight=640&odnWidth=640&odnBg=FFFFFF', '2026-04-04 19:21:37', '2026-04-04 19:21:37', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `user_name`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 'Maharshi Goswami', 5, 'best device .', '2026-04-04 01:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `role`, `password`, `phone`, `address`, `city`, `state`, `postal_code`, `country`, `created_at`, `updated_at`) VALUES
(1, 'Maharshi', 'Goswami', 'maharshigoswami121@gmail.com', 'user', '$2y$10$gBykAaRYi1CrppBAfq4xX.ZgSljtFonlMPonpv.0XlUX5u3OHq4EK', NULL, '23 Holder Drive', 'Brantford', 'ON', 'N3T 0W1', NULL, '2026-04-04 00:22:23', '2026-04-04 00:22:23'),
(2, 'Admin', 'User', 'admin@ChannelMerchant.com', 'admin', 'admin@123', NULL, '123 Admin St', 'Toronto', 'ON', 'M5H 2N2', NULL, '2026-04-04 00:28:17', '2026-04-04 00:28:17'),
(3, 'maharshi', 'goswami', 'maharshigoswami.svmr@gmail.com', 'user', '$2y$10$zIb2tnVLz1mNUjqAoePSqeX.GBF7zP3YzsrirEXFwFnIfGeekBabi', '', '23 holder', 'brantford', 'ON', 'n3t0w1', '', '2026-04-05 00:15:50', '2026-04-05 00:15:50'),
(4, 'maharshi', 'goswami', 'maharshigoswami2024@gmail.com', 'user', '$2y$10$hXHIn.p4K/5yx8b9VjMOV.NowKMF2208qeQlHYiRCvUIj0az89F2.', NULL, '23 HOLDER', 'BRANTFORD', 'ON', 'N3T0W1', NULL, '2026-04-05 00:39:57', '2026-04-05 00:39:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
