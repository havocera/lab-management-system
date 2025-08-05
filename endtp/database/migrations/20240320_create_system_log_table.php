<?php
declare (strict_types = 1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateSystemLogTable extends Migrator
{
    public function change()
    {
        $this->table('system_log')
            ->addColumn('user_id', 'integer', ['comment' => '用户ID'])
            ->addColumn('action', 'string', ['limit' => 50, 'comment' => '操作类型'])
            ->addColumn('target', 'string', ['limit' => 100, 'comment' => '操作目标'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id'])
            ->addIndex(['created_at'])
            ->create();
    }
} 