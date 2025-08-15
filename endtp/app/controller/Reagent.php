<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\Reagent as ReagentModel;
use app\model\ReagentRecord as ReagentRecordModel;
use think\facade\Db;
use think\facade\Validate;
use think\Request;

class Reagent extends BaseController
{
    /**
     * 获取试剂列表
     */
    public function index(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $name = $request->param('name', '');
        $code = $request->param('code', '');
        $labId = $request->param('lab_id', '');
        $dangerLevel = $request->param('danger_level', '');
        $managerId = $request->param('manager_id', '');

        $where = [];
        if ($name) {
            $where[] = ['r.name', 'like', "%{$name}%"];
        }
        if ($code) {
            $where[] = ['r.code', 'like', "%{$code}%"];
        }
        if ($labId) {
            $where[] = ['r.lab_id', '=', $labId];
        }
        if ($dangerLevel) {
            $where[] = ['r.danger_level', '=', $dangerLevel];
        }
        
        // 如果传入了manager_id参数，只显示该管理员管理的实验室的试剂
        if ($managerId) {
            $where[] = ['l.manager_id', '=', $managerId];
        }

        $total = Db::name('reagent')
            ->alias('r')
            ->join('lab l', 'r.lab_id = l.id')
            ->where($where)
            ->count();

        $list = Db::name('reagent')
            ->alias('r')
            ->join('lab l', 'r.lab_id = l.id')
            ->where($where)
            ->field([
                'r.*',
                'l.name as lab_name',
                'l.room_no as lab_room_no'
            ])
            ->page($page, $limit)
            ->order('r.id', 'desc')
            ->select()
            ->toArray();

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $list,
                'total' => $total
            ]
        ]);
    }

    /**
     * 获取试剂详情
     */
    public function detail(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            $info = Db::name('reagent')
                ->alias('r')
                ->join('lab l', 'r.lab_id = l.id')
                ->field([
                    'r.*',
                    'l.name as lab_name',
                    'l.room_no as lab_room_no'
                ])
                ->where('r.id', $id)
                ->find();

            if (!$info) {
                return json(['code' => 1, 'msg' => '试剂不存在']);
            }
            return json(['code' => 0, 'msg' => 'success', 'data' => $info]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '查询失败：' . $e->getMessage()]);
        }
    }

    /**
     * 新增试剂
     */
    public function add(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = Validate::rule([
            'name' => 'require|max:100',
            'code' => 'require|max:50|unique:reagent',
            'lab_id' => 'require|number',
            'specification' => 'require|max:100',
            'stock' => 'require|float|egt:0',
            'min_stock' => 'require|float|egt:0',
            'unit' => 'require|max:20',
            'danger_level' => 'require|in:low,medium,high',
            'expiry_date' => 'require|date',
            'manufacturer' => 'max:100',
            'keeper' => 'max:50',
            'location' => 'max:100',
            'safety_info' => 'max:500'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 添加创建时间
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');

        $id = Db::name('reagent')->insertGetId($data);
        if ($id) {
            return json(['code' => 0, 'msg' => '添加成功', 'data' => ['id' => $id]]);
        } else {
            return json(['code' => 1, 'msg' => '添加失败']);
        }
    }

    /**
     * 更新试剂
     */
    public function update(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = Validate::rule([
            'id' => 'require',
            'name' => 'require|max:100',
            'code' => 'require|max:50|unique:reagent,code,' . $data['id'],
            'lab_id' => 'require|number',
            'specification' => 'require|max:100',
            'stock' => 'require|float|egt:0',
            'min_stock' => 'require|float|egt:0',
            'unit' => 'require|max:20',
            'danger_level' => 'require|in:low,medium,high',
            'expiry_date' => 'require|date',
            'manufacturer' => 'max:100',
            'keeper' => 'max:50',
            'location' => 'max:100',
            'safety_info' => 'max:500'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 添加更新时间
        $data['update_time'] = date('Y-m-d H:i:s');

        $result = Db::name('reagent')->where('id', $data['id'])->update($data);
        if ($result !== false) {
            return json(['code' => 0, 'msg' => '更新成功']);
        } else {
            return json(['code' => 1, 'msg' => '更新失败']);
        }
    }

    /**
     * 删除试剂
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            $result = Db::name('reagent')->where('id', $id)->delete();
            if ($result) {
                return json(['code' => 0, 'msg' => '删除成功']);
            } else {
                return json(['code' => 1, 'msg' => '删除失败']);
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 试剂出入库
     */
    public function inOut(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = Validate::rule([
            'reagent_id' => 'require|number',
            'type' => 'require|in:in,out',
            'amount' => 'require|float|gt:0',
            'unit' => 'require|max:20',
            'operator' => 'require|max:50',
            'remark' => 'max:500'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 获取试剂信息
        $reagent = Db::name('reagent')->where('id', $data['reagent_id'])->find();
        if (!$reagent) {
            return json(['code' => 1, 'msg' => '试剂不存在']);
        }

        // 检查单位是否匹配
        if ($reagent['unit'] !== $data['unit']) {
            return json(['code' => 1, 'msg' => '单位不匹配']);
        }

        // 出库时检查库存是否足够
        if ($data['type'] === 'out' && $reagent['stock'] < $data['amount']) {
            return json(['code' => 1, 'msg' => '库存不足']);
        }

        try {
            // 添加出入库记录
            $record = [
                'reagent_id' => $data['reagent_id'],
                'type' => $data['type'],
                'amount' => $data['amount'],
                'unit' => $data['unit'],
                'operator' => $data['operator'],
                'remark' => $data['remark'] ?? '',
                'status' => $data['type'] === 'in' ? 'approved' : 'pending', // 入库直接通过，出库需要审核
                'create_time' => time()
            ];

            // 如果是入库，直接更新库存
            if ($data['type'] === 'in') {
                Db::startTrans();
                try {
                    // 更新库存
                    Db::name('reagent')->where('id', $data['reagent_id'])->inc('stock', $data['amount'])->update();
                    // 添加记录
                    Db::name('reagent_record')->insert($record);
                    Db::commit();
                    return json(['code' => 0, 'msg' => '入库成功']);
                } catch (\Exception $e) {
                    Db::rollback();
                    return json(['code' => 1, 'msg' => '入库失败：' . $e->getMessage()]);
                }
            } else {
                // 出库需要审核，只添加记录
                if (Db::name('reagent_record')->insert($record)) {
                    return json(['code' => 0, 'msg' => '出库申请已提交，等待审核']);
                } else {
                    return json(['code' => 1, 'msg' => '出库申请提交失败']);
                }
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '操作失败：' . $e->getMessage()]);
        }
    }

    /**
     * 审核出库申请
     */
    public function approve(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = Validate::rule([
            'id' => 'require|number',
            'status' => 'require|in:approved,rejected',
            'approve_remark' => 'require|max:500'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 获取当前登录用户信息
        $userInfo =  $request->username;
        // var_dump($request);
        if (!$userInfo) {
            return json(['code' => 1, 'msg' => '请先登录']);
        }

        // 获取记录信息
        $record = Db::name('reagent_record')->where('id', $data['id'])->find();
        if (!$record) {
            return json(['code' => 1, 'msg' => '记录不存在']);
        }

        // 检查记录状态
        if ($record['status'] !== 'pending') {
            return json(['code' => 1, 'msg' => '该记录已审核']);
        }

        // 获取试剂信息
        $reagent = Db::name('reagent')->where('id', $record['reagent_id'])->find();
        if (!$reagent) {
            return json(['code' => 1, 'msg' => '试剂不存在']);
        }

        // 如果是通过，检查库存是否足够
        if ($data['status'] === 'approved' && $reagent['stock'] < $record['amount']) {
            return json(['code' => 1, 'msg' => '库存不足']);
        }

        Db::startTrans();
        try {
            // 更新记录状态
            $updateData = [
                'status' => $data['status'],
                'approver' => $userInfo ?? $userInfo ?? '未知用户',
                'approve_time' => time(),
                'approve_remark' => $data['approve_remark']
            ];
            
            Db::name('reagent_record')->where('id', $data['id'])->update($updateData);

            // 如果审核通过，更新库存
            if ($data['status'] === 'approved') {
                Db::name('reagent')
                    ->where('id', $record['reagent_id'])
                    ->dec('stock', floatval($record['amount']))
                    ->update();
            }

            Db::commit();
            return json(['code' => 0, 'msg' => '审核成功']);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 1, 'msg' => '审核失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取试剂使用记录
     */
    public function records(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $reagent_id = $request->param('reagent_id', '');
        $reagent_name = $request->param('reagent_name', '');
        $reagent_code = $request->param('reagent_code', '');
        $type = $request->param('type', '');
        $status = $request->param('status', '');
        $start_time = $request->param('start_time', '');
        $end_time = $request->param('end_time', '');

        $query = ReagentRecordModel::with(['reagent' => function($query) {
            $query->field('id,name,code,image');
        }]);

        // 条件筛选
        if ($reagent_id) {
            $query = $query->where('reagent_id', $reagent_id);
        }
        if ($reagent_name || $reagent_code) {
            $query = $query->whereExists(function($query) use ($reagent_name, $reagent_code) {
                $query->table('reagent')
                    ->whereColumn('reagent.id', 'reagent_record.reagent_id');
                if ($reagent_name) {
                    $query->where('reagent.name', 'like', "%{$reagent_name}%");
                }
                if ($reagent_code) {
                    $query->where('reagent.code', 'like', "%{$reagent_code}%");
                }
            });
        }
        if ($type) {
            $query = $query->where('type', $type);
        }
        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($start_time) {
            $query = $query->where('create_time', '>=', strtotime($start_time));
        }
        if ($end_time) {
            $query = $query->where('create_time', '<=', strtotime($end_time . ' 23:59:59'));
        }

        $total = $query->count();
        $list = $query->page($page, $limit)
            ->order('create_time', 'desc')
            ->select()
            ->each(function($item) {
                // 添加试剂相关信息
                $item->reagent_name = $item->reagent->name ?? '';
                $item->reagent_code = $item->reagent->code ?? '';
                $item->reagent_image = $item->reagent->image ?? '';
                $item->status_text = $item->status_text;
                unset($item->reagent);
                return $item;
            });

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $list,
                'total' => $total
            ]
        ]);
    }
    
    /**
     * 获取待审批的试剂申领记录
     */
    public function pendingRecords(Request $request)
    {
        try {
            $pendingList = Db::name('reagent_record')
                ->alias('r')
                ->join('reagent g', 'r.reagent_id = g.id')
                ->where('r.status', 'pending')
                ->field([
                    'r.id',
                    'r.reagent_id',
                    'r.type',
                    'r.amount',
                    'r.unit',
                    'r.operator',
                    'r.remark',
                    'r.create_time',
                    'g.name as reagent_name',
                    'g.code as reagent_code',
                    'g.specification',
                    'g.danger_level'
                ])
                ->order('r.create_time', 'desc')
                ->select();
                
            // 格式化数据
            foreach ($pendingList as &$item) {
                // create_time 已经是时间戳格式
                $item['create_time'] = (int)$item['create_time'];
            }
            
            return json(['code' => 0, 'msg' => '获取成功', 'data' => $pendingList]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取失败：' . $e->getMessage()]);
        }
    }
} 