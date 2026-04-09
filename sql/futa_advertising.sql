-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 19, 2026 lúc 04:54 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `futa_advertising`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `activity_logs` 
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `meta` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `meta`, `ip`, `created_at`) VALUES
(1, 1, 'Xóa dự án ID: 1', 'Projects', NULL, '::1', '2025-12-31 02:46:46'),
(2, 1, 'Xóa dự án ID: 2', 'Projects', NULL, '::1', '2025-12-31 02:46:48'),
(3, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2025-12-31 03:18:18'),
(4, 1, 'Thêm người dùng: Hưng Nguyễn', 'Users', NULL, '::1', '2025-12-31 03:18:53'),
(5, 1, 'Đăng xuất hệ thống', 'Authentication', NULL, '::1', '2025-12-31 04:31:00'),
(6, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2025-12-31 04:31:36'),
(7, 1, 'Cập nhật người dùng: Hưng Nguyễn', 'Users', NULL, '::1', '2025-12-31 04:31:57'),
(8, 1, 'Đăng xuất hệ thống', 'Authentication', NULL, '::1', '2025-12-31 04:32:02'),
(9, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2025-12-31 04:33:10'),
(10, 1, 'Cập nhật người dùng: Hưng Nguyễn', 'Users', NULL, '::1', '2025-12-31 04:33:29'),
(11, 1, 'Đăng xuất hệ thống', 'Authentication', NULL, '::1', '2025-12-31 04:33:31'),
(12, 2, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2025-12-31 04:33:38'),
(13, 2, 'Đăng xuất hệ thống', 'Authentication', NULL, '::1', '2025-12-31 04:33:53'),
(14, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2025-12-31 04:34:04'),
(15, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2026-01-05 01:10:00'),
(16, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2026-01-06 02:31:34'),
(17, 1, 'Cập nhật tin tuyển dụng ID: 1', 'Recruitments', NULL, '::1', '2026-01-06 02:31:45'),
(18, 1, 'Cập nhật tin tuyển dụng ID: 2', 'Recruitments', NULL, '::1', '2026-01-06 02:31:50'),
(19, 1, 'Cập nhật tin tuyển dụng ID: 3', 'Recruitments', NULL, '::1', '2026-01-06 02:31:57'),
(20, 1, 'Thêm slide carousel mới (ID: 1) (ID: 1)', 'Carousel Slides', NULL, '::1', '2026-01-06 03:07:34'),
(21, 1, 'Thêm slide carousel mới (ID: 2) (ID: 2)', 'Carousel Slides', NULL, '::1', '2026-01-06 03:07:41'),
(22, 1, 'Cập nhật slide carousel ID: 1', 'Carousel Slides', NULL, '::1', '2026-01-06 03:08:30'),
(23, 1, 'Cập nhật slide carousel ID: 2', 'Carousel Slides', NULL, '::1', '2026-01-06 03:08:34'),
(24, 1, 'Thêm slide carousel mới (ID: 3) (ID: 3)', 'Carousel Slides', NULL, '::1', '2026-01-06 03:36:46'),
(25, 1, 'Cập nhật slide carousel ID: 3', 'Carousel Slides', NULL, '::1', '2026-01-06 03:36:53'),
(26, 1, 'Cập nhật slide carousel ID: 3', 'Carousel Slides', NULL, '::1', '2026-01-06 03:36:55'),
(27, 1, 'Cập nhật slide carousel ID: 3', 'Carousel Slides', NULL, '::1', '2026-01-06 03:36:56'),
(28, 1, 'Thêm slide carousel mới (ID: 4) (ID: 4)', 'Carousel Slides', NULL, '::1', '2026-01-06 03:37:06'),
(29, 1, 'Cập nhật slide carousel ID: 1', 'Carousel Slides', NULL, '::1', '2026-01-06 03:37:11'),
(30, 1, 'Cập nhật slide carousel ID: 2', 'Carousel Slides', NULL, '::1', '2026-01-06 03:37:16'),
(31, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2026-01-09 01:06:43'),
(32, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2026-01-12 02:44:21'),
(33, 1, 'Đăng nhập hệ thống', 'Authentication', NULL, '::1', '2026-01-19 02:06:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `cv_file` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `intro_video` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `applications`
--

INSERT INTO `applications` (`id`, `fullname`, `email`, `phone`, `position`, `message`, `cv_file`, `profile_image`, `intro_video`, `created_at`) VALUES
(1, 'Nguyen Van A', 'a@example.com', '0123456789', 'Developer', 'Ứng tuyển vị trí Dev', 'uploads/cv/demo-cv.pdf', NULL, NULL, '2025-12-31 02:45:58'),
(2, 'Le Thi B', 'b@example.com', '0987654321', 'Designer', 'Ứng tuyển vị trí Designer', 'uploads/cv/demo-cv.pdf', NULL, NULL, '2025-12-31 02:45:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carousel_slides`
--

CREATE TABLE `carousel_slides` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `carousel_slides`
--

INSERT INTO `carousel_slides` (`id`, `image_path`, `title`, `description`, `button_text`, `button_link`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'uploads/carousel/695c7c76b0c41-TONG-HOP.png', NULL, NULL, NULL, NULL, 0, 'active', '2026-01-06 03:07:34', '2026-01-06 03:37:11'),
(2, 'uploads/carousel/695c7c7dd5192-QC_XE.png', NULL, NULL, NULL, NULL, 1, 'active', '2026-01-06 03:07:41', '2026-01-06 03:37:16'),
(3, 'uploads/carousel/695c834e75e08-snapedit_1767670549618.png', NULL, NULL, NULL, NULL, 2, 'active', '2026-01-06 03:36:46', '2026-01-06 03:36:53'),
(4, 'uploads/carousel/695c836284512-snapedit_1767670574435.png', NULL, NULL, NULL, NULL, 3, 'active', '2026-01-06 03:37:06', '2026-01-06 03:37:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','replied') NOT NULL DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `contact`
--

INSERT INTO `contact` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`, `status`) VALUES
(1, 'Hưng Nguyễn', 'hung@gmail.com', '095676251', 'Quảng Cáo Trên Xe Tuyến, Xe Buýt', 'Tôi quan tâm để quảng cáo trên xe và trên màn hình led', '2026-01-19 02:08:15', 'replied');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_file` varchar(255) DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `industry`, `position`, `branch`, `description`, `description_file`, `status`, `created_at`) VALUES
(1, 'Nhân viên Marketing Online', 'Marketing', 'Nhân viên', 'Hồ Chí Minh', 'Mô tả công việc:\nPhụ trách triển khai các chiến dịch marketing online.\n\nNơi làm việc: Hồ Chí Minh\nMức lương: \nSố lượng: 1\nHạn nộp hồ sơ: ', NULL, 'closed', '2025-12-31 02:45:58'),
(2, 'Trưởng nhóm Kinh doanh', 'Kinh doanh', 'Trưởng nhóm', 'Hà Nội', 'Mô tả công việc:\nQuản lý đội ngũ kinh doanh và phát triển thị trường.\n\nNơi làm việc: Hà Nội\nMức lương: \nSố lượng: 1\nHạn nộp hồ sơ: ', NULL, 'closed', '2025-12-31 02:45:58'),
(3, 'Thiết kế Đồ họa', 'Thiết kế', 'Nhân viên', 'Hồ Chí Minh', 'Mô tả công việc:\nThiết kế ấn phẩm truyền thông, banner, KV.\n\nNơi làm việc: Hồ Chí Minh\nMức lương: \nSố lượng: 1\nHạn nộp hồ sơ: ', NULL, 'closed', '2025-12-31 02:45:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 1, 'contact', 'Có liên hệ mới từ: Hưng Nguyễn', 'view_contact.php?id=1', 1, '2026-01-19 02:08:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `tags` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content`, `image`, `attachments`, `video`, `status`, `tags`, `created_by`, `created_at`) VALUES
(1, 'ĐỊNH VỊ THƯƠNG HIỆU MẠNH MẼ - PHỦ SÓNG ĐƯỜNG DÀI', 'dinh-vi-thuong-hieu-manh-me-phu-song-duong-dai', '', '<div>\r\n<div dir=\"auto\">Quảng c&aacute;o tr&ecirc;n xe tuyến Phương Trang kh&ocirc;ng chỉ l&agrave; hiển thị, m&agrave; l&agrave; lặp lại &ndash; ghi nhớ v&agrave; định vị l&acirc;u d&agrave;i. Với k&iacute;ch thước quảng c&aacute;o lớn, h&igrave;nh ảnh thương hiệu lu&ocirc;n nổi bật v&agrave; thu h&uacute;t &aacute;nh nh&igrave;n ở mọi nơi.</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tf4/1/16/2728.png\" alt=\"✨\" width=\"16\" height=\"16\">Mỗi ng&agrave;y, xe tuyến Phương Trang di chuyển li&ecirc;n tục qua nhiều tỉnh th&agrave;nh phố, đưa th&ocirc;ng điệp thương hiệu tiếp cận h&agrave;ng triệu lượt người, từ h&agrave;nh kh&aacute;ch đến người tham gia giao th&ocirc;ng. Sự xuất hiện lặp lại tr&ecirc;n c&ugrave;ng tuyến đường gi&uacute;p thương hiệu ghi nhớ s&acirc;u trong t&acirc;m tr&iacute; kh&aacute;ch h&agrave;ng, tạo độ tin cậy v&agrave; nhận diện bền vững theo thời gian.</div>\r\n</div>\r\n<div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tfc/1/16/1f44d.png\" alt=\"👍\" width=\"16\" height=\"16\">FUTA Advertising mang đến giải ph&aacute;p quảng c&aacute;o tr&ecirc;n xe tuyến gi&uacute;p thương hiệu lăn b&aacute;nh mỗi ng&agrave;y - ghi nhớ d&agrave;i d&acirc;u</div>\r\n<div dir=\"auto\">--------------------------------</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tb0/1/16/1f3af.png\" alt=\"🎯\" width=\"16\" height=\"16\">FUTA Advertising &ndash; đơn vị dẫn đầu trong lĩnh vực quảng c&aacute;o tr&ecirc;n xe tại Việt Nam.Ch&uacute;ng t&ocirc;i khai th&aacute;c độc quyền quảng c&aacute;o tr&ecirc;n to&agrave;n bộ hệ sinh th&aacute;i của Tập đo&agrave;n Phương Trang - FUTA Group. Bao gồm: FUTA Bus lines, FUTA City Bus, FUTA Express (vận chuyển h&agrave;ng h&oacute;a), FUTA Land, trạm dừng ch&acirc;n, c&aacute;c điểm b&aacute;n v&eacute;, nh&agrave; chờ.</div>\r\n<div dir=\"auto\">C&Ocirc;NG TY CỔ PHẦN QUẢNG C&Aacute;O FUTA PHƯƠNG TRANG VIỆT NAM - FUTA ADVERTISING</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tf8/1/16/1f3e2.png\" alt=\"🏢\" width=\"16\" height=\"16\"> Trụ sở ch&iacute;nh: 218 Đề Th&aacute;m, Phường Phạm Ngũ L&atilde;o, Quận 1, Tp.Hồ Ch&iacute; Minh.</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t22/1/16/260e.png\" alt=\"☎\" width=\"16\" height=\"16\">Hotline: 1900 6912</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tbe/1/16/1f4e7.png\" alt=\"📧\" width=\"16\" height=\"16\">Email: futaadvertising@futa.vn</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tbb/1/16/1f4a0.png\" alt=\"💠\" width=\"16\" height=\"16\">Website: <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://l.facebook.com/l.php?u=https%3A%2F%2Ffutaads.vn%2F%3Ffbclid%3DIwZXh0bgNhZW0CMTAAYnJpZBExdzlKWEtSN2xaNkNDSkZQZXNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR5u276zzRa93tuOV3HYdmKvvXciBLLkCqA4F2ze_7TXKCwoJ8qyYmCu4TC0uQ_aem_458sCR8rYlMA7MQDVpVSGw&amp;h=AT0lHjNxRxP9jnUq7irz-KMNn1AxM2Vglfh0Mc0QLQ82CkGdzz1adny4P03OTgH5scfIROdwLCr-rs4SZ0iHqcWCrhuo8G4wV0fCE91uDJ3uhsvzokiGF_02x2Srl3nNn_FbUEpK5iR2Ad3I0g&amp;__tn__=-UK-R&amp;c[0]=AT1ucg7bWXHr8dzkaxQq9QNAdRqltVKJskiAWbCRIEYNCnLf7LU72P5cENEVRTTc-q7Ow0kpPO6uAM6pSNQrfx5ra-tSEJ-iFATGXSSrDaoTpzhgO2ixRkhhrOLLo5oaIMTri1fGBYLlBU4-2UHpcUO_x7ewhcXv-t3tdfr3aQtn08sVkALXCfwxxXEb0apKU4LK8MomvmLb_ed8DrfcwuE0znXtfA\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">https://futaads.vn</a></div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\">FB: FUTA Advertising - C&ocirc;ng Ty Quảng C&aacute;o Phương Trang Việt Nam</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\">LinkedIn: FUTA ADVERTISING VIET NAM</div>\r\n<div dir=\"auto\"><a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/futaadvertising?__eep__=6&amp;__cft__[0]=AZaW8WbgPOJL_d3Nlo3eOAyFBf8Rtl5M-88mpHQwLYCTH3V-7Mf0zDeUE5sUvx_A6lLwRyvNi8ZPqapir6lsy1AzygMCuAc3b3XGzXde1XTgvtf_EFPZkq2iNNQ4E6HWGF19FtKsTTvO54gw2XdrjEEG9HTL3z1ba0XDE8qzeGbsKN8Fj7VljLVf0wATpwDhym9uYKIZR8J_B5jUQCJAFkUt&amp;__tn__=*NK-R\">#FUTAAdvertising</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/futaads?__eep__=6&amp;__cft__[0]=AZaW8WbgPOJL_d3Nlo3eOAyFBf8Rtl5M-88mpHQwLYCTH3V-7Mf0zDeUE5sUvx_A6lLwRyvNi8ZPqapir6lsy1AzygMCuAc3b3XGzXde1XTgvtf_EFPZkq2iNNQ4E6HWGF19FtKsTTvO54gw2XdrjEEG9HTL3z1ba0XDE8qzeGbsKN8Fj7VljLVf0wATpwDhym9uYKIZR8J_B5jUQCJAFkUt&amp;__tn__=*NK-R\">#FUTAAds</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/quangcaophuongtrang?__eep__=6&amp;__cft__[0]=AZaW8WbgPOJL_d3Nlo3eOAyFBf8Rtl5M-88mpHQwLYCTH3V-7Mf0zDeUE5sUvx_A6lLwRyvNi8ZPqapir6lsy1AzygMCuAc3b3XGzXde1XTgvtf_EFPZkq2iNNQ4E6HWGF19FtKsTTvO54gw2XdrjEEG9HTL3z1ba0XDE8qzeGbsKN8Fj7VljLVf0wATpwDhym9uYKIZR8J_B5jUQCJAFkUt&amp;__tn__=*NK-R\">#quangcaophuongtrang</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/phuongtrangquangcao?__eep__=6&amp;__cft__[0]=AZaW8WbgPOJL_d3Nlo3eOAyFBf8Rtl5M-88mpHQwLYCTH3V-7Mf0zDeUE5sUvx_A6lLwRyvNi8ZPqapir6lsy1AzygMCuAc3b3XGzXde1XTgvtf_EFPZkq2iNNQ4E6HWGF19FtKsTTvO54gw2XdrjEEG9HTL3z1ba0XDE8qzeGbsKN8Fj7VljLVf0wATpwDhym9uYKIZR8J_B5jUQCJAFkUt&amp;__tn__=*NK-R\">#phuongtrangquangcao</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/quangcaoxetuyen?__eep__=6&amp;__cft__[0]=AZaW8WbgPOJL_d3Nlo3eOAyFBf8Rtl5M-88mpHQwLYCTH3V-7Mf0zDeUE5sUvx_A6lLwRyvNi8ZPqapir6lsy1AzygMCuAc3b3XGzXde1XTgvtf_EFPZkq2iNNQ4E6HWGF19FtKsTTvO54gw2XdrjEEG9HTL3z1ba0XDE8qzeGbsKN8Fj7VljLVf0wATpwDhym9uYKIZR8J_B5jUQCJAFkUt&amp;__tn__=*NK-R\">#quangcaoxetuyen</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/transitads?__eep__=6&amp;__cft__[0]=AZaW8WbgPOJL_d3Nlo3eOAyFBf8Rtl5M-88mpHQwLYCTH3V-7Mf0zDeUE5sUvx_A6lLwRyvNi8ZPqapir6lsy1AzygMCuAc3b3XGzXde1XTgvtf_EFPZkq2iNNQ4E6HWGF19FtKsTTvO54gw2XdrjEEG9HTL3z1ba0XDE8qzeGbsKN8Fj7VljLVf0wATpwDhym9uYKIZR8J_B5jUQCJAFkUt&amp;__tn__=*NK-R\">#transitads</a></div>\r\n</div>', 'uploads/posts/1767151855_604535032_122271521558240676_3894928602880192842_n.jpg', NULL, NULL, 'published', '[\"tin-tuc\"]', 1, '2025-12-31 03:30:55'),
(2, '𝗕𝗢𝗢𝗧𝗛 𝗔𝗖𝗧𝗜𝗩𝗔𝗧𝗜𝗢𝗡  - TĂNG NHẬN DIỆN THƯƠNG HIỆU, CHẠM ĐÚNG KHÁCH HÀNG MỤC TIÊU', 'tang-nhan-dien-thuong-hieu-cham-dung-khach-hang-muc-tieu', '', '<div>&nbsp;</div>\r\n<div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t2d/1/16/1f4cd.png\" alt=\"📍\" width=\"16\" height=\"16\">Booth Activation của FUTA Advertising được triển khai tại hệ thống trạm dừng, bến xe, văn ph&ograve;ng v&agrave; ph&ograve;ng v&eacute;, nơi tập trung lượng lớn kh&aacute;ch h&agrave;ng mỗi ng&agrave;y trong thời gian chờ, nghỉ v&agrave; di chuyển &ndash; thời điểm l&yacute; tưởng để thương hiệu thu h&uacute;t sự ch&uacute; &yacute;.</div>\r\n</div>\r\n<div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\">Với c&aacute;c m&ocirc; h&igrave;nh booth s&aacute;ng tạo, trưng b&agrave;y sản phẩm, d&ugrave;ng thử v&agrave; mini-activation, thương hiệu kh&ocirc;ng chỉ tăng độ nhận diện m&agrave; c&ograve;n tạo trải nghiệm trực tiếp, gi&uacute;p kh&aacute;ch h&agrave;ng ghi nhớ tự nhi&ecirc;n v&agrave; hiệu quả hơn.</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\">Sở hữu lợi thế khai th&aacute;c hệ thống điểm triển khai độc quyền, lưu lượng kh&aacute;ch ổn định. FUTA Advertising mang đến giải ph&aacute;p Booth Activation hiệu quả &ndash; đ&uacute;ng điểm chạm &ndash; đ&uacute;ng kh&aacute;ch h&agrave;ng mục ti&ecirc;u.</div>\r\n<div dir=\"auto\">--------------------------------</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tb0/1/16/1f3af.png\" alt=\"🎯\" width=\"16\" height=\"16\">FUTA Advertising &ndash; đơn vị dẫn đầu trong lĩnh vực quảng c&aacute;o tr&ecirc;n xe tại Việt Nam.Ch&uacute;ng t&ocirc;i khai th&aacute;c độc quyền quảng c&aacute;o tr&ecirc;n to&agrave;n bộ hệ sinh th&aacute;i của Tập đo&agrave;n Phương Trang - FUTA Group. Bao gồm: FUTA Bus lines, FUTA City Bus, FUTA Express (vận chuyển h&agrave;ng h&oacute;a), trạm dừng ch&acirc;n, c&aacute;c điểm b&aacute;n v&eacute;, nh&agrave; chờ.</div>\r\n<div dir=\"auto\">C&Ocirc;NG TY CỔ PHẦN QUẢNG C&Aacute;O FUTA PHƯƠNG TRANG VIỆT NAM - FUTA ADVERTISING</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tf8/1/16/1f3e2.png\" alt=\"🏢\" width=\"16\" height=\"16\"> Trụ sở ch&iacute;nh: 218 Đề Th&aacute;m, Phường Phạm Ngũ L&atilde;o, Quận 1, Tp.Hồ Ch&iacute; Minh.</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t22/1/16/260e.png\" alt=\"☎\" width=\"16\" height=\"16\">Hotline: 1900 6912</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tbe/1/16/1f4e7.png\" alt=\"📧\" width=\"16\" height=\"16\">Email: futaadvertising@futa.vn</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tbb/1/16/1f4a0.png\" alt=\"💠\" width=\"16\" height=\"16\">Website: <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://l.facebook.com/l.php?u=https%3A%2F%2Ffutaads.vn%2F%3Ffbclid%3DIwZXh0bgNhZW0CMTAAYnJpZBExdzlKWEtSN2xaNkNDSkZQZXNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR5BiJPg0yGjRCK8jM8ll7MclQtp_QpJwNr2pjHEvM3Pq6vyy89NK3pX58gCdA_aem_G9POpxE-4RnxahGhX6BQgg&amp;h=AT1OJZ4WRsPxLIz1xrWrwUCyOrBp1llZSeFQUDQxpSEfmKcyYb-Jh6JEh-NSiqglSmUm888IV-H1JjvjMeTWqaqS8V7zmAXtttGNDwRySnbpiJ6cB-OwnMvI9BWQx7tILmj7hutTARsfbqSMEA&amp;__tn__=-UK-R&amp;c[0]=AT1bHHO0ZZeaZAXTEZ88WrpmTErj2bn8steXJZGqTce5HXVH4UFSQC9INbFSeTMwRskOeOUOh3rwOjR-DnAaJuXLwnsqc9sQy0gU3T95X44FAKArAhqM0TRrQPToQA4SMxiUCkt7alovJnoGnm9uDXaQTGdN6VVabDBHIhn5I_A\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">https://futaads.vn</a></div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\">FB: FUTA Advertising - C&ocirc;ng Ty Quảng C&aacute;o Phương Trang Việt Nam</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\">LinkedIn: FUTA ADVERTISING VIET NAM</div>\r\n<div dir=\"auto\"><a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/futaadvertising?__eep__=6&amp;__cft__[0]=AZbG0J2LJXoUNXnTUC581MxQNt9FKIwgr7-b66Dgy6i8OEtYcj9znXTpEa9Ds0qHuA-Irh2x0ovHv0q298BE5d1fcnJnzdQ2QKV8DHKfEKR_W21cmUkEHw2ctEuGl1MuI90-m-Kt4yxJ6BxMiANQtiOE&amp;__tn__=*NK-R\">#FUTAAdvertising</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/futaads?__eep__=6&amp;__cft__[0]=AZbG0J2LJXoUNXnTUC581MxQNt9FKIwgr7-b66Dgy6i8OEtYcj9znXTpEa9Ds0qHuA-Irh2x0ovHv0q298BE5d1fcnJnzdQ2QKV8DHKfEKR_W21cmUkEHw2ctEuGl1MuI90-m-Kt4yxJ6BxMiANQtiOE&amp;__tn__=*NK-R\">#FUTAAds</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/quangcaophuongtrang?__eep__=6&amp;__cft__[0]=AZbG0J2LJXoUNXnTUC581MxQNt9FKIwgr7-b66Dgy6i8OEtYcj9znXTpEa9Ds0qHuA-Irh2x0ovHv0q298BE5d1fcnJnzdQ2QKV8DHKfEKR_W21cmUkEHw2ctEuGl1MuI90-m-Kt4yxJ6BxMiANQtiOE&amp;__tn__=*NK-R\">#quangcaophuongtrang</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/phuongtrangquangcao?__eep__=6&amp;__cft__[0]=AZbG0J2LJXoUNXnTUC581MxQNt9FKIwgr7-b66Dgy6i8OEtYcj9znXTpEa9Ds0qHuA-Irh2x0ovHv0q298BE5d1fcnJnzdQ2QKV8DHKfEKR_W21cmUkEHw2ctEuGl1MuI90-m-Kt4yxJ6BxMiANQtiOE&amp;__tn__=*NK-R\">#phuongtrangquangcao</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/boothactivation?__eep__=6&amp;__cft__[0]=AZbG0J2LJXoUNXnTUC581MxQNt9FKIwgr7-b66Dgy6i8OEtYcj9znXTpEa9Ds0qHuA-Irh2x0ovHv0q298BE5d1fcnJnzdQ2QKV8DHKfEKR_W21cmUkEHw2ctEuGl1MuI90-m-Kt4yxJ6BxMiANQtiOE&amp;__tn__=*NK-R\">#BoothActivation</a></div>\r\n</div>', 'uploads/posts/1767151972_597998229_122270070716240676_8362869002955302622_n.jpg', NULL, NULL, 'published', '[\"tin-tức\"]', 1, '2025-12-31 03:32:52'),
(3, 'QUẢNG CÁO TRÊN XE BUÝT - GIẢI PHÁP HOÀN HẢO CHO NGÀNH THỜI TRANG', 'quang-cao-tren-xe-buyt-giai-phap-hoan-hao-cho-nganh-thoi-trang', 'Quảng cáo trên xe buýt Phương Trang là lựa chọn hiệu quả cho các thương hiệu thời trang muốn tăng độ phủ và sức hút hình ảnh.', '<div>\r\n<div dir=\"auto\">Quảng c&aacute;o tr&ecirc;n xe bu&yacute;t Phương Trang l&agrave; lựa chọn hiệu quả cho c&aacute;c thương hiệu thời trang muốn tăng độ phủ v&agrave; sức h&uacute;t h&igrave;nh ảnh.</div>\r\n</div>\r\n<div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t2d/1/16/1f4cd.png\" alt=\"📍\" width=\"16\" height=\"16\">Với diện t&iacute;ch hiển thị lớn gi&uacute;p h&igrave;nh ảnh sản phẩm v&agrave; thương hiệu trở n&ecirc;n nổi bật, dễ d&agrave;ng thu h&uacute;t &aacute;nh nh&igrave;n tr&ecirc;n mọi tuyến đường.</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t2d/1/16/1f4cd.png\" alt=\"📍\" width=\"16\" height=\"16\">Đặc biệt lộ tr&igrave;nh xe bu&yacute;t đi qua c&aacute;c khu trung t&acirc;m thương mại, văn ph&ograve;ng, trường học v&agrave; khu d&acirc;n cư,&hellip; Nơi tập trung kh&aacute;ch h&agrave;ng trẻ, d&acirc;n c&ocirc;ng sở v&agrave; người mua sắm gi&uacute;p tiếp cận đ&uacute;ng nh&oacute;m kh&aacute;ch h&agrave;ng mục ti&ecirc;u.</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t2d/1/16/1f4cd.png\" alt=\"📍\" width=\"16\" height=\"16\">Xe bu&yacute;t di chuyển li&ecirc;n tục trong nội đ&ocirc;, xuất hiện trước mắt h&agrave;ng ngh&igrave;n người mỗi ng&agrave;y, gi&uacute;p thương hiệu thời trang được lặp lại thường xuy&ecirc;n v&agrave; ghi nhớ tốt hơn.</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tfc/1/16/1f44d.png\" alt=\"👍\" width=\"16\" height=\"16\">Quảng c&aacute;o xe bu&yacute;t tại FUTA Advertising l&agrave; giải ph&aacute;p truyền th&ocirc;ng ph&ugrave; hợp cho mọi thương hiệu thời trang muốn tăng nhận diện thương hiệu.</div>\r\n<div dir=\"auto\">--------------------------------</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tb0/1/16/1f3af.png\" alt=\"🎯\" width=\"16\" height=\"16\">FUTA Advertising &ndash; đơn vị dẫn đầu trong lĩnh vực quảng c&aacute;o tr&ecirc;n xe tại Việt Nam.Ch&uacute;ng t&ocirc;i khai th&aacute;c độc quyền quảng c&aacute;o tr&ecirc;n to&agrave;n bộ hệ sinh th&aacute;i của Tập đo&agrave;n Phương Trang - FUTA Group. Bao gồm: FUTA Bus lines, FUTA City Bus, FUTA Express (vận chuyển h&agrave;ng h&oacute;a), trạm dừng ch&acirc;n, c&aacute;c điểm b&aacute;n v&eacute;, nh&agrave; chờ.</div>\r\n<div dir=\"auto\">C&Ocirc;NG TY CỔ PHẦN QUẢNG C&Aacute;O FUTA PHƯƠNG TRANG VIỆT NAM - FUTA ADVERTISING</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tf8/1/16/1f3e2.png\" alt=\"🏢\" width=\"16\" height=\"16\"> Trụ sở ch&iacute;nh: 218 Đề Th&aacute;m, Phường Phạm Ngũ L&atilde;o, Quận 1, Tp.Hồ Ch&iacute; Minh.</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t22/1/16/260e.png\" alt=\"☎\" width=\"16\" height=\"16\">Hotline: 1900 6912</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tbe/1/16/1f4e7.png\" alt=\"📧\" width=\"16\" height=\"16\">Email: futaadvertising@futa.vn</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tbb/1/16/1f4a0.png\" alt=\"💠\" width=\"16\" height=\"16\">Website: <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://l.facebook.com/l.php?u=https%3A%2F%2Ffutaads.vn%2F%3Ffbclid%3DIwZXh0bgNhZW0CMTAAYnJpZBExdzlKWEtSN2xaNkNDSkZQZXNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR5u276zzRa93tuOV3HYdmKvvXciBLLkCqA4F2ze_7TXKCwoJ8qyYmCu4TC0uQ_aem_458sCR8rYlMA7MQDVpVSGw&amp;h=AT3pXVgeN_U17KNx3vPKAoOirlyd_Eqk9uhynQ7ElV8qUfpvaq3fLhnPVXH6wjE65X_8QqEj4Psx7SJa7tA10jyGRz2v9WenPTgmmOlppPV-h91CB-SNh6UFcjJ2KgSclwNOEdwiKRGms_6WGw&amp;__tn__=-UK-R&amp;c[0]=AT3j1VTRfkFlH4LAVISOjKmlJWn9Croh-jV25GFU3l_L_suZWZO80ZCcHurEUXNA2o2Twe1YXLJTcHrUJP8hFUi5x52rOMZDt7MUE2Lqe7-XxlAR4ZZshJjaSIDFi-wLlcQV4iLGJHCuphb2mZ18eeE9XuBo9FV0YpKJBXpEXmexZIbpVG8ibJXCIRMDwr8puZeSPduVSynq4sS7W9fovS6hrm5QIw\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">https://futaads.vn</a></div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\">FB: FUTA Advertising - C&ocirc;ng Ty Quảng C&aacute;o Phương Trang Việt Nam</div>\r\n<div dir=\"auto\"><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\">LinkedIn: FUTA ADVERTISING VIET NAM</div>\r\n<div dir=\"auto\"><a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/futaadvertising?__eep__=6&amp;__cft__[0]=AZY2gHFTaRRFGqQih8nJ9mrVo1MDB2TCCeUBXXeThHUaj5MSk6KUgZIIOa4hCTpXQzINb24CinLKH-4nGLOoH4_EdtJkJc5IHigsU-ZwSe9fqrPlT_ZOQuMcL4iSxk94GoqvV6wymYpLdByFzGoWm2GESPzGh-t5mEEn2lnEEWOErJ7W_uapl7HK-VOxHrVk3HpfiyQ7Gqv4jZguLxToWMeF&amp;__tn__=*NK-R\">#FUTAAdvertising</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/futaads?__eep__=6&amp;__cft__[0]=AZY2gHFTaRRFGqQih8nJ9mrVo1MDB2TCCeUBXXeThHUaj5MSk6KUgZIIOa4hCTpXQzINb24CinLKH-4nGLOoH4_EdtJkJc5IHigsU-ZwSe9fqrPlT_ZOQuMcL4iSxk94GoqvV6wymYpLdByFzGoWm2GESPzGh-t5mEEn2lnEEWOErJ7W_uapl7HK-VOxHrVk3HpfiyQ7Gqv4jZguLxToWMeF&amp;__tn__=*NK-R\">#FUTAAds</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/quangcaoxe?__eep__=6&amp;__cft__[0]=AZY2gHFTaRRFGqQih8nJ9mrVo1MDB2TCCeUBXXeThHUaj5MSk6KUgZIIOa4hCTpXQzINb24CinLKH-4nGLOoH4_EdtJkJc5IHigsU-ZwSe9fqrPlT_ZOQuMcL4iSxk94GoqvV6wymYpLdByFzGoWm2GESPzGh-t5mEEn2lnEEWOErJ7W_uapl7HK-VOxHrVk3HpfiyQ7Gqv4jZguLxToWMeF&amp;__tn__=*NK-R\">#quangcaoxe</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/quangcaophuongtrang?__eep__=6&amp;__cft__[0]=AZY2gHFTaRRFGqQih8nJ9mrVo1MDB2TCCeUBXXeThHUaj5MSk6KUgZIIOa4hCTpXQzINb24CinLKH-4nGLOoH4_EdtJkJc5IHigsU-ZwSe9fqrPlT_ZOQuMcL4iSxk94GoqvV6wymYpLdByFzGoWm2GESPzGh-t5mEEn2lnEEWOErJ7W_uapl7HK-VOxHrVk3HpfiyQ7Gqv4jZguLxToWMeF&amp;__tn__=*NK-R\">#quangcaophuongtrang</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/phuongtrangquangcao?__eep__=6&amp;__cft__[0]=AZY2gHFTaRRFGqQih8nJ9mrVo1MDB2TCCeUBXXeThHUaj5MSk6KUgZIIOa4hCTpXQzINb24CinLKH-4nGLOoH4_EdtJkJc5IHigsU-ZwSe9fqrPlT_ZOQuMcL4iSxk94GoqvV6wymYpLdByFzGoWm2GESPzGh-t5mEEn2lnEEWOErJ7W_uapl7HK-VOxHrVk3HpfiyQ7Gqv4jZguLxToWMeF&amp;__tn__=*NK-R\">#phuongtrangquangcao</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/xebus?__eep__=6&amp;__cft__[0]=AZY2gHFTaRRFGqQih8nJ9mrVo1MDB2TCCeUBXXeThHUaj5MSk6KUgZIIOa4hCTpXQzINb24CinLKH-4nGLOoH4_EdtJkJc5IHigsU-ZwSe9fqrPlT_ZOQuMcL4iSxk94GoqvV6wymYpLdByFzGoWm2GESPzGh-t5mEEn2lnEEWOErJ7W_uapl7HK-VOxHrVk3HpfiyQ7Gqv4jZguLxToWMeF&amp;__tn__=*NK-R\">#xebus</a> <a style=\"list-style-type: none;\" tabindex=\"0\" role=\"link\" href=\"https://www.facebook.com/hashtag/quangcaoxebus?__eep__=6&amp;__cft__[0]=AZY2gHFTaRRFGqQih8nJ9mrVo1MDB2TCCeUBXXeThHUaj5MSk6KUgZIIOa4hCTpXQzINb24CinLKH-4nGLOoH4_EdtJkJc5IHigsU-ZwSe9fqrPlT_ZOQuMcL4iSxk94GoqvV6wymYpLdByFzGoWm2GESPzGh-t5mEEn2lnEEWOErJ7W_uapl7HK-VOxHrVk3HpfiyQ7Gqv4jZguLxToWMeF&amp;__tn__=*NK-R\">#quangcaoxebus</a></div>\r\n</div>', 'uploads/posts/1767152077_596784205_122268513656240676_6519660362166589749_n.jpg', NULL, NULL, 'published', '[\"#FUTAAdvertising #FUTAAds #quangcaoxe #quangcaophuongtrang #phuongtrangquangcao #xebus #quangcaoxebus\"]', 1, '2025-12-31 03:34:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `client` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `preview_image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `preview_video` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending','published') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `projects`
--

INSERT INTO `projects` (`id`, `title`, `client`, `description`, `preview_image`, `gallery`, `preview_video`, `status`, `created_by`, `created_at`) VALUES
(4, '𝐅𝐔𝐓𝐀 𝐀𝐝𝐯𝐞𝐫𝐭𝐢𝐬𝐢𝐧𝐠 𝐱 𝐂𝐡𝐨𝐥𝐢𝐦𝐞𝐱', 'Cholimex - Gia vị cuộc sống đã chọn xe tuyến của FUTA Advertising triển khai chiến dịch quảng bá sản phẩm các dòng tương ớt và gia vị đến hàng triệu người tiêu dùng ở khắp mọi miền.', '<div class=\"x14z9mp xat24cr x1lziwak x1vvkbs xtlvy1s x126k92a\">\r\n<div dir=\"auto\">Với thế mạnh xe tuyến c&oacute; mặt tr&ecirc;n to&agrave;n quốc, ch&uacute;ng t&ocirc;i phủ s&oacute;ng h&igrave;nh ảnh sản phẩm Cholimex bắt mắt, lăn b&aacute;nh qua từng cung đường &ndash; từ th&agrave;nh thị đến n&ocirc;ng th&ocirc;n &ndash; mang th&ocirc;ng điệp &ldquo;Gia vị cuộc sống&rdquo; lan tỏa gần gũi, th&acirc;n quen trong từng bữa ăn của mọi gia đ&igrave;nh.</div>\r\n</div>\r\n<div class=\"x14z9mp xat24cr x1lziwak x1vvkbs xtlvy1s x126k92a\">\r\n<div dir=\"auto\"><span class=\"html-span xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs x3nfvp2 x1j61x8r x1fcty0u xdj266r xat24cr xm2jcoa x1mpyi22 xxymvpz xlup9mm x1kky2od\"><img class=\"xz74otr x15mokao x1ga7v0g x16uus16 xbiv7yw\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t51/1/16/1f449.png\" alt=\"👉\" width=\"16\" height=\"16\"></span>Chọn FUTA Advertising để thương hiệu của bạn xuất hiện ở mọi nơi, trong t&acirc;m tr&iacute; mọi kh&aacute;ch h&agrave;ng</div>\r\n<div dir=\"auto\">--------------------------------</div>\r\n<div dir=\"auto\"><span class=\"html-span xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs x3nfvp2 x1j61x8r x1fcty0u xdj266r xat24cr xm2jcoa x1mpyi22 xxymvpz xlup9mm x1kky2od\"><img class=\"xz74otr x15mokao x1ga7v0g x16uus16 xbiv7yw\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tb0/1/16/1f3af.png\" alt=\"🎯\" width=\"16\" height=\"16\"></span>FUTA Advertising &ndash; đơn vị dẫn đầu trong lĩnh vực quảng c&aacute;o tr&ecirc;n xe tại Việt Nam.Ch&uacute;ng t&ocirc;i khai th&aacute;c độc quyền quảng c&aacute;o tr&ecirc;n to&agrave;n bộ hệ sinh th&aacute;i của Tập đo&agrave;n Phương Trang - FUTA Group. Bao gồm: FUTA Bus lines, FUTA City Bus, FUTA Express (vận chuyển h&agrave;ng h&oacute;a), trạm dừng ch&acirc;n, c&aacute;c điểm b&aacute;n v&eacute;, nh&agrave; chờ.</div>\r\n<div dir=\"auto\">C&Ocirc;NG TY CỔ PHẦN QUẢNG C&Aacute;O FUTA PHƯƠNG TRANG VIỆT NAM - FUTA ADVERTISING</div>\r\n<div dir=\"auto\"><span class=\"html-span xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs x3nfvp2 x1j61x8r x1fcty0u xdj266r xat24cr xm2jcoa x1mpyi22 xxymvpz xlup9mm x1kky2od\"><img class=\"xz74otr x15mokao x1ga7v0g x16uus16 xbiv7yw\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tf8/1/16/1f3e2.png\" alt=\"🏢\" width=\"16\" height=\"16\"></span> Trụ sở ch&iacute;nh: 218 Đề Th&aacute;m, Phường Phạm Ngũ L&atilde;o, Quận 1, Tp.Hồ Ch&iacute; Minh.</div>\r\n<div dir=\"auto\"><span class=\"html-span xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs x3nfvp2 x1j61x8r x1fcty0u xdj266r xat24cr xm2jcoa x1mpyi22 xxymvpz xlup9mm x1kky2od\"><img class=\"xz74otr x15mokao x1ga7v0g x16uus16 xbiv7yw\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t22/1/16/260e.png\" alt=\"☎\" width=\"16\" height=\"16\"></span>Hotline: 1900 6912</div>\r\n<div dir=\"auto\"><span class=\"html-span xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs x3nfvp2 x1j61x8r x1fcty0u xdj266r xat24cr xm2jcoa x1mpyi22 xxymvpz xlup9mm x1kky2od\"><img class=\"xz74otr x15mokao x1ga7v0g x16uus16 xbiv7yw\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tbe/1/16/1f4e7.png\" alt=\"📧\" width=\"16\" height=\"16\"></span>Email: futaadvertising@futa.vn</div>\r\n<div dir=\"auto\"><span class=\"html-span xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs x3nfvp2 x1j61x8r x1fcty0u xdj266r xat24cr xm2jcoa x1mpyi22 xxymvpz xlup9mm x1kky2od\"><img class=\"xz74otr x15mokao x1ga7v0g x16uus16 xbiv7yw\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/tbb/1/16/1f4a0.png\" alt=\"💠\" width=\"16\" height=\"16\"></span>Website: <span class=\"html-span xdj266r x14z9mp xat24cr x1lziwak xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs\"><a class=\"x1i10hfl xjbqb8w x1ejq31n x18oe1m7 x1sy0etr xstzfhl x972fbf x10w94by x1qhh985 x14e42zd x9f619 x1ypdohk xt0psk2 x3ct3a4 xdj266r x14z9mp xat24cr x1lziwak xexx8yu xyri2b x18d9i69 x1c1uobl x16tdsg8 x1hl2dhg xggy1nq x1a2a7pz xkrqix3 x1sur9pj x1fey0fg x1s688f\" tabindex=\"0\" role=\"link\" href=\"https://l.facebook.com/l.php?u=https%3A%2F%2Ffutaads.vn%2F%3Ffbclid%3DIwZXh0bgNhZW0CMTAAYnJpZBExdzlKWEtSN2xaNkNDSkZQZXNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR61t7L3cVpELWgGTtf6_NjBzeys6HMsWBRzyPkEJx1RV6Ar-sZI3aVpO3o1Ag_aem_0y9Is53fi9PYFgU0mjZMEA&amp;h=AT0KYXxyIbtjrc2UooyvCQfH0SkNv8Al3DRqJqddSXUa2YSgvvwFSJZhWnFJiKeOQwZWwaFc-kXRHcPBgApwXC3GLqcvXpJGirt8NVjNBgn4D_uRJEuSAJT8F-KWKqb0v8EAuxfzGMzAzxGCJw&amp;__tn__=-UK-R&amp;c[0]=AT3ERKRAskiPRY65BB9zfYEyR1HtdGfsAWExxZn3SGCP2eKF6PiE2jf5YcwIQgc6IabqQfpZ_KEEoUz39j3pC3EBGvkRXaOEVLdLj32VIw4hwRmGnuXUVcbTTo4diyZUz_yz2SHoSlMS41oPtXJidEETlUv9IxEJJyrE2I26X7lhaBqF-NnVv8OvWiuETFFtT2OzgpZCtrYc00PuRo3j6ANRd9jb8Q\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">https://futaads.vn</a></span></div>\r\n<div dir=\"auto\"><span class=\"html-span xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs x3nfvp2 x1j61x8r x1fcty0u xdj266r xat24cr xm2jcoa x1mpyi22 xxymvpz xlup9mm x1kky2od\"><img class=\"xz74otr x15mokao x1ga7v0g x16uus16 xbiv7yw\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\"></span>FB: FUTA Advertising - C&ocirc;ng Ty Quảng C&aacute;o Phương Trang Việt Nam</div>\r\n<div dir=\"auto\"><span class=\"html-span xexx8yu xyri2b x18d9i69 x1c1uobl x1hl2dhg x16tdsg8 x1vvkbs x3nfvp2 x1j61x8r x1fcty0u xdj266r xat24cr xm2jcoa x1mpyi22 xxymvpz xlup9mm x1kky2od\"><img class=\"xz74otr x15mokao x1ga7v0g x16uus16 xbiv7yw\" src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t33/1/16/2705.png\" alt=\"✅\" width=\"16\" height=\"16\"></span>LinkedIn: FUTA ADVERTISING VIET NAM</div>\r\n</div>', 'uploads/projects/6954a5fc54688-566230954_122256917444240676_4222892922518099904_n.jpg', NULL, '', 'published', 1, '2025-12-31 04:26:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `role` varchar(255) DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `phone`, `avatar`, `bio`, `role`, `status`, `created_at`) VALUES
(1, 'admin', '12b8b22a0a72eb9303ebcc134850d752', 'Administrator', 'hung.nguyen@futa.vn', NULL, 'uploads/avatars/demo-avatar.jpg', NULL, 'admin', 'active', '2025-12-31 02:45:58'),
(2, 'Hưng Nguyễn', '25d55ad283aa400af464c76d713c07ad', 'HN', 'hung@gmail.com', '0912345678', NULL, '', 'news_manager,recruitment_manager,contact_manager', 'active', '2025-12-31 03:18:53');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `carousel_slides`
--
ALTER TABLE `carousel_slides`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT cho bảng `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `carousel_slides`
--
ALTER TABLE `carousel_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
