<?php
declare (strict_types = 1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateReagentRecordTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('reagent_record', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('reagent_id', 'integer', ['null' => false, 'comment' => '试剂ID'])
            ->addColumn('type', 'enum', ['values' => ['in', 'out'], 'null' => false, 'comment' => '操作类型：in=入库，out=出库'])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'comment' => '数量'])
            ->addColumn('unit', 'string', ['limit' => 10, 'null' => false, 'comment' => '单位'])
            ->addColumn('operator', 'string', ['limit' => 50, 'null' => false, 'comment' => '操作人'])
            ->addColumn('remark', 'string', ['limit' => 255, 'null' => true, 'comment' => '备注'])
            ->addColumn('status', 'enum', ['values' => ['pending', 'approved', 'rejected'], 'default' => 'pending', 'null' => false, 'comment' => '状态：pending=待审核，approved=已通过，rejected=已拒绝'])
            ->addColumn('approver', 'string', ['limit' => 50, 'null' => true, 'comment' => '审核人'])
            ->addColumn('approve_time', 'integer', ['signed' => false, 'null' => true, 'comment' => '审核时间'])
            ->addColumn('approve_remark', 'string', ['limit' => 255, 'null' => true, 'comment' => '审核备注'])
            ->addColumn('create_time', 'integer', ['signed' => false, 'null' => false, 'comment' => '创建时间'])
            ->addIndex(['reagent_id'])
            ->addIndex(['type'])
            ->addIndex(['status'])
            ->addIndex(['create_time'])
            ->create();
    }
} 