<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateRolePermissionTables extends Migrator
{
    public function change()
    {
        // 角色表
        $this->table('role', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '角色名称'])
            ->addColumn('code', 'string', ['limit' => 50, 'null' => false, 'comment' => '角色编码'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '角色描述'])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactive'], 'default' => 'active', 'comment' => '状态'])
            ->addColumn('create_time', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['code'], ['unique' => true])
            ->create();

        // 权限表
        $this->table('permission', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '权限名称'])
            ->addColumn('code', 'string', ['limit' => 50, 'null' => false, 'comment' => '权限编码'])
            ->addColumn('type', 'enum', ['values' => ['menu', 'button', 'api'], 'default' => 'api', 'comment' => '权限类型：菜单/按钮/接口'])
            ->addColumn('parent_id', 'integer', ['null' => true, 'default' => 0, 'comment' => '父级ID'])
            ->addColumn('path', 'string', ['limit' => 255, 'null' => true, 'comment' => '路径'])
            ->addColumn('component', 'string', ['limit' => 255, 'null' => true, 'comment' => '前端组件'])
            ->addColumn('icon', 'string', ['limit' => 50, 'null' => true, 'comment' => '图标'])
            ->addColumn('sort', 'integer', ['null' => false, 'default' => 0, 'comment' => '排序'])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactive'], 'default' => 'active', 'comment' => '状态'])
            ->addColumn('create_time', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['code'], ['unique' => true])
            ->create();

        // 角色权限关联表
        $this->table('role_permission', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('role_id', 'integer', ['null' => false, 'comment' => '角色ID'])
            ->addColumn('permission_id', 'integer', ['null' => false, 'comment' => '权限ID'])
            ->addIndex(['role_id', 'permission_id'], ['unique' => true])
            ->create();

        // 用户角色关联表
        $this->table('user_role', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('user_id', 'integer', ['null' => false, 'comment' => '用户ID'])
            ->addColumn('role_id', 'integer', ['null' => false, 'comment' => '角色ID'])
            ->addIndex(['user_id', 'role_id'], ['unique' => true])
            ->create();

        // 登录日志表
        $this->table('login_log', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('user_id', 'integer', ['null' => false, 'comment' => '用户ID'])
            ->addColumn('username', 'string', ['limit' => 50, 'null' => false, 'comment' => '用户名'])
            ->addColumn('ip', 'string', ['limit' => 50, 'null' => false, 'comment' => '登录IP'])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true, 'comment' => '浏览器信息'])
            ->addColumn('status', 'enum', ['values' => ['success', 'failed'], 'default' => 'success', 'comment' => '登录状态'])
            ->addColumn('message', 'string', ['limit' => 255, 'null' => true, 'comment' => '登录消息'])
            ->addColumn('create_time', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->create();
    }
} 