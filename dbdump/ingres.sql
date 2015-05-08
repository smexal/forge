-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 08. Mai 2015 um 20:21
-- Server Version: 5.6.21
-- PHP-Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `ingres`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
`id` int(7) NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `groups`
--

INSERT INTO `groups` (`id`, `name`) VALUES
(1, 'Administrator');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups_users`
--

CREATE TABLE IF NOT EXISTS `groups_users` (
`id` int(11) NOT NULL,
  `groupid` int(11) NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `groups_users`
--

INSERT INTO `groups_users` (`id`, `groupid`, `userid`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
`id` int(7) NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `permissions`
--

INSERT INTO `permissions` (`id`, `name`) VALUES
(24, 'manage'),
(25, 'manage.users.display'),
(27, 'manage.settings'),
(28, 'manage.users.add'),
(29, 'manage.users.delete'),
(30, 'manage.groups'),
(31, 'manage.groups.add'),
(32, 'manage.users.edit');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions_groups`
--

CREATE TABLE IF NOT EXISTS `permissions_groups` (
`id` int(7) NOT NULL,
  `groupid` int(7) NOT NULL,
  `permissionid` int(7) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `permissions_groups`
--

INSERT INTO `permissions_groups` (`id`, `groupid`, `permissionid`) VALUES
(3, 1, 24),
(4, 1, 25),
(5, 1, 28),
(6, 1, 27),
(7, 1, 31),
(8, 1, 29),
(9, 1, 30),
(10, 1, 32);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'Voyze', 'silas.maechler@gmail.com', '$2y$10$BXak/4dYYrcTZ0WlCl5AXeqbCszfe2hYfO/Sk33jpNe8lwqSsbvKS'),
(5, 'Procc', 'simon.pfister88@gmail.com', '$2y$10$8dH4yiUGvA4iQ9KojD0oduH2VugDIrMcTW0nYxuKc9x6/edkfWYlO'),
(6, 'Jarekk82', 'jarekk82@ing-res.ch', '$2y$10$1j2BgzaS5ABLgBFcTD.ZVeIz9DnyKTXGZRd7iOPvxr2vDkk4vIF36'),
(8, 'qwerqwer', '123sadf@sdcoi.ch', '$2y$10$TxXmOJUkvw18e/zmypGJqeeso.Oclp8J/WmSSvABSo7u4ube6psY2'),
(9, 'qwerqewr', 'sxfddsaf@lkjc.ch', '$2y$10$bIFT5n3utpm.h0LZMpfr8OLwKbZhe0CmdADrh5njCD5grb89I21YW');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `groups`
--
ALTER TABLE `groups`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `groups_users`
--
ALTER TABLE `groups_users`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `permissions`
--
ALTER TABLE `permissions`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `permissions_groups`
--
ALTER TABLE `permissions_groups`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `groups`
--
ALTER TABLE `groups`
MODIFY `id` int(7) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `groups_users`
--
ALTER TABLE `groups_users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `permissions`
--
ALTER TABLE `permissions`
MODIFY `id` int(7) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT für Tabelle `permissions_groups`
--
ALTER TABLE `permissions_groups`
MODIFY `id` int(7) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
