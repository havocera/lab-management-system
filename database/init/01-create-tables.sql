#!/bin/bash

# 设置数据库初始化脚本
echo "Initializing Laboratory Management System Database..."

# 创建基础表结构
mysql -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} << 'EOF'

-- 用户表
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `role` varchar(20) NOT NULL DEFAULT 'student' COMMENT '角色',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_phone` (`phone`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 角色表
CREATE TABLE IF NOT EXISTS `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `code` varchar(20) NOT NULL COMMENT '角色代码',
  `description` text COMMENT '描述',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

-- 用户角色关系表
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_role` (`user_id`, `role_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色关系表';

-- 权限表
CREATE TABLE IF NOT EXISTS `permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '权限名称',
  `code` varchar(50) NOT NULL COMMENT '权限代码',
  `type` varchar(20) NOT NULL DEFAULT 'menu' COMMENT '类型：menu菜单，button按钮',
  `parent_id` int(11) DEFAULT '0' COMMENT '父级ID',
  `path` varchar(200) DEFAULT NULL COMMENT '路径',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';

-- 角色权限关系表
CREATE TABLE IF NOT EXISTS `role_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `permission_id` int(11) NOT NULL COMMENT '权限ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role_id`, `permission_id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限关系表';

-- 实验室表
CREATE TABLE IF NOT EXISTS `lab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '实验室名称',
  `room_no` varchar(50) DEFAULT NULL COMMENT '房间号',
  `location` varchar(200) DEFAULT NULL COMMENT '位置',
  `capacity` int(11) DEFAULT '0' COMMENT '容量',
  `manager_id` int(11) DEFAULT NULL COMMENT '负责人ID',
  `manager_name` varchar(50) DEFAULT NULL COMMENT '负责人姓名',
  `manager_phone` varchar(20) DEFAULT NULL COMMENT '负责人电话',
  `manager_email` varchar(100) DEFAULT NULL COMMENT '负责人邮箱',
  `description` text COMMENT '描述',
  `status` varchar(20) NOT NULL DEFAULT '0' COMMENT '状态：0可用，1不可用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_manager_id` (`manager_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='实验室表';

-- 设备表
CREATE TABLE IF NOT EXISTS `equipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NOT NULL COMMENT '实验室ID',
  `name` varchar(100) NOT NULL COMMENT '设备名称',
  `model` varchar(100) DEFAULT NULL COMMENT '型号',
  `manufacturer` varchar(100) DEFAULT NULL COMMENT '制造商',
  `serial_number` varchar(100) DEFAULT NULL COMMENT '序列号',
  `purchase_date` date DEFAULT NULL COMMENT '采购日期',
  `purchase_price` decimal(10,2) DEFAULT NULL COMMENT '采购价格',
  `warranty_date` date DEFAULT NULL COMMENT '保修期至',
  `location` varchar(200) DEFAULT NULL COMMENT '位置',
  `status` varchar(20) NOT NULL DEFAULT 'normal' COMMENT '状态：normal正常，maintenance维修中，damaged故障，scrapped报废',
  `description` text COMMENT '描述',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_lab_id` (`lab_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='设备表';

-- 试剂表
CREATE TABLE IF NOT EXISTS `reagent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NOT NULL COMMENT '实验室ID',
  `name` varchar(100) NOT NULL COMMENT '试剂名称',
  `code` varchar(50) DEFAULT NULL COMMENT '试剂编号',
  `cas_number` varchar(50) DEFAULT NULL COMMENT 'CAS号',
  `molecular_formula` varchar(100) DEFAULT NULL COMMENT '分子式',
  `specification` varchar(100) DEFAULT NULL COMMENT '规格',
  `manufacturer` varchar(100) DEFAULT NULL COMMENT '制造商',
  `batch_number` varchar(50) DEFAULT NULL COMMENT '批号',
  `stock` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '库存量',
  `min_stock` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '最低库存',
  `unit` varchar(20) NOT NULL DEFAULT 'g' COMMENT '单位',
  `unit_price` decimal(10,2) DEFAULT NULL COMMENT '单价',
  `purchase_date` date DEFAULT NULL COMMENT '采购日期',
  `expiry_date` date DEFAULT NULL COMMENT '有效期',
  `storage_condition` varchar(100) DEFAULT NULL COMMENT '储存条件',
  `danger_level` varchar(20) DEFAULT 'low' COMMENT '危险等级：low低危，medium中危，high高危',
  `safety_info` text COMMENT '安全信息',
  `location` varchar(200) DEFAULT NULL COMMENT '存放位置',
  `keeper` varchar(50) DEFAULT NULL COMMENT '保管人',
  `image` varchar(500) DEFAULT NULL COMMENT '图片URL',
  `status` varchar(20) NOT NULL DEFAULT 'normal' COMMENT '状态：normal正常，expired过期，insufficient库存不足',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_lab_id` (`lab_id`),
  KEY `idx_code` (`code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试剂表';

-- 实验室预约表
CREATE TABLE IF NOT EXISTS `lab_reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NOT NULL COMMENT '实验室ID',
  `user_id` int(11) NOT NULL COMMENT '预约用户ID',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `purpose` text COMMENT '使用目的',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '状态：pending待审核，approved已批准，rejected已拒绝，completed已完成，cancelled已取消',
  `approve_time` datetime DEFAULT NULL COMMENT '审核时间',
  `approver_id` int(11) DEFAULT NULL COMMENT '审核人ID',
  `approve_remark` text COMMENT '审核备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_lab_id` (`lab_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_time_range` (`start_time`, `end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='实验室预约表';

-- 实验室使用记录表
CREATE TABLE IF NOT EXISTS `lab_usage_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL COMMENT '预约ID',
  `lab_id` int(11) NOT NULL COMMENT '实验室ID',
  `user_id` int(11) NOT NULL COMMENT '使用者ID',
  `actual_start_time` datetime NOT NULL COMMENT '实际开始时间',
  `actual_end_time` datetime NOT NULL COMMENT '实际结束时间',
  `power_off` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否断电：1是，0否',
  `air_conditioning_off` tinyint(1) NOT NULL DEFAULT '1' COMMENT '空调是否关闭：1是，0否',
  `hygiene_completed` tinyint(1) NOT NULL DEFAULT '1' COMMENT '卫生是否完成：1是，0否',
  `equipment_normal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '设备是否正常：1是，0否',
  `equipment_issues` text COMMENT '设备问题描述',
  `other_notes` text COMMENT '其他备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_id` (`reservation_id`),
  KEY `idx_lab_id` (`lab_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='实验室使用记录表';

-- 设备维护记录表
CREATE TABLE IF NOT EXISTS `maintenance_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL COMMENT '设备ID',
  `type` varchar(20) NOT NULL COMMENT '类型：scheduled定期维护，repair故障维修，upgrade升级改造',
  `title` varchar(200) NOT NULL COMMENT '维护标题',
  `description` text COMMENT '维护描述',
  `maintenance_date` date NOT NULL COMMENT '维护日期',
  `cost` decimal(10,2) DEFAULT NULL COMMENT '维护费用',
  `maintainer` varchar(100) DEFAULT NULL COMMENT '维护人员',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '状态：pending待处理，processing进行中，completed已完成，cancelled已取消',
  `result` text COMMENT '维护结果',
  `next_maintenance_date` date DEFAULT NULL COMMENT '下次维护日期',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_equipment_id` (`equipment_id`),
  KEY `idx_status` (`status`),
  KEY `idx_maintenance_date` (`maintenance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='设备维护记录表';

-- 试剂使用记录表
CREATE TABLE IF NOT EXISTS `reagent_usage_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reagent_id` int(11) NOT NULL COMMENT '试剂ID',
  `user_id` int(11) NOT NULL COMMENT '使用者ID',
  `amount` decimal(10,3) NOT NULL COMMENT '使用数量',
  `unit` varchar(20) NOT NULL COMMENT '单位',
  `purpose` varchar(500) DEFAULT NULL COMMENT '使用目的',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '状态：pending待审核，approved已通过，rejected已拒绝，completed已完成',
  `approve_time` datetime DEFAULT NULL COMMENT '审核时间',
  `approver_id` int(11) DEFAULT NULL COMMENT '审核人ID',
  `approve_remark` text COMMENT '审核备注',
  `usage_time` datetime DEFAULT NULL COMMENT '实际使用时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_reagent_id` (`reagent_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试剂使用记录表';

-- 登录日志表
CREATE TABLE IF NOT EXISTS `login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `username` varchar(50) DEFAULT NULL COMMENT '用户名',
  `ip` varchar(45) DEFAULT NULL COMMENT 'IP地址',
  `user_agent` text COMMENT '用户代理',
  `status` varchar(20) NOT NULL COMMENT '状态：success成功，failed失败',
  `message` varchar(500) DEFAULT NULL COMMENT '消息',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='登录日志表';

EOF

echo "Database tables created successfully!"