<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Request;

class Lab extends BaseController
{
    /**
     * 获取实验室列表
     */
    public function index(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $name = $request->param('name', '');
        $location = $request->param('location', '');

        $where = [];
        if ($name) {
            $where[] = ['l.name', 'like', "%{$name}%"];
        }
        if ($location) {
            $where[] = ['l.location', 'like', "%{$location}%"];
        }

        try {
            $total = Db::name('lab')->alias('l')->where($where)->count();
            
            $list = Db::name('lab')
                ->alias('l')
                ->leftJoin('user u', 'l.manager_id = u.id')
                ->field('l.*, u.name as manager_name, u.email as manager_email, u.phone as manager_phone')
                ->where($where)
                ->page($page, $limit)
                ->order('l.id', 'desc')
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
            return json(['code' => 500, 'msg' => '查询失败：' . $e->getMessage(), 'data' => []]);
        }
    }

    /**
     * 新增实验室
     */
    public function add(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = validate([
            'name' => 'require|max:100',
            'location' => 'max:200',
            'capacity' => 'number',
            'description' => 'max:500',
            'manager_id' => 'number',
            'status' => 'in:0,1'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 422, 'msg' => $validate->getError(), 'data' => []]);
        }

        // 验证负责人是否存在
        if (isset($data['manager_id']) && $data['manager_id']) {
            $manager = Db::name('user')->where('id', $data['manager_id'])->find();
            if (!$manager) {
                return json(['code' => 400, 'msg' => '指定的负责人不存在', 'data' => []]);
            }
        }

        $data['create_time'] = date('Y-m-d H:i:s');
        
