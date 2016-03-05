-- phpMyAdmin SQL Dump
-- version 4.3.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2016 at 07:53 AM
-- Server version: 5.6.24
-- PHP Version: 5.5.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `epay`
--

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `id` int(6) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `price` decimal(26,6) NOT NULL,
  `quantity` tinyint(2) NOT NULL,
  `currency` char(3) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `invoice_number`, `item_name`, `price`, `quantity`, `currency`, `description`) VALUES
(1, '56d67340c4d40', 'Ground Coffee 40 oz', '12.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(2, '56d673d416544', 'Ground Coffee 40 oz', '12.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(3, '56d69b6392eb7', 'Ground Coffee 40 oz', '12.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(4, '56d69bac66b86', 'Ground Coffee 40 oz', '12.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(5, '56d69bd71ca64', 'Ground Coffee 40 oz', '12.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(6, '56d69cec90a01', 'Ground Coffee 40 oz', '123.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(7, '56d69d536af3e', 'Ground Coffee 40 oz', '123.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(8, '56d69d8824173', 'Ground Coffee 40 oz', '123.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(9, '56d69e35bf773', 'Ground Coffee 40 oz', '123.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(10, '56d69eb13abcc', 'Ground Coffee 40 oz', '123.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(11, '56d69ee57cf1a', 'Ground Coffee 40 oz', '123.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(12, '56d69f23538c5', 'Ground Coffee 40 oz', '50.000000', 1, 'EUR', 'Ground Coffee 40 oz'),
(13, '56d6b4f5bd35d', 'Ground Coffee 40 oz', '22.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(14, '56d6d0353fa72', 'Ground Coffee 40 oz', '22.000000', 1, 'USD', 'Ground Coffee 40 oz'),
(15, 'c5vr2c', 'Sample Product', '77.000000', 1, 'SGD', ''),
(16, '5rjpbc', 'Sample Product', '45.000000', 1, 'THB', ''),
(17, 'ht56ch', 'Sample Product', '20.000000', 1, 'THB', ''),
(18, 'fqx57h', 'Sample Product', '75.000000', 1, 'HKD', ''),
(19, '9x9qd3', 'Sample Product', '23.000000', 1, 'THB', ''),
(20, '56d984ee9771c', 'Ground Coffee 40 oz', '5.000000', 1, 'EUR', 'Ground Coffee 40 oz');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(6) NOT NULL,
  `payment_id` varchar(100) NOT NULL,
  `currency` char(3) NOT NULL,
  `subtotal` decimal(26,6) NOT NULL,
  `total` decimal(26,6) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `payment_id`, `currency`, `subtotal`, `total`, `invoice_number`, `description`) VALUES
(1, 'PAY-0YK39373BL4633529K3LHHVQ', 'USD', '12.000000', '12.000000', '56d673d416544', 'Payment description'),
(2, 'PAY-1A344257VU188422FK3LJWZI', 'USD', '12.000000', '12.000000', '56d69b6392eb7', 'Payment description'),
(3, 'PAY-27U71442WL887060CK3LJXLI', 'USD', '12.000000', '12.000000', '56d69bac66b86', 'Payment description'),
(4, 'PAY-7MJ756357E717674YK3LJXWA', 'USD', '12.000000', '12.000000', '56d69bd71ca64', 'Payment description'),
(5, 'PAY-2TF502824W518042NK3LJ2VA', 'USD', '123.000000', '123.000000', '56d69d536af3e', 'Payment description'),
(6, 'PAY-8CY172349R6058222K3LJ3DI', 'USD', '123.000000', '123.000000', '56d69d8824173', 'Payment description'),
(7, 'PAY-39N14130GA943560VK3LJ4NY', 'USD', '123.000000', '123.000000', '56d69e35bf773', 'Payment description'),
(8, 'PAY-059610560R046450YK3LJ5MQ', 'USD', '123.000000', '123.000000', '56d69eb13abcc', 'Payment description'),
(9, 'PAY-8Y3761828A134440KK3LJ5ZQ', 'USD', '123.000000', '123.000000', '56d69ee57cf1a', 'Payment description'),
(10, 'PAY-4EX39113N6643504SK3LJ6JA', 'EUR', '50.000000', '50.000000', '56d69f23538c5', 'Payment description'),
(11, 'PAY-61H089505X5599026K3LNANQ', 'USD', '22.000000', '22.000000', '56d6d0353fa72', 'Payment description'),
(12, 'c5vr2c', 'SGD', '0.000000', '77.000000', '', ''),
(13, '5rjpbc', 'THB', '0.000000', '45.000000', '', ''),
(14, 'jtjy9nv8mjxmz6g7', 'THB', '0.000000', '20.000000', 'ht56ch', ''),
(15, 'th4pzb4wkzv9pzsz', 'HKD', '0.000000', '75.000000', 'fqx57h', ''),
(16, '2pfx7bjcgnns4xkt', 'THB', '0.000000', '23.000000', '9x9qd3', ''),
(17, 'PAY-92T387558R6071401K3MYJ4I', 'EUR', '5.000000', '5.000000', '56d984ee9771c', 'Payment description');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=18;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
