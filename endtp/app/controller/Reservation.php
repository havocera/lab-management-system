<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Request;

class Reservation extends BaseController
{
    /**
     * 创建预约
     */
    public function create(Request $request)
    {
        $data = $request->post();
        
        // 验证数据
        $validate = validate([
            'lab_id' => 'require|number',
            'start_time' => 'require|date',
            'end_time' => 'require|date',
            'purpose' => 'require|max:500'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 处理日期时间格式
        $data['start_time'] = date('Y-m-d H:i:s', strtotime($data['start_time']));
        $data['end_time'] = date('Y-m-d H:i:s', strtotime($data['end_time']));

        // 验证时间
        if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
            return json(['code' => 1, 'msg' => '结束时间必须大于开始时间']);
        }

        // 检查实验室是否存在且可用
        $lab = Db::name('lab')->where('id', $data['lab_id'])->find();
        if (!$lab || $lab['status'] !== '0') {
            return json(['code' => 1, 'msg' => '实验室不存在或不可用']);
        }

        // 检查时间段是否已被预约
        $exists = Db::name('lab_reservation')
            ->where('lab_id', $data['lab_id'])
            ->where('status', 'in', ['pending', 'approved'])
            ->where(function ($query) use ($data) {
                $query->whereOr([
                    ['start_time', 'between', [$data['start_time'], $data['end_time']]],
                    ['end_time', 'between', [$data['start_time'], $data['end_time']]]
                ]);
            })
            ->find();

        if ($exists) {
            return json(['code' => 1, 'msg' => '该时间段已被预约']);
        }

        // 添加预约记录
        $data['user_id'] = $request->user->uid;
        $data['status'] = 'pending';
        $data['create_time'] = date('Y-m-d H:i:s');

        try {
            Db::name('lab_reservation')->strict(false)->insert($data);
            return json(['code' => 0, 'msg' => '预约成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '预约失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取预约列表
     */
    public function list(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $lab_id = $request->param('lab_id');
        $status = $request->param('status');
        $start_time = $request->param('start_time');
        $end_time = $request->param('end_time');

        $where = [];
        if ($lab_id) {
            $where[] = ['r.lab_id', '=', $lab_id];
        }
        if ($status) {
            $where[] = ['r.status', '=', $status];
        }
        if ($start_time) {
            $where[] = ['r.start_time', '>=', $start_time];
        }
        if ($end_time) {
            $where[] = ['r.end_time', '<=', $end_time];
        }

        $total = Db::name('lab_reservation')
            ->alias('r')
            ->join('lab l', 'r.lab_id = l.id')
            ->join('user u', 'r.user_id = u.id')
            ->where($where)
            ->field('r.*, l.name as lab_name, u.name as user_name')
            ->order('r.create_time', 'desc')
           
            ->count();

        $list = Db::name('lab_reservation')
            ->alias('r')
            ->join('lab l', 'r.lab_id = l.id')
            ->join('user u', 'r.user_id = u.id')
            ->where($where)
            ->field('r.*, l.name as lab_name, u.name as user_name')
            ->order('r.create_time', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $list,
                'total' => $total
            ]
        ]);
    }

    /**
     * 获取我的预约
     */
    public function my(Request $request)
    {
        // 检查用户是否已登录
        if (!$request->user) {
            return json(['code' => 401, 'msg' => '请先登录']);
        }

        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $status = $request->param('status');

        $where = [['r.user_id', '=', $request->user->uid]];
        if ($status) {
            $where[] = ['r.status', '=', $status];
        }

        try {
            $total = Db::name('lab_reservation')
                ->alias('r')
                ->where($where)
                ->count();

            $list = Db::name('lab_reservation')
                ->alias('r')
                ->join('lab l', 'r.lab_id = l.id')
                ->where($where)
                ->field('r.*, l.name as lab_name')
                ->order('r.create_time', 'desc')
                ->page($page, $limit)
                ->select()
                ->toArray();

            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => [
                    'list' => $list,
                    'total' => $total
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取预约列表失败：' . $e->getMessage()]);
        }
    }

    /**
     * 取消预约
     */
    public function cancel(Request $request)
    {
        $id = $request->post('id');
        
        if (empty($id)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        // var_dump($request->user);
        // 检查预约是否存在且属于当前用户
        $reservation = Db::name('lab_reservation')
            ->where('id', $id)
            ->where('user_id', $request->user->uid)
            ->find();

        if (!$reservation) {
            return json(['code' => 1, 'msg' => '预约不存在或无权限取消']);
        }

        // 检查状态是否允许取消
        if (!in_array($reservation['status'], ['pending', 'approved'])) {
            return json(['code' => 1, 'msg' => '当前状态不允许取消']);
        }

        try {
            Db::name('lab_reservation')
                ->where('id', $id)
                ->update([
                    'status' => 'cancelled',
                    'update_time' => date('Y-m-d H:i:s')
                ]);
            return json(['code' => 0, 'msg' => '取消成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '取消失败：' . $e->getMessage()]);
        }
    }

    /**
     * 审核预约
     */
    public function review(Request $request)
    {
        $data = $request->post();
        
        if (empty($data['id']) || empty($data['status'])) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        if (!in_array($data['status'], ['approved', 'rejected'])) {
            return json(['code' => 1, 'msg' => '状态错误']);
        }

        // 检查预约是否存在
        $reservation = Db::name('lab_reservation')
            ->where('id', $data['id'])
            ->find();

        if (!$reservation) {
            return json(['code' => 1, 'msg' => '预约不存在']);
        }

        if ($reservation['status'] !== 'pending') {
            return json(['code' => 1, 'msg' => '只能审核待审核的预约']);
        }

        try {
            Db::name('lab_reservation')
                ->where('id', $data['id'])
                ->update([
                    'status' => $data['status'],
                    'update_time' => date('Y-m-d H:i:s')
                ]);
            return json(['code' => 0, 'msg' => '审核成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '审核失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取明日预约数量
     */
    public function tomorrowCount(Request $request)
    {
        try {
            // 获取明天的日期范围
            $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));
            $tomorrowEnd = date('Y-m-d 23:59:59', strtotime('+1 day'));
            
            // 查询明天的预约数量（包括待审核和已批准的）
            $count = Db::name('lab_reservation')
                ->where('status', 'in', ['pending', 'approved'])
                ->where('start_time', '>=', $tomorrowStart)
                ->where('start_time', '<=', $tomorrowEnd)
                ->count();
                
            // 获取明天的预约列表
            $list = Db::name('lab_reservation')
                ->alias('r')
                ->join('lab l', 'r.lab_id = l.id')
                ->join('user u', 'r.user_id = u.id')
                ->where('r.status', 'in', ['pending', 'approved'])
                ->where('r.start_time', '>=', $tomorrowStart)
                ->where('r.start_time', '<=', $tomorrowEnd)
                ->field([
                    'r.id',
                    'r.lab_id',
                    'r.user_id',
                    'r.start_time',
                    'r.end_time',
                    'r.purpose',
                    'r.status',
                    'l.name as lab_name',
                    'l.room_no',
                    'u.name as user_name'
                ])
                ->order('r.start_time', 'asc')
                ->limit(10)  // 只返回前10条
                ->select();
                
            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'count' => $count,
                    'list' => $list
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取失败：' . $e->getMessage()]);
        }
    }

    /**
     * 创建使用记录
     */
    public function createUsageRecord(Request $request)
    {
        $data = $request->post();
        
        // 验证数据
        $validate = validate([
            'reservation_id' => 'require|number',
            'power_off' => 'require|in:0,1',
            'air_conditioning_off' => 'require|in:0,1', 
            'hygiene_completed' => 'require|in:0,1',
            'equipment_normal' => 'require|in:0,1',
            'actual_start_time' => 'require|date',
            'actual_end_time' => 'require|date'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 检查预约是否存在且属于当前用户
        $reservation = Db::name('lab_reservation')
            ->where('id', $data['reservation_id'])
            ->where('user_id', $request->user->uid)
            ->where('status', 'approved')
            ->find();

        if (!$reservation) {
            return json(['code' => 1, 'msg' => '预约不存在或无权限操作']);
        }

        // 检查是否已存在使用记录
        $exists = Db::name('lab_usage_record')
            ->where('reservation_id', $data['reservation_id'])
            ->find();

        if ($exists) {
            return json(['code' => 1, 'msg' => '使用记录已存在']);
        }

        // 验证时间
        if (strtotime($data['actual_end_time']) <= strtotime($data['actual_start_time'])) {
            return json(['code' => 1, 'msg' => '结束时间必须大于开始时间']);
        }

        // 准备数据
        $recordData = [
            'reservation_id' => $data['reservation_id'],
            'lab_id' => $reservation['lab_id'],
            'user_id' => $request->user->uid,
            'power_off' => $data['power_off'],
            'air_conditioning_off' => $data['air_conditioning_off'],
            'hygiene_completed' => $data['hygiene_completed'],
            'equipment_normal' => $data['equipment_normal'],
            'equipment_issues' => $data['equipment_issues'] ?? '',
            'other_notes' => $data['other_notes'] ?? '',
            'actual_start_time' => date('Y-m-d H:i:s', strtotime($data['actual_start_time'])),
            'actual_end_time' => date('Y-m-d H:i:s', strtotime($data['actual_end_time'])),
            'create_time' => date('Y-m-d H:i:s')
        ];

        try {
            // 开始事务
            Db::startTrans();
            
            // 插入使用记录
            Db::name('lab_usage_record')->strict(false)->insert($recordData);
            
            // 更新预约状态为已完成
            Db::name('lab_reservation')
                ->where('id', $data['reservation_id'])
                ->update([
                    'status' => 'completed',
                    'update_time' => date('Y-m-d H:i:s')
                ]);
            
            Db::commit();
            return json(['code' => 0, 'msg' => '使用记录创建成功']);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 1, 'msg' => '创建失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取使用记录
     */
    public function getUsageRecord(Request $request)
    {
        $reservation_id = $request->param('reservation_id');
        
        if (empty($reservation_id)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            $record = Db::name('lab_usage_record')
                ->alias('ur')
                ->join('lab_reservation r', 'ur.reservation_id = r.id')
                ->join('lab l', 'ur.lab_id = l.id')
                ->join('user u', 'ur.user_id = u.id')
                ->where('ur.reservation_id', $reservation_id)
                ->field([
                    'ur.*',
                    'r.start_time as scheduled_start_time',
                    'r.end_time as scheduled_end_time',
                    'r.purpose',
                    'l.name as lab_name',
                    'l.room_no',
                    'u.name as user_name'
                ])
                ->find();

            if (!$record) {
                return json(['code' => 1, 'msg' => '使用记录不存在']);
            }

            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $record
            ]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取使用记录列表
     */
    public function getUsageRecords(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $lab_id = $request->param('lab_id');
        $user_id = $request->param('user_id');
        $start_date = $request->param('start_date');
        $end_date = $request->param('end_date');

        $where = [];
        if ($lab_id) {
            $where[] = ['ur.lab_id', '=', $lab_id];
        }
        if ($user_id) {
            $where[] = ['ur.user_id', '=', $user_id];
        }
        if ($start_date) {
            $where[] = ['ur.actual_start_time', '>=', $start_date . ' 00:00:00'];
        }
        if ($end_date) {
            $where[] = ['ur.actual_end_time', '<=', $end_date . ' 23:59:59'];
        }

        try {
            $total = Db::name('lab_usage_record')
                ->alias('ur')
                ->where($where)
                ->count();

            $list = Db::name('lab_usage_record')
                ->alias('ur')
                ->join('lab_reservation r', 'ur.reservation_id = r.id')
                ->join('lab l', 'ur.lab_id = l.id')
                ->join('user u', 'ur.user_id = u.id')
                ->where($where)
                ->field([
                    'ur.*',
                    'r.start_time as scheduled_start_time',
                    'r.end_time as scheduled_end_time',
                    'r.purpose',
                    'l.name as lab_name',
                    'l.room_no',
                    'u.name as user_name'
                ])
                ->order('ur.create_time', 'desc')
                ->page($page, $limit)
                ->select()
                ->toArray();

            return json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'list' => $list,
                    'total' => $total
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取失败：' . $e->getMessage()]);
        }
    }
} 