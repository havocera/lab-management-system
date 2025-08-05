-- 创建试剂表
CREATE TABLE IF NOT EXISTS `reagent` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(50) NOT NULL COMMENT '试剂名称',
  `code` varchar(50) NOT NULL COMMENT '试剂编号',
  `lab_id` int(11) NOT NULL COMMENT '所属实验室ID',
  `image` varchar(255) DEFAULT NULL COMMENT '试剂图片',
  `specification` varchar(100) NOT NULL COMMENT '规格',
  `stock` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '库存量',
  `min_stock` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最低库存',
  `unit` varchar(20) NOT NULL COMMENT '单位',
  `danger_level` enum('low','medium','high') NOT NULL DEFAULT 'low' COMMENT '危险等级',
  `expiry_date` date NOT NULL COMMENT '有效期至',
  `manufacturer` varchar(100) NOT NULL COMMENT '生产厂商',
  `keeper` varchar(50) NOT NULL COMMENT '保管人',
  `location` varchar(100) NOT NULL COMMENT '存放位置',
  `safety_info` text COMMENT '安全说明',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_lab_id` (`lab_id`),
  KEY `idx_danger_level` (`danger_level`),
  KEY `idx_expiry_date` (`expiry_date`),
  CONSTRAINT `fk_reagent_lab` FOREIGN KEY (`lab_id`) REFERENCES `lab` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试剂表';

-- 创建试剂记录表
CREATE TABLE IF NOT EXISTS `reagent_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `reagent_id` int(11) NOT NULL COMMENT '试剂ID',
  `type` enum('in','out') NOT NULL COMMENT '操作类型：入库/出库',
  `amount` decimal(10,2) NOT NULL COMMENT '数量',
  `unit` varchar(20) NOT NULL COMMENT '单位',
  `operator` varchar(50) NOT NULL COMMENT '操作人',
  `remark` text COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_reagent_id` (`reagent_id`),
  KEY `idx_type` (`type`),
  KEY `idx_create_time` (`create_time`),
  CONSTRAINT `fk_record_reagent` FOREIGN KEY (`reagent_id`) REFERENCES `reagent` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试剂记录表'; 