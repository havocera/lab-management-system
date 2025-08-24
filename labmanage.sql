/*
 Navicat Premium Data Transfer

 Source Server         : 8.130.116.205
 Source Server Type    : MySQL
 Source Server Version : 50736 (5.7.36)
 Source Host           : 8.130.116.205:23306
 Source Schema         : labmanage

 Target Server Type    : MySQL
 Target Server Version : 50736 (5.7.36)
 File Encoding         : 65001

 Date: 23/08/2025 21:30:20
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for equipment
-- ----------------------------
DROP TABLE IF EXISTS `equipment`;
CREATE TABLE `equipment`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '设备名称',
  `model` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '型号',
  `serial_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '序列号',
  `lab_id` int(11) NULL DEFAULT NULL COMMENT '所属实验室ID',
  `status` enum('normal','maintenance','scrapped') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'normal' COMMENT '状态：正常/维护中/已报废',
  `purchase_date` date NOT NULL COMMENT '购买日期',
  `price` decimal(10, 2) NOT NULL COMMENT '购买价格',
  `manufacturer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '制造商',
  `maintenance_cycle` int(11) NOT NULL DEFAULT 0 COMMENT '维护周期(天)',
  `last_maintenance_time` datetime NULL DEFAULT NULL COMMENT '上次维护时间',
  `next_maintenance_time` datetime NULL DEFAULT NULL COMMENT '下次维护时间',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '描述',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '设备图片',
  `maintainer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '维护人员',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `serial_number`(`serial_number`) USING BTREE,
  INDEX `lab_id`(`lab_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of equipment
-- ----------------------------
INSERT INTO `equipment` VALUES (1, '测试', '测试', '1234567890', 2, 'normal', '2025-04-01', 0.00, '测试', 0, NULL, NULL, '', '2025-04-05 21:04:30', '2025-08-14 22:47:19', '', '测试');
INSERT INTO `equipment` VALUES (2, 'ces', 'ces', 'ces', 3, 'normal', '2025-08-14', 1.00, '1', 0, NULL, NULL, '', '2025-08-14 23:04:12', NULL, '', '1');

-- ----------------------------
-- Table structure for equipment_record
-- ----------------------------
DROP TABLE IF EXISTS `equipment_record`;
CREATE TABLE `equipment_record`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL COMMENT '设备ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `start_time` datetime NOT NULL COMMENT '开始使用时间',
  `end_time` datetime NULL DEFAULT NULL COMMENT '结束使用时间',
  `purpose` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '使用目的',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态：0-申请中，1-使用中，2-已完成，3-已取消',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '备注',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_equipment_id`(`equipment_id`) USING BTREE,
  INDEX `idx_user_id`(`user_id`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '设备使用记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of equipment_record
-- ----------------------------
INSERT INTO `equipment_record` VALUES (1, 1, 2, '2025-08-05 09:00:00', '2025-08-05 11:00:00', '细胞观察', 1, NULL, '2025-08-05 08:45:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (2, 2, 3, '2025-08-05 11:00:00', '2025-08-05 13:00:00', '样品分离', 2, NULL, '2025-08-05 10:45:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (3, 3, 2, '2025-08-05 14:00:00', '2025-08-05 15:00:00', '样品称重', 2, NULL, '2025-08-05 13:45:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (4, 1, 3, '2025-08-05 16:00:00', '2025-08-05 17:00:00', '组织观察', 1, NULL, '2025-08-05 15:45:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (5, 1, 2, '2025-08-04 10:00:00', '2025-08-04 12:00:00', '病理观察', 2, NULL, '2025-08-04 09:00:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (6, 2, 3, '2025-08-04 14:00:00', '2025-08-04 16:00:00', '蛋白分离', 2, NULL, '2025-08-04 13:00:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (7, 3, 2, '2025-08-04 16:00:00', '2025-08-04 17:00:00', '精密称重', 2, NULL, '2025-08-04 15:00:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (8, 1, 3, '2025-08-03 09:00:00', '2025-08-03 11:00:00', '血液观察', 2, NULL, '2025-08-03 08:00:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (9, 2, 2, '2025-08-03 15:00:00', '2025-08-03 17:00:00', 'DNA分离', 2, NULL, '2025-08-03 14:00:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (10, 3, 2, '2025-08-02 11:00:00', '2025-08-02 12:00:00', '化学试剂称重', 2, NULL, '2025-08-02 10:00:00', '2025-08-05 19:39:35');
INSERT INTO `equipment_record` VALUES (11, 1, 3, '2025-08-02 17:00:00', '2025-08-02 18:00:00', '细菌观察', 2, NULL, '2025-08-02 16:00:00', '2025-08-05 19:39:35');

-- ----------------------------
-- Table structure for lab
-- ----------------------------
DROP TABLE IF EXISTS `lab`;
CREATE TABLE `lab`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '实验室名称',
  `room_no` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '房间号',
  `type` enum('physics','chemistry','biology','computer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'physics' COMMENT '实验室类型：物理/化学/生物/计算机',
  `capacity` int(11) NOT NULL DEFAULT 0 COMMENT '容纳人数',
  `manager` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '管理员',
  `contact` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '联系电话',
  `status` enum('0','1','2','active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'active' COMMENT '状态：空闲/使用中/维护中',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '描述',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `manager_id` int(11) NULL DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `room_no`(`room_no`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of lab
-- ----------------------------
INSERT INTO `lab` VALUES (2, '等厚干涉现象实验室', 'A301', 'physics', 50, '王震宇', '17664005851', '0', '', '2025-04-05 20:09:39', '2025-08-05 21:40:52', 1, '测试');
INSERT INTO `lab` VALUES (3, '静电场模拟实验室', NULL, 'physics', 30, NULL, NULL, '0', '', '2025-08-10 22:28:28', NULL, 5, '306');

-- ----------------------------
-- Table structure for lab_reservation
-- ----------------------------
DROP TABLE IF EXISTS `lab_reservation`;
CREATE TABLE `lab_reservation`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NULL DEFAULT NULL COMMENT '实验室ID',
  `user_id` int(11) NULL DEFAULT NULL COMMENT '预约用户ID',
  `start_time` datetime NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime NULL DEFAULT NULL COMMENT '结束时间',
  `purpose` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '用途说明',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pending' COMMENT '状态：pending-待审核，approved-已批准，rejected-已拒绝，completed-已完成，cancelled-已取消',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_lab_id`(`lab_id`) USING BTREE,
  INDEX `idx_user_id`(`user_id`) USING BTREE,
  INDEX `idx_status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of lab_reservation
-- ----------------------------
INSERT INTO `lab_reservation` VALUES (1, 2, 1, '2025-04-07 00:00:00', '2025-04-15 00:00:00', 'ces', 'completed', '2025-04-06 19:51:25', '2025-08-15 19:01:13');
INSERT INTO `lab_reservation` VALUES (2, 2, 1, '2025-08-05 00:00:00', '2025-08-14 00:00:00', '测试', 'cancelled', '2025-08-05 19:35:16', '2025-08-05 20:40:52');
INSERT INTO `lab_reservation` VALUES (3, 2, 1, '2025-08-06 00:00:00', '2025-08-07 00:00:00', '测试2', 'cancelled', '2025-08-05 19:36:15', '2025-08-05 20:40:37');
INSERT INTO `lab_reservation` VALUES (15, 2, 1, '2025-08-10 03:00:00', '2025-08-10 15:00:00', '测试', 'pending', '2025-08-10 11:40:59', NULL);
INSERT INTO `lab_reservation` VALUES (16, 2, 1, '2025-08-11 01:00:00', '2025-08-11 20:00:00', 'ces', 'pending', '2025-08-10 19:21:35', NULL);
INSERT INTO `lab_reservation` VALUES (17, 2, 1, '2025-08-12 08:00:00', '2025-08-12 10:00:00', 'dfasfsafsf', 'cancelled', '2025-08-10 20:36:53', '2025-08-10 20:37:07');
INSERT INTO `lab_reservation` VALUES (18, 2, 1, '2025-08-12 19:00:00', '2025-08-12 20:00:00', '1323\n555', 'pending', '2025-08-10 20:52:04', NULL);
INSERT INTO `lab_reservation` VALUES (19, 2, 1, '2025-08-13 19:00:00', '2025-08-13 20:00:00', '1323\n555', 'pending', '2025-08-10 20:52:04', NULL);
INSERT INTO `lab_reservation` VALUES (20, 3, 5, '2025-08-11 09:00:00', '2025-08-11 10:00:00', '其味无穷玩儿', 'pending', '2025-08-10 22:35:08', NULL);
INSERT INTO `lab_reservation` VALUES (21, 2, 5, '2025-08-14 08:00:00', '2025-08-14 11:00:00', '测试中敌法舒服', 'approved', '2025-08-13 22:19:25', '2025-08-15 14:36:09');

-- ----------------------------
-- Table structure for lab_usage_record
-- ----------------------------
DROP TABLE IF EXISTS `lab_usage_record`;
CREATE TABLE `lab_usage_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL COMMENT '预约记录ID',
  `lab_id` int(11) NOT NULL COMMENT '实验室ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `power_off` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否断电：0-否，1-是',
  `air_conditioning_off` tinyint(1) NOT NULL DEFAULT 0 COMMENT '空调是否关闭：0-否，1-是',
  `hygiene_completed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '卫生是否完成：0-否，1-是',
  `equipment_normal` tinyint(1) NOT NULL DEFAULT 1 COMMENT '设备是否正常：0-否，1-是',
  `equipment_issues` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '设备问题描述',
  `other_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '其他备注',
  `actual_start_time` datetime NULL DEFAULT NULL COMMENT '实际开始使用时间',
  `actual_end_time` datetime NULL DEFAULT NULL COMMENT '实际结束使用时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `reservation_id`(`reservation_id`) USING BTREE,
  INDEX `lab_id`(`lab_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `create_time`(`create_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '实验室使用记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of lab_usage_record
-- ----------------------------
INSERT INTO `lab_usage_record` VALUES (1, 1, 2, 1, 1, 1, 1, 1, '', '', '2025-04-07 00:00:00', '2025-04-15 00:00:00', '2025-08-15 19:01:13', NULL);

-- ----------------------------
-- Table structure for login_log
-- ----------------------------
DROP TABLE IF EXISTS `login_log`;
CREATE TABLE `login_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '登录IP',
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '浏览器信息',
  `status` enum('success','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'success' COMMENT '登录状态',
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '登录消息',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 101 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of login_log
-- ----------------------------
INSERT INTO `login_log` VALUES (1, 0, 'dfsfs', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'failed', '用户名或密码错误', '2025-04-05 00:50:15');
INSERT INTO `login_log` VALUES (2, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 00:50:25');
INSERT INTO `login_log` VALUES (3, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 00:53:03');
INSERT INTO `login_log` VALUES (4, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 00:56:09');
INSERT INTO `login_log` VALUES (5, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 00:57:39');
INSERT INTO `login_log` VALUES (6, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'failed', '用户名或密码错误', '2025-04-05 01:02:17');
INSERT INTO `login_log` VALUES (7, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:02:26');
INSERT INTO `login_log` VALUES (8, 0, 'gfsdgfdsg', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'failed', '用户名或密码错误', '2025-04-05 01:15:29');
INSERT INTO `login_log` VALUES (9, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:15:42');
INSERT INTO `login_log` VALUES (10, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:16:14');
INSERT INTO `login_log` VALUES (11, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:16:52');
INSERT INTO `login_log` VALUES (12, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:18:00');
INSERT INTO `login_log` VALUES (13, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:18:20');
INSERT INTO `login_log` VALUES (14, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:29:10');
INSERT INTO `login_log` VALUES (15, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:29:24');
INSERT INTO `login_log` VALUES (16, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:31:24');
INSERT INTO `login_log` VALUES (17, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:39:12');
INSERT INTO `login_log` VALUES (18, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:39:43');
INSERT INTO `login_log` VALUES (19, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:41:27');
INSERT INTO `login_log` VALUES (20, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:42:31');
INSERT INTO `login_log` VALUES (21, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:43:29');
INSERT INTO `login_log` VALUES (22, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:43:59');
INSERT INTO `login_log` VALUES (23, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:44:14');
INSERT INTO `login_log` VALUES (24, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:45:27');
INSERT INTO `login_log` VALUES (25, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:46:56');
INSERT INTO `login_log` VALUES (26, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:47:16');
INSERT INTO `login_log` VALUES (27, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:47:28');
INSERT INTO `login_log` VALUES (28, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:51:10');
INSERT INTO `login_log` VALUES (29, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:54:09');
INSERT INTO `login_log` VALUES (30, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:55:39');
INSERT INTO `login_log` VALUES (31, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:56:23');
INSERT INTO `login_log` VALUES (32, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 01:58:07');
INSERT INTO `login_log` VALUES (33, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 02:03:11');
INSERT INTO `login_log` VALUES (34, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 02:05:32');
INSERT INTO `login_log` VALUES (35, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 02:06:27');
INSERT INTO `login_log` VALUES (36, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 19:59:26');
INSERT INTO `login_log` VALUES (37, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-05 22:23:51');
INSERT INTO `login_log` VALUES (38, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 10:04:35');
INSERT INTO `login_log` VALUES (39, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'failed', '用户名或密码错误', '2025-04-06 12:59:50');
INSERT INTO `login_log` VALUES (40, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 12:59:57');
INSERT INTO `login_log` VALUES (41, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:12:02');
INSERT INTO `login_log` VALUES (42, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:12:54');
INSERT INTO `login_log` VALUES (43, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:13:09');
INSERT INTO `login_log` VALUES (44, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:14:27');
INSERT INTO `login_log` VALUES (45, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:24:33');
INSERT INTO `login_log` VALUES (46, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:25:39');
INSERT INTO `login_log` VALUES (47, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:26:07');
INSERT INTO `login_log` VALUES (48, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:28:06');
INSERT INTO `login_log` VALUES (49, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:40:48');
INSERT INTO `login_log` VALUES (50, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:43:43');
INSERT INTO `login_log` VALUES (51, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 13:46:13');
INSERT INTO `login_log` VALUES (52, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 14:32:35');
INSERT INTO `login_log` VALUES (53, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 14:34:46');
INSERT INTO `login_log` VALUES (54, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 15:33:57');
INSERT INTO `login_log` VALUES (55, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 18:40:53');
INSERT INTO `login_log` VALUES (56, 4, '邓春丽', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 19:03:39');
INSERT INTO `login_log` VALUES (57, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 19:10:08');
INSERT INTO `login_log` VALUES (58, 4, '邓春丽', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 19:15:43');
INSERT INTO `login_log` VALUES (59, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 19:18:15');
INSERT INTO `login_log` VALUES (60, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 19:46:11');
INSERT INTO `login_log` VALUES (61, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 20:00:54');
INSERT INTO `login_log` VALUES (62, 4, '邓春丽', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 20:04:01');
INSERT INTO `login_log` VALUES (63, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 20:04:15');
INSERT INTO `login_log` VALUES (64, 4, '邓春丽', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 20:04:50');
INSERT INTO `login_log` VALUES (65, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 20:34:59');
INSERT INTO `login_log` VALUES (66, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 21:42:57');
INSERT INTO `login_log` VALUES (67, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-06 22:59:08');
INSERT INTO `login_log` VALUES (68, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-07 11:05:30');
INSERT INTO `login_log` VALUES (69, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-07 11:11:01');
INSERT INTO `login_log` VALUES (70, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-07 11:45:17');
INSERT INTO `login_log` VALUES (71, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-07 11:47:08');
INSERT INTO `login_log` VALUES (72, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-07 11:48:29');
INSERT INTO `login_log` VALUES (73, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-07 14:09:14');
INSERT INTO `login_log` VALUES (74, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'success', '登录成功', '2025-04-08 14:30:49');
INSERT INTO `login_log` VALUES (75, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-04 20:59:15');
INSERT INTO `login_log` VALUES (76, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'failed', '用户名或密码错误', '2025-08-04 23:54:28');
INSERT INTO `login_log` VALUES (77, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'failed', '用户名或密码错误', '2025-08-04 23:54:35');
INSERT INTO `login_log` VALUES (78, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-04 23:54:48');
INSERT INTO `login_log` VALUES (79, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-05 11:16:13');
INSERT INTO `login_log` VALUES (80, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-05 14:31:00');
INSERT INTO `login_log` VALUES (81, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-05 19:25:49');
INSERT INTO `login_log` VALUES (82, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-05 21:30:28');
INSERT INTO `login_log` VALUES (83, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-06 09:17:52');
INSERT INTO `login_log` VALUES (84, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-06 13:30:30');
INSERT INTO `login_log` VALUES (85, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-06 16:03:12');
INSERT INTO `login_log` VALUES (86, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-06 19:16:55');
INSERT INTO `login_log` VALUES (87, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-08 19:57:42');
INSERT INTO `login_log` VALUES (88, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-09 21:39:38');
INSERT INTO `login_log` VALUES (89, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-10 11:14:48');
INSERT INTO `login_log` VALUES (90, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-10 19:21:01');
INSERT INTO `login_log` VALUES (91, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-10 21:42:52');
INSERT INTO `login_log` VALUES (92, 5, '王震宇', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-10 21:46:29');
INSERT INTO `login_log` VALUES (93, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-10 22:25:53');
INSERT INTO `login_log` VALUES (94, 5, '王震宇', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-10 22:30:24');
INSERT INTO `login_log` VALUES (95, 5, '王震宇', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-13 22:18:33');
INSERT INTO `login_log` VALUES (96, 5, '王震宇', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-14 21:58:35');
INSERT INTO `login_log` VALUES (97, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-14 23:19:47');
INSERT INTO `login_log` VALUES (98, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-15 13:55:22');
INSERT INTO `login_log` VALUES (99, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-15 18:50:50');
INSERT INTO `login_log` VALUES (100, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'success', '登录成功', '2025-08-15 20:51:54');

-- ----------------------------
-- Table structure for maintenance_record
-- ----------------------------
DROP TABLE IF EXISTS `maintenance_record`;
CREATE TABLE `maintenance_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL COMMENT '设备ID',
  `type` enum('routine','preventive','corrective','emergency') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'routine' COMMENT '维护类型：定期维护/预防性维护/故障维修/紧急维修',
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium' COMMENT '优先级：低/中/高/紧急',
  `status` enum('pending','in_progress','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '状态：待维护/维护中/已完成/已取消',
  `scheduled_date` date NOT NULL COMMENT '计划维护日期',
  `actual_date` date NULL DEFAULT NULL COMMENT '实际维护日期',
  `technician` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '维护人员',
  `cost` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '维护费用',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '维护内容描述',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '备注信息',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `equipment_id`(`equipment_id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `priority`(`priority`) USING BTREE,
  INDEX `scheduled_date`(`scheduled_date`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '设备维护记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of maintenance_record
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (20240320, 'CreateLabReservationTable', '2025-04-06 19:31:37', '2025-04-06 19:31:37', 0);
INSERT INTO `migrations` VALUES (2024040401, 'CreateLabTable', '2025-04-04 22:41:56', '2025-04-04 22:41:56', 0);
INSERT INTO `migrations` VALUES (2024040402, 'CreateEquipmentTable', '2025-04-04 22:41:56', '2025-04-04 22:41:56', 0);
INSERT INTO `migrations` VALUES (2024040403, 'CreateUserTable', '2025-04-04 22:47:30', '2025-04-04 22:47:30', 0);
INSERT INTO `migrations` VALUES (2024040404, 'CreateRolePermissionTables', '2025-04-04 22:56:00', '2025-04-04 22:56:00', 0);
INSERT INTO `migrations` VALUES (2024040405, 'CreateReagentTable', '2025-04-05 21:29:23', '2025-04-05 21:29:24', 0);
INSERT INTO `migrations` VALUES (2024040406, 'CreateReagentRecordTable', '2025-04-05 21:29:24', '2025-04-05 21:29:24', 0);
INSERT INTO `migrations` VALUES (2024040407, 'CreateReagentRecordTable', '2025-04-06 11:12:00', '2025-04-06 11:12:00', 0);
INSERT INTO `migrations` VALUES (2024040707, 'CreateSystemLogTable', '2025-04-07 11:44:55', '2025-04-07 11:44:55', 0);

-- ----------------------------
-- Table structure for permission
-- ----------------------------
DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '权限名称',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '权限编码',
  `type` enum('menu','button','api') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'api' COMMENT '权限类型：菜单/按钮/接口',
  `parent_id` int(11) NULL DEFAULT 0 COMMENT '父级ID',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '路径',
  `component` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '前端组件',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '图标',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'active' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of permission
-- ----------------------------
INSERT INTO `permission` VALUES (1, '仪表盘', 'dashboard', 'menu', 0, '/dashboard', 'DashboardView', 'i-carbon-home', 1, 'active', '2025-04-06 15:29:52', '2025-08-06 13:47:28');
INSERT INTO `permission` VALUES (2, '实验室管理', 'labs', 'menu', 0, '/labs', 'LabListView', 'i-carbon-dashboard', 2, 'active', '2025-04-06 15:29:52', '2025-08-06 19:30:38');
INSERT INTO `permission` VALUES (3, '设备管理', 'equipment', 'menu', 0, '/equipment', 'EquipmentListView', 'i-carbon-sun', 3, 'active', '2025-04-06 15:29:52', '2025-04-06 22:02:34');
INSERT INTO `permission` VALUES (4, '试剂管理', 'reagents', 'menu', 0, '/reagents', 'ReagentListView', 'i-carbon-sun', 4, 'active', '2025-04-06 15:29:52', '2025-04-06 22:02:16');
INSERT INTO `permission` VALUES (5, '试剂使用', 'reagent_records', 'menu', 0, '/reagent/records', 'ReagentRecordView', 'i-carbon-sun', 5, 'active', '2025-04-06 15:29:52', '2025-08-15 13:56:11');
INSERT INTO `permission` VALUES (6, '用户管理', 'users', 'menu', 0, '/users', 'UserListView', 'i-carbon-user-multiple', 6, 'active', '2025-04-06 15:29:52', NULL);
INSERT INTO `permission` VALUES (7, '角色管理', 'roles', 'menu', 0, '/roles', 'RoleListView', 'i-carbon-sun', 8, 'active', '2025-04-06 16:12:36', '2025-04-06 22:02:24');
INSERT INTO `permission` VALUES (8, '菜单管理', 'permissions', 'menu', 0, '/permissions', 'PermissionListView', 'i-carbon-sun', 7, 'active', '2025-04-06 16:12:36', '2025-04-06 22:02:20');
INSERT INTO `permission` VALUES (9, '实验室预约', 'LabReservation', 'menu', 0, '/lab/reservation', 'LabReservationView', 'i-carbon-account', 2, 'active', '2025-04-06 19:26:07', '2025-08-09 21:42:01');
INSERT INTO `permission` VALUES (10, '试剂领用', 'ReagentUsage', 'menu', 0, '/reagent-usage', 'ReagentUsageView', 'i-carbon-view', 2, 'active', '2025-04-06 20:35:50', '2025-08-09 21:41:20');

-- ----------------------------
-- Table structure for reagent
-- ----------------------------
DROP TABLE IF EXISTS `reagent`;
CREATE TABLE `reagent`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '试剂名称',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '试剂编号',
  `lab_id` int(11) NULL DEFAULT 0 COMMENT '所属实验室ID',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '试剂图片',
  `specification` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '规格',
  `stock` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '库存量',
  `min_stock` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '最低库存',
  `unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '单位',
  `danger_level` enum('low','medium','high') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'low' COMMENT '危险等级',
  `expiry_date` date NOT NULL COMMENT '有效期至',
  `manufacturer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '生产厂商',
  `keeper` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '保管人',
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '存放位置',
  `safety_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '安全说明',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE,
  INDEX `lab_id`(`lab_id`) USING BTREE,
  INDEX `danger_level`(`danger_level`) USING BTREE,
  INDEX `expiry_date`(`expiry_date`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of reagent
-- ----------------------------
INSERT INTO `reagent` VALUES (1, '22', 'RG1234567', 2, '', '20', 100.00, 20.00, 'ml', 'low', '2025-04-01', '1', '1', '1', '', '2025-04-05 21:58:51', '2025-04-06 10:04:54');

-- ----------------------------
-- Table structure for reagent_record
-- ----------------------------
DROP TABLE IF EXISTS `reagent_record`;
CREATE TABLE `reagent_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reagent_id` int(11) NOT NULL COMMENT '试剂ID',
  `type` enum('in','out') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作类型：in=入库，out=出库',
  `amount` decimal(10, 2) NOT NULL COMMENT '数量',
  `unit` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '单位',
  `operator` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作人',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '备注',
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '状态：pending=待审核，approved=已通过，rejected=已拒绝',
  `approver` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '审核人',
  `approve_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '审核时间',
  `approve_remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '审核备注',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `reagent_id`(`reagent_id`) USING BTREE,
  INDEX `type`(`type`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `create_time`(`create_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of reagent_record
-- ----------------------------
INSERT INTO `reagent_record` VALUES (1, 1, 'out', 10.00, 'ml', 'admin', '', 'approved', 'admin', 1743918869, '1', 1743909131);
INSERT INTO `reagent_record` VALUES (2, 1, 'in', 10.00, 'ml', '1', '1', 'approved', NULL, NULL, NULL, 1743943106);
INSERT INTO `reagent_record` VALUES (3, 1, 'out', 30.00, 'ml', 'admin', '1', 'pending', NULL, NULL, NULL, 1754797407);
INSERT INTO `reagent_record` VALUES (4, 1, 'out', 20.00, 'ml', 'admin', 'ces', 'pending', NULL, NULL, NULL, 1755262172);

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名称',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色编码',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '角色描述',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'active' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES (1, '超级管理员', 'admin', '系统超级管理员', 'active', '2025-04-06 15:30:36', NULL);
INSERT INTO `role` VALUES (2, '实验员', 'teacher', '实验室教师', 'active', '2025-04-06 15:30:36', NULL);
INSERT INTO `role` VALUES (3, '学生', 'student', '实验室学生', 'active', '2025-04-06 15:30:36', NULL);
INSERT INTO `role` VALUES (4, '授课教师', 'cteacher', '授课教师', 'active', '2025-04-06 18:45:23', '2025-04-06 18:45:31');

-- ----------------------------
-- Table structure for role_permission
-- ----------------------------
DROP TABLE IF EXISTS `role_permission`;
CREATE TABLE `role_permission`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `permission_id` int(11) NOT NULL COMMENT '权限ID',
  `create_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `role_id`(`role_id`, `permission_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_permission
-- ----------------------------
INSERT INTO `role_permission` VALUES (5, 2, 1, '2025-04-06 20:04:31');
INSERT INTO `role_permission` VALUES (6, 2, 2, '2025-04-06 20:04:31');
INSERT INTO `role_permission` VALUES (7, 2, 9, '2025-04-06 20:04:31');
INSERT INTO `role_permission` VALUES (8, 2, 3, '2025-04-06 20:04:31');
INSERT INTO `role_permission` VALUES (9, 2, 4, '2025-04-06 20:04:31');

-- ----------------------------
-- Table structure for system_log
-- ----------------------------
DROP TABLE IF EXISTS `system_log`;
CREATE TABLE `system_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL COMMENT '用户ID',
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '操作类型',
  `target` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '操作目标',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `created_at`(`created_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of system_log
-- ----------------------------

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '姓名',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '手机号',
  `role` enum('admin','teacher','student') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'student' COMMENT '身份：管理员/教师/学生',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'active' COMMENT '状态：启用/禁用',
  `last_login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '最后登录IP',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE,
  UNIQUE INDEX `phone`(`phone`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 'admin', '$2y$10$y0PoNdPrq35OiBVf8AfW3.xAgpHxy1RQSJbstfnoSupZ5Uq8aeeoy', '管理员', '13800138000', 'admin', 'active', '2025-08-15 20:51:54', '127.0.0.1', '2025-04-04 22:47:49', '2025-04-07 11:23:11', NULL);
INSERT INTO `user` VALUES (4, '邓春丽', '$2y$10$/SuFPouwnBP6s4EPjHiRkehP0snNAzgpoWxQHFYHTwIXFgsbaNPpO', '邓春丽', '13838888888', 'teacher', 'active', '2025-04-06 20:04:50', '127.0.0.1', '2025-04-06 19:03:23', NULL, 'qq@qq.com');
INSERT INTO `user` VALUES (5, '王震宇', '$2y$10$bTuaiCiPM7X0CwTCOSO5PuTxztSJBNH8.lOqexWbr25irAANj67sa', 'wangzhneyu', '17664005851', 'teacher', 'active', '2025-08-14 21:58:35', '127.0.0.1', '2025-08-06 13:32:30', '2025-08-15 21:16:53', 'ihavoc@163.com');

-- ----------------------------
-- Table structure for user_role
-- ----------------------------
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `create_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `user_id`(`user_id`, `role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_role
-- ----------------------------
INSERT INTO `user_role` VALUES (1, 1, 1, NULL);
INSERT INTO `user_role` VALUES (2, 4, 2, '2025-04-06 19:03:23');
INSERT INTO `user_role` VALUES (3, 1, 2, NULL);
INSERT INTO `user_role` VALUES (4, 1, 3, NULL);
INSERT INTO `user_role` VALUES (5, 1, 4, NULL);
INSERT INTO `user_role` VALUES (8, 5, 2, '2025-08-15 21:16:53');

SET FOREIGN_KEY_CHECKS = 1;
