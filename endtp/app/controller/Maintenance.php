<?php
namespace app\controller;

use think\Request;
use think\facade\Db;
use app\BaseController;

class Maintenance extends BaseController
{
    /**
     * 获取维护记录列表
     */
    public function records(Request $request)
    {
        $page = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 10);
        $equipmentName = $request->param('equipment_name', '');
        $equipmentId = $request->param('equipment_id', '');
        $labId = $request->param('lab_id', '');
        $status = $request->param('status', '');
        $priority = $request->param('priority', '');
        $managerId = $request->param('manager_id', '');

        $where = [];
        if ($equipmentName) {
            $where[] = ['e.name', 'like', "%{$equipmentName}%"];
        }
        if ($equipmentId) {
            $where[] = ['m.equipment_id', '=', $equipmentId];
        }
        if ($labId) {
            $where[] = ['e.lab_id', '=', $labId];
        }
        if ($status) {
            $where[] = ['m.status', '=', $status];
        }
        if ($priority) {
            $where[] = ['m.priority', '=', $priority];
        }
        
        // 如果传入了manager_id参数，只显示该管理员管理的实验室的设备维护记录
        if ($managerId) {
            $where[] = ['l.manager_id', '=', $managerId];
        }

        $total = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->count();

        $list = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->field([
                'm.*',
                'e.name as equipment_name',
                'e.serial_number as equipment_serial',
                'e.image as equipment_image',
                'l.name as lab_name'
            ])
            ->page($page, $limit)
            ->order('m.id', 'desc')
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
     * 获取维护记录详情
     */
    public function detail(Request $request)
    {
        $id = $request->param('id');

        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        $detail = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where('m.id', $id)
            ->field([
                'm.*',
                'e.name as equipment_name',
                'e.serial_number as equipment_serial',
                'e.image as equipment_image',
                'l.name as lab_name'
            ])
            ->find();

        if (!$detail) {
            return json(['code' => 1, 'msg' => '记录不存在']);
        }

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $detail
        ]);
    }

    /**
     * 新增维护记录
     */
    public function add(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = validate([
            'equipment_id' => 'require|integer',
            'type' => 'require|in:routine,preventive,corrective,emergency',
            'priority' => 'require|in:low,medium,high,urgent',
            'status' => 'require|in:pending,in_progress,completed,cancelled',
            'scheduled_date' => 'require|date',
            'technician' => 'require|max:50',
            'description' => 'require|max:500'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }
        
        // 验证设备是否存在
        $equipment = Db::name('equipment')->where('id', $data['equipment_id'])->find();
        if (!$equipment) {
            return json(['code' => 1, 'msg' => '设备不存在']);
        }

        // 转换日期格式
        if (isset($data['scheduled_date'])) {
            $data['scheduled_date'] = date('Y-m-d', strtotime($data['scheduled_date']));
        }
        if (isset($data['actual_date']) && $data['actual_date']) {
            $data['actual_date'] = date('Y-m-d', strtotime($data['actual_date']));
        }
        
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        
        try {
            $id = Db::name('maintenance_record')->insertGetId($data);
            
            // 如果维护类型是紧急维修，更新设备状态为维修中
            if ($data['type'] === 'emergency' || $data['priority'] === 'urgent') {
                Db::name('equipment')->where('id', $data['equipment_id'])->update([
                    'status' => 'maintenance',
                    'update_time' => date('Y-m-d H:i:s')
                ]);
            }
            
            return json(['code' => 0, 'msg' => '新增成功', 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '新增失败：' . $e->getMessage()]);
        }
    }

    /**
     * 更新维护记录
     */
    public function update(Request $request)
    {
        $data = $request->post();
        
        // 数据验证
        $validate = validate([
            'id' => 'require|integer',
            'equipment_id' => 'require|integer',
            'type' => 'require|in:routine,preventive,corrective,emergency',
            'priority' => 'require|in:low,medium,high,urgent',
            'status' => 'require|in:pending,in_progress,completed,cancelled',
            'scheduled_date' => 'require|date',
            'technician' => 'require|max:50',
            'description' => 'require|max:500'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 检查记录是否存在
        $record = Db::name('maintenance_record')->where('id', $data['id'])->find();
        if (!$record) {
            return json(['code' => 1, 'msg' => '记录不存在']);
        }

        // 转换日期格式
        if (isset($data['scheduled_date'])) {
            $data['scheduled_date'] = date('Y-m-d', strtotime($data['scheduled_date']));
        }
        if (isset($data['actual_date']) && $data['actual_date']) {
            $data['actual_date'] = date('Y-m-d', strtotime($data['actual_date']));
        }

        // 如果状态从其他状态改为已完成，设置实际完成时间
        if ($data['status'] === 'completed' && $record['status'] !== 'completed') {
            if (!isset($data['actual_date']) || !$data['actual_date']) {
                $data['actual_date'] = date('Y-m-d');
            }
            
            // 更新设备状态为正常
            Db::name('equipment')->where('id', $data['equipment_id'])->update([
                'status' => 'normal',
                'last_maintenance_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ]);
        }
        
        $data['update_time'] = date('Y-m-d H:i:s');
        
        try {
            Db::name('maintenance_record')->where('id', $data['id'])->update($data);
            return json(['code' => 0, 'msg' => '更新成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 删除维护记录
     */
    public function delete(Request $request)
    {
        $id = $request->post('id');

        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            $record = Db::name('maintenance_record')->where('id', $id)->find();
            if (!$record) {
                return json(['code' => 1, 'msg' => '记录不存在']);
            }

            Db::name('maintenance_record')->where('id', $id)->delete();
            return json(['code' => 0, 'msg' => '删除成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 完成维护
     */
    public function complete(Request $request)
    {
        $data = $request->post();
        $id = $data['id'];

        if (!$id) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        try {
            $record = Db::name('maintenance_record')->where('id', $id)->find();
            if (!$record) {
                return json(['code' => 1, 'msg' => '记录不存在']);
            }

            if ($record['status'] === 'completed') {
                return json(['code' => 1, 'msg' => '该记录已经完成']);
            }

            $updateData = [
                'status' => 'completed',
                'actual_date' => $data['actual_date'] ?? date('Y-m-d'),
                'update_time' => date('Y-m-d H:i:s')
            ];

            if (isset($data['cost'])) {
                $updateData['cost'] = $data['cost'];
            }
            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }

            Db::name('maintenance_record')->where('id', $id)->update($updateData);
            
            // 更新设备状态为正常
            Db::name('equipment')->where('id', $record['equipment_id'])->update([
                'status' => 'normal',
                'last_maintenance_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ]);

            return json(['code' => 0, 'msg' => '维护完成']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '操作失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取维护统计数据
     */
    public function stats(Request $request)
    {
        $managerId = $request->param('manager_id', '');
        
        $where = [];
        if ($managerId) {
            $where[] = ['l.manager_id', '=', $managerId];
        }

        // 总维护记录数
        $total = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->count();

        // 已完成维护数
        $completed = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->where('m.status', 'completed')
            ->count();

        // 待维护数
        $pending = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->where('m.status', 'pending')
            ->count();

        // 紧急维护数
        $urgent = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->where('m.priority', 'urgent')
            ->count();

        // 本月维护数
        $thisMonth = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->whereTime('m.create_time', 'month')
            ->count();

        // 维护费用统计
        $totalCost = Db::name('maintenance_record')
            ->alias('m')
            ->join('equipment e', 'm.equipment_id = e.id')
            ->join('lab l', 'e.lab_id = l.id')
            ->where($where)
            ->where('m.status', 'completed')
            ->sum('m.cost');

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'total' => $total,
                'completed' => $completed,
                'pending' => $pending,
                'urgent' => $urgent,
                'this_month' => $thisMonth,
                'total_cost' => $totalCost ?: 0
            ]
        ]);
    }

    /**
     * 获取维护记录趋势数据
     */
    public function trends(Request $request)
    {
        $managerId = $request->param('manager_id', '');
        $type = $request->param('type', 'monthly'); // monthly, weekly
        
        $where = [];
        if ($managerId) {
            $where[] = ['l.manager_id', '=', $managerId];
        }

        if ($type === 'monthly') {
            // 获取过去12个月的维护记录数据
            $trends = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-{$i} month"));
                $count = Db::name('maintenance_record')
                    ->alias('m')
                    ->join('equipment e', 'm.equipment_id = e.id')
                    ->join('lab l', 'e.lab_id = l.id')
                    ->where($where)
                    ->whereTime('m.create_time', 'between', [
                        $month . '-01 00:00:00',
                        date('Y-m-t 23:59:59', strtotime($month . '-01'))
                    ])
                    ->count();
                
                $trends[] = [
                    'period' => $month,
                    'count' => $count
                ];
            }
        } else {
            // 获取过去8周的维护记录数据
            $trends = [];
            for ($i = 7; $i >= 0; $i--) {
                $startDate = date('Y-m-d', strtotime("-{$i} week monday"));
                $endDate = date('Y-m-d', strtotime("-{$i} week sunday"));
                
                $count = Db::name('maintenance_record')
                    ->alias('m')
                    ->join('equipment e', 'm.equipment_id = e.id')
                    ->join('lab l', 'e.lab_id = l.id')
                    ->where($where)
                    ->whereTime('m.create_time', 'between', [
                        $startDate . ' 00:00:00',
                        $endDate . ' 23:59:59'
                    ])
                    ->count();
                
                $trends[] = [
                    'period' => $startDate . ' ~ ' . $endDate,
                    'count' => $count
                ];
            }
        }

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $trends
        ]);
    }
}