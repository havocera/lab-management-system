<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserTable extends Migrator
{
    public function change()
    {
        $table = $this->table('user', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('username', 'string', ['limit' => 50, 'null' => false, 'comment' => '用户名'])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false, 'comment' => '密码'])
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '姓名'])
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => false, 'comment' => '手机号'])
            ->addColumn('role', 'enum', ['values' => ['admin', 'teacher', 'student'], 'default' => 'student', 'comment' => '身份：管理员/教师/学生'])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactive'], 'default' => 'active', 'comment' => '状态：启用/禁用'])
            ->addColumn('last_login_time', 'datetime', ['null' => true, 'comment' => '最后登录时间'])
            ->addColumn('last_login_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '最后登录IP'])
            ->addColumn('create_time', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['username'], ['unique' => true])
            ->addIndex(['phone'], ['unique' => true])
            ->create();
    }
} 