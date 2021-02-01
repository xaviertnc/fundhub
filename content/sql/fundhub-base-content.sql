-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `wp_postmeta`;
CREATE TABLE `wp_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1,	1,	'Header Title',	'Independent platform profiling asset managers.'),
(2,	1,	'Header Subtitle',	'TRADITIONAL, PASSIVE, BOUTIQUE AND ALTERNATIVE STRATEGIES.'),
(4,	1,	'_oembed_ed6cca1cb425899b3b7f85282396a3df',	'<iframe title=\"Terebinth - Definition of an Enhanced Income Fund - BLACK ONYX\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/ME0bJ_Eda3M?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(5,	1,	'_oembed_time_ed6cca1cb425899b3b7f85282396a3df',	'1607546298'),
(6,	1,	'_oembed_a94fc772c77cb6644bfce8836439914a',	'<iframe title=\"36ONE - Hedge Fund vs Unit Trust - BLACK ONYX\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/gdDFWJ0V4a0?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(7,	1,	'_oembed_time_a94fc772c77cb6644bfce8836439914a',	'1607546298'),
(8,	1,	'_oembed_e011540d863edafbbab212679214b8c6',	'<iframe title=\"Schroders - Infrastructure attracts sub-markets such as tech trends  - BLACK ONYX\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/ft5tQ8mcjGQ?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(9,	1,	'_oembed_time_e011540d863edafbbab212679214b8c6',	'1607546299'),
(10,	1,	'_oembed_5f871157aac11ba215428aa321658ad0',	'<iframe title=\"Terebinth - Definition of an Enhanced Income Fund - BLACK ONYX\" width=\"750\" height=\"422\" src=\"https://www.youtube.com/embed/ME0bJ_Eda3M?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(11,	1,	'_oembed_time_5f871157aac11ba215428aa321658ad0',	'1607547953'),
(12,	1,	'_oembed_227bb2b348b6668e3e56c1bf7b7b3887',	'<iframe title=\"36ONE - Hedge Fund vs Unit Trust - BLACK ONYX\" width=\"750\" height=\"422\" src=\"https://www.youtube.com/embed/gdDFWJ0V4a0?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(13,	1,	'_oembed_time_227bb2b348b6668e3e56c1bf7b7b3887',	'1607547953'),
(14,	1,	'_oembed_63c840050f0efb382e924ea9a9f43b4d',	'<iframe title=\"Schroders - Infrastructure attracts sub-markets such as tech trends  - BLACK ONYX\" width=\"750\" height=\"422\" src=\"https://www.youtube.com/embed/ft5tQ8mcjGQ?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(15,	1,	'_oembed_time_63c840050f0efb382e924ea9a9f43b4d',	'1607547954'),
(16,	1,	'_wp_page_template',	'page_fundhub.php'),
(20,	2,	'_oembed_time_3f0f6d49f0cf99a2496ccbd4c6d416b4',	'1608386487'),
(21,	2,	'_oembed_975bd484fd31fbb8afcf628310cfa9e7',	'<iframe title=\"NEWORLD - Part 1 - Developers&#039; Background and the Portuguese Opportunity\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/8E8x5w0FXYw?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(22,	2,	'_oembed_time_975bd484fd31fbb8afcf628310cfa9e7',	'1608386516'),
(23,	2,	'_oembed_ed6cca1cb425899b3b7f85282396a3df',	'<iframe title=\"Terebinth - Definition of an Enhanced Income Fund - BLACK ONYX\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/ME0bJ_Eda3M?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(24,	2,	'_oembed_time_ed6cca1cb425899b3b7f85282396a3df',	'1608386424'),
(25,	2,	'_oembed_a94fc772c77cb6644bfce8836439914a',	'<iframe title=\"36ONE - Hedge Fund vs Unit Trust - BLACK ONYX\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/gdDFWJ0V4a0?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(26,	2,	'_oembed_time_a94fc772c77cb6644bfce8836439914a',	'1608386425'),
(27,	2,	'_oembed_e011540d863edafbbab212679214b8c6',	'<iframe title=\"Schroders - Infrastructure attracts sub-markets such as tech trends  - BLACK ONYX\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/ft5tQ8mcjGQ?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(28,	2,	'_oembed_time_e011540d863edafbbab212679214b8c6',	'1608386487'),
(29,	2,	'_oembed_1e3b32875a025656354a7e1bfe6d9f52',	'<iframe title=\"Property Investments | Long Strategy | Paul Duncan - Catalyst Fund Managers (Part 2)\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/5x__fx6GhDk?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(30,	2,	'_oembed_time_1e3b32875a025656354a7e1bfe6d9f52',	'1608386486'),
(31,	2,	'_oembed_3f0f6d49f0cf99a2496ccbd4c6d416b4',	'<iframe title=\"36ONE - How 36ONE’s hedge fund differs from its peers - BLACK ONYX\" width=\"500\" height=\"281\" src=\"https://www.youtube.com/embed/Ob0euhTPSRw?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>'),
(32,	3,	'Header Title',	'Advisors and Financial Professionals'),
(33,	6,	'Header Title',	'Continuing Professional Development'),
(39,	10,	'_menu_item_type',	'post_type'),
(40,	10,	'_menu_item_menu_item_parent',	'0'),
(41,	10,	'_menu_item_object_id',	'1'),
(42,	10,	'_menu_item_object',	'page'),
(43,	10,	'_menu_item_target',	''),
(44,	10,	'_menu_item_classes',	'a:1:{i:0;s:0:\"\";}'),
(45,	10,	'_menu_item_xfn',	''),
(46,	10,	'_menu_item_url',	''),
(48,	11,	'_menu_item_type',	'post_type'),
(49,	11,	'_menu_item_menu_item_parent',	'0'),
(50,	11,	'_menu_item_object_id',	'2'),
(51,	11,	'_menu_item_object',	'page'),
(52,	11,	'_menu_item_target',	''),
(53,	11,	'_menu_item_classes',	'a:1:{i:0;s:0:\"\";}'),
(54,	11,	'_menu_item_xfn',	''),
(55,	11,	'_menu_item_url',	''),
(57,	12,	'_menu_item_type',	'post_type'),
(58,	12,	'_menu_item_menu_item_parent',	'0'),
(59,	12,	'_menu_item_object_id',	'3'),
(60,	12,	'_menu_item_object',	'page'),
(61,	12,	'_menu_item_target',	''),
(62,	12,	'_menu_item_classes',	'a:1:{i:0;s:0:\"\";}'),
(63,	12,	'_menu_item_xfn',	''),
(64,	12,	'_menu_item_url',	''),
(66,	13,	'_menu_item_type',	'post_type'),
(67,	13,	'_menu_item_menu_item_parent',	'0'),
(68,	13,	'_menu_item_object_id',	'4'),
(69,	13,	'_menu_item_object',	'page'),
(70,	13,	'_menu_item_target',	''),
(71,	13,	'_menu_item_classes',	'a:1:{i:0;s:0:\"\";}'),
(72,	13,	'_menu_item_xfn',	''),
(73,	13,	'_menu_item_url',	''),
(75,	14,	'_menu_item_type',	'post_type'),
(76,	14,	'_menu_item_menu_item_parent',	'0'),
(77,	14,	'_menu_item_object_id',	'5'),
(78,	14,	'_menu_item_object',	'page'),
(79,	14,	'_menu_item_target',	''),
(80,	14,	'_menu_item_classes',	'a:1:{i:0;s:0:\"\";}'),
(81,	14,	'_menu_item_xfn',	''),
(82,	14,	'_menu_item_url',	''),
(84,	15,	'_menu_item_type',	'post_type'),
(85,	15,	'_menu_item_menu_item_parent',	'0'),
(86,	15,	'_menu_item_object_id',	'6'),
(87,	15,	'_menu_item_object',	'page'),
(88,	15,	'_menu_item_target',	''),
(89,	15,	'_menu_item_classes',	'a:1:{i:0;s:0:\"\";}'),
(90,	15,	'_menu_item_xfn',	''),
(91,	15,	'_menu_item_url',	''),
(93,	16,	'_menu_item_type',	'post_type'),
(94,	16,	'_menu_item_menu_item_parent',	'0'),
(95,	16,	'_menu_item_object_id',	'7'),
(96,	16,	'_menu_item_object',	'page'),
(97,	16,	'_menu_item_target',	''),
(98,	16,	'_menu_item_classes',	'a:1:{i:0;s:0:\"\";}'),
(99,	16,	'_menu_item_xfn',	''),
(100,	16,	'_menu_item_url',	'');

DROP TABLE IF EXISTS `wp_posts`;
CREATE TABLE `wp_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(1,	1,	'2020-12-09 19:33:33',	'2020-12-09 19:33:33',	'<!-- wp:heading {\"level\":3} -->\n<h3>Investors need to be better informed to achieve better risk-adjusted returns.</h3>\n<!-- /wp:heading -->\n\n<!-- wp:list {\"className\":\"fancy-list\"} -->\n<ul class=\"fancy-list\"><li>FUND HUB provides investors with a primary layer of due diligence.</li><li>Brings you face-to-face with the actual people who manage your money.</li><li>Quick access to the fund managers, their research and fund factsheets.</li><li>Frees up advisors to service clients and fund managers to manage your money.</li></ul>\n<!-- /wp:list -->\n\n<!-- wp:heading {\"level\":4} -->\n<h4><strong>Our content is complimentary, supporting economic transformation and financial literacy.</strong></h4>\n<!-- /wp:heading -->\n\n<!-- wp:columns -->\n<div class=\"wp-block-columns\"><!-- wp:column -->\n<div class=\"wp-block-column\"><!-- wp:embed {\"url\":\"https://youtu.be/ME0bJ_Eda3M\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"responsive\":true,\"className\":\"wp-embed-aspect-16-9 wp-has-aspect-ratio\"} -->\n<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio\"><div class=\"wp-block-embed__wrapper\">\nhttps://youtu.be/ME0bJ_Eda3M\n</div></figure>\n<!-- /wp:embed --></div>\n<!-- /wp:column -->\n\n<!-- wp:column -->\n<div class=\"wp-block-column\"><!-- wp:embed {\"url\":\"https://youtu.be/gdDFWJ0V4a0\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"responsive\":true,\"className\":\"wp-embed-aspect-16-9 wp-has-aspect-ratio\"} -->\n<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio\"><div class=\"wp-block-embed__wrapper\">\nhttps://youtu.be/gdDFWJ0V4a0\n</div></figure>\n<!-- /wp:embed --></div>\n<!-- /wp:column -->\n\n<!-- wp:column -->\n<div class=\"wp-block-column\"><!-- wp:embed {\"url\":\"https://youtu.be/ft5tQ8mcjGQ\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"responsive\":true,\"className\":\"wp-embed-aspect-16-9 wp-has-aspect-ratio\"} -->\n<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio\"><div class=\"wp-block-embed__wrapper\">\nhttps://youtu.be/ft5tQ8mcjGQ\n</div></figure>\n<!-- /wp:embed --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->\n\n<!-- wp:paragraph -->\n<p><em><span class=\"has-inline-color has-vivid-cyan-blue-color\">If these YouTube videos are not displaying, please contact your IT department for access to this site.</span></em></p>\n<!-- /wp:paragraph -->',	'About',	'',	'publish',	'closed',	'closed',	'',	'about',	'',	'',	'2020-12-09 19:33:33',	'2020-12-09 19:33:33',	'',	0,	'https://decentdemos.co.za/fundhub/?page_id=1',	0,	'page',	'',	0),
(2,	1,	'2020-12-09 19:35:48',	'2020-12-09 19:35:48',	'<!-- wp:heading {\"level\":3} -->\n<h3>Want FREE access to the same asset manager research as your advisor?</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Financial professionals are under increasing pressure to expand their product knowledge and deliver a more valuable client experience. FUND HUB is a resource to support you and your advisors to work together and be better informed.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Benefits of using FUND HUB:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:list {\"className\":\"fancy-list\"} -->\n<ul class=\"fancy-list\"><li>Broaden your investment knowledge, become a better investor.</li><li>Exposure to new managers and strategies, diversify your portfolio.</li><li>Increase your investment knowledge, reduce your portfolio risk.</li><li>Easy to watch, easy to listen, easy to understand, be better informed.</li></ul>\n<!-- /wp:list -->\n\n<!-- wp:columns -->\n<div class=\"wp-block-columns\"><!-- wp:column -->\n<div class=\"wp-block-column\"><!-- wp:embed {\"url\":\"https://youtu.be/5x__fx6GhDk\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"responsive\":true,\"className\":\"wp-embed-aspect-16-9 wp-has-aspect-ratio\"} -->\n<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio\"><div class=\"wp-block-embed__wrapper\">\nhttps://youtu.be/5x__fx6GhDk\n</div></figure>\n<!-- /wp:embed --></div>\n<!-- /wp:column -->\n\n<!-- wp:column -->\n<div class=\"wp-block-column\"><!-- wp:embed {\"url\":\"https://youtu.be/Ob0euhTPSRw\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"responsive\":true,\"className\":\"wp-embed-aspect-16-9 wp-has-aspect-ratio\"} -->\n<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio\"><div class=\"wp-block-embed__wrapper\">\nhttps://youtu.be/Ob0euhTPSRw\n</div></figure>\n<!-- /wp:embed --></div>\n<!-- /wp:column -->\n\n<!-- wp:column -->\n<div class=\"wp-block-column\"><!-- wp:embed {\"url\":\"https://youtu.be/8E8x5w0FXYw\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"responsive\":true,\"className\":\"wp-embed-aspect-16-9 wp-has-aspect-ratio\"} -->\n<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio\"><div class=\"wp-block-embed__wrapper\">\nhttps://youtu.be/8E8x5w0FXYw\n</div></figure>\n<!-- /wp:embed --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->\n\n<!-- wp:paragraph -->\n<p><em><span class=\"has-inline-color has-vivid-cyan-blue-color\">If these YouTube videos are not displaying, please contact your IT department for access to this site.</span></em></p>\n<!-- /wp:paragraph -->',	'Investors',	'',	'publish',	'closed',	'closed',	'',	'investors',	'',	'',	'2020-12-09 19:35:48',	'2020-12-09 19:35:48',	'',	0,	'https://decentdemos.co.za/fundhub/?page_id=2',	1,	'page',	'',	0),
(3,	1,	'2020-12-09 19:36:26',	'2020-12-09 19:36:26',	'',	'Advisors',	'',	'publish',	'closed',	'closed',	'',	'advisors',	'',	'',	'2020-12-09 19:36:26',	'2020-12-09 19:36:26',	'',	0,	'https://decentdemos.co.za/fundhub/?page_id=3',	2,	'page',	'',	0),
(4,	1,	'2020-12-09 19:36:59',	'2020-12-09 19:36:59',	'',	'Asset Managers',	'',	'publish',	'closed',	'closed',	'',	'asset-managers',	'',	'',	'2020-12-09 19:36:59',	'2020-12-09 19:36:59',	'',	0,	'https://decentdemos.co.za/fundhub/?page_id=4',	3,	'page',	'',	0),
(5,	1,	'2020-12-09 19:37:52',	'2020-12-09 19:37:52',	'',	'Strategies',	'',	'publish',	'closed',	'closed',	'',	'strategies',	'',	'',	'2020-12-09 19:37:52',	'2020-12-09 19:37:52',	'',	0,	'https://decentdemos.co.za/fundhub/?page_id=5',	4,	'page',	'',	0),
(6,	1,	'2020-12-09 19:38:31',	'2020-12-09 19:38:31',	'',	'CPD',	'',	'publish',	'closed',	'closed',	'',	'cpd',	'',	'',	'2020-12-09 19:38:31',	'2020-12-09 19:38:31',	'',	0,	'https://decentdemos.co.za/fundhub/?page_id=6',	5,	'page',	'',	0),
(7,	1,	'2020-12-09 19:40:58',	'2020-12-09 19:40:58',	'',	'Contact',	'',	'publish',	'closed',	'closed',	'',	'contact',	'',	'',	'2020-12-09 19:40:58',	'2020-12-09 19:40:58',	'',	0,	'https://decentdemos.co.za/fundhub/?page_id=7',	6,	'page',	'',	0),
(10,	1,	'2020-12-20 07:25:09',	'2020-12-20 05:25:09',	' ',	'',	'',	'publish',	'closed',	'closed',	'',	'10',	'',	'',	'2020-12-20 07:25:17',	'2020-12-20 05:25:17',	'',	0,	'https://www.decentdemos.co.za/wordpress/?p=10',	1,	'nav_menu_item',	'',	0),
(11,	1,	'2020-12-20 07:25:09',	'2020-12-20 05:25:09',	' ',	'',	'',	'publish',	'closed',	'closed',	'',	'11',	'',	'',	'2020-12-20 07:25:17',	'2020-12-20 05:25:17',	'',	0,	'https://www.decentdemos.co.za/wordpress/?p=11',	2,	'nav_menu_item',	'',	0),
(12,	1,	'2020-12-20 07:25:09',	'2020-12-20 05:25:09',	' ',	'',	'',	'publish',	'closed',	'closed',	'',	'12',	'',	'',	'2020-12-20 07:25:17',	'2020-12-20 05:25:17',	'',	0,	'https://www.decentdemos.co.za/wordpress/?p=12',	3,	'nav_menu_item',	'',	0),
(13,	1,	'2020-12-20 07:25:09',	'2020-12-20 05:25:09',	' ',	'',	'',	'publish',	'closed',	'closed',	'',	'13',	'',	'',	'2020-12-20 07:25:17',	'2020-12-20 05:25:17',	'',	0,	'https://www.decentdemos.co.za/wordpress/?p=13',	4,	'nav_menu_item',	'',	0),
(14,	1,	'2020-12-20 07:25:09',	'2020-12-20 05:25:09',	' ',	'',	'',	'publish',	'closed',	'closed',	'',	'14',	'',	'',	'2020-12-20 07:25:17',	'2020-12-20 05:25:17',	'',	0,	'https://www.decentdemos.co.za/wordpress/?p=14',	5,	'nav_menu_item',	'',	0),
(15,	1,	'2020-12-20 07:25:09',	'2020-12-20 05:25:09',	' ',	'',	'',	'publish',	'closed',	'closed',	'',	'15',	'',	'',	'2020-12-20 07:25:17',	'2020-12-20 05:25:17',	'',	0,	'https://www.decentdemos.co.za/wordpress/?p=15',	6,	'nav_menu_item',	'',	0),
(16,	1,	'2020-12-20 07:25:09',	'2020-12-20 05:25:09',	' ',	'',	'',	'publish',	'closed',	'closed',	'',	'16',	'',	'',	'2020-12-20 07:25:17',	'2020-12-20 05:25:17',	'',	0,	'https://www.decentdemos.co.za/wordpress/?p=16',	7,	'nav_menu_item',	'',	0);

DROP TABLE IF EXISTS `wp_termmeta`;
CREATE TABLE `wp_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_terms`;
CREATE TABLE `wp_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_terms` (`term_id`, `name`, `slug`, `term_group`) VALUES
(1,	'Uncategorised', 'uncategorised',	0),
(2,	'Asset Manager', 'asset-manager',	0),
(3,	'Primary Menu', 'primary-menu',	0);

DROP TABLE IF EXISTS `wp_term_relationships`;
CREATE TABLE `wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(10,	3,	0),
(11,	3,	0),
(12,	3,	0),
(13,	3,	0),
(14,	3,	0),
(15,	3,	0),
(16,	3,	0);

DROP TABLE IF EXISTS `wp_term_taxonomy`;
CREATE TABLE `wp_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
(1,	1,	'category',	'',	0,	0),
(2,	2,	'category',	'',	0,	0),
(3,	3,	'nav_menu',	'',	0,	7);

-- 2020-12-20 05:30:50