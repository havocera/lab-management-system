<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Request;

class Permission extends BaseController
{
    /**
     * 获取权限列表
     */
    public function index(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $name = $request->param('name', '');
        $type = $request->param('type', '');
        $status = $request->param('status', '');

        $where = [];
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        if ($type) {
            $where[] = ['type', '=', $type];
        }
        if ($status) {
            $where[] = ['status', '=', $status];
        }

        $total = Db::name('permission')->where($where)->count();
        $list = Db::name('permission')
            ->where($where)
            ->page($page, $limit)
            ->order('sort', 'asc')
            ->order('id', 'asc')
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
     * 获取权限树形结构
     */
    public function tree()
    {
        $list = Db::name('permission')
            ->where('status', 'active')
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        $tree = $this->buildTree($list);

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $tree
        ]);
    }

    /**
     * 创建权限
     */
    public function create(Request $request)
    {
        $data = $request->post();
        
        // 验证数据
        $validate = validate([
            'name' => 'require|max:50',
            'code' => 'require|max:50|unique:permission',
            'type' => 'require|in:menu,button,api',
            'parent_id' => 'number',
            'path' => 'max:255',
            'component' => 'max:255',
            'icon' => 'max:50',
            'sort' => 'number',
            'status' => 'require|in:active,inactive'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        $data['create_time'] = date('Y-m-d H:i:s');

        try {
            Db::name('permission')->insert($data);
            return json(['code' => 0, 'msg' => '创建成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '创建失败：' . $e->getMessage()]);
        }
    }

    /**
     * 更新权限
     */
    public function update(Request $request)
    {
        $data = $request->post();
        
        if (empty($data['id'])) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        // 验证数据
        $validate = validate([
            'name' => 'require|max:50',
            'code' => 'require|max:50',
            'type' => 'require|in:menu,button,api',
            'parent_id' => 'number',
            'path' => 'max:255',
            'component' => 'max:255',
            'icon' => 'max:50',
            'sort' => 'number',
            'status' => 'require|in:active,inactive'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 检查code是否重复
        $exists = Db::name('permission')
            ->where('code', $data['code'])
            ->where('id', '<>', $data['id'])
            ->find();
        
        if ($exists) {
            return json(['code' => 1, 'msg' => '权限编码已存在']);
        }

        $data['update_time'] = date('Y-m-d H:i:s');

        try {
            Db::name('permission')->update($data);
            return json(['code' => 0, 'msg' => '更新成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 删除权限
     */
    public function delete(Request $request)
    {
        $id = $request->post('id');
        
        if (empty($id)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        // 检查是否有子权限
        $hasChildren = Db::name('permission')
            ->where('parent_id', $id)
            ->find();
        
        if ($hasChildren) {
            return json(['code' => 1, 'msg' => '请先删除子权限']);
        }

        // 检查是否被角色使用
        $hasRoles = Db::name('role_permission')
            ->where('permission_id', $id)
            ->find();
        
        if ($hasRoles) {
            return json(['code' => 1, 'msg' => '该权限已被角色使用，无法删除']);
        }

        try {
            Db::name('permission')->delete($id);
            return json(['code' => 0, 'msg' => '删除成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 构建树形结构
     */
    private function buildTree($list, $parentId = 0)
    {
        $tree = [];
        foreach ($list as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($list, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
