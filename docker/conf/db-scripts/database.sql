SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `scaner`
--

-- --------------------------------------------------------

--
-- Table structure for table `gitscanpassive`
--

CREATE TABLE `gitscanpassive` (
  `id` int(11) NOT NULL,
  `companyid` int(11) NOT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `repourl` varchar(500) DEFAULT NULL,
  `companyurl` varchar(500) DEFAULT NULL,
  `userid` int(11) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT '1',
  `last_scan_monthday` int(11) DEFAULT NULL,
  `gitscan_previous` mediumtext,
  `gitscan_new` mediumtext,
  `needs_to_notify` int(11) NOT NULL,
  `token` varchar(100) NOT NULL DEFAULT '0',
  `viewed` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `passive_scan`
--

CREATE TABLE `passive_scan` (
  `PassiveScanid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `scanday` tinyint(1) NOT NULL,
  `dirscanUrl` varchar(255) DEFAULT NULL,
  `dirscanIP` varchar(255) DEFAULT NULL,
  `amassDomain` varchar(255) DEFAULT NULL,
  `nmapDomain` varchar(255) DEFAULT NULL,
  `amass_previous` longtext,
  `amass_new` longtext,
  `nmap_previous` longtext,
  `nmap_new` longtext,
  `dirscan_previous` longtext,
  `dirscan_new` longtext,
  `gitscan` longtext,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_notified` tinyint(1) NOT NULL DEFAULT '0',
  `needs_to_notify` tinyint(1) NOT NULL DEFAULT '0',
  `notify_instrument` varchar(20) DEFAULT NULL,
  `last_scan_monthday` int(11) NOT NULL DEFAULT '0',
  `viewed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int(11) NOT NULL,
  `taskid` int(11) NOT NULL,
  `passivescan` tinyint(1) NOT NULL DEFAULT '0',
  `instrument` int(11) NOT NULL,
  `working` int(11) NOT NULL DEFAULT '0',
  `todelete` int(11) NOT NULL DEFAULT '0',
  `amassdomain` varchar(255) DEFAULT NULL,
  `wordlist` tinyint(4) DEFAULT '0',
  `dirscanUrl` varchar(255) DEFAULT NULL,
  `dirscanIP` varchar(255) DEFAULT NULL,
  `nmap` varchar(6000) DEFAULT NULL,
  `vhostport` varchar(20) DEFAULT NULL,
  `vhostip` varchar(255) DEFAULT NULL,
  `vhostdomain` varchar(255) DEFAULT NULL,
  `vhostssl` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sent_email`
--

CREATE TABLE `sent_email` (
  `emailid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `scanid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `taskid` int(11) NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '10',
  `notification_enabled` tinyint(1) DEFAULT '1',
  `status` varchar(20) DEFAULT 'Working',
  `host` varchar(5600) DEFAULT NULL,
  `nmap` longtext,
  `amass` longtext,
  `nuclei` longtext,
  `amass_intel` longtext,
  `subtakeover` text,
  `aquatone` mediumtext,
  `dirscan` longtext,
  `wayback` longtext,
  `gitscan` mediumtext,
  `ips` text,
  `vhost` mediumtext,
  `vhostwordlist` mediumtext,
  `js` longtext,
  `reverseip` mediumtext,
  `nmap_status` varchar(20) DEFAULT NULL,
  `amass_status` varchar(20) DEFAULT NULL,
  `dirscan_status` varchar(20) DEFAULT NULL,
  `gitscan_status` varchar(20) DEFAULT NULL,
  `ips_status` varchar(20) DEFAULT NULL,
  `vhost_status` varchar(20) DEFAULT NULL,
  `js_status` varchar(20) DEFAULT NULL,
  `reverseip_status` varchar(20) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `notified` mediumint(9) NOT NULL DEFAULT '0',
  `notify_instrument` varchar(20) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tools_amount` (
  `id` smallint(6) NOT NULL,
  `amass` smallint(6) NOT NULL DEFAULT '0',
  `nmap` smallint(6) NOT NULL DEFAULT '0',
  `vhosts` smallint(6) NOT NULL DEFAULT '0',
  `dirscan` smallint(6) NOT NULL DEFAULT '0',
  `googlescan` smallint(6) NOT NULL DEFAULT '0',
  `gitscan` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tools_amount`
--

INSERT INTO `tools_amount` (`id`, `amass`, `nmap`, `vhosts`, `dirscan`, `googlescan`, `gitscan`) VALUES
(1, 0, 10, 0, 8, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL DEFAULT '10',
  `rights` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `scans_counter` int(11) NOT NULL DEFAULT '0',
  `scan_timeout` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `rights`, `created_at`, `updated_at`, `scans_counter`, `scan_timeout`) VALUES
(10, 'k8RkFpLhU44Bqgal0tKQNYp-e7mE-e9A', '$2y$12$5A6Y7v1gKaNtYsRrZsHiUe7VXsxe.v2iiprJm/2tH5RMVSKCIvtYe', NULL, 'admin@admin.com', 10, 0, 1575122687, 1630514317, 352, 1630432689);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gitscanpassive`
--
ALTER TABLE `gitscanpassive`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `passive_scan`
--
ALTER TABLE `passive_scan`
  ADD PRIMARY KEY (`PassiveScanid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `scanid` (`PassiveScanid`),
  ADD KEY `last_scan_monthday` (`last_scan_monthday`),
  ADD KEY `notifications_enabled` (`notifications_enabled`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `sent_email`
--
ALTER TABLE `sent_email`
  ADD PRIMARY KEY (`emailid`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`taskid`),
  ADD UNIQUE KEY `taskid` (`taskid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `notification_enabled` (`notification_enabled`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `tools_amount`
--
ALTER TABLE `tools_amount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `password_reset_token` (`password_reset_token`),
  ADD KEY `id` (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `scans_counter` (`scans_counter`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gitscanpassive`
--
ALTER TABLE `gitscanpassive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `passive_scan`
--
ALTER TABLE `passive_scan`
  MODIFY `PassiveScanid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10198;

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3608;

--
-- AUTO_INCREMENT for table `sent_email`
--
ALTER TABLE `sent_email`
  MODIFY `emailid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `taskid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5132;

--
-- AUTO_INCREMENT for table `tools_amount`
--
ALTER TABLE `tools_amount`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `passive_scan`
--
ALTER TABLE `passive_scan`
  ADD CONSTRAINT `passive_scan_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`);

--
-- Constraints for table `sent_email`
--
ALTER TABLE `sent_email`
  ADD CONSTRAINT `sent_email_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`);

ALTER TABLE `tasks` CHANGE `host` `host` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `queue` CHANGE `dirscanUrl` `dirscanUrl` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `queue` CHANGE `taskid` `taskid` INT(11) NULL DEFAULT NULL; 
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
