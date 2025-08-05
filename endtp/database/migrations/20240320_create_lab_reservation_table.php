<?php
declare (strict_types = 1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateLabReservationTable extends Migrator
{
    public function change()
    {
        $this->table('lab_reservation')
            ->addColumn('lab_id', 'integer', ['comment' => '实验室ID'])
            ->addColumn('user_id', 'integer', ['comment' => '预约用户ID'])
            ->addColumn('start_time', 'datetime', ['comment' => '开始时间'])
            ->addColumn('end_time', 'datetime', ['comment' => '结束时间'])
            ->addColumn('purpose', 'string', ['limit' => 500, 'comment' => '用途说明'])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'pending', 'comment' => '状态：pending-待审核，approved-已批准，rejected-已拒绝，completed-已完成，cancelled-已取消'])
            ->addColumn('create_time', 'datetime', ['comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['lab_id'], ['name' => 'idx_lab_id'])
            ->addIndex(['user_id'], ['name' => 'idx_user_id'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->create();
    }
} 