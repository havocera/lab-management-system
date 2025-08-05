<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Lab extends Model
{
    // 设置表名
    protected $name = 'lab';

    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'name' => 'string',
        'room_no' => 'string',
        'description' => 'string',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];

    // 关联试剂
    public function reagents()
    {
        return $this->hasMany(Reagent::class, 'lab_id', 'id');
    }
} 