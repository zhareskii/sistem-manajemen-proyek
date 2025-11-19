-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 19 Nov 2025 pada 12.37
-- Versi server: 10.11.14-MariaDB-cll-lve-log
-- Versi PHP: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zmanager_zizi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `boards`
--

CREATE TABLE `boards` (
  `board_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `board_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `boards`
--

INSERT INTO `boards` (`board_id`, `project_id`, `board_name`, `created_at`) VALUES
(3, 15, 'Project Status Board', '2025-10-21 00:51:57'),
(4, 16, 'Project Status Board', '2025-10-21 00:52:25'),
(5, 17, 'Project Status Board', '2025-11-10 18:49:48'),
(6, 18, 'Default Board - coba lagi', '2025-11-18 13:53:44'),
(7, 19, 'Default Board - try', '2025-11-18 14:53:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cards`
--

CREATE TABLE `cards` (
  `card_id` int(11) NOT NULL,
  `board_id` int(11) NOT NULL,
  `card_title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `assigned_role` enum('developer','designer') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL,
  `status` enum('todo','in_progress','review','done') DEFAULT 'todo',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `estimated_hours` decimal(5,2) DEFAULT 0.00,
  `actual_hours` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cards`
--

INSERT INTO `cards` (`card_id`, `board_id`, `card_title`, `description`, `position`, `created_by`, `assigned_role`, `created_at`, `due_date`, `status`, `priority`, `estimated_hours`, `actual_hours`) VALUES
(4, 3, 'coba2', 'ssss', 0, 10, 'designer', '2025-10-22 06:46:58', '2025-11-08', 'done', 'medium', 3.00, 27.78),
(6, 5, 'demo', 'ayaaa', 0, 11, 'designer', '2025-11-16 08:54:20', '2025-12-30', 'done', 'medium', 2.00, 0.00),
(7, 4, 'coba', 'bababa', 0, 11, 'developer', '2025-11-16 11:54:41', '2025-11-30', 'done', 'medium', 2.00, 0.00),
(8, 6, 'coba lagi', 'coba lagi lagii ', 0, 10, 'developer', '2025-11-18 05:59:03', '2025-11-19', 'done', 'medium', 2.00, 0.00),
(9, 7, 'try', 'try try', 0, 11, 'designer', '2025-11-18 06:55:28', '2025-11-19', 'done', 'medium', 1.00, 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `card_assignments`
--

CREATE TABLE `card_assignments` (
  `assignment_id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  `assignment_status` enum('assigned','in_progress','completed') DEFAULT 'assigned',
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `card_assignments`
--

INSERT INTO `card_assignments` (`assignment_id`, `card_id`, `user_id`, `assigned_at`, `assignment_status`, `started_at`, `completed_at`) VALUES
(4, 4, 11, '2025-10-22 06:46:58', 'assigned', NULL, NULL),
(7, 6, 10, '2025-11-16 08:54:20', 'assigned', NULL, NULL),
(8, 7, 10, '2025-11-16 11:54:41', 'assigned', NULL, NULL),
(10, 8, 6, '2025-11-18 06:10:16', 'assigned', NULL, NULL),
(11, 9, 10, '2025-11-18 06:55:28', 'assigned', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `card_submissions`
--

CREATE TABLE `card_submissions` (
  `submission_id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `submission_notes` text DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `card_submissions`
--

INSERT INTO `card_submissions` (`submission_id`, `card_id`, `submitted_by`, `reviewer_id`, `status`, `submission_notes`, `review_notes`, `created_at`, `reviewed_at`) VALUES
(1, 4, 10, 9, 'accepted', '', 'sip dehh', '2025-11-13 02:58:30', '2025-11-12 21:18:32'),
(2, 4, 10, 9, 'rejected', '', '', '2025-11-12 21:25:52', '2025-11-13 19:51:57'),
(3, 4, 10, 9, 'accepted', 'Pengajuan Project: lalalala', '', '2025-11-14 01:55:29', '2025-11-14 09:03:02'),
(4, 6, 11, 9, 'accepted', 'Pengajuan Project: demo', '', '2025-11-16 17:01:20', '2025-11-16 09:01:48'),
(5, 6, 11, 9, 'accepted', 'Pengajuan Project: demo', '', '2025-11-16 20:03:06', '2025-11-17 09:00:17'),
(6, 7, 11, 9, 'accepted', 'Pengajuan Project: maasyaallah', '', '2025-11-18 05:45:20', '2025-11-17 21:45:31'),
(7, 8, 10, 9, 'accepted', 'Pengajuan Project: coba lagi', '', '2025-11-18 14:15:00', '2025-11-18 06:15:31'),
(8, 9, 11, 9, 'rejected', 'Pengajuan Project: try', '', '2025-11-18 14:59:04', '2025-11-18 07:05:44'),
(9, 9, 11, 9, 'accepted', 'Pengajuan Project: try', '', '2025-11-19 09:50:52', '2025-11-19 01:51:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `card_id` int(11) DEFAULT NULL,
  `subtask_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `comment_type` enum('card','subtask') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `comments`
--

INSERT INTO `comments` (`comment_id`, `card_id`, `subtask_id`, `user_id`, `comment_text`, `comment_type`, `created_at`) VALUES
(1, NULL, 1, 11, 'halo haloo', 'subtask', '2025-10-26 05:35:36'),
(2, NULL, 1, 11, 'haloo', 'subtask', '2025-10-26 08:54:55'),
(3, NULL, 1, 11, 'p', 'subtask', '2025-10-26 08:55:10'),
(4, NULL, 1, 10, 'hai haii', 'subtask', '2025-11-06 05:12:56'),
(5, NULL, 1, 10, 'oi', 'subtask', '2025-11-09 09:56:14'),
(6, NULL, 1, 11, 'assalamualaikum\r\n', 'subtask', '2025-11-09 10:04:19'),
(7, NULL, 1, 11, 'assalamualaikum\r\n', 'subtask', '2025-11-09 10:04:20'),
(8, NULL, 1, 10, 'biii', 'subtask', '2025-11-09 10:22:47'),
(9, NULL, 1, 10, 'boo', 'subtask', '2025-11-09 10:31:31'),
(10, NULL, 1, 10, 'boo', 'subtask', '2025-11-09 10:31:43'),
(11, NULL, 1, 10, 'boo', 'subtask', '2025-11-09 10:49:25'),
(12, NULL, 8, 10, 'haii', 'subtask', '2025-11-16 08:56:07'),
(13, NULL, 10, 6, 'lancar', 'subtask', '2025-11-18 06:12:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `help_requests`
--

CREATE TABLE `help_requests` (
  `request_id` int(11) NOT NULL,
  `subtask_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `issue_description` text NOT NULL,
  `status` enum('pending','in_progress','fixed','completed') DEFAULT 'pending',
  `resolved_by` int(11) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `help_requests`
--

INSERT INTO `help_requests` (`request_id`, `subtask_id`, `user_id`, `issue_description`, `status`, `resolved_by`, `resolution_notes`, `created_at`, `resolved_at`) VALUES
(1, 1, 11, 'gtoloong', 'completed', 10, NULL, '2025-10-25 07:38:10', '2025-10-27 20:48:59'),
(2, 1, 11, 'hh', 'completed', 10, NULL, '2025-10-26 08:55:17', '2025-10-27 20:38:00'),
(3, 2, 11, 'tolongin aku pwisss', 'completed', 10, NULL, '2025-10-27 20:59:42', '2025-10-27 21:00:23'),
(4, 4, 11, 'tuloong', 'completed', 10, NULL, '2025-10-27 21:29:49', '2025-10-27 21:30:20'),
(5, 1, 11, 'haiii\r\n', 'completed', 10, NULL, '2025-10-28 20:28:34', '2025-11-07 02:33:39'),
(6, 8, 10, 'tolong domg', 'completed', 11, NULL, '2025-11-16 08:56:24', '2025-11-16 16:59:07'),
(7, 8, 10, 'tolong domg', 'completed', 11, NULL, '2025-11-16 08:56:27', '2025-11-16 16:59:05'),
(8, 8, 10, 'tolong domg', 'completed', 11, NULL, '2025-11-16 08:56:29', '2025-11-16 16:59:02'),
(9, 8, 10, 'tolong domg', 'completed', 11, NULL, '2025-11-16 08:56:30', '2025-11-16 16:58:55'),
(10, 8, 10, 'tolong domg', 'completed', 11, NULL, '2025-11-16 08:56:30', '2025-11-16 16:58:57'),
(11, 8, 10, 'tolong domg', 'completed', 11, NULL, '2025-11-16 08:56:30', '2025-11-16 16:58:59'),
(12, 8, 10, 'tolong domg', 'completed', 11, NULL, '2025-11-16 08:56:40', '2025-11-16 16:58:53'),
(13, 8, 10, 'tolong domg', 'completed', 11, NULL, '2025-11-16 08:56:47', '2025-11-16 16:58:50'),
(14, 9, 10, 'tuloong', 'completed', 11, NULL, '2025-11-16 11:56:42', '2025-11-16 19:58:13'),
(15, 9, 10, 'tuloong', 'completed', 11, NULL, '2025-11-16 11:56:44', '2025-11-16 19:58:10'),
(16, 9, 10, 'tuloong', 'completed', 11, NULL, '2025-11-16 11:56:53', '2025-11-16 19:58:01'),
(17, 10, 6, 'bismillah', 'completed', 10, NULL, '2025-11-18 06:12:29', '2025-11-18 14:13:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1760264144),
('m240000_000001_create_submission_tables', 1762894901);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('task_assigned','task_updated','status_changed','deadline_reminder','comment_added','project_update') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `related_type` enum('project','card','subtask') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `team_lead_id` int(11) DEFAULT NULL,
  `difficulty_level` enum('easy','medium','hard') NOT NULL,
  `status` enum('planning','active','completed','cancelled') DEFAULT 'planning',
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `deadline` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `projects`
--

INSERT INTO `projects` (`project_id`, `project_name`, `description`, `created_by`, `team_lead_id`, `difficulty_level`, `status`, `progress_percentage`, `created_at`, `deadline`) VALUES
(15, 'lalalala', 'nananana', 9, 10, 'easy', 'completed', 100.00, '2025-10-21 00:51:57', '2025-12-06'),
(16, 'maasyaallah', 'alhamdulillah', 9, 11, 'easy', 'completed', 100.00, '2025-10-21 00:52:25', '2025-12-27'),
(17, 'demo', 'demo demo', 9, 11, 'easy', 'completed', 100.00, '2025-11-11 01:49:48', '2025-12-02'),
(18, 'coba lagi', 'commit di repo git http://github.com/nananana', 9, 10, 'medium', 'completed', 100.00, '2025-11-18 05:53:44', '2025-12-06'),
(19, 'try', 'try try', 9, 11, 'medium', 'completed', 100.00, '2025-11-18 06:53:59', '2025-11-20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `project_members`
--

CREATE TABLE `project_members` (
  `member_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `project_members`
--

INSERT INTO `project_members` (`member_id`, `project_id`, `user_id`, `joined_at`) VALUES
(5, 15, 11, '2025-10-22 06:46:58'),
(14, 17, 10, '2025-11-16 08:54:20'),
(15, 16, 10, '2025-11-16 11:54:41'),
(19, 19, 10, '2025-11-18 06:55:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subtasks`
--

CREATE TABLE `subtasks` (
  `subtask_id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `subtask_title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('todo','in_progress','done') DEFAULT 'todo',
  `estimated_hours` decimal(5,2) DEFAULT 0.00,
  `actual_hours` decimal(5,2) DEFAULT 0.00,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `subtasks`
--

INSERT INTO `subtasks` (`subtask_id`, `card_id`, `subtask_title`, `description`, `status`, `estimated_hours`, `actual_hours`, `position`, `created_by`, `created_at`) VALUES
(1, 4, 'asikk', 'asikkk', 'done', 5.00, 3.43, 0, 11, '2025-10-24 05:45:09'),
(2, 4, 'bismillah', 'alhamdulillah', 'done', 3.00, 24.48, 0, 11, '2025-10-24 06:01:06'),
(4, 4, 'coba', 'aaaaa', 'done', 3.00, 0.00, 0, 11, '2025-10-27 21:29:29'),
(5, 4, 'llllllll', 'gagagaga', 'done', 2.00, 0.00, 0, 11, '2025-11-12 21:20:40'),
(6, 4, 'bismillah2', '1234567', 'done', 2.00, 0.00, 0, 11, '2025-11-12 21:49:13'),
(7, 4, 'dudung', 'diding', 'done', 1.00, 0.00, 0, 11, '2025-11-13 21:21:31'),
(8, 6, 'ayaa', 'lululuu', 'done', 2.00, 0.00, 0, 10, '2025-11-16 08:55:20'),
(9, 7, 'ayayayay', 'ngungungu', 'done', 1.00, 0.00, 0, 10, '2025-11-16 11:55:38'),
(10, 8, 'coba lagi', 'lagi lagi coba', 'done', 1.00, 0.00, 0, 6, '2025-11-18 06:12:03'),
(11, 9, 'try', 'try', 'done', 1.00, 0.00, 0, 10, '2025-11-18 06:58:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subtask_submissions`
--

CREATE TABLE `subtask_submissions` (
  `submission_id` int(11) NOT NULL,
  `subtask_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `submission_notes` text DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `subtask_submissions`
--

INSERT INTO `subtask_submissions` (`submission_id`, `subtask_id`, `submitted_by`, `reviewer_id`, `status`, `submission_notes`, `review_notes`, `created_at`, `reviewed_at`) VALUES
(1, 4, 11, 10, 'rejected', '', 'elek', '2025-11-12 06:48:57', '2025-11-12 06:49:24'),
(2, 4, 11, 10, 'accepted', 'Pengajuan ulang setelah ditolak', 'sip', '2025-11-12 06:49:42', '2025-11-12 08:33:41'),
(3, 2, 11, 10, 'accepted', '', '', '2025-11-13 02:54:22', '2025-11-13 02:55:57'),
(4, 5, 11, 10, 'accepted', '', '', '2025-11-13 04:25:01', '2025-11-13 04:25:26'),
(5, 6, 11, 10, 'accepted', '', '', '2025-11-13 04:49:48', '2025-11-13 04:50:30'),
(6, 7, 11, 10, 'accepted', '', '', '2025-11-14 04:25:34', '2025-11-14 04:26:47'),
(7, 8, 10, 11, 'accepted', '', '', '2025-11-16 08:59:28', '2025-11-16 08:59:42'),
(8, 9, 10, 11, 'rejected', '', '', '2025-11-16 12:00:30', '2025-11-16 12:01:11'),
(9, 9, 10, 11, 'accepted', 'Pengajuan ulang setelah ditolak', '', '2025-11-16 12:01:33', '2025-11-16 12:02:51'),
(10, 10, 6, 10, 'accepted', '', '', '2025-11-18 06:14:04', '2025-11-18 06:14:45'),
(11, 11, 10, 11, 'accepted', '', '', '2025-11-18 06:58:26', '2025-11-18 06:58:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `time_logs`
--

CREATE TABLE `time_logs` (
  `log_id` int(11) NOT NULL,
  `card_id` int(11) DEFAULT NULL,
  `subtask_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `time_logs`
--

INSERT INTO `time_logs` (`log_id`, `card_id`, `subtask_id`, `user_id`, `start_time`, `end_time`, `duration_minutes`, `description`, `created_at`) VALUES
(1, 4, 2, 11, '2025-10-24 06:15:00', '2025-10-24 06:15:05', 0, 'bismillah', '2025-10-23 23:15:00'),
(2, 4, 2, 11, '2025-10-24 06:15:05', '2025-10-24 06:15:13', 0, 'bismillah', '2025-10-23 23:15:05'),
(3, 4, 2, 11, '2025-10-24 06:15:13', '2025-10-25 06:44:19', 1469, 'asikk', '2025-10-23 23:15:13'),
(4, 4, 1, 11, '2025-10-25 06:44:19', '2025-10-25 06:44:42', 0, 'asikk', '2025-10-24 23:44:19'),
(5, 4, 1, 11, '2025-10-25 06:44:42', '2025-10-25 07:09:46', 25, 'asikk', '2025-10-24 23:44:42'),
(6, 4, 1, 11, '2025-10-25 19:53:09', '2025-10-25 19:53:17', 0, 'asikk', '2025-10-25 12:53:09'),
(7, 4, 1, 11, '2025-10-25 19:53:17', '2025-10-25 20:50:36', 57, 'asikk', '2025-10-25 12:53:17'),
(8, 4, 1, 11, '2025-10-26 04:28:23', '2025-10-26 04:28:29', 0, 'asikk', '2025-10-25 21:28:23'),
(9, 4, 1, 11, '2025-10-26 04:28:36', '2025-10-26 05:23:31', 55, 'asikk', '2025-10-25 21:28:36'),
(10, 4, 2, 11, '2025-10-26 05:23:48', '2025-10-26 05:23:51', 0, 'bismillah', '2025-10-25 22:23:48'),
(11, 4, 1, 11, '2025-10-26 05:35:48', '2025-10-26 05:35:52', 0, 'Working on: asikk', '2025-10-25 22:35:48'),
(12, 4, 1, 11, '2025-10-26 06:32:20', '2025-10-26 06:32:21', 0, 'Working on: asikk', '2025-10-25 23:32:20'),
(13, 4, 1, 11, '2025-10-26 06:47:21', '2025-10-26 06:57:06', 10, 'Working on: asikk', '2025-10-25 23:47:21'),
(14, 4, 1, 11, '2025-10-26 08:03:53', '2025-10-26 08:03:59', 0, 'Working on: asikk', '2025-10-26 01:03:53'),
(15, 4, 1, 11, '2025-10-26 08:04:02', '2025-10-26 08:54:42', 51, 'Working on: asikk', '2025-10-26 01:04:02'),
(16, 4, 2, 11, '2025-10-27 20:58:51', '2025-10-27 20:58:52', 0, 'bismillah', '2025-10-27 13:58:51'),
(17, 4, 1, 11, '2025-10-28 20:28:53', '2025-10-28 20:36:26', 8, 'asikk', '2025-10-28 13:28:53'),
(18, NULL, 2, 10, '2025-11-07 11:17:21', '2025-11-08 10:58:27', NULL, NULL, '2025-11-06 21:17:21'),
(19, NULL, 4, 10, '2025-11-08 10:57:40', '2025-11-08 10:59:13', NULL, 'Testing timer', '2025-11-08 03:57:40'),
(20, NULL, 4, 10, '2025-11-08 10:59:58', '2025-11-16 16:55:32', 11876, 'Testing timer again', '2025-11-08 03:59:58'),
(21, 4, 1, 11, '2025-11-09 10:03:38', '2025-11-09 10:19:32', 16, 'asikk', '2025-11-09 03:03:38'),
(22, 4, 1, 11, '2025-11-09 10:20:16', '2025-11-09 10:21:10', 1, 'asikk', '2025-11-09 03:20:16'),
(23, 4, 2, 11, '2025-11-11 00:26:43', '2025-11-11 00:26:50', 0, 'bismillah', '2025-11-10 17:26:43'),
(24, 4, 2, 11, '2025-11-11 00:26:54', '2025-11-11 00:27:13', 0, 'bismillah', '2025-11-10 17:26:54'),
(25, 4, 1, 11, '2025-11-11 01:54:07', '2025-11-12 21:21:09', 2607, 'asikk', '2025-11-10 18:54:07'),
(26, 4, 5, 11, '2025-11-12 21:23:52', '2025-11-12 21:24:45', 1, 'llllllll', '2025-11-12 14:23:52'),
(27, 4, 6, 11, '2025-11-12 21:49:21', '2025-11-12 21:49:26', 0, 'bismillah2', '2025-11-12 14:49:21'),
(28, 4, 6, 11, '2025-11-12 21:49:28', '2025-11-12 21:49:28', 0, 'bismillah2', '2025-11-12 14:49:28'),
(29, 4, 7, 11, '2025-11-13 21:21:40', '2025-11-13 21:21:48', 0, 'dudung', '2025-11-13 14:21:40'),
(30, 4, 4, 10, '2025-11-16 16:55:40', '2025-11-16 16:55:47', 0, 'coba', '2025-11-16 16:55:40'),
(31, 6, 8, 10, '2025-11-16 16:55:47', '2025-11-16 16:56:02', 0, 'ayaa', '2025-11-16 16:55:47'),
(32, 7, 9, 10, '2025-11-16 19:55:45', '2025-11-18 14:58:14', 2582, 'ayayayay', '2025-11-16 19:55:45'),
(33, 8, 10, 6, '2025-11-18 14:12:09', NULL, NULL, 'coba lagi', '2025-11-18 14:12:09'),
(34, 9, 11, 10, '2025-11-18 14:58:14', NULL, NULL, 'try', '2025-11-18 14:58:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `time_tracking`
--

CREATE TABLE `time_tracking` (
  `tracking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `time_tracking`
--

INSERT INTO `time_tracking` (`tracking_id`, `user_id`, `start_time`, `end_time`, `duration_minutes`, `created_at`) VALUES
(1, 10, '2025-11-07 23:28:52', '2025-11-07 23:49:58', 21, '2025-11-07 23:28:52'),
(2, 10, '2025-11-07 23:49:58', '2025-11-08 00:03:19', 13, '2025-11-07 23:49:58'),
(3, 10, '2025-11-08 00:03:19', '2025-11-08 00:04:59', 2, '2025-11-08 00:03:19'),
(4, 10, '2025-11-08 00:04:59', '2025-11-08 04:42:34', 278, '2025-11-08 00:04:59'),
(5, 10, '2025-11-08 04:42:34', '2025-11-08 05:03:06', 21, '2025-11-08 04:42:34'),
(6, 10, '2025-11-08 05:03:06', '2025-11-08 05:03:31', 0, '2025-11-08 05:03:06'),
(7, 10, '2025-11-08 05:03:31', '2025-11-08 05:11:55', 8, '2025-11-08 05:03:31'),
(8, 10, '2025-11-08 05:11:55', '2025-11-08 05:27:07', 15, '2025-11-08 05:11:55'),
(9, 10, '2025-11-08 05:27:07', '2025-11-08 05:31:40', 5, '2025-11-08 05:27:07'),
(10, 10, '2025-11-08 05:31:40', '2025-11-08 05:46:26', 15, '2025-11-08 05:31:40'),
(11, 10, '2025-11-08 05:46:26', '2025-11-08 05:46:32', 434, '2025-11-08 05:46:26'),
(12, 10, '2025-11-08 05:46:45', '2025-11-08 05:46:49', 0, '2025-11-08 05:46:45'),
(13, 10, '2025-11-08 06:45:49', '2025-11-08 07:14:15', 3, '2025-11-08 06:45:49'),
(14, 10, '2025-11-09 10:50:52', '2025-11-09 11:00:43', 1, '2025-11-09 10:50:52'),
(15, 10, '2025-11-09 11:00:54', '2025-11-09 11:01:07', 0, '2025-11-09 11:00:54'),
(16, 11, '2025-11-11 00:24:11', '2025-11-11 00:26:33', 0, '2025-11-11 00:24:11'),
(17, 10, '2025-11-11 01:50:54', '2025-11-11 01:51:08', 0, '2025-11-11 01:50:54'),
(18, 11, '2025-11-11 23:50:06', '2025-11-11 23:50:49', 0, '2025-11-11 23:50:06'),
(19, 10, '2025-11-13 00:59:28', '2025-11-13 01:03:29', 4, '2025-11-13 00:59:28'),
(20, 11, '2025-11-13 04:05:27', '2025-11-13 04:05:31', 0, '2025-11-13 04:05:27'),
(21, 11, '2025-11-13 04:06:00', '2025-11-13 04:06:02', 0, '2025-11-13 04:06:00'),
(22, 11, '2025-11-13 04:11:09', '2025-11-13 04:12:22', 1, '2025-11-13 04:11:09'),
(23, 11, '2025-11-13 04:26:48', '2025-11-13 04:29:34', 3, '2025-11-13 04:26:48'),
(24, 11, '2025-11-13 20:10:20', '2025-11-13 20:10:25', 0, '2025-11-13 20:10:20'),
(25, 10, '2025-11-14 05:41:55', '2025-11-14 05:41:59', 0, '2025-11-14 05:41:55'),
(26, 10, '2025-11-16 16:51:31', '2025-11-16 16:51:42', 0, '2025-11-16 08:51:31'),
(27, 14, '2025-11-16 17:42:01', '2025-11-16 17:42:06', 0, '2025-11-16 09:42:01'),
(28, 11, '2025-11-16 19:50:22', '2025-11-16 19:51:28', 1, '2025-11-16 11:50:22'),
(29, 11, '2025-11-16 19:51:35', '2025-11-17 16:59:08', 1267, '2025-11-16 11:51:35'),
(30, 19, '2025-11-18 09:40:03', '2025-11-18 09:40:09', 0, '2025-11-18 01:40:03'),
(31, 19, '2025-11-18 09:40:10', '2025-11-18 09:40:11', 0, '2025-11-18 01:40:10'),
(32, 19, '2025-11-18 09:40:12', '2025-11-18 09:40:57', 0, '2025-11-18 01:40:12'),
(33, 19, '2025-11-18 09:41:39', '2025-11-18 09:41:42', 0, '2025-11-18 01:41:39'),
(34, 19, '2025-11-18 09:41:45', '2025-11-18 09:41:57', 0, '2025-11-18 01:41:45'),
(35, 11, '2025-11-18 09:48:02', '2025-11-18 09:48:13', 0, '2025-11-18 01:48:02'),
(36, 11, '2025-11-18 13:54:55', '2025-11-18 14:43:38', 48, '2025-11-18 05:54:55'),
(37, 6, '2025-11-18 14:11:42', '2025-11-18 14:11:44', 0, '2025-11-18 06:11:42'),
(38, 10, '2025-11-18 14:13:13', '2025-11-18 14:13:18', 0, '2025-11-18 06:13:13'),
(39, 6, '2025-11-18 14:13:52', NULL, NULL, '2025-11-18 06:13:52'),
(40, 10, '2025-11-18 14:14:27', '2025-11-18 15:49:50', 95, '2025-11-18 06:14:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `profile_picture` blob DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `current_task_status` enum('idle','working') DEFAULT 'idle',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `role`, `profile_picture`, `created_at`, `current_task_status`, `is_active`) VALUES
(6, 'zhazha', '$2y$13$S6A5R/zPqejuqeL8TXVID.LgiALWThN918rsJjG9DksQ65xMGehZm', 'Arifzha Reski', 'zhazha@gmail.com', 'member', NULL, '2025-09-21 13:26:48', 'working', 1),
(7, 'isal', '$2y$13$mg26H/RwRwUQ9or2xV5jlOMjSS/BL3M4/kwSQBbo5r11FMuXqUTtm', 'isal isal', 'isal@gmail.com', 'admin', NULL, '2025-10-11 15:27:19', 'idle', 1),
(8, 'admin1', '$2y$13$8U.4NOkU58VIFKTezfBKYeaK0uk4cXlIx0FIYH0btIMSKhIyx2JoG', 'admin1', 'admin123@gmail.com', 'member', NULL, '2025-10-15 13:51:58', 'idle', 1),
(9, 'ana', '$2y$13$rM6b3LJDGTomlsgDnyP53OIUOYhUAiiRGPrWHk1.SOwtQLukwOYTS', 'ana jariyatun', 'ana@gmail.com', 'admin', NULL, '2025-10-19 01:23:57', 'idle', 1),
(10, 'lala', '$2y$13$1cqOGjYddf8wVUniaDu4fe57dXHRM5nWcFUP8hubAazSX2JoQWhA.', 'lala lia', 'lala@yahoo.com', 'member', NULL, '2025-10-19 08:45:51', 'idle', 1),
(11, 'qisra', '$2y$13$PWV4W1W/SlDprM9MKTt8qOdyswLdR1yp1YXnHF59fm9Dhck.ZN6Ca', 'ara ura', 'ara@gmail.com', 'member', NULL, '2025-10-20 19:09:42', 'idle', 1),
(12, 'apinn', '$2y$13$nDG8X2.mDb5/nmEQJnMRnOWi8bYlkVkz5jOJIE36bOIXjU4BM6wuC', 'apin zudi', 'apin@gmail.com', 'member', NULL, '2025-10-20 23:29:15', 'idle', 1),
(13, 'fitri', '$2y$13$YBw3GiDoLLcEvfTyspUqcOLVThsig6cAc4.LVZhdQn2B1qnRE9P.O', 'safitri', 'fitri@gmail.com', 'admin', NULL, '2025-10-21 13:28:45', 'idle', 1),
(14, 'fitri ridho', '$2y$13$2EzWnyV4M2Nz4LZhfZyrh.7gpVbXFvRyPYljjqilFP9jIYANVShyq', 'mimimimi', 'fitriridho@gmail.com', 'member', NULL, '2025-11-16 17:41:41', 'idle', 1),
(15, 'ciwi', '$2y$13$vvBzHtvUiu3jXXiRmShSeef8MSJLt7O0Hxy4zPAvOwSe9m5EeHvM.', 'ciwiciwi', 'ciwi@gmail.com', 'member', NULL, '2025-11-16 17:52:44', 'idle', 1),
(16, 'Mugi', '$2y$13$9aIK72YjyLWyZ9CLaXLTr.IdbUbevTc7LYPE2ixgWUEViiw1Ql1sW', 'Mugiiii', 'mugilay3@gmail.com', 'member', NULL, '2025-11-16 20:10:35', 'idle', 1),
(17, 'geluhhehe', '$2y$13$K0x5VkKbE38RAwNGgFepC.PevbDgan/hL0dx0vK2BnRF1Z4WsL.WC', 'geluh hehe', 'galuh@gmail.com', 'member', NULL, '2025-11-16 20:14:55', 'idle', 1),
(18, 'Budi', '$2y$13$Gdy9zjPuKcTolouVPYPJPe81yXuJmcvULmk0R9gzShfYRYFnbk8xa', 'Budi susanti', 'absd@gmail.com', 'member', NULL, '2025-11-16 20:21:25', 'idle', 1),
(19, 'Parikesit', '$2y$13$jO2lFug5Hm6fcjOk556nveCXGLs/dpHjpRkcAzo761T3gW7jskK/W', 'Satrio Parikesit', 'satrioparikesit88@gmail.com', 'member', NULL, '2025-11-18 09:39:44', 'working', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `boards`
--
ALTER TABLE `boards`
  ADD PRIMARY KEY (`board_id`),
  ADD KEY `idx_boards_project` (`project_id`);

--
-- Indeks untuk tabel `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`card_id`),
  ADD KEY `idx_cards_board` (`board_id`),
  ADD KEY `idx_cards_created_by` (`created_by`),
  ADD KEY `idx_cards_status` (`status`),
  ADD KEY `idx_cards_assigned_role` (`assigned_role`);

--
-- Indeks untuk tabel `card_assignments`
--
ALTER TABLE `card_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `idx_card_assignments_card` (`card_id`),
  ADD KEY `idx_card_assignments_user` (`user_id`),
  ADD KEY `idx_card_assignments_status` (`assignment_status`);

--
-- Indeks untuk tabel `card_submissions`
--
ALTER TABLE `card_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `fk_card_submission_card` (`card_id`),
  ADD KEY `fk_card_submission_submitter` (`submitted_by`),
  ADD KEY `fk_card_submission_reviewer` (`reviewer_id`);

--
-- Indeks untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_comments_card` (`card_id`),
  ADD KEY `idx_comments_subtask` (`subtask_id`);

--
-- Indeks untuk tabel `help_requests`
--
ALTER TABLE `help_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `subtask_id` (`subtask_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indeks untuk tabel `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_read` (`is_read`),
  ADD KEY `idx_notifications_type` (`type`);

--
-- Indeks untuk tabel `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_projects_team_lead` (`team_lead_id`),
  ADD KEY `idx_projects_status` (`status`);

--
-- Indeks untuk tabel `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `unique_project_user` (`project_id`,`user_id`),
  ADD KEY `idx_project_members_project` (`project_id`),
  ADD KEY `idx_project_members_user` (`user_id`);

--
-- Indeks untuk tabel `subtasks`
--
ALTER TABLE `subtasks`
  ADD PRIMARY KEY (`subtask_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_subtasks_card` (`card_id`);

--
-- Indeks untuk tabel `subtask_submissions`
--
ALTER TABLE `subtask_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `fk_subtask_submission_subtask` (`subtask_id`),
  ADD KEY `fk_subtask_submission_submitter` (`submitted_by`),
  ADD KEY `fk_subtask_submission_reviewer` (`reviewer_id`);

--
-- Indeks untuk tabel `time_logs`
--
ALTER TABLE `time_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_time_logs_card` (`card_id`),
  ADD KEY `idx_time_logs_subtask` (`subtask_id`),
  ADD KEY `idx_time_logs_user` (`user_id`);

--
-- Indeks untuk tabel `time_tracking`
--
ALTER TABLE `time_tracking`
  ADD PRIMARY KEY (`tracking_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_start_time` (`start_time`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_active` (`is_active`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `boards`
--
ALTER TABLE `boards`
  MODIFY `board_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `cards`
--
ALTER TABLE `cards`
  MODIFY `card_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `card_assignments`
--
ALTER TABLE `card_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `card_submissions`
--
ALTER TABLE `card_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `help_requests`
--
ALTER TABLE `help_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `project_members`
--
ALTER TABLE `project_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `subtasks`
--
ALTER TABLE `subtasks`
  MODIFY `subtask_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `subtask_submissions`
--
ALTER TABLE `subtask_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `time_logs`
--
ALTER TABLE `time_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `time_tracking`
--
ALTER TABLE `time_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `boards`
--
ALTER TABLE `boards`
  ADD CONSTRAINT `boards_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cards`
--
ALTER TABLE `cards`
  ADD CONSTRAINT `cards_ibfk_1` FOREIGN KEY (`board_id`) REFERENCES `boards` (`board_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cards_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `card_assignments`
--
ALTER TABLE `card_assignments`
  ADD CONSTRAINT `card_assignments_ibfk_1` FOREIGN KEY (`card_id`) REFERENCES `cards` (`card_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `card_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `card_submissions`
--
ALTER TABLE `card_submissions`
  ADD CONSTRAINT `fk_card_submission_card` FOREIGN KEY (`card_id`) REFERENCES `cards` (`card_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_card_submission_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_card_submission_submitter` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`subtask_id`) REFERENCES `subtasks` (`subtask_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`team_lead_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `subtasks`
--
ALTER TABLE `subtasks`
  ADD CONSTRAINT `subtasks_ibfk_1` FOREIGN KEY (`card_id`) REFERENCES `cards` (`card_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subtasks_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `subtask_submissions`
--
ALTER TABLE `subtask_submissions`
  ADD CONSTRAINT `fk_subtask_submission_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subtask_submission_submitter` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subtask_submission_subtask` FOREIGN KEY (`subtask_id`) REFERENCES `subtasks` (`subtask_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `time_logs`
--
ALTER TABLE `time_logs`
  ADD CONSTRAINT `time_logs_ibfk_1` FOREIGN KEY (`card_id`) REFERENCES `cards` (`card_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `time_logs_ibfk_2` FOREIGN KEY (`subtask_id`) REFERENCES `subtasks` (`subtask_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `time_logs_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `time_tracking`
--
ALTER TABLE `time_tracking`
  ADD CONSTRAINT `fk_time_tracking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
