-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 2017-07-17 20:53:36
-- 服务器版本： 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `morrison`
--
CREATE DATABASE IF NOT EXISTS `morrison` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `morrison`;

-- --------------------------------------------------------

--
--
-- 表的结构 `email_send_queue`
--

DROP TABLE IF EXISTS `email_send_queue`;
CREATE TABLE `email_send_queue` (
  `id` bigint(20) NOT NULL,
  `email_address` varchar(255) COLLATE utf8_bin NOT NULL,
  `subject` varchar(100) COLLATE utf8_bin NOT NULL,
  `content` text COLLATE utf8_bin NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '0 text/plain 1 text/html'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
CREATE TABLE `migration_versions` (
  `version` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `vote_list`
--

DROP TABLE IF EXISTS `vote_list`;
CREATE TABLE `vote_list` (
  `id` bigint(20) NOT NULL,
  `email_address` varchar(255) COLLATE utf8_bin NOT NULL,
  `sticker` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `ip_address` char(15) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `email_send_queue`
--
ALTER TABLE `email_send_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vote_list`
--
ALTER TABLE `vote_list`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `email_send_queue`
--
ALTER TABLE `email_send_queue`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `vote_list`
--
ALTER TABLE `vote_list`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;