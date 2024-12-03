/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : sigoolblog

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 03/12/2024 14:44:37
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sgbgcomments
-- ----------------------------
DROP TABLE IF EXISTS `sgbgcomments`;
CREATE TABLE `sgbgcomments`  (
  `coid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cid` int(10) UNSIGNED NULL DEFAULT 0,
  `created` int(10) UNSIGNED NULL DEFAULT 0,
  `author` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `authorId` int(10) UNSIGNED NULL DEFAULT 0,
  `ownerId` int(10) UNSIGNED NULL DEFAULT 0,
  `mail` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `agent` varchar(511) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'comment',
  `status` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'approved',
  `parent` int(10) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`coid`) USING BTREE,
  INDEX `cid`(`cid`) USING BTREE,
  INDEX `created`(`created`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sgbgcomments
-- ----------------------------
INSERT INTO `sgbgcomments` VALUES (1, 1, 1710843891, 'Typecho', 0, 1, NULL, 'https://typecho.org', '127.0.0.1', 'Typecho 1.2.1', '欢迎加入 Typecho 大家族', 'comment', 'approved', 0);

-- ----------------------------
-- Table structure for sgbgcontents
-- ----------------------------
DROP TABLE IF EXISTS `sgbgcontents`;
CREATE TABLE `sgbgcontents`  (
  `cid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `slug` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created` int(10) UNSIGNED NULL DEFAULT 0,
  `modified` int(10) UNSIGNED NULL DEFAULT 0,
  `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `order` int(10) UNSIGNED NULL DEFAULT 0,
  `authorId` int(10) UNSIGNED NULL DEFAULT 0,
  `template` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'post',
  `status` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'publish',
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `commentsNum` int(10) UNSIGNED NULL DEFAULT 0,
  `allowComment` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `allowPing` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `allowFeed` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0',
  `parent` int(10) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`cid`) USING BTREE,
  UNIQUE INDEX `slug`(`slug`) USING BTREE,
  INDEX `created`(`created`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sgbgcontents
-- ----------------------------
INSERT INTO `sgbgcontents` VALUES (1, '欢迎使用 Typecho', 'start', 1710843891, 1710843891, '<!--markdown-->如果您看到这篇文章,表示您的 blog 已经安装成功.', 0, 1, NULL, 'post', 'publish', NULL, 1, '1', '1', '1', 0);
INSERT INTO `sgbgcontents` VALUES (2, '关于', 'start-page', 1710843891, 1710843891, '<!--markdown-->本页面由 Typecho 创建, 这只是个测试页面.', 0, 1, NULL, 'page', 'publish', NULL, 0, '1', '1', '1', 0);
INSERT INTO `sgbgcontents` VALUES (18, '31d1bca95-20240323154337-51610a638.mp4', '31d1bca95-20240323154337-51610a638-mp4', 1711179822, 1711179822, 'a:5:{s:4:\"name\";s:38:\"31d1bca95-20240323154337-51610a638.mp4\";s:4:\"path\";s:34:\"/usr/uploads/2024/03/193453856.mp4\";s:4:\"size\";i:153594;s:4:\"type\";s:3:\"mp4\";s:4:\"mime\";s:9:\"video/mp4\";}', 1, 1, NULL, 'attachment', 'publish', NULL, 0, '1', '0', '1', 20);
INSERT INTO `sgbgcontents` VALUES (19, '635cc66d1-20240323154337-f5e41cce2.jpg', '635cc66d1-20240323154337-f5e41cce2-jpg', 1711179823, 1711179823, 'a:5:{s:4:\"name\";s:38:\"635cc66d1-20240323154337-f5e41cce2.jpg\";s:4:\"path\";s:35:\"/usr/uploads/2024/03/1248717724.jpg\";s:4:\"size\";i:113802;s:4:\"type\";s:3:\"jpg\";s:4:\"mime\";s:10:\"image/jpeg\";}', 2, 1, NULL, 'attachment', 'publish', NULL, 0, '1', '0', '1', 20);
INSERT INTO `sgbgcontents` VALUES (20, '这是标题', '20', 1711179780, 1711179824, '<!--markdown-->\r\n/::D这是文字\r\n<video src=\"https://gzh2typecho.com/usr/uploads/2024/03/193453856.mp4\" controls></video>\r\n![](https://gzh2typecho.com/usr/uploads/2024/03/1248717724.jpg)', 0, 1, NULL, 'post', 'publish', NULL, 0, '1', '1', '1', 0);

-- ----------------------------
-- Table structure for sgbgfields
-- ----------------------------
DROP TABLE IF EXISTS `sgbgfields`;
CREATE TABLE `sgbgfields`  (
  `cid` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'str',
  `str_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `int_value` int(10) NULL DEFAULT 0,
  `float_value` float NULL DEFAULT 0,
  PRIMARY KEY (`cid`, `name`) USING BTREE,
  INDEX `int_value`(`int_value`) USING BTREE,
  INDEX `float_value`(`float_value`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sgbgfields
-- ----------------------------

-- ----------------------------
-- Table structure for sgbgmetas
-- ----------------------------
DROP TABLE IF EXISTS `sgbgmetas`;
CREATE TABLE `sgbgmetas`  (
  `mid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `slug` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `count` int(10) UNSIGNED NULL DEFAULT 0,
  `order` int(10) UNSIGNED NULL DEFAULT 0,
  `parent` int(10) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`mid`) USING BTREE,
  INDEX `slug`(`slug`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sgbgmetas
-- ----------------------------
INSERT INTO `sgbgmetas` VALUES (1, '默认分类', 'default', 'category', '只是一个默认分类', 1, 0, 0);
INSERT INTO `sgbgmetas` VALUES (2, '分类1', '分类1', 'category', '', 1, 1, 0);
INSERT INTO `sgbgmetas` VALUES (3, '标签1', '标签1', 'tag', NULL, 1, 0, 0);
INSERT INTO `sgbgmetas` VALUES (4, '标签99', '标签99', 'tag', NULL, 1, 0, 0);

-- ----------------------------
-- Table structure for sgbgoptions
-- ----------------------------
DROP TABLE IF EXISTS `sgbgoptions`;
CREATE TABLE `sgbgoptions`  (
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`name`, `user`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sgbgoptions
-- ----------------------------
INSERT INTO `sgbgoptions` VALUES ('actionTable', 0, 'a:0:{}');
INSERT INTO `sgbgoptions` VALUES ('allowRegister', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('allowXmlRpc', 0, '2');
INSERT INTO `sgbgoptions` VALUES ('attachmentTypes', 0, '@image@,@media@');
INSERT INTO `sgbgoptions` VALUES ('autoSave', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('charset', 0, 'UTF-8');
INSERT INTO `sgbgoptions` VALUES ('commentDateFormat', 0, 'F jS, Y \\a\\t h:i a');
INSERT INTO `sgbgoptions` VALUES ('commentsAntiSpam', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('commentsAutoClose', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('commentsAvatar', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('commentsAvatarRating', 0, 'G');
INSERT INTO `sgbgoptions` VALUES ('commentsCheckReferer', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('commentsHTMLTagAllowed', 0, NULL);
INSERT INTO `sgbgoptions` VALUES ('commentsListSize', 0, '10');
INSERT INTO `sgbgoptions` VALUES ('commentsMarkdown', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('commentsMaxNestingLevels', 0, '5');
INSERT INTO `sgbgoptions` VALUES ('commentsOrder', 0, 'ASC');
INSERT INTO `sgbgoptions` VALUES ('commentsPageBreak', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('commentsPageDisplay', 0, 'last');
INSERT INTO `sgbgoptions` VALUES ('commentsPageSize', 0, '20');
INSERT INTO `sgbgoptions` VALUES ('commentsPostInterval', 0, '60');
INSERT INTO `sgbgoptions` VALUES ('commentsPostIntervalEnable', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('commentsPostTimeout', 0, '2592000');
INSERT INTO `sgbgoptions` VALUES ('commentsRequireMail', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('commentsRequireModeration', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('commentsRequireURL', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('commentsShowCommentOnly', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('commentsShowUrl', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('commentsThreaded', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('commentsUrlNofollow', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('commentsWhitelist', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('contentType', 0, 'text/html');
INSERT INTO `sgbgoptions` VALUES ('defaultAllowComment', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('defaultAllowFeed', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('defaultAllowPing', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('defaultCategory', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('description', 0, 'Your description here.');
INSERT INTO `sgbgoptions` VALUES ('editorSize', 0, '350');
INSERT INTO `sgbgoptions` VALUES ('feedFullText', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('frontArchive', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('frontPage', 0, 'recent');
INSERT INTO `sgbgoptions` VALUES ('generator', 0, 'Typecho 1.2.1');
INSERT INTO `sgbgoptions` VALUES ('gzip', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('installed', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('keywords', 0, 'typecho,php,blog');
INSERT INTO `sgbgoptions` VALUES ('lang', 0, NULL);
INSERT INTO `sgbgoptions` VALUES ('markdown', 0, '1');
INSERT INTO `sgbgoptions` VALUES ('pageSize', 0, '5');
INSERT INTO `sgbgoptions` VALUES ('panelTable', 0, 'a:0:{}');
INSERT INTO `sgbgoptions` VALUES ('plugin:Gzh2typecho', 0, 'a:25:{s:22:\"Gzh2typecho-head-commc\";s:0:\"\";s:16:\"commcAllowOpenid\";s:1:\"*\";s:14:\"getMenuKeyWord\";s:9:\"@菜单|>\";s:7:\"menuSrc\";s:0:\"\";s:17:\"newArticleKeyWord\";s:12:\"@新文章|>\";s:15:\"setTitleKeyWord\";s:12:\"@设标题|>\";s:17:\"setContentKeyWord\";s:12:\"@设内容|>\";s:13:\"imgContentTpl\";s:27:\"![](MEDIA_URL_REPLACE_FLAG)\";s:15:\"videoContentTpl\";s:53:\"<video src=\"MEDIA_URL_REPLACE_FLAG\" controls></video>\";s:17:\"setPubDateKeyWord\";s:18:\"@设发布日期|>\";s:19:\"setCategeoryKeyWord\";s:12:\"@设分类|>\";s:16:\"categeoryOptions\";s:20:\"默认分类/分类1\";s:15:\"setLabelKeyWord\";s:12:\"@设标签|>\";s:18:\"setSecretLvKeyWord\";s:15:\"@设公开度|>\";s:15:\"defaultSecretLv\";s:6:\"公开\";s:18:\"setPrivCtrlKeyWord\";s:18:\"@设权限控制|>\";s:15:\"defaultPrivCtrl\";s:5:\"1,1,1\";s:21:\"setUsingNoticeKeyWord\";s:18:\"@设引用通告|>\";s:17:\"getPreviewKeyWord\";s:9:\"@预览|>\";s:14:\"PubPostKeyWord\";s:9:\"@发布|>\";s:20:\"Gzh2typecho-head-gzh\";s:0:\"\";s:10:\"mpDevAppID\";s:18:\"wxd419acb74f94858c\";s:14:\"mpDevAppSecret\";s:32:\"99f96c03a6e047337d13996c5ba67a09\";s:10:\"mpDevToken\";s:32:\"9XhsZQnrAGsCjNF7IwYQ2yVkbpOGW8NU\";s:19:\"mpDevEncodingAESKey\";s:43:\"EDfpwDeM4NsADjAYmsNxdiLG2SnTNYzpKGf8x9JqFH6\";}');
INSERT INTO `sgbgoptions` VALUES ('plugins', 0, 'a:2:{s:9:\"activated\";a:1:{s:11:\"Gzh2typecho\";a:1:{s:7:\"handles\";a:1:{s:22:\"Widget_Login:loginFail\";a:1:{i:0;s:48:\"TypechoPlugin\\Gzh2typecho\\Plugin::loginLoginFail\";}}}}s:7:\"handles\";a:1:{s:22:\"Widget_Login:loginFail\";a:1:{i:0;s:48:\"TypechoPlugin\\Gzh2typecho\\Plugin::loginLoginFail\";}}}');
INSERT INTO `sgbgoptions` VALUES ('postDateFormat', 0, 'Y-m-d');
INSERT INTO `sgbgoptions` VALUES ('postsListSize', 0, '10');
INSERT INTO `sgbgoptions` VALUES ('rewrite', 0, '0');
INSERT INTO `sgbgoptions` VALUES ('routingTable', 0, 'a:26:{i:0;a:25:{s:5:\"index\";a:6:{s:3:\"url\";s:1:\"/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:8:\"|^[/]?$|\";s:6:\"format\";s:1:\"/\";s:6:\"params\";a:0:{}}s:7:\"archive\";a:6:{s:3:\"url\";s:6:\"/blog/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:13:\"|^/blog[/]?$|\";s:6:\"format\";s:6:\"/blog/\";s:6:\"params\";a:0:{}}s:2:\"do\";a:6:{s:3:\"url\";s:22:\"/action/[action:alpha]\";s:6:\"widget\";s:14:\"\\Widget\\Action\";s:6:\"action\";s:6:\"action\";s:4:\"regx\";s:32:\"|^/action/([_0-9a-zA-Z-]+)[/]?$|\";s:6:\"format\";s:10:\"/action/%s\";s:6:\"params\";a:1:{i:0;s:6:\"action\";}}s:4:\"post\";a:6:{s:3:\"url\";s:24:\"/archives/[cid:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:26:\"|^/archives/([0-9]+)[/]?$|\";s:6:\"format\";s:13:\"/archives/%s/\";s:6:\"params\";a:1:{i:0;s:3:\"cid\";}}s:10:\"attachment\";a:6:{s:3:\"url\";s:26:\"/attachment/[cid:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:28:\"|^/attachment/([0-9]+)[/]?$|\";s:6:\"format\";s:15:\"/attachment/%s/\";s:6:\"params\";a:1:{i:0;s:3:\"cid\";}}s:8:\"category\";a:6:{s:3:\"url\";s:17:\"/category/[slug]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:25:\"|^/category/([^/]+)[/]?$|\";s:6:\"format\";s:13:\"/category/%s/\";s:6:\"params\";a:1:{i:0;s:4:\"slug\";}}s:3:\"tag\";a:6:{s:3:\"url\";s:12:\"/tag/[slug]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:20:\"|^/tag/([^/]+)[/]?$|\";s:6:\"format\";s:8:\"/tag/%s/\";s:6:\"params\";a:1:{i:0;s:4:\"slug\";}}s:6:\"author\";a:6:{s:3:\"url\";s:22:\"/author/[uid:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:24:\"|^/author/([0-9]+)[/]?$|\";s:6:\"format\";s:11:\"/author/%s/\";s:6:\"params\";a:1:{i:0;s:3:\"uid\";}}s:6:\"search\";a:6:{s:3:\"url\";s:19:\"/search/[keywords]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:23:\"|^/search/([^/]+)[/]?$|\";s:6:\"format\";s:11:\"/search/%s/\";s:6:\"params\";a:1:{i:0;s:8:\"keywords\";}}s:10:\"index_page\";a:6:{s:3:\"url\";s:21:\"/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:22:\"|^/page/([0-9]+)[/]?$|\";s:6:\"format\";s:9:\"/page/%s/\";s:6:\"params\";a:1:{i:0;s:4:\"page\";}}s:12:\"archive_page\";a:6:{s:3:\"url\";s:26:\"/blog/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:27:\"|^/blog/page/([0-9]+)[/]?$|\";s:6:\"format\";s:14:\"/blog/page/%s/\";s:6:\"params\";a:1:{i:0;s:4:\"page\";}}s:13:\"category_page\";a:6:{s:3:\"url\";s:32:\"/category/[slug]/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:34:\"|^/category/([^/]+)/([0-9]+)[/]?$|\";s:6:\"format\";s:16:\"/category/%s/%s/\";s:6:\"params\";a:2:{i:0;s:4:\"slug\";i:1;s:4:\"page\";}}s:8:\"tag_page\";a:6:{s:3:\"url\";s:27:\"/tag/[slug]/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:29:\"|^/tag/([^/]+)/([0-9]+)[/]?$|\";s:6:\"format\";s:11:\"/tag/%s/%s/\";s:6:\"params\";a:2:{i:0;s:4:\"slug\";i:1;s:4:\"page\";}}s:11:\"author_page\";a:6:{s:3:\"url\";s:37:\"/author/[uid:digital]/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:33:\"|^/author/([0-9]+)/([0-9]+)[/]?$|\";s:6:\"format\";s:14:\"/author/%s/%s/\";s:6:\"params\";a:2:{i:0;s:3:\"uid\";i:1;s:4:\"page\";}}s:11:\"search_page\";a:6:{s:3:\"url\";s:34:\"/search/[keywords]/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:32:\"|^/search/([^/]+)/([0-9]+)[/]?$|\";s:6:\"format\";s:14:\"/search/%s/%s/\";s:6:\"params\";a:2:{i:0;s:8:\"keywords\";i:1;s:4:\"page\";}}s:12:\"archive_year\";a:6:{s:3:\"url\";s:18:\"/[year:digital:4]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:19:\"|^/([0-9]{4})[/]?$|\";s:6:\"format\";s:4:\"/%s/\";s:6:\"params\";a:1:{i:0;s:4:\"year\";}}s:13:\"archive_month\";a:6:{s:3:\"url\";s:36:\"/[year:digital:4]/[month:digital:2]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:30:\"|^/([0-9]{4})/([0-9]{2})[/]?$|\";s:6:\"format\";s:7:\"/%s/%s/\";s:6:\"params\";a:2:{i:0;s:4:\"year\";i:1;s:5:\"month\";}}s:11:\"archive_day\";a:6:{s:3:\"url\";s:52:\"/[year:digital:4]/[month:digital:2]/[day:digital:2]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:41:\"|^/([0-9]{4})/([0-9]{2})/([0-9]{2})[/]?$|\";s:6:\"format\";s:10:\"/%s/%s/%s/\";s:6:\"params\";a:3:{i:0;s:4:\"year\";i:1;s:5:\"month\";i:2;s:3:\"day\";}}s:17:\"archive_year_page\";a:6:{s:3:\"url\";s:38:\"/[year:digital:4]/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:33:\"|^/([0-9]{4})/page/([0-9]+)[/]?$|\";s:6:\"format\";s:12:\"/%s/page/%s/\";s:6:\"params\";a:2:{i:0;s:4:\"year\";i:1;s:4:\"page\";}}s:18:\"archive_month_page\";a:6:{s:3:\"url\";s:56:\"/[year:digital:4]/[month:digital:2]/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:44:\"|^/([0-9]{4})/([0-9]{2})/page/([0-9]+)[/]?$|\";s:6:\"format\";s:15:\"/%s/%s/page/%s/\";s:6:\"params\";a:3:{i:0;s:4:\"year\";i:1;s:5:\"month\";i:2;s:4:\"page\";}}s:16:\"archive_day_page\";a:6:{s:3:\"url\";s:72:\"/[year:digital:4]/[month:digital:2]/[day:digital:2]/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:55:\"|^/([0-9]{4})/([0-9]{2})/([0-9]{2})/page/([0-9]+)[/]?$|\";s:6:\"format\";s:18:\"/%s/%s/%s/page/%s/\";s:6:\"params\";a:4:{i:0;s:4:\"year\";i:1;s:5:\"month\";i:2;s:3:\"day\";i:3;s:4:\"page\";}}s:12:\"comment_page\";a:6:{s:3:\"url\";s:53:\"[permalink:string]/comment-page-[commentPage:digital]\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:36:\"|^(.+)/comment\\-page\\-([0-9]+)[/]?$|\";s:6:\"format\";s:18:\"%s/comment-page-%s\";s:6:\"params\";a:2:{i:0;s:9:\"permalink\";i:1;s:11:\"commentPage\";}}s:4:\"feed\";a:6:{s:3:\"url\";s:20:\"/feed[feed:string:0]\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:4:\"feed\";s:4:\"regx\";s:17:\"|^/feed(.*)[/]?$|\";s:6:\"format\";s:7:\"/feed%s\";s:6:\"params\";a:1:{i:0;s:4:\"feed\";}}s:8:\"feedback\";a:6:{s:3:\"url\";s:31:\"[permalink:string]/[type:alpha]\";s:6:\"widget\";s:16:\"\\Widget\\Feedback\";s:6:\"action\";s:6:\"action\";s:4:\"regx\";s:29:\"|^(.+)/([_0-9a-zA-Z-]+)[/]?$|\";s:6:\"format\";s:5:\"%s/%s\";s:6:\"params\";a:2:{i:0;s:9:\"permalink\";i:1;s:4:\"type\";}}s:4:\"page\";a:6:{s:3:\"url\";s:12:\"/[slug].html\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";s:4:\"regx\";s:22:\"|^/([^/]+)\\.html[/]?$|\";s:6:\"format\";s:8:\"/%s.html\";s:6:\"params\";a:1:{i:0;s:4:\"slug\";}}}s:5:\"index\";a:3:{s:3:\"url\";s:1:\"/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:7:\"archive\";a:3:{s:3:\"url\";s:6:\"/blog/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:2:\"do\";a:3:{s:3:\"url\";s:22:\"/action/[action:alpha]\";s:6:\"widget\";s:14:\"\\Widget\\Action\";s:6:\"action\";s:6:\"action\";}s:4:\"post\";a:3:{s:3:\"url\";s:24:\"/archives/[cid:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:10:\"attachment\";a:3:{s:3:\"url\";s:26:\"/attachment/[cid:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:8:\"category\";a:3:{s:3:\"url\";s:17:\"/category/[slug]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:3:\"tag\";a:3:{s:3:\"url\";s:12:\"/tag/[slug]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:6:\"author\";a:3:{s:3:\"url\";s:22:\"/author/[uid:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:6:\"search\";a:3:{s:3:\"url\";s:19:\"/search/[keywords]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:10:\"index_page\";a:3:{s:3:\"url\";s:21:\"/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:12:\"archive_page\";a:3:{s:3:\"url\";s:26:\"/blog/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:13:\"category_page\";a:3:{s:3:\"url\";s:32:\"/category/[slug]/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:8:\"tag_page\";a:3:{s:3:\"url\";s:27:\"/tag/[slug]/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:11:\"author_page\";a:3:{s:3:\"url\";s:37:\"/author/[uid:digital]/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:11:\"search_page\";a:3:{s:3:\"url\";s:34:\"/search/[keywords]/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:12:\"archive_year\";a:3:{s:3:\"url\";s:18:\"/[year:digital:4]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:13:\"archive_month\";a:3:{s:3:\"url\";s:36:\"/[year:digital:4]/[month:digital:2]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:11:\"archive_day\";a:3:{s:3:\"url\";s:52:\"/[year:digital:4]/[month:digital:2]/[day:digital:2]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:17:\"archive_year_page\";a:3:{s:3:\"url\";s:38:\"/[year:digital:4]/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:18:\"archive_month_page\";a:3:{s:3:\"url\";s:56:\"/[year:digital:4]/[month:digital:2]/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:16:\"archive_day_page\";a:3:{s:3:\"url\";s:72:\"/[year:digital:4]/[month:digital:2]/[day:digital:2]/page/[page:digital]/\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:12:\"comment_page\";a:3:{s:3:\"url\";s:53:\"[permalink:string]/comment-page-[commentPage:digital]\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}s:4:\"feed\";a:3:{s:3:\"url\";s:20:\"/feed[feed:string:0]\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:4:\"feed\";}s:8:\"feedback\";a:3:{s:3:\"url\";s:31:\"[permalink:string]/[type:alpha]\";s:6:\"widget\";s:16:\"\\Widget\\Feedback\";s:6:\"action\";s:6:\"action\";}s:4:\"page\";a:3:{s:3:\"url\";s:12:\"/[slug].html\";s:6:\"widget\";s:15:\"\\Widget\\Archive\";s:6:\"action\";s:6:\"render\";}}');
INSERT INTO `sgbgoptions` VALUES ('secret', 0, 'YFYCn6&4eZ4eGJ)&s#jv3Tf897ZZUNcz');
INSERT INTO `sgbgoptions` VALUES ('siteUrl', 0, 'https://gzh2typecho.com');
INSERT INTO `sgbgoptions` VALUES ('theme', 0, 'default');
INSERT INTO `sgbgoptions` VALUES ('theme:default', 0, 'a:2:{s:7:\"logoUrl\";N;s:12:\"sidebarBlock\";a:5:{i:0;s:15:\"ShowRecentPosts\";i:1;s:18:\"ShowRecentComments\";i:2;s:12:\"ShowCategory\";i:3;s:11:\"ShowArchive\";i:4;s:9:\"ShowOther\";}}');
INSERT INTO `sgbgoptions` VALUES ('timezone', 0, '28800');
INSERT INTO `sgbgoptions` VALUES ('title', 0, 'Hello World');
INSERT INTO `sgbgoptions` VALUES ('xmlrpcMarkdown', 0, '0');

-- ----------------------------
-- Table structure for sgbgrelationships
-- ----------------------------
DROP TABLE IF EXISTS `sgbgrelationships`;
CREATE TABLE `sgbgrelationships`  (
  `cid` int(10) UNSIGNED NOT NULL,
  `mid` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`cid`, `mid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sgbgrelationships
-- ----------------------------
INSERT INTO `sgbgrelationships` VALUES (1, 1);
INSERT INTO `sgbgrelationships` VALUES (20, 2);
INSERT INTO `sgbgrelationships` VALUES (20, 3);
INSERT INTO `sgbgrelationships` VALUES (20, 4);

-- ----------------------------
-- Table structure for sgbgusers
-- ----------------------------
DROP TABLE IF EXISTS `sgbgusers`;
CREATE TABLE `sgbgusers`  (
  `uid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `mail` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `url` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `screenName` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created` int(10) UNSIGNED NULL DEFAULT 0,
  `activated` int(10) UNSIGNED NULL DEFAULT 0,
  `logged` int(10) UNSIGNED NULL DEFAULT 0,
  `group` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'visitor',
  `authCode` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`uid`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE,
  UNIQUE INDEX `mail`(`mail`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sgbgusers
-- ----------------------------
INSERT INTO `sgbgusers` VALUES (1, 'sigool', '$P$BPzs.txqvd0sjYNZSrlxOyvZz8n65//', 'sigool@sina.com', 'https://gzh2typecho.com', 'sigool', 1710843891, 1711180190, 1711179834, 'administrator', '0839cbd093dfc87a30c4dc1ddd60bab6');

SET FOREIGN_KEY_CHECKS = 1;
