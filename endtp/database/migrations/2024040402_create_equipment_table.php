<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateEquipmentTable extends Migrator
{
    public function change()
    {
        $table = $this->table('equipment', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '设备名称'])
            ->addColumn('model', 'string', ['limit' => 50, 'null' => false, 'comment' => '型号'])
            ->addColumn('serial_number', 'string', ['limit' => 50, 'null' => false, 'comment' => '序列号'])
            ->addColumn('lab_id', 'integer', ['null' => false, 'comment' => '所属实验室ID'])
            ->addColumn('status', 'enum', ['values' => ['normal', 'maintenance', 'scrapped'], 'default' => 'normal', 'comment' => '状态：正常/维护中/已报废'])
            ->addColumn('purchase_date', 'date', ['null' => false, 'comment' => '购买日期'])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'comment' => '购买价格'])
            ->addColumn('manufacturer', 'string', ['limit' => 100, 'null' => false, 'comment' => '制造商'])
            ->addColumn('maintenance_cycle', 'integer', ['null' => false, 'default' => 0, 'comment' => '维护周期(天)'])
            ->addColumn('last_maintenance_time', 'datetime', ['null' => true, 'comment' => '上次维护时间'])
            ->addColumn('next_maintenance_time', 'datetime', ['null' => true, 'comment' => '下次维护时间'])
            ->addColumn('description', 'text', ['null' => true, 'comment' => '描述'])
            ->addColumn('create_time', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['serial_number'], ['unique' => true])
            ->addIndex(['lab_id'])
            ->create();
    }
} 