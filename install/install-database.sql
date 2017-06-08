-- phpMyAdmin SQL Dump
-- version 4.6.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 08. Jun 2017 um 11:17
-- Server-Version: 5.7.13
-- PHP-Version: 5.6.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `robertodonetto-web`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `keey` varchar(200) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(450) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections`
--

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `type` varchar(500) NOT NULL,
  `author` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collection_categories`
--

CREATE TABLE `collection_categories` (
  `id` int(11) NOT NULL,
  `collection` varchar(150) NOT NULL,
  `meta` text NOT NULL,
  `parent` int(11) NOT NULL,
  `sequence` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collection_meta`
--

CREATE TABLE `collection_meta` (
  `id` int(11) NOT NULL,
  `keyy` varchar(200) NOT NULL,
  `value` text NOT NULL,
  `lang` varchar(10) NOT NULL,
  `item` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collection_settings`
--

CREATE TABLE `collection_settings` (
  `id` int(11) NOT NULL,
  `keyy` varchar(150) NOT NULL,
  `value` text NOT NULL,
  `lang` varchar(20) NOT NULL,
  `type` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups`
--

CREATE TABLE `groups` (
  `id` int(7) NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups_users`
--

CREATE TABLE `groups_users` (
  `id` int(11) NOT NULL,
  `groupid` int(11) NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(250) NOT NULL,
  `default` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `language_strings`
--

CREATE TABLE `language_strings` (
  `id` int(11) NOT NULL,
  `string` varchar(1000) NOT NULL,
  `domain` varchar(100) NOT NULL,
  `used` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `language_strings_translations`
--

CREATE TABLE `language_strings_translations` (
  `id` int(11) NOT NULL,
  `stringid` int(11) NOT NULL,
  `translation` varchar(1500) NOT NULL,
  `languageid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mime` varchar(150) NOT NULL,
  `autor` int(11) NOT NULL,
  `path` varchar(200) NOT NULL,
  `title` varchar(400) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `module` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `navigations`
--

CREATE TABLE `navigations` (
  `id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `position` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `navigation_items`
--

CREATE TABLE `navigation_items` (
  `id` int(11) NOT NULL,
  `item_id` varchar(250) NOT NULL,
  `name` varchar(450) NOT NULL,
  `navigation_id` int(11) NOT NULL,
  `item_type` varchar(300) NOT NULL,
  `order` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `lang` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `sequence` int(11) NOT NULL,
  `name` varchar(350) NOT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `creator` int(11) NOT NULL,
  `url` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `start` int(7) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `page_elements`
--

CREATE TABLE `page_elements` (
  `id` int(7) NOT NULL,
  `pageid` int(7) NOT NULL,
  `elementid` varchar(100) NOT NULL,
  `prefs` text NOT NULL,
  `parent` int(11) NOT NULL,
  `lang` varchar(20) NOT NULL,
  `position` int(11) NOT NULL,
  `position_x` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `page_meta`
--

CREATE TABLE `page_meta` (
  `id` int(7) NOT NULL,
  `keyy` varchar(200) NOT NULL,
  `lang` varchar(11) NOT NULL,
  `value` text NOT NULL,
  `page` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions`
--

CREATE TABLE `permissions` (
  `id` int(7) NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions_groups`
--

CREATE TABLE `permissions_groups` (
  `id` int(7) NOT NULL,
  `groupid` int(7) NOT NULL,
  `permissionid` int(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `permissions_keys`
--

CREATE TABLE `permissions_keys` (
  `id` int(11) NOT NULL,
  `permission` int(11) NOT NULL,
  `keey` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `keey` varchar(500) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`);

--
-- Indizes für die Tabelle `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author` (`author`);

--
-- Indizes für die Tabelle `collection_categories`
--
ALTER TABLE `collection_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `collection_meta`
--
ALTER TABLE `collection_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item` (`item`);

--
-- Indizes für die Tabelle `collection_settings`
--
ALTER TABLE `collection_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `groups_users`
--
ALTER TABLE `groups_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupid` (`groupid`),
  ADD KEY `userid` (`userid`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `stringid` (`stringid`),
  ADD KEY `languageid` (`languageid`);

--
-- Indizes für die Tabelle `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor` (`autor`);

--
-- Indizes für die Tabelle `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `navigations`
--
ALTER TABLE `navigations`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `navigation_items`
--
ALTER TABLE `navigation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `navigation_id` (`navigation_id`);

--
-- Indizes für die Tabelle `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator` (`creator`);

--
-- Indizes für die Tabelle `page_elements`
--
ALTER TABLE `page_elements`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `page_meta`
--
ALTER TABLE `page_meta`
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
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupid` (`groupid`),
  ADD KEY `permissionid` (`permissionid`);

--
-- Indizes für die Tabelle `permissions_keys`
--
ALTER TABLE `permissions_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `keey` (`keey`),
  ADD KEY `permission` (`permission`);

--
-- Indizes für die Tabelle `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT für Tabelle `collections`
--
ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
--
-- AUTO_INCREMENT für Tabelle `collection_categories`
--
ALTER TABLE `collection_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT für Tabelle `collection_meta`
--
ALTER TABLE `collection_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;
--
-- AUTO_INCREMENT für Tabelle `collection_settings`
--
ALTER TABLE `collection_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `groups_users`
--
ALTER TABLE `groups_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT für Tabelle `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `language_strings`
--
ALTER TABLE `language_strings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=604;
--
-- AUTO_INCREMENT für Tabelle `language_strings_translations`
--
ALTER TABLE `language_strings_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;
--
-- AUTO_INCREMENT für Tabelle `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;
--
-- AUTO_INCREMENT für Tabelle `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `navigations`
--
ALTER TABLE `navigations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT für Tabelle `navigation_items`
--
ALTER TABLE `navigation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
--
-- AUTO_INCREMENT für Tabelle `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
--
-- AUTO_INCREMENT für Tabelle `page_elements`
--
ALTER TABLE `page_elements`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;
--
-- AUTO_INCREMENT für Tabelle `page_meta`
--
ALTER TABLE `page_meta`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
--
-- AUTO_INCREMENT für Tabelle `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;
--
-- AUTO_INCREMENT für Tabelle `permissions_groups`
--
ALTER TABLE `permissions_groups`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;
--
-- AUTO_INCREMENT für Tabelle `permissions_keys`
--
ALTER TABLE `permissions_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT für Tabelle `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
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
