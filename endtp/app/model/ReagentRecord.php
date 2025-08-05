<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ReagentRecord extends Model
{
    // 设置表名
    protected $name = 'reagent_record';

    // 设置字段信息
    protected $schema = [
        'id'             => 'int',
        'reagent_id'     => 'int',
        'type'          => 'string',
        'amount'        => 'float',
        'unit'          => 'string',
        'operator'      => 'string',
        'remark'        => 'string',
        'status'        => 'string',
        'approver'      => 'string',
        'approve_time'  => 'int',
        'approve_remark'=> 'string',
        'create_time'   => 'int'
    ];

    // 关联试剂
    public function reagent()
    {
        return $this->belongsTo(Reagent::class, 'reagent_id', 'id');
    }

    // 获取状态文本
    public function getStatusTextAttr()
    {
        $status = [
            'pending' => '待审核',
            'approved' => '已通过',
            'rejected' => '已拒绝'
        ];
        return $status[$this->status] ?? '未知';
    }
} 