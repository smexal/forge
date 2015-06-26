-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 26. Jun 2015 um 17:52
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
(2, 'en', 'English', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `language_strings`
--

CREATE TABLE IF NOT EXISTS `language_strings` (
`id` int(11) NOT NULL,
  `string` varchar(1000) NOT NULL,
  `domain` varchar(100) NOT NULL,
  `used` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `language_strings`
--

INSERT INTO `language_strings` (`id`, `string`, `domain`, `used`) VALUES
(2, 'User %1$s (%2$s) has been created.', '', 1),
(3, 'Username', '', 1),
(4, 'E-Mail', '', 1),
(5, 'Password', '', 1),
(7, 'User has been deleted.', '', 1),
(8, 'There was an error while deleting the user.', '', 1),
(9, 'Delete user \\''%s\\''?', '', 1),
(10, 'Do you really want to delete user with the email \\''%s\\''?', '', 1),
(11, 'Yes, delete user', '', 1),
(12, 'No, cancel.', '', 1),
(13, 'Edit user %s', '', 1),
(14, 'User modifications on the user %1$s (%2$s) have been saved.', '', 1),
(15, 'Leave empty if you don\\''t want to change the password.', '', 1),
(16, 'Repeat password', '', 1),
(18, 'User Management', '', 1),
(19, 'Add user', '', 1),
(20, 'id', '', 1),
(21, 'Actions', '', 1),
(22, 'delete user', '', 1),
(23, 'edit user', '', 1),
(24, 'Permission Management', '', 1),
(25, 'Permission', '', 1),
(26, 'Add Permission', '', 1),
(27, 'Remove Permission', '', 1),
(28, 'Add new Language', '', 1),
(29, 'Language Code', '', 1),
(30, 'Language Name', '', 1),
(31, 'Add', '', 1),
(32, 'New language %1$s (%2$s) has been added.', '', 1),
(33, 'Language Configuration', '', 1),
(34, 'Add language', '', 1),
(35, 'Code', '', 1),
(36, 'Name', '', 1),
(37, 'Default', '', 1),
(38, 'Set Default', '', 1),
(39, 'String Translations', '', 1),
(40, 'Update Strings', '', 1),
(41, 'String translation update running', '', 1),
(42, 'Create new group', '', 1),
(43, 'Groups %s has been created.', '', 1),
(44, 'Group name', '', 1),
(45, 'Group has been deleted.', '', 1),
(46, 'Delete groups \\''%s\\''?', '', 1),
(47, 'Do you really want the group with the name \\''%s\\''?', '', 1),
(48, 'Yes, delete group', '', 1),
(49, 'Edit group %s', '', 1),
(50, 'Successfully renamed group.', '', 1),
(51, 'Modify %s\\''s Members', '', 1),
(52, 'Add Users by typing their username:', '', 1),
(53, 'Remove', '', 1),
(54, 'remove user', '', 1),
(55, 'Group Management', '', 1),
(56, 'Add new Group', '', 1),
(57, 'Members', '', 1),
(58, 'edit group', '', 1),
(59, 'delete group', '', 1),
(60, 'manage members', '', 1),
(61, 'Four Oh! Four', '', 1),
(62, 'The requested page could not be loaded.', '', 1),
(63, 'Access denied', '', 1),
(64, 'You do not have the required permission to view this page.', '', 1),
(65, 'Login', '', 1),
(66, 'login_intro_text', '', 1),
(67, 'Log in', '', 1),
(68, 'Username and/or password is wrong.', '', 1),
(69, 'Dashboard', '', 1),
(70, 'Sites', '', 1),
(71, 'Navigations', '', 1),
(72, 'Modules', '', 1),
(73, 'Localization', '', 1),
(74, 'Users', '', 1),
(75, 'Groups', '', 1),
(76, 'Permissions', '', 1),
(77, 'Settings', '', 1),
(78, 'Logout', '', 1),
(79, 'The given group name is too short.', '', 1),
(80, 'A group with that name already exists', '', 0),
(81, 'A group with that name already exists.', '', 1),
(82, 'Permission denied', '', 1),
(83, 'Unable to delete a group, where the current user is in.', '', 1),
(84, 'A language with that code already exists.', '', 1),
(85, 'Scanning %s *.php Files', '', 1),
(86, '+ STRING: &lt;%1$s&gt; - <small>FILE:\\''%2$s\\''</small> - <small>LINE:\\''%3$s\\''</small> - DOMAIN:\\''%4$s\\''', 'logs', 0),
(87, 'Could not read file: \\''%s\\''', '', 1),
(88, 'No new strings found.', '', 1),
(89, '- INACTIVE STRING: %s', '', 0),
(90, '+ ACTIVATE STRING: &gt;%s&lt;', '', 0),
(91, 'Nothing has changed, me friend..', '', 1),
(92, 'Translation String update complete.', '', 1),
(93, 'Queried field ', '%1$s'' which does not exist', 1),
(94, 'Permission denied to edit users.', '', 1),
(95, 'The given passwort and the repetition do not match.', '', 1),
(96, 'User with that name already exists', '', 1),
(97, 'User with that email already exists', '', 1),
(98, 'Username is too short.', '', 1),
(99, 'User with that name already exists.', '', 1),
(100, 'Invalid e-mail address.', '', 1),
(101, 'User with that email address already exists.', '', 1),
(102, 'Given password is too short.', '', 1),
(103, 'Create new user', '', 1),
(104, 'Create', '', 1),
(105, 'Save', '', 1),
(106, 'String', '', 1),
(107, 'Translate', '', 1),
(108, 'In use', '', 1),
(109, 'NEW STRING: &lt;%1$s&gt; - <small>FILE:\\''%2$s\\''</small> - <small>LINE:\\''%3$s\\''</small> - DOMAIN:\\''%4$s\\''', 'logs', 1),
(110, 'INACTIVE STRING: %s', '', 1),
(111, 'ACTIVATE STRING: &gt;%s&lt;', '', 1),
(112, 'Translate String', '', 1),
(113, 'Save Translation', '', 1),
(114, 'Do not replace <code>%s</code> or strings like <code>%1$s</code>, these are placeholders and will be filled with actual values.', '', 1),
(115, 'Domain', '', 1),
(116, 'Orignal String', '', 1),
(117, 'Update Translation', '', 1),
(118, 'ACTIVATE STRING: &gt;%s&lt;', 'logs', 1),
(119, 'Site Management', '', 1),
(120, 'Create new Site', '', 1),
(121, 'Title', '', 1),
(122, 'Last Modified', '', 1),
(123, 'Creator', '', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `language_strings_translations`
--

CREATE TABLE IF NOT EXISTS `language_strings_translations` (
`id` int(11) NOT NULL,
  `stringid` int(11) NOT NULL,
  `translation` varchar(1500) NOT NULL,
  `languageid` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `language_strings_translations`
--

INSERT INTO `language_strings_translations` (`id`, `stringid`, `translation`, `languageid`) VALUES
(1, 81, 'Eine Gruppe mit diesem Namen existiert bereits.', 1),
(2, 81, 'A group with that name already exists.', 2),
(3, 39, 'System Übersetzung', 1),
(4, 39, 'String Translations', 2),
(5, 63, 'Zugriff verweigert', 1),
(6, 63, 'Access denied', 2),
(7, 21, 'Aktionen', 1),
(8, 21, 'Actions', 2),
(9, 31, 'Hinzufügen', 1),
(10, 31, 'Add', 2),
(11, 34, 'Sprache hinzufügen', 1),
(12, 34, 'Add language', 2),
(13, 56, 'Neue Gruppe erstellen', 1),
(14, 56, 'Add new Group', 2),
(15, 28, 'Neue Sprache erstellen', 1),
(16, 28, 'Add new Language', 2),
(17, 26, 'Berechtigung hinzufügen', 1),
(18, 26, 'Add Permission', 2),
(19, 111, 'AKTIVIERE STRING: >%s<', 1),
(20, 111, 'ACTIVATE STRING: >%s<', 2),
(21, 118, 'AKTIVIERE STRING: >%s<', 1),
(22, 118, 'ACTIVATE STRING: >%s<', 2),
(23, 19, 'Benutzer hinzufügen', 1),
(24, 19, 'Add user', 2),
(25, 119, 'Seitenverwaltung', 1),
(26, 119, 'Site Management', 2),
(27, 120, 'Neue Seite erstellen', 1),
(28, 120, 'Create new Site', 2),
(29, 122, 'Letzte Änderung', 1),
(30, 122, 'Last Modified', 2),
(31, 67, 'Anmelden', 1),
(32, 67, 'Log in', 2),
(33, 70, 'Seiten', 1),
(34, 70, 'Sites', 2),
(35, 71, 'Navigationen', 1),
(36, 71, 'Navigations', 2),
(37, 72, 'Module', 1),
(38, 72, 'Modules', 2),
(39, 123, 'Ersteller', 1),
(40, 123, 'Creator', 2),
(41, 84, 'Eine Sprache mit diesem Sprachcode existiert bereits.', 1),
(42, 84, 'A language with that code already exists.', 2),
(43, 52, 'Mit dem tippen des Benutzernamens beginnen um weitere hinzuzufügen.', 1),
(44, 52, 'Add Users by typing their username:', 2),
(45, 42, 'Neue Gruppe erstellen', 1),
(46, 42, 'Create new group', 2),
(47, 69, 'Dashboard', 1),
(48, 69, 'Dashboard', 2),
(49, 37, 'Standard', 1),
(50, 37, 'Default', 2),
(51, 22, 'Benutzer löschen', 1),
(52, 22, 'delete user', 2),
(53, 9, 'Benutzer \\''%s\\'' löschen?', 1),
(54, 9, 'Delete user \\''%s\\''?', 2),
(55, 114, 'Du darfst Zeichen wie <code>%s</code> oder <code>%1$s</code> nicht entfernen. Diese sind Platzhalter und werden durch das System mit richtigen Werten ersetzt.', 1),
(56, 114, 'Do not replace <code>%s</code> or strings like <code>%1$s</code>, these are placeholders and will be filled with actual values.', 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
`id` int(7) NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;

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
(47, 'manage.locales.strings.update'),
(48, 'manage.locales.strings.translate'),
(49, 'manage.sites.add');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions_groups`
--

CREATE TABLE IF NOT EXISTS `permissions_groups` (
`id` int(7) NOT NULL,
  `groupid` int(7) NOT NULL,
  `permissionid` int(7) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

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
(25, 1, 46),
(26, 1, 48),
(27, 1, 49);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sites`
--

CREATE TABLE IF NOT EXISTS `sites` (
`id` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `sequence` int(11) NOT NULL,
  `name` varchar(350) NOT NULL,
  `title` varchar(350) NOT NULL,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creator` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Indizes für die Tabelle `language_strings_translations`
--
ALTER TABLE `language_strings_translations`
 ADD PRIMARY KEY (`id`), ADD KEY `stringid` (`stringid`), ADD KEY `languageid` (`languageid`);

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
-- Indizes für die Tabelle `sites`
--
ALTER TABLE `sites`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `parent_2` (`parent`), ADD KEY `parent` (`parent`,`creator`), ADD KEY `creator` (`creator`);

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
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=124;
--
-- AUTO_INCREMENT für Tabelle `language_strings_translations`
--
ALTER TABLE `language_strings_translations`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=57;
--
-- AUTO_INCREMENT für Tabelle `permissions`
--
ALTER TABLE `permissions`
MODIFY `id` int(7) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=50;
--
-- AUTO_INCREMENT für Tabelle `permissions_groups`
--
ALTER TABLE `permissions_groups`
MODIFY `id` int(7) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT für Tabelle `sites`
--
ALTER TABLE `sites`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
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
-- Constraints der Tabelle `language_strings_translations`
--
ALTER TABLE `language_strings_translations`
ADD CONSTRAINT `language_strings_translations_ibfk_1` FOREIGN KEY (`stringid`) REFERENCES `language_strings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `language_strings_translations_ibfk_2` FOREIGN KEY (`languageid`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `permissions_groups`
--
ALTER TABLE `permissions_groups`
ADD CONSTRAINT `permissions_groups_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `permissions_groups_ibfk_2` FOREIGN KEY (`permissionid`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
