<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Request;

class Equipment extends BaseController
{
    /**
     * 获取设备列表
     */
    public function index(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $name = $request->param('name', '');
        $serialNumber = $request->param('serial_number', '');
        $labId = $request->param('lab_id', '');
        $status = $request->param('status', '');
        $managerId = $request->param('manager_id', '');

        $where = [];
        if ($name) {
            $where[] = ['e.name', 'like', "%{$name}%"];
        }
        if ($serialNumber) {
            $where[] = ['e.serial_number', 'like', "%{$serialNumber}%"];
        }
        if ($labId) {
            $where[] = ['e.lab_id', '=', $labId];
        }
        if ($status) {
            $where[] = ['e.status', '=', $status];
        }
        
        // 如果传入了manager_id参数，只显示该管理员管理的实验室的设备
        if ($managerId) {
            $where[] = ['l.manager_id', '=', $managerId];
        }

        $total = Db::name('equipment')
            ->alias('e')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->count();

        $list = Db::name('equipment')
            ->alias('e')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->field([
                'e.*',
                'l.name as lab_name',
                'l.room_no as lab_room_no'
            ])
            ->page($page, $limit)
            ->order('e.id', 'desc')
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
     * 新增设备
     */
    public function add(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = validate([
            'name' => 'require|max:50',
            'model' => 'require|max:50',
            'serial_number' => 'require|max:50|unique:equipment',
            'lab_id' => 'require|number', // 验证lab_id字段:必填|必须为数字|必须存在于lab表的id字段中
            'status' => 'require|in:normal,maintenance,scrapped',
            'purchase_date' => 'require|date',
            'price' => 'require|float|egt:0',
            'manufacturer' => 'require|max:100',
            'image' => 'string',  // 添加 image 字段，设为可选
            'description' => 'string'  // 添加 description 字段，设为可选
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 转换日期格式
        if (isset($data['purchase_date'])) {
            $data['purchase_date'] = date('Y-m-d', strtotime($data['purchase_date']));
        }
        
        $data['create_time'] = date('Y-m-d H:i:s');
        
        try {
            Db::name('equipment')->insert($data);
            return json(['code' => 0, 'msg' => '添加成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '添加失败：' . $e->getMessage()]);
        }
    }

    /**
     * 更新设备
     */
    public function update(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = validate([
            'id' => 'require',
            'name' => 'require|max:50',
            'model' => 'require|max:50',
            'serial_number' => "require|max:50|unique:equipment,serial_number,{$data['id']},id",
            'lab_id' => 'require|number',
            'status' => 'require|in:normal,maintenance,scrapped',
            'purchase_date' => 'require|date',
            'price' => 'require|float|egt:0',
            'manufacturer' => 'require|max:100',
            'maintenance_cycle' => 'number|egt:0'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 如果状态改为维护中，更新维护时间
        $oldData = Db::name('equipment')->where('id', $data['id'])->find();
        if ($data['status'] == 'maintenance' && $oldData['status'] != 'maintenance') {
            $data['last_maintenance_time'] = date('Y-m-d H:i:s');
            if ($data['maintenance_cycle'] > 0) {
                $data['next_maintenance_time'] = date('Y-m-d H:i:s', strtotime("+{$data['maintenance_cycle']} days"));
            }
        }

        $data['update_time'] = date('Y-m-d H:i:s');
        
        try {
            Db::name('equipment')->where('id', $data['id'])->update($data);
            return json(['code' => 0, 'msg' => '更新成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 删除设备
     */
    public function delete(Request $request)
    {
        $id = $request->post('id');
        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            Db::name('equipment')->where('id', $id)->delete();
            return json(['code' => 0, 'msg' => '删除成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取设备详情
     */
    public function detail(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            $info = Db::name('equipment')
                ->alias('e')
                ->join('lab l', 'e.lab_id = l.id')
                ->field([
                    'e.*',
                    'l.name as lab_name',
                    'l.room_no as lab_room_no'
                ])
                ->where('e.id', $id)
                ->find();

            if (!$info) {
                return json(['code' => 1, 'msg' => '设备不存在']);
            }
            return json(['code' => 0, 'msg' => 'success', 'data' => $info]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '查询失败：' . $e->getMessage()]);
        }
    }
} 