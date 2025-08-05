<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Reagent extends Model
{
    // 设置表名
    protected $name = 'reagent';

    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'name' => 'string',
        'code' => 'string',
        'lab_id' => 'int',
        'specification' => 'string',
        'stock' => 'float',
        'min_stock' => 'float',
        'unit' => 'string',
        'danger_level' => 'string',
        'expiry_date' => 'date',
        'manufacturer' => 'string',
        'keeper' => 'string',
        'location' => 'string',
        'safety_info' => 'string',
        'image' => 'string',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];

    // 关联实验室
    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id', 'id');
    }

    // 关联使用记录
    public function records()
    {
        return $this->hasMany(ReagentRecord::class, 'reagent_id', 'id');
    }
} 