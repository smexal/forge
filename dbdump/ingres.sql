-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 20. Mai 2015 um 21:59
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
(1, 'Administratoren');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups_users`
--

CREATE TABLE IF NOT EXISTS `groups_users` (
`id` int(11) NOT NULL,
  `groupid` int(11) NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `groups_users`
--

INSERT INTO `groups_users` (`id`, `groupid`, `userid`) VALUES
(1, 1, 21),
(3, 1, 23);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
`id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(250) NOT NULL,
  `default` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `languages`
--

INSERT INTO `languages` (`id`, `code`, `name`, `default`) VALUES
(1, 'de', 'Deutsch', 0),
(2, 'en', 'English', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `language_strings`
--

CREATE TABLE IF NOT EXISTS `language_strings` (
`id` int(11) NOT NULL,
  `string` varchar(1000) NOT NULL,
  `domain` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
`id` int(7) NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;

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
(32, 'manage.groups.edit'),
(33, 'manage.users'),
(35, 'manage.groups.delete'),
(36, 'manage.groups.members'),
(38, 'manage.users.edit'),
(39, 'manage.permissions'),
(40, 'manage.sites'),
(42, 'manage.modules'),
(43, 'manage.navigations'),
(44, 'manage.locales'),
(45, 'manage.locales.add'),
(46, 'manage.locales.strings'),
(47, 'manage.locales.strings.update');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions_groups`
--

CREATE TABLE IF NOT EXISTS `permissions_groups` (
`id` int(7) NOT NULL,
  `groupid` int(7) NOT NULL,
  `permissionid` int(7) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `permissions_groups`
--

INSERT INTO `permissions_groups` (`id`, `groupid`, `permissionid`) VALUES
(4, 1, 25),
(5, 1, 28),
(6, 1, 29),
(7, 1, 30),
(8, 1, 31),
(9, 1, 33),
(10, 1, 36),
(12, 1, 35),
(13, 1, 39),
(14, 1, 24),
(15, 1, 32),
(16, 1, 27),
(17, 1, 38),
(18, 1, 40),
(20, 1, 43),
(21, 1, 42),
(22, 1, 44),
(23, 1, 45),
(24, 1, 47),
(25, 1, 46);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(21, 'Voyze', 'silas.maechler@gmail.com', '$2y$10$fZuEnlJkwpsLk1RndaIoM.9fTi4XfJIEqNaIhrpFUis3af8S1KQje'),
(23, 'Procc', 'simon.pfister88@gmail.com', '$2y$10$Ec/qV/8Uzch.nKECRWpjG.eFx2hE6BOTCBX1bhpezcwMRvnjbjOnK'),
(25, 'lakjsdf', 'lkjasdflk@lkajck.com', '$2y$10$RFxbDikBIhwTcgF/MYQvseo75mGjVUOzaxl8OEWwDMOrFCgVNly8C'),
(26, 'lkajsdflkjääciööio', 'loeaksjdfl@lksjdf.com', '$2y$10$h1U1Ific31rgSQsgKTU/c.KHLuyQSjPPMmkqWMkuthi2BI3pRmhYa'),
(27, 'smaehchler', 'laksjdf@kljv.com', '$2y$10$NerJ3KqvbDxskoyt2hHKo.wrjLGyl9xwR7nkDjVa5YYkqWDMejY4a');

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
 ADD PRIMARY KEY (`id`), ADD KEY `groupid` (`groupid`), ADD KEY `userid` (`userid`);

--
-- Indizes für die Tabelle `languages`
--
ALTER TABLE `languages`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `language_strings`
--
ALTER TABLE `language_strings`
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
 ADD PRIMARY KEY (`id`), ADD KEY `groupid` (`groupid`), ADD KEY `permissionid` (`permissionid`);

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
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT für Tabelle `languages`
--
ALTER TABLE `languages`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `language_strings`
--
ALTER TABLE `language_strings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `permissions`
--
ALTER TABLE `permissions`
MODIFY `id` int(7) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=48;
--
-- AUTO_INCREMENT für Tabelle `permissions_groups`
--
ALTER TABLE `permissions_groups`
MODIFY `id` int(7) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=28;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `groups_users`
--
ALTER TABLE `groups_users`
ADD CONSTRAINT `groups_users_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `groups_users_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `permissions_groups`
--
ALTER TABLE `permissions_groups`
ADD CONSTRAINT `permissions_groups_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `permissions_groups_ibfk_2` FOREIGN KEY (`permissionid`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
