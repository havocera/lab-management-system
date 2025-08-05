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
        $roomNo = $request->param('room_no', '');

        $where = [];
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        if ($roomNo) {
            $where[] = ['room_no', 'like', "%{$roomNo}%"];
        }

        try {
            $total = Db::name('lab')->where($where)->count();
            $list = Db::name('lab')
                ->where($where)
                ->page($page, $limit)
                ->order('id', 'desc')
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
            return json(['code' => 1, 'msg' => '查询失败：' . $e->getMessage()]);
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
            'name' => 'require|max:50',
            'room_no' => 'require|max:10',
            'type' => 'require|in:physics,chemistry,biology,computer',
            'capacity' => 'require|number',
            'manager' => 'require|max:20',
            'contact' => 'require|mobile',
            'status' => 'require|in:idle,inuse,maintenance'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        $data['create_time'] = date('Y-m-d H:i:s');
        
        try {
            Db::name('lab')->insert($data);
            return json(['code' => 0, 'msg' => '添加成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '添加失败：' . $e->getMessage()]);
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
            'id' => 'require',
            'name' => 'require|max:50',
            'room_no' => 'require|max:10',
            'type' => 'require|in:physics,chemistry,biology,computer',
            'capacity' => 'require|number',
            'manager' => 'require|max:20',
            'contact' => 'require|mobile',
            'status' => 'require|in:active,inactive'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        $data['update_time'] = date('Y-m-d H:i:s');
        
        try {
            Db::name('lab')->where('id', $data['id'])->update($data);
            return json(['code' => 0, 'msg' => '更新成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 删除实验室
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            // 检查是否有关联的设备
            $equipmentCount = Db::name('equipment')->where('lab_id', $id)->count();
            
            if ($equipmentCount > 0) {
                return json(['code' => 1, 'msg' => '该实验室下还有设备，不能删除']);
            }

            Db::name('lab')->where('id', $id)->delete();
            return json(['code' => 0, 'msg' => '删除成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取实验室详情
     */
    public function detail(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            $info = Db::name('lab')->where('id', $id)->find();
            if (!$info) {
                return json(['code' => 1, 'msg' => '实验室不存在']);
            }
            return json(['code' => 0, 'msg' => 'success', 'data' => $info]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '查询失败：' . $e->getMessage()]);
        }
    }
} 