-- SQL used to create the tables for the cart.

-- phpMyAdmin SQL Dump
-- version 3.5.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2013 at 04:04 PM
-- Server version: 5.1.69
-- PHP Version: 5.3.27

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `cart_main`
--

-- --------------------------------------------------------

--
-- Cart Permissions
--

-- Note: these IDs are based on the template permission IDs

INSERT INTO `permission` VALUES(31, 'cart/admin', 'Cart Admin', 'Allows the user to access the Shopping Cart Admin.');
INSERT INTO `permission` VALUES(32, 'cart/admin/order', 'Cart Admin - Orders', 'Allows the user to access the Shopping Cart Orders.');
INSERT INTO `permission` VALUES(33, 'cart/admin/shipping', 'Cart Admin - Shipping Rates', 'Allows the user to access the Shopping Cart Shipping Rates.');
INSERT INTO `permission` VALUES(34, 'cart/admin/tax', 'Cart Admin - Taxes', 'Allows the user to access the Shopping Cart Taxes.');

INSERT INTO `group_permission` VALUES(NULL, 1, 31);
INSERT INTO `group_permission` VALUES(NULL, 1, 32);
INSERT INTO `group_permission` VALUES(NULL, 1, 33);
INSERT INTO `group_permission` VALUES(NULL, 1, 34);
INSERT INTO `group_permission` VALUES(NULL, 2, 31);
INSERT INTO `group_permission` VALUES(NULL, 2, 32);
INSERT INTO `group_permission` VALUES(NULL, 2, 33);
INSERT INTO `group_permission` VALUES(NULL, 2, 34);

-- --------------------------------------------------------

--
-- Table structure for table `cart_additional_charge`
--

CREATE TABLE `cart_additional_charge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `calculation_method` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `display_order` smallint(5) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_discount`
--

CREATE TABLE `cart_discount` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `calculation_method` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `free_flag` tinyint(1) unsigned NOT NULL,
  `discount_reason` char(15) COLLATE utf8_unicode_ci NOT NULL,
  `val1` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `val2` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `discount_type` char(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`,`start`,`end`,`val1`),
  KEY `expiry_date_2` (`expiry_date`,`start`,`end`,`val1`,`val2`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_discount_product`
--

CREATE TABLE `cart_discount_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_discount_id` int(10) unsigned NOT NULL,
  `cart_product_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`,`cart_discount_id`,`cart_product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_gift_card`
--

CREATE TABLE `cart_gift_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`,`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_gift_card_log`
--

CREATE TABLE `cart_gift_card_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_order_id` int(10) unsigned NOT NULL,
  `cart_gift_card_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `action` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order`
--

CREATE TABLE `cart_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `sub_total` decimal(10,2) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_processor_fee` decimal(8,2) NOT NULL,
  `exchange_rate` decimal(11,5) NOT NULL,
  `country_id` int(10) unsigned NOT NULL,
  `order_num` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `internal_order_num` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(2) unsigned NOT NULL,
  `po_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `order_note` text COLLATE utf8_unicode_ci NOT NULL,
  `user_address_loaded_flag` tinyint(1) unsigned NOT NULL,
  `shipping_first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_address_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_address_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_municipality` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_state_id` int(10) unsigned NOT NULL,
  `shipping_state` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_postal_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_country_id` int(10) unsigned NOT NULL,
  `shipping_phone` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_email` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `same_as_shipping_flag` tinyint(1) unsigned NOT NULL,
  `billing_first_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `billing_last_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `billing_company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_address_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_address_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_municipality` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `billing_state_id` int(10) unsigned NOT NULL,
  `billing_state` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `billing_postal_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `billing_country_id` int(10) unsigned NOT NULL,
  `billing_phone` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `billing_email` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_expired` (`expiry_date`),
  KEY `user_id` (`user_id`),
  KEY `country_id` (`country_id`),
  KEY `invoice` (`order_num`),
  KEY `status_id` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_additional_charge`
--

CREATE TABLE `cart_order_additional_charge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_order_id` int(10) unsigned NOT NULL,
  `cart_additional_charge_id` int(10) unsigned NOT NULL,
  `display_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_expired` (`expiry_date`,`cart_order_id`,`cart_additional_charge_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_discount`
--

CREATE TABLE `cart_order_discount` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_order_id` int(10) unsigned NOT NULL,
  `cart_discount_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`,`cart_order_id`,`cart_discount_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_log`
--

CREATE TABLE `cart_order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cart_order_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `action` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`cart_order_id`,`user_id`,`timestamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_payment`
--

CREATE TABLE `cart_order_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_order_id` int(10) unsigned NOT NULL,
  `date_attempted` datetime NOT NULL,
  `date_completed` datetime NOT NULL,
  `date_refunded` datetime NOT NULL,
  `ip_address` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `payment_processor` int(10) unsigned NOT NULL,
  `status` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `response` text COLLATE utf8_unicode_ci NOT NULL,
  `transaction_id` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`,`cart_order_id`,`date_attempted`,`date_completed`,`date_refunded`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_payment_log`
--

CREATE TABLE `cart_order_payment_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cart_order_payment_id` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `status` int(10) unsigned NOT NULL,
  `status_string` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `details` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_product`
--

CREATE TABLE `cart_order_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_order_id` int(10) unsigned NOT NULL,
  `cart_product_id` int(10) unsigned NOT NULL,
  `quantity` smallint(6) unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_shipping`
--

CREATE TABLE `cart_order_shipping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_order_id` int(10) unsigned NOT NULL,
  `cart_shipping_id` int(10) unsigned NOT NULL,
  `display_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `manual_flag` tinyint(1) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`,`cart_order_id`,`cart_shipping_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_order_tax`
--

CREATE TABLE `cart_order_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_order_id` int(10) unsigned NOT NULL,
  `cart_tax_id` int(10) unsigned NOT NULL,
  `display_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `display_order` smallint(5) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expiry_date` (`expiry_date`,`cart_order_id`,`cart_tax_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_product`
--

CREATE TABLE `cart_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_product_property`
--

CREATE TABLE `cart_product_property` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `cart_product_id` int(10) unsigned NOT NULL,
  `cart_property_id` int(10) unsigned NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `display_order` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`,`cart_product_id`,`cart_property_id`,`display_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_property`
--

CREATE TABLE `cart_property` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `label` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `edit_flag` tinyint(1) unsigned NOT NULL,
  `required_flag` tinyint(1) unsigned NOT NULL,
  `field_type` char(10) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiry_date` (`expiry_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_shipping`
--

CREATE TABLE `cart_shipping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `calculation_method` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `display_order` smallint(6) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `main` (`expiry_date`,`start`,`end`,`calculation_method`,`display_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_tax`
--

CREATE TABLE `cart_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expiry_date` datetime NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `all_locations_flag` tinyint(1) unsigned NOT NULL,
  `only_without_flag` tinyint(1) unsigned NOT NULL,
  `country_id` int(10) unsigned NOT NULL,
  `state_id` int(10) unsigned NOT NULL,
  `display_order` smallint(6) unsigned NOT NULL,
  `calculation_method` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(8,3) NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_expired` (`expiry_date`,`start`,`end`,`country_id`,`state_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