        try {
            $id = Db::name('lab')->insertGetId($data);
            return json(['code' => 0, 'msg' => '添加成功', 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '添加失败：' . $e->getMessage(), 'data' => []]);
        }
    }

    /**
     * 更新实验室
     */
    public function update(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = validate([
            'id' => 'require|number',
            'name' => 'require|max:100',
            'location' => 'max:200',
            'capacity' => 'number',
            'description' => 'max:500',
            'manager_id' => 'number',
            'status' => 'in:0,1,2'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 422, 'msg' => $validate->getError(), 'data' => []]);
        }

        // 验证实验室是否存在
        $lab = Db::name('lab')->where('id', $data['id'])->find();
        if (!$lab) {
            return json(['code' => 404, 'msg' => '实验室不存在', 'data' => []]);
        }

        // 验证负责人是否存在
        if (isset($data['manager_id']) && $data['manager_id']) {
            $manager = Db::name('user')->where('id', $data['manager_id'])->find();
            if (!$manager) {
                return json(['code' => 400, 'msg' => '指定的负责人不存在', 'data' => []]);
            }
        }

        $data['update_time'] = date('Y-m-d H:i:s');
        $id = $data['id'];
        unset($data['id']);
        
        try {
            Db::name('lab')->where('id', $id)->update($data);
            return json(['code' => 0, 'msg' => '更新成功', 'data' => []]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '更新失败：' . $e->getMessage(), 'data' => []]);
        }
    }

    /**
     * 删除实验室
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            return json(['code' => 400, 'msg' => '参数错误', 'data' => []]);
        }

        try {
            // 验证实验室是否存在
            $lab = Db::name('lab')->where('id', $id)->find();
            if (!$lab) {
                return json(['code' => 404, 'msg' => '实验室不存在', 'data' => []]);
            }

            // 检查是否有关联的设备
            $equipmentCount = Db::name('equipment')->where('lab_id', $id)->count();
            
            if ($equipmentCount > 0) {
                return json(['code' => 400, 'msg' => '该实验室下还有设备，不能删除', 'data' => []]);
            }

            // 检查是否有未完成的预约
            $reservationCount = Db::name('lab_reservation')
                ->where('lab_id', $id)
                ->where('status', 'in', ['pending', 'approved'])
                ->count();
                
            if ($reservationCount > 0) {
                return json(['code' => 400, 'msg' => '该实验室还有未完成的预约，不能删除', 'data' => []]);
            }

            Db::name('lab')->where('id', $id)->delete();
            return json(['code' => 0, 'msg' => '删除成功', 'data' => []]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '删除失败：' . $e->getMessage(), 'data' => []]);
        }
    }

    /**
     * 获取实验室详情
     */
    public function detail(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            return json(['code' => 400, 'msg' => '参数错误', 'data' => []]);
        }

        try {
            $info = Db::name('lab')
                ->alias('l')
                ->leftJoin('user u', 'l.manager_id = u.id')
                ->field('l.*, u.name as manager_name, u.email as manager_email, u.phone as manager_phone')
                ->where('l.id', $id)
                ->find();
                
            if (!$info) {
                return json(['code' => 404, 'msg' => '实验室不存在', 'data' => []]);
            }
            
            return json(['code' => 0, 'msg' => '获取成功', 'data' => $info]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '查询失败：' . $e->getMessage(), 'data' => []]);
        }
    }

    /**
     * 获取可选择的用户列表（用于设置负责人）
     */
    public function getUsers(Request $request)
    {
        try {
            $users = Db::name('user')
                ->field('id, name, email, phone')
                ->where('status', 1)
                ->order('name', 'asc')
                ->select()
                ->toArray();

            return json(['code' => 0, 'msg' => '获取成功', 'data' => $users]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '查询失败：' . $e->getMessage(), 'data' => []]);
        }
    }

    /**
     * 更换实验室负责人
     */
    public function changeManager(Request $request)
    {
        $labId = $request->param('lab_id');
        $managerId = $request->param('manager_id');

        if (!$labId) {
            return json(['code' => 400, 'msg' => '实验室ID不能为空', 'data' => []]);
        }

        try {
            // 验证实验室是否存在
            $lab = Db::name('lab')->where('id', $labId)->find();
            if (!$lab) {
                return json(['code' => 404, 'msg' => '实验室不存在', 'data' => []]);
            }

            // 验证新负责人是否存在（如果提供了负责人ID）
            if ($managerId) {
                $manager = Db::name('user')->where('id', $managerId)->where('status', 1)->find();
                if (!$manager) {
                    return json(['code' => 400, 'msg' => '指定的负责人不存在或已被禁用', 'data' => []]);
                }
            }

            // 更新负责人
            Db::name('lab')->where('id', $labId)->update([
                'manager_id' => $managerId ?: null,
                'update_time' => date('Y-m-d H:i:s')
            ]);

            return json(['code' => 0, 'msg' => '负责人更换成功', 'data' => []]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '更换失败：' . $e->getMessage(), 'data' => []]);
        }
    }
    
    /**
     * 获取待审批的实验室预约
     */
    public function pendingReservations()
    {
        try {
            $pendingList = Db::name('lab_reservation')
                ->alias('r')
                ->join('lab l', 'r.lab_id = l.id')
                ->join('user u', 'r.user_id = u.id')
                ->where('r.status', 'pending')
                ->field([
                    'r.id',
                    'r.lab_id',
                    'r.user_id',
                    'r.start_time',
                    'r.end_time',
                    'r.purpose',
                    'r.create_time',
                    'l.name as lab_name',
                    'l.room_no',
                    'u.name as user_name',
                    'u.phone as user_phone'
                ])
                ->order('r.create_time', 'desc')
                ->select();
                
            // 格式化时间
            foreach ($pendingList as &$item) {
                $item['start_time'] = date('Y-m-d H:i', strtotime($item['start_time']));
                $item['end_time'] = date('Y-m-d H:i', strtotime($item['end_time']));
                $item['create_time'] = strtotime($item['create_time']);
            }
            
            return json(['code' => 0, 'msg' => '获取成功', 'data' => $pendingList]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取失败：' . $e->getMessage()]);
        }
    }
} 