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
                ->where('status', "pending")  // 待审批状态
                ->count();
            
            // 获取在线用户数量
            $onlineUsers = Db::table('user')
                ->where('last_login_time', '>', date('Y-m-d H:i:s', strtotime('-5 minutes')))
                ->count();
            
            return json([
                'code' => 200,
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
                'msg' => '获取统计数据失败：' . $e->getMessage()
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
                'code' => 200,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取今日实验室使用情况失败：' . $e->getMessage()
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
                'code' => 200,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取明日预约情况失败：' . $e->getMessage()
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
                ->where('ra.status', 0)  // 待审批状态
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
                'code' => 200,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取待审批试剂申领失败：' . $e->getMessage()
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
                return json(['code' => 200, 'msg' => '审批通过成功']);
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
                
            return json(['code' => 200, 'msg' => '拒绝成功']);
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
                'code' => 200,
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
                'msg' => '获取设备使用趋势失败：' . $e->getMessage()
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
                'code' => 200,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取实验室分布失败：' . $e->getMessage()
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
                'code' => 200,
                'msg' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取最近动态失败：' . $e->getMessage()
            ]);
        }
    }
} 