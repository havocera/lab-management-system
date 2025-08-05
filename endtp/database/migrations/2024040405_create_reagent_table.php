<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateReagentTable extends Migrator
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
        $table = $this->table('reagent', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '试剂名称'])
              ->addColumn('code', 'string', ['limit' => 50, 'default' => '', 'comment' => '试剂编号'])
              ->addColumn('lab_id', 'integer', ['signed' => true, 'default' => 0, 'comment' => '所属实验室ID'])
              ->addColumn('image', 'string', ['limit' => 255, 'null' => true, 'comment' => '试剂图片'])
              ->addColumn('specification', 'string', ['limit' => 100, 'default' => '', 'comment' => '规格'])
              ->addColumn('stock', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0, 'comment' => '库存量'])
              ->addColumn('min_stock', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0, 'comment' => '最低库存'])
              ->addColumn('unit', 'string', ['limit' => 20, 'default' => '', 'comment' => '单位'])
              ->addColumn('danger_level', 'enum', ['values' => ['low', 'medium', 'high'], 'default' => 'low', 'comment' => '危险等级'])
              ->addColumn('expiry_date', 'date', ['null' => false, 'comment' => '有效期至'])
              ->addColumn('manufacturer', 'string', ['limit' => 100, 'default' => '', 'comment' => '生产厂商'])
              ->addColumn('keeper', 'string', ['limit' => 50, 'default' => '', 'comment' => '保管人'])
              ->addColumn('location', 'string', ['limit' => 100, 'default' => '', 'comment' => '存放位置'])
              ->addColumn('safety_info', 'text', ['null' => true, 'comment' => '安全说明'])
              ->addColumn('create_time', 'datetime', ['null' => false, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['code'], ['unique' => true])
              ->addIndex(['lab_id'])
              ->addIndex(['danger_level'])
              ->addIndex(['expiry_date'])
              ->create();
    }
} 