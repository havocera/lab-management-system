-- 插入默认角色
INSERT IGNORE INTO `role` (`name`, `code`, `description`, `status`) VALUES
('超级管理员', 'admin', '系统超级管理员，拥有所有权限', 'active'),
('教师', 'teacher', '教师角色，可以管理实验室和设备', 'active'),
('学生', 'student', '学生角色，可以预约实验室和使用设备', 'active');

-- 插入默认管理员用户
INSERT IGNORE INTO `user` (`username`, `password`, `name`, `phone`, `email`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系统管理员', '13800000000', 'admin@labmanage.com', 'admin', 'active');

-- 获取管理员用户和角色ID
SET @admin_user_id = (SELECT id FROM user WHERE username = 'admin');
SET @admin_role_id = (SELECT id FROM role WHERE code = 'admin');

-- 分配管理员角色
INSERT IGNORE INTO `user_role` (`user_id`, `role_id`) VALUES (@admin_user_id, @admin_role_id);

-- 插入系统权限
INSERT IGNORE INTO `permission` (`name`, `code`, `type`, `parent_id`, `path`, `icon`, `sort`, `status`) VALUES
('系统管理', 'system', 'menu', 0, '/system', 'Setting', 1000, 'active'),
('用户管理', 'system:user', 'menu', (SELECT id FROM permission WHERE code = 'system' LIMIT 1), '/system/user', 'User', 1010, 'active'),
('角色管理', 'system:role', 'menu', (SELECT id FROM permission WHERE code = 'system' LIMIT 1), '/system/role', 'UserFilled', 1020, 'active'),
('实验室管理', 'lab', 'menu', 0, '/lab', 'OfficeBuilding', 2000, 'active'),
('实验室列表', 'lab:list', 'menu', (SELECT id FROM permission WHERE code = 'lab' LIMIT 1), '/lab/list', 'List', 2010, 'active'),
('实验室预约', 'lab:reservation', 'menu', (SELECT id FROM permission WHERE code = 'lab' LIMIT 1), '/lab/reservation', 'Calendar', 2020, 'active'),
('设备管理', 'equipment', 'menu', 0, '/equipment', 'Monitor', 3000, 'active'),
('设备列表', 'equipment:list', 'menu', (SELECT id FROM permission WHERE code = 'equipment' LIMIT 1), '/equipment/list', 'List', 3010, 'active'),
('维护记录', 'equipment:maintenance', 'menu', (SELECT id FROM permission WHERE code = 'equipment' LIMIT 1), '/equipment/maintenance', 'Tools', 3020, 'active'),
('试剂管理', 'reagent', 'menu', 0, '/reagent', 'Opportunity', 4000, 'active'),
('试剂列表', 'reagent:list', 'menu', (SELECT id FROM permission WHERE code = 'reagent' LIMIT 1), '/reagent/list', 'List', 4010, 'active'),
('试剂领用', 'reagent:usage', 'menu', (SELECT id FROM permission WHERE code = 'reagent' LIMIT 1), '/reagent/usage', 'DocumentAdd', 4020, 'active');

-- 为管理员角色分配所有权限
INSERT IGNORE INTO `role_permission` (`role_id`, `permission_id`)
SELECT @admin_role_id, id FROM permission WHERE status = 'active';

-- 插入示例实验室数据
INSERT IGNORE INTO `lab` (`name`, `room_no`, `location`, `capacity`, `manager_name`, `manager_phone`, `manager_email`, `description`, `status`) VALUES
('计算机实验室', 'A101', '教学楼A栋1楼', 50, '张老师', '13800000001', 'zhang@school.edu', '配备50台计算机，用于计算机基础课程教学', '0'),
('物理实验室', 'B201', '实验楼B栋2楼', 30, '李老师', '13800000002', 'li@school.edu', '物理实验室，配备各种物理实验设备', '0'),
('化学实验室', 'C301', '实验楼C栋3楼', 40, '王老师', '13800000003', 'wang@school.edu', '化学实验室，配备通风设备和化学试剂', '0');

-- 插入示例设备数据
INSERT IGNORE INTO `equipment` (`lab_id`, `name`, `model`, `manufacturer`, `purchase_date`, `location`, `status`, `description`) VALUES
(1, '台式计算机', 'OptiPlex 7090', 'Dell', '2023-01-15', 'A101-001', 'normal', 'Intel i7处理器，16GB内存，512GB SSD'),
(1, '投影仪', 'EB-2247U', 'Epson', '2023-02-20', 'A101讲台', 'normal', '4200流明，WUXGA分辨率投影仪'),
(2, '数字万用表', 'FLUKE-179', 'Fluke', '2022-09-10', 'B201-柜子1', 'normal', '真有效值数字万用表'),
(2, '示波器', 'TDS2012C', 'Tektronix', '2022-11-05', 'B201-实验台1', 'normal', '100MHz带宽，2通道数字示波器'),
(3, '分析天平', 'ME204E', 'Mettler Toledo', '2023-03-12', 'C301-天平台', 'normal', '精度0.1mg的分析天平'),
(3, '通风柜', 'FH-1200', '本地制造', '2022-08-20', 'C301-墙边', 'normal', '排风量1200m³/h的通风柜');

-- 插入示例试剂数据
INSERT IGNORE INTO `reagent` (`lab_id`, `name`, `code`, `specification`, `manufacturer`, `stock`, `min_stock`, `unit`, `danger_level`, `safety_info`, `location`, `keeper`) VALUES
(3, '氢氧化钠', 'NaOH-001', 'AR，500g/瓶', '国药集团', 5.000, 2.000, '瓶', 'medium', '强碱性，避免接触皮肤和眼睛，储存在阴凉干燥处', 'C301-试剂柜A', '王老师'),
(3, '盐酸', 'HCl-001', 'AR，37%，500mL/瓶', '西陇科学', 3.000, 1.000, '瓶', 'high', '强酸性，腐蚀性强，使用时必须在通风柜内操作', 'C301-试剂柜B', '王老师'),
(3, '蒸馏水', 'H2O-001', '分析纯，4L/桶', '本地制备', 10.000, 5.000, '桶', 'low', '无特殊安全要求，常温储存即可', 'C301-储物柜', '王老师');

-- 更新统计信息
ANALYZE TABLE user, role, user_role, permission, role_permission, lab, equipment, reagent, lab_reservation, lab_usage_record, maintenance_record, reagent_usage_record, login_log;

-- 设置完成标志
SELECT 'Laboratory Management System database initialization completed!' as message;