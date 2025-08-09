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
        if (!$lab || $lab['status'] !== 'active') {
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
            ->where($where)
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
} 