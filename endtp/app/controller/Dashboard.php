<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Cache;

class Dashboard extends BaseController
{
    /**
     * 获取仪表盘统计数据
     */
    public function statistics()
    {
        try {
            // 获取今日实验室使用数量
            $todayLabUsage = Db::table('lab_reservation')
                ->where('status', 'approved')  // 已批准的记录
                ->whereTime('start_time', '<=', date('Y-m-d H:i:s'))
                ->whereTime('end_time', '>=', date('Y-m-d H:i:s'))
                ->count();
            
            // 获取明日预约数量
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $tomorrowReservations = Db::table('lab_reservation')
                ->where('status', 'approved')
                ->whereDay('start_time', $tomorrow)
                ->count();
            
            // 获取待审批试剂数量
            $pendingReagents = Db::table('reagent_record')
                ->where('status', 'pending')  // 待审批状态
                ->count();
            
            // 获取在线用户数量
            $onlineUsers = Db::table('user')
                ->where('last_login_time', '>', date('Y-m-d H:i:s', strtotime('-5 minutes')))
                ->count();
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'todayLabUsage' => $todayLabUsage,
                    'tomorrowReservations' => $tomorrowReservations,
                    'pendingReagents' => $pendingReagents,
                    'onlineUsers' => $onlineUsers
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取统计数据失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取今日实验室使用情况
     */
    public function todayLabUsage()
    {
        try {
            $labs = Db::table('lab_reservation')
                ->alias('lr')
                ->join('lab l', 'l.id = lr.lab_id')
                // ->join('user u', 'u.id = lr.user_id')
                // ->field('lr.*, l.name, u.name as user_name, u.avatar as user_avatar')
                ->where('lr.status', 'approved')
                ->whereTime('lr.start_time', '<=', date('Y-m-d H:i:s'))
                ->whereTime('lr.end_time', '>=', date('Y-m-d H:i:s'))
                ->order('lr.start_time', 'asc')
                ->select();
                
            $data = [];
            foreach ($labs as $lab) {
                $data[] = [
                    'id' => $lab['id'],
                    'name' => $lab['name'],
                    // 'userName' => $lab['user_name'],
                    // 'userAvatar' => $lab['user_avatar'] ?: 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . $lab['user_id'],
                    'startTime' => date('H:i', strtotime($lab['start_time'])),
                    'endTime' => date('H:i', strtotime($lab['end_time'])),
                    'purpose' => $lab['purpose'],
                    'status' => '使用中'
                ];
            }
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取今日实验室使用情况失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取明日预约情况
     */
    public function tomorrowReservations()
    {
        try {
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $labs = Db::table('lab_reservation')
                ->alias('lr')
                ->join('lab l', 'l.id = lr.lab_id')
                // ->join('user u', 'u.id = lr.user_id')
                ->field('lr.*, l.name')
                ->where('lr.status', 'approved')
                ->whereDay('lr.start_time', $tomorrow)
                ->order('lr.start_time', 'asc')
                ->select();
                
            $data = [];
            foreach ($labs as $lab) {
                $data[] = [
                    'id' => $lab['id'],
                    'name' => $lab['name'],
                    // 'userName' => $lab['user_name'],
                    // 'userAvatar' => $lab['user_avatar'] ?: 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . $lab['user_id'],
                    'startTime' => date('H:i', strtotime($lab['start_time'])),
                    'endTime' => date('H:i', strtotime($lab['end_time'])),
                    'purpose' => $lab['purpose'],
                    'status' => '已预约'
                ];
            }
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取明日预约情况失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取待审批试剂申领
     */
    public function pendingReagents()
    {
        try {
            $reagents = Db::table('reagent_record')
                ->alias('ra')
                ->join('reagent r', 'r.id = ra.reagent_id')
                // ->join('user u', 'u.id = ra.user_id')
                ->field('ra.*, r.name as reagent_name, r.unit')
                ->where('ra.status', 'pending')  // 待审批状态
                ->order('ra.create_time', 'desc')
                ->select();
                
            $data = [];
            foreach ($reagents as $reagent) {
                $data[] = [
                    'id' => $reagent['id'],
                        // 'userName' => $reagent['user_name'],
                        // 'userAvatar' => $reagent['user_avatar'] ?: 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . $reagent['user_id'],
                    'reagentName' => $reagent['reagent_name'],
                    'amount' => $reagent['amount'],
                    'unit' => $reagent['unit'],
                    'purpose' => $reagent['purpose'],
                    'applyTime' => $reagent['create_time']
                ];
            }
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取待审批试剂申领失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 审批通过试剂申领
     */
    public function approveReagent()
    {
        try {
            $id = input('id');
            
            // 开启事务
            Db::startTrans();
            try {
                // 更新申请状态
                Db::table('reagent_record')
                    ->where('id', $id)
                    ->update([
                        'status' => 1,  // 已通过
                        'approved_by' => $this->request->user->id,
                        'approved_at' => date('Y-m-d H:i:s')
                    ]);
                    
                // 更新试剂库存
                $apply = Db::table('reagent_apply')->where('id', $id)->find();
                Db::table('reagent')
                    ->where('id', $apply['reagent_id'])
                    ->dec('stock', $apply['amount'])
                    ->update();
                    
                Db::commit();
                return json(['code' => 0, 'msg' => '审批通过成功']);
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '审批通过失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 拒绝试剂申领
     */
    public function rejectReagent()
    {
        try {
            $id = input('id');
            $reason = input('reason', '');
            
            Db::table('reagent_record')
                ->where('id', $id)
                ->update([
                    'status' => 2,  // 已拒绝
                    'rejected_by' => $this->request->user->id,
                    'rejected_at' => date('Y-m-d H:i:s'),
                    'reject_reason' => $reason
                ]);
                
            return json(['code' => 0, 'msg' => '拒绝成功']);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '拒绝失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 获取设备使用趋势
     */
    public function equipmentTrend()
    {
        try {
            $type = input('type', 'week');
            $dates = [];
            $equipmentRates = [];
            $labRates = [];
            
            switch ($type) {
                case 'week':
                    // 获取最近7天的数据
                    for ($i = 6; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-{$i} days"));
                        $dates[] = $date;
                        
                        // 计算设备使用率
                        $totalEquipment = Db::table('equipment')->count();
                        $usedEquipment = Db::table('equipment_record')
                            ->where('status', 'in', [1, 2])  // 使用中和已完成的记录
                            ->whereDay('created_at', $date)
                            ->count();
                        $equipmentRates[] = $totalEquipment > 0 ? round(($usedEquipment / $totalEquipment) * 100, 2) : 0;
                        
                        // 计算实验室使用率
                        $totalLabs = Db::table('lab')->count();
                        $usedLabs = Db::table('lab_record')
                            ->where('status', 'in', [1, 2])  // 使用中和已完成的记录
                            ->whereDay('created_at', $date)
                            ->count();
                        $labRates[] = $totalLabs > 0 ? round(($usedLabs / $totalLabs) * 100, 2) : 0;
                    }
                    break;
                    
                case 'month':
                    // 获取最近30天的数据
                    for ($i = 29; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-{$i} days"));
                        $dates[] = $date;
                        
                        // 计算设备使用率
                        $totalEquipment = Db::table('equipment')->count();
                        $usedEquipment = Db::table('equipment_record')
                            ->where('status', 'in', [1, 2])  // 使用中和已完成的记录
                            ->whereDay('created_at', $date)
                            ->count();
                        $equipmentRates[] = $totalEquipment > 0 ? round(($usedEquipment / $totalEquipment) * 100, 2) : 0;
                        
                        // 计算实验室使用率
                        $totalLabs = Db::table('lab')->count();
                        $usedLabs = Db::table('lab_record')
                            ->where('status', 'in', [1, 2])  // 使用中和已完成的记录
                            ->whereDay('created_at', $date)
                            ->count();
                        $labRates[] = $totalLabs > 0 ? round(($usedLabs / $totalLabs) * 100, 2) : 0;
                    }
                    break;
                    
                case 'year':
                    // 获取最近12个月的数据
                    for ($i = 11; $i >= 0; $i--) {
                        $date = date('Y-m', strtotime("-{$i} months"));
                        $dates[] = $date;
                        
                        // 计算设备使用率
                        $totalEquipment = Db::table('equipment')->count();
                        $usedEquipment = Db::table('equipment_record')
                            ->where('status', 'in', [1, 2])  // 使用中和已完成的记录
                            ->whereMonth('created_at', $date)
                            ->count();
                        $equipmentRates[] = $totalEquipment > 0 ? round(($usedEquipment / $totalEquipment) * 100, 2) : 0;
                        
                        // 计算实验室使用率
                        $totalLabs = Db::table('lab')->count();
                        $usedLabs = Db::table('lab_record')
                            ->where('status', 'in', [1, 2])  // 使用中和已完成的记录
                            ->whereMonth('created_at', $date)
                            ->count();
                        $labRates[] = $totalLabs > 0 ? round(($usedLabs / $totalLabs) * 100, 2) : 0;
                    }
                    break;
            }
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'dates' => $dates,
                    'equipmentRates' => $equipmentRates,
                    'labRates' => $labRates
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取设备使用趋势失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取实验室分布
     */
    public function labDistribution()
    {
        try {
            $type = input('type', 'usage');
            $data = [];
            
            if ($type === 'usage') {
                // 按使用率统计
                $labs = Db::table('lab')
                    ->field('id, name')
                    ->select();
                    
                foreach ($labs as $lab) {
                    $totalRecords = Db::table('lab_record')
                        ->where('lab_id', $lab['id'])
                        ->count();
                        
                    $data[] = [
                        'name' => $lab['name'],
                        'value' => $totalRecords
                    ];
                }
            } else {
                // 按设备数量统计
                $labs = Db::table('lab')
                    ->field('id, name')
                    ->select();
                    
                foreach ($labs as $lab) {
                    $equipmentCount = Db::table('equipment')
                        ->where('lab_id', $lab['id'])
                        ->count();
                        
                    $data[] = [
                        'name' => $lab['name'],
                        'value' => $equipmentCount
                    ];
                }
            }
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取实验室分布失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取最近动态
     */
    public function recentActivities()
    {
        try {
            $activities = Db::table('system_log')
                ->field('id, user_id, action, target, created_at')
                ->order('created_at', 'desc')
                ->limit(10)
                ->select();
                
            $data = [];
            foreach ($activities as $activity) {
                // 获取用户信息
                $user = Db::table('user')
                    ->field('name, avatar')
                    ->where('id', $activity['user_id'])
                    ->find();
                    
                $data[] = [
                    'id' => $activity['id'],
                    'user' => $user['name'],
                    'avatar' => $user['avatar'] ?: 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . $activity['user_id'],
                    'action' => $activity['action'],
                    'target' => $activity['target'],
                    'time' => $activity['created_at']
                ];
            }
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取最近动态失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取今日概览数据
     */
    public function todayOverview()
    {
        try {
            // 已完成任务（今日完成的实验室使用）
            $completedTasks = Db::table('lab_reservation')
                ->where('status', 'completed')
                ->whereDay('end_time', date('Y-m-d'))
                ->count();
            
            // 待处理任务（今日待审批的试剂申请）
            $pendingTasks = Db::table('reagent_record')
                ->where('status', 'pending')
                ->whereDay('create_time', date('Y-m-d'))
                ->count();
            
            // 活跃设备（今日有使用记录的设备）
            $activeEquipment = Db::table('equipment_record')
                ->whereDay('create_time', date('Y-m-d'))
                ->group('equipment_id')
                ->count();
            
            // 系统警报（待维护的设备）
            $alerts = Db::table('equipment')
                ->where('status', 'maintenance')
                ->count();
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'completedTasks' => $completedTasks,
                    'pendingTasks' => $pendingTasks,
                    'activeEquipment' => $activeEquipment,
                    'alerts' => $alerts
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取今日概览失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取系统状态
     */
    public function systemStatus()
    {
        try {
            // 模拟系统状态数据
            $cpu = rand(30, 80);
            $memory = rand(40, 85);
            $disk = rand(25, 70);
            $network = 'normal';
            
            // 也可以通过系统命令获取真实数据
            // $cpu = sys_getloadavg()[0] * 100;
            // $memory = memory_get_usage(true) / memory_get_peak_usage(true) * 100;
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'cpu' => $cpu,
                    'memory' => $memory,
                    'disk' => $disk,
                    'network' => $network
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取系统状态失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取使用趋势图表数据
     */
    public function usageTrend()
    {
        try {
            $period = input('period', 'today');
            $data = [];
            
            switch ($period) {
                case 'today':
                    // 今日每4小时的数据
                    for ($i = 0; $i < 24; $i += 4) {
                        $hourStart = sprintf('%02d:00:00', $i);
                        $hourEnd = sprintf('%02d:00:00', $i + 4);
                        $today = date('Y-m-d');
                        
                        // 实验室使用数量
                        $labUsage = Db::table('lab_reservation')
                            ->where('status', 'approved')
                            ->where('start_time', '>=', $today . ' ' . $hourStart)
                            ->where('start_time', '<', $today . ' ' . $hourEnd)
                            ->count();
                        
                        // 设备使用数量
                        $equipmentUsage = Db::table('equipment_record')
                            ->where('status', 'in', [1, 2])
                            ->where('create_time', '>=', $today . ' ' . $hourStart)
                            ->where('create_time', '<', $today . ' ' . $hourEnd)
                            ->count();
                        
                        // 试剂消耗数量
                        $reagentConsumption = Db::table('reagent_record')
                            ->where('status', 'completed')
                            ->where('create_time', '>=', $today . ' ' . $hourStart)
                            ->where('create_time', '<', $today . ' ' . $hourEnd)
                            ->count();
                        
                        $data[] = [
                            'time' => sprintf('%02d:00', $i),
                            'labUsage' => (int)$labUsage,
                            'equipmentUsage' => (int)$equipmentUsage,
                            'reagentConsumption' => (int)$reagentConsumption
                        ];
                    }
                    break;
                    
                case 'week':
                    // 本周每天的数据
                    for ($i = 6; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-{$i} days"));
                        
                        // 实验室使用数量
                        $labUsage = Db::table('lab_reservation')
                            ->where('status', 'approved')
                            ->whereDay('start_time', $date)
                            ->count();
                        
                        // 设备使用数量
                        $equipmentUsage = Db::table('equipment_record')
                            ->where('status', 'in', [1, 2])
                            ->whereDay('create_time', $date)
                            ->count();
                        
                        // 试剂消耗数量
                        $reagentConsumption = Db::table('reagent_record')
                            ->where('status', 'completed')
                            ->whereDay('create_time', $date)
                            ->count();
                        
                        $data[] = [
                            'time' => date('m-d', strtotime($date)),
                            'labUsage' => (int)$labUsage,
                            'equipmentUsage' => (int)$equipmentUsage,
                            'reagentConsumption' => (int)$reagentConsumption
                        ];
                    }
                    break;
                    
                case 'month':
                    // 本月每周的数据
                    for ($i = 3; $i >= 0; $i--) {
                        $weekStart = date('Y-m-d', strtotime("-{$i} weeks Monday"));
                        $weekEnd = date('Y-m-d', strtotime("-{$i} weeks Sunday"));
                        
                        // 实验室使用数量
                        $labUsage = Db::table('lab_reservation')
                            ->where('status', 'approved')
                            ->where('start_time', '>=', $weekStart . ' 00:00:00')
                            ->where('start_time', '<=', $weekEnd . ' 23:59:59')
                            ->count();
                        
                        // 设备使用数量
                        $equipmentUsage = Db::table('equipment_record')
                            ->where('status', 'in', [1, 2])
                            ->where('create_time', '>=', $weekStart . ' 00:00:00')
                            ->where('create_time', '<=', $weekEnd . ' 23:59:59')
                            ->count();
                        
                        // 试剂消耗数量
                        $reagentConsumption = Db::table('reagent_record')
                            ->where('status', 'completed')
                            ->where('create_time', '>=', $weekStart . ' 00:00:00')
                            ->where('create_time', '<=', $weekEnd . ' 23:59:59')
                            ->count();
                        
                        $data[] = [
                            'time' => '第' . (4 - $i) . '周',
                            'labUsage' => (int)$labUsage,
                            'equipmentUsage' => (int)$equipmentUsage,
                            'reagentConsumption' => (int)$reagentConsumption
                        ];
                    }
                    break;
            }
            
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取使用趋势失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
} 