<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateLabTable extends Migrator
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
        $table = $this->table('lab', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '实验室名称'])
            ->addColumn('room_no', 'string', ['limit' => 10, 'null' => false, 'comment' => '房间号'])
            ->addColumn('type', 'enum', ['values' => ['physics', 'chemistry', 'biology', 'computer'], 'default' => 'physics', 'comment' => '实验室类型：物理/化学/生物/计算机'])
            ->addColumn('capacity', 'integer', ['null' => false, 'default' => 0, 'comment' => '容纳人数'])
            ->addColumn('manager', 'string', ['limit' => 20, 'null' => false, 'comment' => '管理员'])
            ->addColumn('contact', 'string', ['limit' => 20, 'null' => false, 'comment' => '联系电话'])
            ->addColumn('status', 'enum', ['values' => ['idle', 'inuse', 'maintenance'], 'default' => 'idle', 'comment' => '状态：空闲/使用中/维护中'])
            ->addColumn('description', 'text', ['null' => true, 'comment' => '描述'])
            ->addColumn('create_time', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['room_no'], ['unique' => true])
            ->create();
    }
} 