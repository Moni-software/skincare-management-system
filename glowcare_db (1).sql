-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2026 at 02:30 PM
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
-- Database: `glowcare_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `full_name`, `username`, `password`, `email`, `contact_no`, `created_at`) VALUES
(1, 'GlowCare IT Admin', 'admin', 'admin123', 'support@glowcare.com', '+94 71 234 5678', '2026-08-06 10:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `notification_id` int(11) NOT NULL,
  `channel` enum('Email','SMS','App Push') NOT NULL,
  `recipient` varchar(190) NOT NULL,
  `subject` varchar(190) DEFAULT NULL,
  `message` text NOT NULL,
  `schedule_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'Queued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `product_image` varchar(255) NOT NULL,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `customer_name_text` varchar(150) DEFAULT NULL,
  `customer_email` varchar(190) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `customer_id`, `order_id`, `message`, `admin_reply`, `status`, `created_at`, `customer_name_text`, `customer_email`, `customer_phone`, `subject`) VALUES
(1, 1, 2, 'why it is late', 'ok', 'In Progress', '2026-08-17 18:04:19', NULL, NULL, NULL, NULL),
(2, NULL, NULL, 'case 2 issue 2', NULL, 'Pending', '2026-08-19 10:58:41', 'chathumini liyanage', 'chathuminiliyanage@icloud.com', '0716667878', 'case2'),
(3, NULL, NULL, 'case 6', NULL, 'Pending', '2026-08-19 11:29:16', 'risadi vonara', 'risadidasilva623@gmail.com', '0766042964', 'issue 6');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `password`, `address`, `phone`, `created_at`) VALUES
(1, 'Vindya perera', 'vindya@gmail.com', '$2y$10$/AyrOklt3.G/OTdbiKjJduzMuHx.Jgkd6aqrALXC8GQzn90bvHC1C', 'No 45, Galle Road, Colombo', '0771234567', '2026-08-17 00:01:49'),
(2, 'chathu liyanage', 'chathuminiliyanage@icloud.com', '$2y$10$YHdTYze3rk9Y3ZNQpiDUAulU0ZiFZYsDuhccsl7gkWGYPc.Uk4guS', 'chathuminiliyanage@icloud.com', '0718880237', '2026-08-19 09:07:10');

-- --------------------------------------------------------

--
-- Table structure for table `customer_skin_history`
--

