
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `gitscanpassive` (
  `id` int(11) NOT NULL,
  `companyid` int(11) NOT NULL,
  `companyname` varchar(255) DEFAULT NULL,
  `repourl` varchar(500) DEFAULT NULL,
  `companyurl` varchar(500) DEFAULT NULL,
  `userid` int(11) NOT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1',
  `last_scan_monthday` int(2) DEFAULT NULL,
  `gitscan_previous` mediumtext,
  `gitscan_new` mediumtext,
  `needs_to_notify` int(1) NOT NULL,
  `token` varchar(100) NOT NULL DEFAULT '0',
  `viewed` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `passive_scan` (
  `scanid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `scanday` tinyint(1) NOT NULL,
  `dirscanUrl` varchar(255) DEFAULT NULL,
  `amassDomain` varchar(255) DEFAULT NULL,
  `nmapDomain` varchar(255) DEFAULT NULL,
  `amass_previous` text,
  `amass_new` text,
  `nmap_previous` text,
  `nmap_new` text,
  `dirscan_previous` text,
  `dirscan_new` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_notified` tinyint(1) NOT NULL DEFAULT '0',
  `needs_to_notify` tinyint(1) NOT NULL DEFAULT '0',
  `notify_instrument` varchar(20) DEFAULT NULL,
  `last_scan_monthday` int(2) NOT NULL DEFAULT '0',
  `viewed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `queue` (
  `id` int(10) NOT NULL,
  `taskid` int(10) NOT NULL,
  `instrument` int(2) NOT NULL,
  `working` int(1) NOT NULL DEFAULT '0',
  `todelete` int(11) NOT NULL DEFAULT '0',
  `amassdomain` varchar(255) DEFAULT NULL,
  `dirscanUrl` varchar(255) DEFAULT NULL,
  `dirscanIP` varchar(255) DEFAULT NULL,
  `nmap` varchar(6000) DEFAULT NULL,
  `vhostport` varchar(20) DEFAULT NULL,
  `vhostip` varchar(255) DEFAULT NULL,
  `vhostdomain` varchar(255) DEFAULT NULL,
  `vhostssl` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `sent_email` (
  `emailid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `scanid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tasks` (
  `taskid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `notification_enabled` tinyint(1) DEFAULT '1',
  `status` varchar(20) DEFAULT 'Working',
  `host` varchar(5600) DEFAULT NULL,
  `nmap` mediumtext,
  `amass` mediumtext,
  `subtakeover` text,
  `aquatone` mediumtext,
  `dirscan` mediumtext,
  `wayback` mediumtext,
  `gitscan` mediumtext,
  `ips` text,
  `vhost` mediumtext,
  `js` mediumtext,
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
  `notified` mediumint(1) NOT NULL DEFAULT '0',
  `notify_instrument` varchar(20) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(4) NOT NULL DEFAULT '10',
  `rights` int(4) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `scans_counter` int(3) NOT NULL DEFAULT '0',
  `scan_timeout` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `gitscanpassive`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `passive_scan`
  ADD PRIMARY KEY (`scanid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `scanid` (`scanid`),
  ADD KEY `last_scan_monthday` (`last_scan_monthday`),
  ADD KEY `notifications_enabled` (`notifications_enabled`);

ALTER TABLE `queue`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `sent_email`
  ADD PRIMARY KEY (`emailid`),
  ADD KEY `userid` (`userid`);

ALTER TABLE `tasks`
  ADD PRIMARY KEY (`taskid`),
  ADD UNIQUE KEY `taskid` (`taskid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `taskid_2` (`taskid`),
  ADD KEY `notification_enabled` (`notification_enabled`),
  ADD KEY `status` (`status`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `password_reset_token` (`password_reset_token`),
  ADD KEY `id` (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `scans_counter` (`scans_counter`);

ALTER TABLE `gitscanpassive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `passive_scan`
  MODIFY `scanid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `queue`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1052;

ALTER TABLE `sent_email`
  MODIFY `emailid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tasks`
  MODIFY `taskid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2895;

ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `passive_scan`
  ADD CONSTRAINT `passive_scan_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`);

ALTER TABLE `sent_email`
  ADD CONSTRAINT `sent_email_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`);

ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`);

INSERT INTO `user` (`id`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `rights`, `created_at`, `updated_at`, `scans_counter`, `scan_timeout`) VALUES (1, 'k8RkFpLhU44Bqgal0tKQNYp-e7mE-e9A', '$2y$12$5A6Y7v1gKaNtYsRrZsHiUe7VXsxe.v2iiprJm/2tH5RMVSKCIvtYe', NULL, 'admin@admin.com', '10', '0', '1575122687', '1575122687', '0', '0') 

/*default user admin:admin*/;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
