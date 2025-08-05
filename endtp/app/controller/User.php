<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Request;
use think\facade\Cache;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class User extends BaseController
{
    /**
     * 用户登录
     */
    public function login(Request $request)
    {
        $username = $request->post('username');
        $password = $request->post('password');

        if (!$username || !$password) {
            return json(['code' => 1, 'msg' => '用户名和密码不能为空']);
        }

        try {
            $user = Db::name('user')
                ->where('username', $username)
                ->where('status', 'active')
                ->find();

            if (!$user || !password_verify($password, $user['password'])) {
                // 记录登录失败日志
                $this->recordLoginLog($user ? $user['id'] : 0, $username, $request->ip(), $request->header('user-agent'), 'failed', '用户名或密码错误');
                return json(['code' => 1, 'msg' => '用户名或密码错误']);
            }

            // 获取用户角色和权限
            $roles = $this->getUserRoles($user['id']);
            $permissions = $this->getUserPermissions($user['id']);

            // 生成JWT token
            $key = config('jwt.key');
            $payload = [
                'iss' => 'labmanage',
                'aud' => 'labmanage',
                'iat' => time(),
                'exp' => time() + 7200,
                'uid' => $user['id'],
                'username' => $user['username'],
                'roles' => $roles,
                'permissions' => $permissions
            ];
            $token = JWT::encode($payload, $key, 'HS256');

            // 更新登录信息
            Db::name('user')->where('id', $user['id'])->update([
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_ip' => $request->ip()
            ]);

            // 记录登录成功日志
            $this->recordLoginLog($user['id'], $username, $request->ip(), $request->header('user-agent'), 'success', '登录成功');

            // 返回用户信息和token
            unset($user['password']);
            return json([
                'code' => 0,
                'msg' => '登录成功',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                    'roles' => $roles,
                    'permissions' => $permissions
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '登录失败：' . $e->getMessage()]);
        }
    }

    /**
     * 用户注册
     */
    public function register(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = validate([
            'username' => 'require|max:50|unique:user',
            'password' => 'require|min:6|max:20',
            'name' => 'require|max:50',
            'phone' => 'require|mobile|unique:user',
            'role' => 'require|in:admin,teacher,student'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        try {
            // 密码加密
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['create_time'] = date('Y-m-d H:i:s');
            
            Db::startTrans();
            try {
                // 创建用户
                $userId = Db::name('user')->insertGetId($data);
                
                // 分配默认角色
                $roleId = Db::name('role')->where('code', $data['role'])->value('id');
                if ($roleId) {
                    Db::name('user_role')->insert([
                        'user_id' => $userId,
                        'role_id' => $roleId
                    ]);
                }
                
                Db::commit();
                return json(['code' => 0, 'msg' => '注册成功']);
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '注册失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取用户列表
     */
    public function index(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $username = $request->param('username', '');
        $name = $request->param('name', '');
        $role = $request->param('role', '');
        $status = $request->param('status', '');

        $where = [];
        if ($username) {
            $where[] = ['u.username', 'like', "%{$username}%"];
        }
        if ($name) {
            $where[] = ['u.name', 'like', "%{$name}%"];
        }
        if ($role) {
            $where[] = ['u.role', '=', $role];
        }
        if ($status) {
            $where[] = ['u.status', '=', $status];
        }

        $total = Db::name('user')->alias('u')->where($where)->count();
        $list = Db::name('user')
            ->alias('u')
            ->where($where)
            ->field([
                'u.*',
                'GROUP_CONCAT(r.name) as role_names'
            ])
            ->join('user_role ur', 'u.id = ur.user_id', 'left')
            ->join('role r', 'ur.role_id = r.id', 'left')
            ->group('u.id')
            ->page($page, $limit)
            ->order('u.id', 'desc')
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
     * 更新用户状态
     */
    public function updateStatus(Request $request)
    {
        $id = $request->post('id');
        $status = $request->post('status');

        if (!$id || !$status) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            Db::name('user')->where('id', $id)->update([
                'status' => $status,
                'update_time' => date('Y-m-d H:i:s')
            ]);
            return json(['code' => 0, 'msg' => '更新成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 重置密码
     */
    public function resetPassword(Request $request)
    {
        $id = $request->post('user_id');
        $newPassword = $request->post('new_password');

        if (!$id || !$newPassword) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            Db::name('user')->where('id', $id)->update([
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'update_time' => date('Y-m-d H:i:s')
            ]);
            return json(['code' => 0, 'msg' => '密码重置成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '密码重置失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取当前用户信息
     */
    public function info(Request $request)
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

            // 获取用户信息
            $user = Db::name('user')
                ->where('id', $userId)
                ->where('status', 'active')
                ->find();

            if (!$user) {
                return json(['code' => 404, 'msg' => '用户不存在或已被禁用']);
            }

            // 获取用户角色和权限
            $roles = $this->getUserRoles($userId);
            $permissions = $this->getUserPermissions($userId);

            // 移除敏感信息
            unset($user['password']);

            // 返回用户信息
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => [
                    'user' => $user,
                    'roles' => $roles,
                    'permissions' => $permissions
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '获取用户信息失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取用户角色
     */
    private function getUserRoles($userId)
    {
        return Db::name('user_role')
            ->alias('ur')
            ->join('role r', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->where('r.status', 'active')
            ->column('r.code');
    }

    /**
     * 获取用户权限
     */
    private function getUserPermissions($userId)
    {
        return Db::name('user_role')
            ->alias('ur')
            ->join('role_permission rp', 'ur.role_id = rp.role_id')
            ->join('permission p', 'rp.permission_id = p.id')
            ->where('ur.user_id', $userId)
            ->where('p.status', 'active')
            ->column('p.code');
    }

    /**
     * 记录登录日志
     */
    private function recordLoginLog($userId, $username, $ip, $userAgent, $status, $message)
    {
        try {
            Db::name('login_log')->insert([
                'user_id' => $userId,
                'username' => $username,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'status' => $status,
                'message' => $message,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // 记录日志失败不影响主流程
        }
    }

    /**
     * 获取登录日志
     */
    public function loginLogs(Request $request)
    {
        $page = $request->param('page', 1);
        $limit = $request->param('limit', 10);
        $username = $request->param('username', '');
        $status = $request->param('status', '');
        $startTime = $request->param('start_time', '');
        $endTime = $request->param('end_time', '');

        $where = [];
        if ($username) {
            $where[] = ['username', 'like', "%{$username}%"];
        }
        if ($status) {
            $where[] = ['status', '=', $status];
        }
        if ($startTime) {
            $where[] = ['create_time', '>=', $startTime];
        }
        if ($endTime) {
            $where[] = ['create_time', '<=', $endTime];
        }

        $total = Db::name('login_log')->where($where)->count();
        $list = Db::name('login_log')
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

    /**
     * 创建用户
     */
    public function create(Request $request)
    {
        $data = $request->post();
        
        // 验证数据
        $validate = validate([
            'username' => 'require|min:3|max:20|unique:user',
            'password' => 'require|min:6|max:20',
            'name' => 'require|max:50',
            'phone' => 'require|mobile',
            'email' => 'require|email',
            'role' => 'require|max:20'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 检查角色是否存在
        $role = Db::name('role')->where('code', $data['role'])->find();
        if (!$role) {
            return json(['code' => 1, 'msg' => '所选角色不存在']);
        }

        // 密码加密
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['status'] = 'active';

        try {
            // 开启事务
            Db::startTrans();
            
            // 创建用户
            $userId = Db::name('user')->insertGetId($data);
            
            // 分配角色
            Db::name('user_role')->insert([
                'user_id' => $userId,
                'role_id' => $role['id'],
                'create_time' => date('Y-m-d H:i:s')
            ]);
            
            Db::commit();
            return json(['code' => 0, 'msg' => '创建成功']);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 1, 'msg' => '创建失败：' . $e->getMessage()]);
        }
    }
} 