CREATE TABLE `customer_skin_history` (
  `history_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `skin_issue` varchar(120) NOT NULL,
  `notes` text DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_skin_photos`
--

CREATE TABLE `customer_skin_photos` (
  `photo_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `photo_type` enum('Before','After','Other') DEFAULT 'Other',
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `old_price` int(11) DEFAULT NULL,
  `size` varchar(100) NOT NULL,
  `image_url` text NOT NULL,
  `description` text DEFAULT NULL,
  `max_qty` int(11) DEFAULT NULL,
  `section_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deals`
--

INSERT INTO `deals` (`id`, `name`, `price`, `old_price`, `size`, `image_url`, `description`, `max_qty`, `section_type`) VALUES
(101, 'Pro-Growth Hair Shampoo', 4900, NULL, '1000ml', 'large-shampoo.jpg', NULL, 2, 'large_volume'),
(102, 'Herbal Refreshing Body Wash', 4200, NULL, '900ml', 'large-body-wash.jpg', NULL, 2, 'large_volume'),
(103, 'Deep Repair Hair Conditioner', 4600, NULL, '850ml', 'large-hair-conditioner.jpg', NULL, 2, 'large_volume'),
(201, 'Luxury Herbal Body Lotion', 3800, 4500, '600g', 'large-body-lotion.jpg', NULL, NULL, 'heavy_weight'),
(202, 'Smoothing Face & Body Scrub', 3100, 3900, '550g', 'large-face-scrub.jpg', NULL, NULL, 'heavy_weight'),
(203, 'Keratin Hair Mask Tub', 4100, 5200, '750g', 'laege-haier-mask.jpg', NULL, NULL, 'heavy_weight'),
(301, 'Complete Face Care Kit', 6500, NULL, '3 Items Box', 'bundle-face-1.jpg', 'Includes: Face Wash, Exfoliating Scrub, & Hydrating Toner', NULL, 'bundle'),
(302, 'Glow Radiance Face Set', 7400, NULL, '3 Items Set', 'bundle-face-2.jpg', 'Includes: Vitamin C Serum, Brightening Cream, & Night Gel', NULL, 'bundle'),
(303, 'Acne Control & Clear Pack', 5900, NULL, '3 Items Pack', 'bundle-face-3.jpg', 'Includes: Neem Face Wash, Spot Gel, & Clay Mask', NULL, 'bundle'),
(304, 'Ultimate Makeup Pack', 8900, NULL, '3 Items Set', 'bundle-makeup-2.jpg', 'Includes: Matte Lipstick, Foundation, & Makeup Brushes Set', NULL, 'bundle'),
(305, 'Glam Eye & Lip Collection', 6800, NULL, '3 Items Set', 'makeup-lip-colection.jpg', 'Includes: Eyeshadow Palette, Waterproof Eyeliner, & Liquid Lipstick', NULL, 'bundle'),
(306, 'Flawless Base & Glow Kit', 7900, NULL, '3 Items Kit', 'bundle-makeup-3.jpg', 'Includes: Primer, Liquid Foundation, & Setting Spray', NULL, 'bundle'),
(307, 'Hair Growth Box', 7200, NULL, '3 Items Box', 'bundle-hair-1.jpg', 'Includes: Herbal Shampoo, Nourishing Conditioner, & Hair Oil', NULL, 'bundle'),
(308, 'Silk & Shine Repair Routine', 8100, NULL, '3 Items Routine', 'bundle-hair-2.jpg', 'Includes: Keratin Shampoo, Hair Mask, & Anti-Frizz Serum', NULL, 'bundle'),
(309, 'Scalp Health & Volume Pack', 7600, NULL, '3 Items Pack', 'bundle-hair-4.jpg', 'Includes: Scalp Scrub, Volume Shampoo, & Strengthening Tonic', NULL, 'bundle');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `products` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending Delivery',
  `payment_status` varchar(50) DEFAULT 'Paid',
  `cancel_reason` varchar(255) DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `products`, `total_amount`, `status`, `payment_status`, `cancel_reason`, `order_date`) VALUES
(1, 1, '✨ GlowCare Luxury Items Package', 9400.00, 'Delivered', 'Paid', NULL, '2026-08-16 02:53:29'),
(2, 1, 'Matte Body Hydro Gel x1, Pro-Growth Hair Shampoo x1', 9203.00, 'Shipped', 'Pending', NULL, '2026-08-17 18:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `P_id` int(10) NOT NULL,
  `P_name` varchar(35) NOT NULL,
  `image` varchar(115) NOT NULL,
  `category` varchar(15) NOT NULL,
  `sub_category` varchar(25) NOT NULL,
  `Skin/Hair_type` varchar(20) NOT NULL,
  `P_price` decimal(10,2) DEFAULT NULL,
  `P_quantity` varchar(15) NOT NULL,
  `In_stock` varchar(15) NOT NULL,
  `guide` varchar(200) NOT NULL,
  `benifits` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`P_id`, `P_name`, `image`, `category`, `sub_category`, `Skin/Hair_type`, `P_price`, `P_quantity`, `In_stock`, `guide`, `benifits`) VALUES
(163, 'Salicylic Foam Cleanser', 'image/foam cleanser.jpeg', 'Facecare', 'Facewash', 'Oily Skin', 2850.00, '150ml', 'Yes', 'Massage gently onto wet face for 60 seconds. Rinse thoroughly with water.', 'Removes excess oil and unclogs pores. Controls shine all day.'),
(164, 'Hydrating Cream Cleanser', 'image/cream cleanser.jpeg', 'Facecare', 'Facewash', 'Dry Skin', 3200.00, '200ml', 'Yes', 'Apply to damp skin using gentle upward strokes. Rinse with water.', 'Cleanses gently without stripping moisture. Leaves skin soft.'),
(165, 'Balancing Gel Cleanser', 'image/gel cleanser.jpeg', 'Facecare', 'Facewash', 'Combination Skin', 2450.00, '150ml', 'Yes', 'Apply to wet face. Work into soft foam and rinse thoroughly.', 'Cleanses T-zone oil without drying cheeks. Maintains balance.'),
(166, 'Oil-Free Matte Water Gel', 'image/wate gel.jpg', 'Facecare', 'Moisturizer', 'Oily Skin', 4890.00, '50ml', 'Yes', 'Smooth a nickel-sized amount over clean face morning and night.', 'Provides lightweight hydration. Controls shine for 12 hours.'),
(167, 'Ceramide Repair Cream', 'image/repair cream.jpeg', 'Facecare', 'Moisturizer', 'Dry Skin', 4562.00, '50ml', 'Yes', 'Warm a small amount between fingers. Press gently onto face.', 'Locks in moisture for 24 hours. Repairs damaged skin barrier.'),
(168, 'Daily Balancing Lotion', 'image/balancing lotion.jpg', 'Facecare', 'Moisturizer', 'Combination Skin', 4167.00, '50ml', 'Yes', 'Apply 1-2 pumps across face and neck twice daily.', 'Hydrates dry areas while mattifying greasy T-zone.'),
(169, 'BHA Pore Clarifying Toner', 'image/BHA.jpg', 'Facecare', 'Toner', 'Oily Skin', 2351.00, '150ml', 'Yes', 'Saturate a cotton pad and sweep across clean face.', 'Exfoliates dead skin inside pores. Keeps skin oil-free.'),
(170, 'Milky Hydrating Toner', 'image/milky.jpeg', 'Facecare', 'Toner', 'Dry Skin', 2256.00, '150ml', 'Yes', 'Pour into palms and press directly into face gently.', 'Provides instant hydration boost. Prepares skin for serum.'),
(171, 'Green Tea Balance Toner', 'image/teabalance.jpeg', 'Facecare', 'Toner', 'Combination Skin', 4625.00, '150ml', 'Yes', 'Apply with cotton pad from center of face outward.', 'Normalizes oil and moisture ratio. Calms irritated zones.'),
(172, 'Niacinamide 10% Serum', 'image/niacinamide.jpeg', 'Facecare', 'Serum', 'Oily Skin', 3758.00, '30ml', 'Yes', 'Apply 2-3 drops to clean face before heavy creams.', 'Regulates sebum production. Visibly tightens pores.'),
(173, 'Hyaluronic Acid Pure Serum', 'image/hyaluronic.jpeg', 'Facecare', 'Serum', 'Dry Skin', 2716.00, '30ml', 'Yes', 'Apply a few drops to damp face. Lock with face cream.', 'Attracts water deep into skin. Fills out fine dry lines.'),
(174, 'Vitamin C Glow Serum', 'image/vitaminC.jpeg', 'Facecare', 'Serum', 'Combination Skin', 2707.00, '30ml', 'Yes', 'Smooth over clean face before moisturizing step.', 'Evens out dull skin patches. Brightens overall complexion.'),
(175, 'Matte Finish SPF Lip Gel', 'image/lip gel.jpeg', 'Facecare', 'Lip balm', 'Oily Skin', 3188.00, '15g', 'Yes', 'Squeeze small amount and apply directly to lips.', 'Non-shiny matte protection. Shields lips from UV damage.'),
(176, 'Shea Butter Lip Balm', 'image/white sheabutter lip.jpeg', 'Facecare', 'Lip balm', 'Dry Skin', 3018.00, '15g', 'Yes', 'Apply generously to dry lips whenever needed.', 'Instantly heals cracked lips. Provides long lasting barrier.'),
(177, 'Berry Hydrating Lip Tint', 'image/berrylip.jpeg', 'Facecare', 'Lip balm', 'Combination Skin', 3328.00, '15g', 'Yes', 'Glide over lips for light color and moisture.', 'Adds subtle natural tint. Keeps lips hydrated all day.'),
(178, 'Tea Tree Exfoliating Wash', 'image/teatreeexfoliatingfacewash.jpeg', 'Bodycare', 'Bodywash', 'Oily Skin', 2787.00, '250ml', 'Yes', 'Apply to damp loofah. Massage gently across body.', 'Clears back and body acne. Controls excess body oil.'),
(179, 'Oatmeal Cream Body Wash', 'image/oatmealcreambodywash.jpg', 'Bodycare', 'Bodywash', 'Dry Skin', 4350.00, '300ml', 'Yes', 'Smooth over damp body. Rinse with lukewarm water.', 'Relieves skin tightness. Restores dry moisture balance.'),
(180, 'Aloe Vera Gel Body Wash', 'image/aloeveraGelbodywash.jpg', 'Bodycare', 'Bodywash', 'Combination Skin', 3391.00, '250ml', 'Yes', 'Apply to wet skin daily. Work into soft lather.', 'Balances oil and hydration. Soothes irritated zones.'),
(181, 'Matte Body Hydro Gel', 'image/mattebodyhydrogel.jpeg', 'Bodycare', 'Body Lotion', 'Oily Skin', 4303.00, '200ml', 'Yes', 'Apply to clean skin after shower. Smooth gently.', 'Provides non-greasy moisture. Controls surface grease.'),
(182, 'Intensive Ceramide Cream', 'image/ceramide cream.jpeg', 'Bodycare', 'Body Lotion', 'Dry Skin', 3945.00, '200ml', 'Yes', 'Apply generously to dry skin areas daily.', 'Repairs dry skin barrier. Locks in 24h moisture.'),
(183, 'Daily Nourishing Lotion', 'image/dailynurishing.jpeg', 'Bodycare', 'Body Lotion', 'Combination Skin', 4615.00, '200ml', 'Yes', 'Apply evenly across body post-shower.', 'Hydrates dry skin areas while keeping oily zones balanced.'),
(184, 'Charcoal Clarifying Bar', 'image/charcolclarifyingbar1.jpeg', 'Bodycare', 'Soap Bar', 'Oily Skin', 3842.00, '100g', 'Yes', 'Rub between wet hands. Massage foam onto body.', 'Purifies clogged pores. Absorbs excess surface oil.'),
(185, 'Goat Milk Hydrating Bar', 'image/goatmilkhydratingbar.jpeg', 'Bodycare', 'Soap Bar', 'Dry Skin', 3166.00, '100g', 'Yes', 'Lather gently with hands. Wash body and rinse.', 'Protects skin moisture. Prevents dry itching feel.'),
(186, 'Lavender Calming Soap Bar', 'image/lavendercalmingsoapbar.jpeg', 'Bodycare', 'Soap Bar', 'Combination Skin', 4702.00, '100g', 'Yes', 'Create lather with water. Wash gently and rinse.', 'Calms skin irritation. Balances skin moisture levels.'),
(187, 'Non-Greasy Hand Gel', 'image/nongreasyhandgel.jpeg', 'Bodycare', 'Hand & Foot care', 'Oily Skin', 4015.00, '75ml', 'Yes', 'Apply small drop to hands. Rub until absorbed.', 'Hydrates sweaty hands fast without leaving grease.'),
(188, 'Ultra Repair Heel Balm', 'image/ultrarepaiheelbalm.jpeg', 'Bodycare', 'Hand & Foot care', 'Dry Skin', 3768.00, '100ml', 'Yes', 'Apply generously to cracked heels before bedtime.', 'Heals deep heel cracks. Softens tough calluses.'),
(189, 'Daily Hydrating Hand Cream', 'image/dailyhydratinghandcream.jpeg', 'Bodycare', 'Hand & Foot care', 'Combination Skin', 4595.00, '75ml', 'Yes', 'Apply after hand washing over palms and knuckles.', 'Maintains soft hands daily with smooth non-greasy feel.'),
(190, 'Jojoba Dry Body Oil', 'image/jojobadrybodyoil.jpeg', 'Bodycare', 'Body oil', 'Oily Skin', 4271.00, '100ml', 'Yes', 'Spray onto damp skin post-shower. Massage lightly.', 'Absorbs instantly without grease. Mimics natural oil.'),
(191, 'Argan Moisture Body Oil', 'image/argonmoisturebodyoil.jpeg', 'Bodycare', 'Body oil', 'Dry Skin', 2771.00, '100ml', 'Yes', 'Massage a few drops into dry skin daily.', 'Deeply nourishes dry skin. Prevents flaking.'),
(192, 'Rosehip Balancing Oil', 'image/roseshipbalancingoil.jpeg\r\n', 'Bodycare', 'Body oil', 'Combination Skin', 4041.00, '100ml', 'Yes', 'Apply a few drops after shower across body.', 'Evens out skin tone and delivers balanced sheen.'),
(193, 'Scalp Purifying Shampoo', 'image/scalppurifyingshampoo.jpeg', 'Haircare', 'Shampoo', 'Oily Hair', 4493.00, '300ml', 'Yes', 'Apply to wet scalp. Massage into lather and rinse.', 'Removes scalp buildup. Controls grease at roots.'),
(194, 'Argan Moisture Shampoo', 'image/argonmoistureshampoo.jpeg', 'Haircare', 'Shampoo', 'Dry Hair', 2944.00, '300ml', 'Yes', 'Massage gently into wet hair from roots to tips.', 'Restores lost moisture. Softens dry coarse strands.'),
(195, 'Balancing Daily Shampoo', 'image/balancingshampoo.jpeg', 'Haircare', 'Shampoo', 'Combination Hair', 4239.00, '300ml', 'Yes', 'Focus application on scalp. Work down to tips.', 'Cleans oily scalp gently while hydrating dry ends.'),
(196, 'Weightless Volume Rinse', 'image/weightlessvolume.jpeg', 'Haircare', 'Conditioner', 'Oily Hair', 2363.00, '250ml', 'Yes', 'Apply strictly from mid-lengths to ends.', 'Detangles fine hair without adding weight or oil.'),
(197, 'Keratin Silk Conditioner', 'image/keratinsilk.jpeg', 'Haircare', 'Conditioner', 'Dry Hair', 4701.00, '300ml', 'Yes', 'Apply generously to damp lengths for 3 minutes.', 'Repairs dry damaged ends. Restores soft silkiness.'),
(198, 'Hydrating Balance Rinse', 'image/hydratingbalance.jpeg', 'Haircare', 'Conditioner', 'Combination Hair', 3817.00, '250ml', 'Yes', 'Apply from mid-length to tips and rinse.', 'Moisturizes dry ends gently while keeping roots light.'),
(199, 'Grapeseed Light Hair Oil', 'image/grapespeedlighthairoil.jpeg', 'Haircare', 'Hair oil', 'Oily Hair', 2782.00, '50ml', 'Yes', 'Warm two drops in palms. Apply strictly to ends.', 'Fast absorbing oil. Gives shine without greasiness.'),
(200, 'Argan Hair Gloss Oil', 'image/argonhairglossoil.jpeg', 'Haircare', 'Hair oil', 'Dry Hair', 2860.00, '50ml', 'Yes', 'Apply three drops to damp hair. Work through ends.', 'Tames stubborn frizzy hair. Adds brilliant shine.'),
(201, 'Sweet Almond Harmony Oil', 'image/sweetalmondhairoil.jpeg', 'Haircare', 'Hair oil', 'Combination Hair', 3754.00, '50ml', 'Yes', 'Warm two drops in hands. Smooth over tips only.', 'Nourishes dry hair tips without heavy buildup.'),
(202, 'Water Anti-Frizz Serum', 'image/waterantifrizzseerum.jpeg', 'Haircare', 'Hair serum', 'Oily Hair', 2789.00, '100ml', 'Yes', 'Pump once onto palms. Apply through wet ends.', 'Controls daily humidity frizz with zero greasy feel.'),
(203, 'Keratin Repair Serum', 'image/repair cream.jpeg', 'Haircare', 'Hair serum', 'Dry Hair', 3084.00, '100ml', 'Yes', 'Apply two pumps on damp ends before styling.', 'Seals rough split ends. Restores hair smoothness.'),
(204, 'Smooth & Shine Serum', 'image/smoothandshinerserum.jpeg', 'Haircare', 'Hair serum', 'Combination Hair', 2256.00, '100ml', 'Yes', 'Apply one pump to damp ends. Spread evenly.', 'Tames stray flyaways easily. Softens dry ends.'),
(205, 'Clay Scalp Detox Mask', 'image/clayscalpdetox.jpeg', 'Haircare', 'Hair mask', 'Oily Hair', 2426.00, '200ml', 'Yes', 'Apply to roots before shampooing for 10 minutes.', 'Absorbs deep scalp oils. Purifies clogged follicles.'),
(206, 'Deep Repair Protein Mask', 'image/deeprepairproteinmask.jpeg', 'Haircare', 'Hair mask', 'Dry Hair', 3162.00, '200ml', 'Yes', 'Apply generously to damp hair for 15 minutes.', 'Deeply repairs damaged hair. Restores elasticity.'),
(207, 'Nourishing Aloe Mask', 'image/nourishingaloemask.jpeg', 'Haircare', 'Hair mask', 'Combination Hair', 3731.00, '200ml', 'Yes', 'Apply from mid-lengths to ends for 10 minutes.', 'Hydrates dry tips gently while keeping scalp fresh.'),
(208, 'Matte Velvet Foundation', 'image/mattevelevtfoundation.jpeg\r\n', 'Makeup', 'Face makeup', 'N/A', 4373.00, 'N/A', 'Yes', 'Apply to center of face using a damp sponge and blend outward smoothly.', 'N/A'),
(209, 'Dewy Hydrating BB Cream', 'image/dewyhydratingbbcream.jpeg', 'Makeup', 'Face makeup', 'N/A', 3270.00, 'N/A', 'Yes', 'Smooth evenly across face with clean fingertips or foundation brush.', 'N/A'),
(210, 'Silk Shimmer Eyeshadow', 'image/silkshimmereye.jpeg', 'Makeup', 'Eye makeup', 'N/A', 3633.00, 'N/A', 'Yes', 'Apply to eyelids using a soft shadow brush and blend edges gently.', 'N/A'),
(211, 'Longwear Gel Eyeliner', 'image/longweargeleyeliner.jpeg', 'Makeup', 'Eye makeup', 'N/A', 3555.00, 'N/A', 'Yes', 'Glide along the upper lash line starting from the inner corner outward.', 'N/A'),
(212, 'Satin Liquid Lipstick', 'image/satinliwuidlipstick.avif', 'Makeup', 'Lip makeup', 'N/A', 4677.00, 'N/A', 'Yes', 'Outline lips with applicator tip and fill in center starting from middle.', 'N/A'),
(213, 'Hydrating Lip Gloss Tint', 'image/hydratinglipglosstint.jpeg', 'Makeup', 'Lip makeup', 'N/A', 2718.00, 'N/A', 'Yes', 'Glide directly over lips or layer on top of your favorite lipstick.', 'N/A'),
(214, 'Peach Cream Tint Blush', 'image/peachcreamtintblush.jpeg', 'Makeup', 'Cheek makeup', 'N/A', 2563.00, 'N/A', 'Yes', 'Dab onto apples of cheeks and blend upward toward temples using fingers.', 'N/A'),
(215, 'Pressed Powder Bronzer', 'image/powderpressbronzer.jpeg', 'Makeup', 'Cheek makeup', 'N/A', 2459.00, 'N/A', 'Yes', 'Sweep along cheekbones and jawline using a soft fluffy blush brush.', 'N/A'),
(216, 'Pro Beauty Sponge', 'image/probeautysponge.jpeg', 'Makeup', 'Makeup tools', 'N/A', 2408.00, 'N/A', 'Yes', 'Dampen sponge before use and bounce liquid foundation softly on skin.', 'N/A'),
(217, 'Fluffy Powder Brush', 'image/fluffypowderbrush.jpeg\r\n', 'Makeup', 'Makeup tools', 'N/A', 2462.00, 'N/A', 'Yes', 'Swirl brush in loose or pressed powder and sweep lightly across face.', 'N/A'),
(218, 'Rose Garden EDP', 'image/rosegradernedp.jpeg', 'Fragrance', 'Women\'s perfume', 'N/A', 2888.00, 'N/A', 'Yes', 'N/A', 'N/A'),
(219, 'Jasmine Blossom Cologne', 'image/jasmineblossomcolonge.jpeg', 'Fragrance', 'Women\'s perfume', 'N/A', 2253.00, 'N/A', 'Yes', 'N/A', 'N/A'),
(220, 'Vanilla Sunset Body Mist', 'image/vanillasunstbodymist.jpeg', 'Fragrance', 'Body spray', 'N/A', 3001.00, 'N/A', 'Yes', 'N/A', 'N/A'),
(221, 'Tropical Coconut Mist', 'image/tropicalcocnutmist.jpeg', 'Fragrance', 'Body spray', 'N/A', 3447.00, 'N/A', 'Yes', 'N/A', 'N/A'),
(222, 'Amber Musk Attar Oil', 'image/Ambermusk.jpeg', 'Fragrance', 'Oil perfume', 'N/A', 3433.00, 'N/A', 'Yes', 'Roll gently onto wrist pulse points and behind ears for long-lasting aroma.', 'N/A'),
(223, 'Royal Oud Concentrated Oil', 'image/royalconcentrateoud.jpeg', 'Fragrance', 'Oil perfume', 'N/A', 4624.00, 'N/A', 'Yes', 'Dab a small drop onto palms and apply to neck and collarbone.', 'N/A'),
(224, 'Ocean Breeze Eau De Parfum', 'image/oceanbreeze.jpeg', 'Fragrance', 'Unisex perfume', 'N/A', 2820.00, 'N/A', 'Yes', 'N/A', 'N/A'),
(225, 'Citrus Wood Balance Spray', 'image/citruswoodbalance.jpeg', 'Fragrance', 'Unisex perfume', 'N/A', 3228.00, 'N/A', 'Yes', 'N/A', 'N/A'),
(226, 'Woody Leather EDP', 'image/woodleatheredp.jpeg', 'Fragrance', 'Men\'s Perfume', 'N/A', 2882.00, 'N/A', 'Yes', 'N/A', 'N/A'),
(227, 'Fresh Cedar Intense Cologne', 'image/freshcederintense.jpeg', 'Fragrance', 'Men\'s Perfume', 'N/A', 2527.00, 'N/A', 'Yes', 'N/A', 'N/A');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customer_skin_history`
--
ALTER TABLE `customer_skin_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `customer_skin_photos`
--
ALTER TABLE `customer_skin_photos`
  ADD PRIMARY KEY (`photo_id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`P_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_skin_history`
--
ALTER TABLE `customer_skin_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_skin_photos`
--
ALTER TABLE `customer_skin_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `P_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
