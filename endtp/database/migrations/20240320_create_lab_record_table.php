<?php
declare (strict_types = 1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateLabRecordTable extends Migrator
{
    public function change()
    {
        $this->table('lab_record')
            ->addColumn('lab_id', 'integer', ['comment' => '实验室ID'])
            ->addColumn('user_id', 'integer', ['comment' => '用户ID'])
            ->addColumn('start_time', 'datetime', ['comment' => '开始时间'])
            ->addColumn('end_time', 'datetime', ['null' => true, 'comment' => '结束时间'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：1使用中 2已完成 3已取消'])
            ->addColumn('remark', 'string', ['limit' => 255, 'null' => true, 'comment' => '备注'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['lab_id'])
            ->addIndex(['user_id'])
            ->addIndex(['status'])
            ->addIndex(['created_at'])
            ->create();
    }
} 