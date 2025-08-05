<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Role extends BaseController
{
    /**
     * 获取角色列表
     */
    public function index(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $name = $request->param('name', '');
        $code = $request->param('code', '');
        $status = $request->param('status', '');

        $where = [];
        if ($name) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        if ($code) {
            $where[] = ['code', 'like', "%{$code}%"];
        }
        if ($status) {
            $where[] = ['status', '=', $status];
        }

        $total = Db::name('role')->where($where)->count();
        $list = Db::name('role')
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
    }

    /**
     * 创建角色
     */
    public function create(Request $request)
    {
        $data = $request->post();
        
        // 验证数据
        $validate = validate([
            'name' => 'require|max:50',
            'code' => 'require|max:50|unique:role',
            'description' => 'max:255',
            'status' => 'require|in:active,inactive'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        $data['create_time'] = date('Y-m-d H:i:s');

        try {
            Db::name('role')->insert($data);
            return json(['code' => 0, 'msg' => '创建成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '创建失败：' . $e->getMessage()]);
        }
    }

    /**
     * 更新角色
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
            'description' => 'max:255',
            'status' => 'require|in:active,inactive'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 检查code是否重复
        $exists = Db::name('role')
            ->where('code', $data['code'])
            ->where('id', '<>', $data['id'])
            ->find();
        
        if ($exists) {
            return json(['code' => 1, 'msg' => '角色编码已存在']);
        }

        $data['update_time'] = date('Y-m-d H:i:s');

        try {
            Db::name('role')->update($data);
            return json(['code' => 0, 'msg' => '更新成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 删除角色
     */
    public function delete(Request $request)
    {
        $id = $request->post('id');
        
        if (empty($id)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        // 检查是否有用户使用该角色
        $hasUsers = Db::name('user_role')
            ->where('role_id', $id)
            ->find();
        
        if ($hasUsers) {
            return json(['code' => 1, 'msg' => '该角色已被用户使用，无法删除']);
        }

        try {
            Db::startTrans();
            try {
                // 删除角色
                Db::name('role')->delete($id);
                // 删除角色权限关联
                Db::name('role_permission')->where('role_id', $id)->delete();
                
                Db::commit();
                return json(['code' => 0, 'msg' => '删除成功']);
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取角色权限
     */
    public function permissions(Request $request)
    {
        $roleId = (int)$request->param('role_id');
        if (!$roleId) {
            return json(['code' => 1, 'msg' => '角色ID不能为空']);
        }

        // 获取角色权限
        $permissions = Db::name('role_permission')
            ->alias('rp')
            ->join('permission p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->field('p.*')
            ->select()
            ->toArray();

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $permissions
        ]);
    }

    /**
     * 更新角色权限
     */
    public function updatePermissions(Request $request)
    {
        $roleId = (int)$request->post('role_id');
        $permissionIds = $request->post('permission_ids/a', []);
        
        if (!$roleId) {
            return json(['code' => 1, 'msg' => '角色ID不能为空']);
        }

        // 开启事务
        Db::startTrans();
        try {
            // 删除原有权限
            Db::name('role_permission')->where('role_id', $roleId)->delete();
            
            // 添加新权限
            if (!empty($permissionIds)) {
                $data = [];
                foreach ($permissionIds as $permissionId) {
                    $data[] = [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'create_time' => date('Y-m-d H:i:s')
                    ];
                }
                Db::name('role_permission')->insertAll($data);
            }
            
            Db::commit();
            return json(['code' => 0, 'msg' => '更新成功']);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取用户菜单
     */
    public function menus(Request $request)
    {
        try {
            // 从请求头获取token
            $token = $request->header('Authorization');
            if (!$token) {
                return json(['code' => 401, 'msg' => '未登录或token已过期']);
            }
            $token = str_replace('Bearer ', '', $token);
            // 验证token
            $key = config('jwt.key');
            try {
                $decoded = JWT::decode($token, new Key($key, 'HS256'));
                $userId = $decoded->uid;
            } catch (\Exception $e) {
                return json(['code' => 401, 'msg' => 'token无效或已过期']);
            }

            // 获取用户角色
            $roles = $this->getUserRoles($userId);
            
            // 如果是超级管理员，获取所有菜单
            if (in_array('admin', $roles)) {
                $menus = Db::name('permission')
                    ->where('type', 'menu')
                    ->where('status', 'active')
                    ->order('sort', 'asc')
                    ->order('id', 'asc')
                    ->select()
                    ->toArray();
            } else {
                // 获取用户角色的菜单权限
                $menus = Db::name('permission')
                    ->alias('p')
                    ->join('role_permission rp', 'p.id = rp.permission_id')
                    ->join('user_role ur', 'rp.role_id = ur.role_id')
                    ->where('ur.user_id', $userId)
                    ->where('p.type', 'menu')
                    ->where('p.status', 'active')
                    ->order('p.sort', 'asc')
                    ->order('p.id', 'asc')
                    ->select()
                    ->toArray();
            }

            // 构建菜单树
            $menuTree = $this->buildMenuTree($menus);

            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $menuTree
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '获取菜单失败：' . $e->getMessage()]);
        }
    }

    /**
     * 构建菜单树
     */
    private function buildMenuTree($menus, $parentId = 0)
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $children = $this->buildMenuTree($menus, $menu['id']);
                if ($children) {
                    $menu['children'] = $children;
                }
                $tree[] = $menu;
            }
        }
        return $tree;
    }
} 