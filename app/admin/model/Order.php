<?php

namespace app\admin\model;

use app\admin\validate\Order as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\Model;

class Order extends Model
{
    //按订单状态查询总记录
    public function totalCount($orderStateId = 0)
    {
        $map['is_recycle'] = 0;
        if ($orderStateId) {
            $map['order_state_id'] = $orderStateId;
        }
        return $this->where($map)->where($this->managerId())->count();
    }

    //查询所有
    public function all()
    {
        try {
            return $this->field('id,order_id,manager_id,template_id,product_id,price,count,name,tel,province,city,' .
                'county,town,address,note,email,ip,referrer,payment_id,pay_id,pay_scene,pay_date,order_state_id,' .
                'express_id,express_number,date')
                ->where($this->map()['where'], $this->map()['value'])
                ->order(['date' => 'DESC'])
                ->paginate(Config::get('app.page_size'));
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（不分页）
    public function all2()
    {
        try {
            return $this->field('id,order_id,manager_id,template_id,product_id,price,count,name,tel,province,city,' .
                'county,town,address,note,email,ip,referrer,payment_id,pay_id,pay_scene,pay_date,order_state_id,' .
                'express_id,express_number,date')
                ->where($this->map()['where'], $this->map()['value'])
                ->order(['date' => 'DESC'])
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（不分页，IN）
    public function all3()
    {
        try {
            return $this->field('id,order_id,manager_id,template_id,product_id,price,count,name,tel,province,city,' .
                'county,town,address,note,email,ip,referrer,payment_id,pay_id,pay_scene,pay_date,order_state_id,' .
                'express_id,express_number,date')
                ->where('id', 'IN', Request::post('ids'))
                ->where('is_recycle', Request::controller() == 'OrderRecycle' ? 1 : 0)
                ->where($this->managerId())
                ->order(['date' => 'DESC'])
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
    public function all4()
    {
        try {
            return $this->field('order_id')
                ->where('id', 'IN', Request::post('ids'))
                ->where('is_recycle', Request::controller() == 'OrderRecycle' ? 1 : 0)
                ->where($this->managerId())
                ->order(['date' => 'DESC'])
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //按自定义时间统计
    public function diyTime($time1 = 0, $time2 = 0)
    {
        try {
            $map = $this->map();
            if ($time1 && $time2) {
                $map['where'] .= ' AND `date`>=:date3 AND `date`<=:date4';
                $map['value']['date3'] = strtotime($time1 . ' 00:00:00') . '';
                $map['value']['date4'] = strtotime($time2 . ' 23:59:59') . '';
            }
            return $this->field('COUNT(CASE WHEN `order_state_id`=1 THEN `id` END) `count1`,' .
                'SUM(CASE WHEN `order_state_id`=1 THEN `price`*`count` ELSE 0 END) `sum1`,' .
                'COUNT(CASE WHEN `order_state_id`=2 THEN `id` END) `count2`,' .
                'SUM(CASE WHEN `order_state_id`=2 THEN `price`*`count` ELSE 0 END) `sum2`,' .
                'COUNT(CASE WHEN `order_state_id`=3 THEN `id` END) `count3`,' .
                'SUM(CASE WHEN `order_state_id`=3 THEN `price`*`count` ELSE 0 END) `sum3`,' .
                'COUNT(CASE WHEN `order_state_id`=4 THEN `id` END) `count4`,' .
                'SUM(CASE WHEN `order_state_id`=4 THEN `price`*`count` ELSE 0 END) `sum4`,' .
                'COUNT(CASE WHEN `order_state_id`=5 THEN `id` END) `count5`,' .
                'SUM(CASE WHEN `order_state_id`=5 THEN `price`*`count` ELSE 0 END) `sum5`,' .
                'COUNT(CASE WHEN `order_state_id`=6 THEN `id` END) `count6`,' .
                'SUM(CASE WHEN `order_state_id`=6 THEN `price`*`count` ELSE 0 END) `sum6`')
                ->where($map['where'], $map['value'])
                ->select()
                ->toArray()[0];
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //按天、月、年统计
    public function dayMonthYear($time, $paginate = true)
    {
        try {
            switch (Request::get('order')) {
                case 1:
                    $order = 'count1';
                    break;
                case 2:
                    $order = 'count2';
                    break;
                case 3:
                    $order = 'count3';
                    break;
                case 4:
                    $order = 'count4';
                    break;
                case 5:
                    $order = 'count5';
                    break;
                case 6:
                    $order = 'count6';
                    break;
                case 7:
                    $order = 'sum1';
                    break;
                case 8:
                    $order = 'sum2';
                    break;
                case 9:
                    $order = 'sum3';
                    break;
                case 10:
                    $order = 'sum4';
                    break;
                case 11:
                    $order = 'sum5';
                    break;
                case 12:
                    $order = 'sum6';
                    break;
                default:
                    $order = 'time';
            }
            $all = $this->field('COUNT(CASE WHEN `order_state_id`=1 THEN `id` END) `count1`,' .
                'SUM(CASE WHEN `order_state_id`=1 THEN `price`*`count` ELSE 0 END) `sum1`,' .
                'COUNT(CASE WHEN `order_state_id`=2 THEN `id` END) `count2`,' .
                'SUM(CASE WHEN `order_state_id`=2 THEN `price`*`count` ELSE 0 END) `sum2`,' .
                'COUNT(CASE WHEN `order_state_id`=3 THEN `id` END) `count3`,' .
                'SUM(CASE WHEN `order_state_id`=3 THEN `price`*`count` ELSE 0 END) `sum3`,' .
                'COUNT(CASE WHEN `order_state_id`=4 THEN `id` END) `count4`,' .
                'SUM(CASE WHEN `order_state_id`=4 THEN `price`*`count` ELSE 0 END) `sum4`,' .
                'COUNT(CASE WHEN `order_state_id`=5 THEN `id` END) `count5`,' .
                'SUM(CASE WHEN `order_state_id`=5 THEN `price`*`count` ELSE 0 END) `sum5`,' .
                'COUNT(CASE WHEN `order_state_id`=6 THEN `id` END) `count6`,' .
                'SUM(CASE WHEN `order_state_id`=6 THEN `price`*`count` ELSE 0 END) `sum6`,' .
                'FROM_UNIXTIME(`date`,\'' . $time . '\') `time`')
                ->group('FROM_UNIXTIME(`date`,\'' . $time . '\')')
                ->where($this->map()['where'], $this->map()['value'])
                ->order([$order => 'DESC']);
            return $paginate ? $all->paginate(Config::get('app.page_size')) : $all->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,order_id,manager_id,template_id,product_id,price,count,name,tel,province,city,' .
                'county,town,address,note,email,ip,referrer,payment_id,pay_id,pay_scene,pay_date,order_state_id,' .
                'express_id,express_number,date')
                ->where([
                    'id' => $id ?: Request::post('id'),
                    'is_recycle' => Request::controller() == 'OrderRecycle' ? 1 : 0
                ])
                ->where($this->managerId())
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询最老一条
    public function older()
    {
        try {
            return $this->field('date')
                ->where(['is_recycle' => 0])
                ->where($this->managerId())
                ->order(['date' => 'ASC'])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询最新一条
    public function newer()
    {
        try {
            return $this->field('date')
                ->where(['is_recycle' => 0])
                ->where($this->managerId())
                ->order(['date' => 'DESC'])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //修改
    public function modify()
    {
        $templateOne = (new Template())->one(Request::post('template_id'));
        if (!$templateOne) {
            return '您选择的模板不存在！';
        }
        $scene = ['product_id', 'price', 'count', 'payment_id', 'order_state_id', 'express_number'];
        $data = [
            'template_id' => Request::post('template_id'),
            'product_id' => Request::post('product_id'),
            'price' => Request::post('price'),
            'count' => Request::post('count'),
            'name' => Request::post('name'),
            'tel' => Request::post('tel'),
            'address' => Request::post('address'),
            'note' => Request::post('note'),
            'email' => Request::post('email'),
            'payment_id' => Request::post('payment_id'),
            'order_state_id' => Request::post('order_state_id'),
            'express_id' => Request::post('express_id'),
            'express_number' => Request::post('express_number')
        ];
        $fieldTemp = explode(',', $templateOne['field_ids']);
        if (in_array(2, $fieldTemp) || Request::post('name')) {
            $scene[] = 'name';
        }
        if (in_array(3, $fieldTemp) || Request::post('tel')) {
            $scene[] = 'tel';
        }
        if (in_array(4, $fieldTemp) || in_array(5, $fieldTemp) || Request::post('province')) {
            $data['province'] = $data['province2'] = Request::post('province');
            $scene[] = 'province2';
        }
        if (in_array(4, $fieldTemp) || in_array(5, $fieldTemp) || Request::post('city')) {
            $data['city'] = $data['city2'] = Request::post('city');
            $scene[] = 'city2';
        }
        if (in_array(4, $fieldTemp) || in_array(5, $fieldTemp) || Request::post('county')) {
            $data['county'] = $data['county2'] = Request::post('county');
            $scene[] = 'county2';
        }
        if (in_array(4, $fieldTemp) || in_array(5, $fieldTemp) || Request::post('town')) {
            $data['town'] = $data['town2'] = Request::post('town');
            $scene[] = 'town2';
        }
        if (in_array(6, $fieldTemp) || Request::post('address')) {
            $scene[] = 'address';
        }
        if (in_array(7, $fieldTemp) || Request::post('note')) {
            $scene[] = 'note';
        }
        if (in_array(8, $fieldTemp) || Request::post('email')) {
            $scene[] = 'email';
        }
        $validate = new validate();
        if ($validate->only($scene)->check($data)) {
            if (!(new Product())->one($data['product_id'])) {
                return '您选择的商品不存在！';
            }
            if (!(new OrderState())->one($data['order_state_id'])) {
                return '您选择的订单状态不存在！';
            }
            if ($data['express_id'] && !(new Express())->one($data['express_id'])) {
                return '您选择的快递公司不存在！';
            }
            unset($data['province2'], $data['city2'], $data['county2'], $data['town2']);
            return $this->where([
                'id' => Request::post('id'),
                'is_recycle' => Request::controller() == 'OrderRecycle' ? 1 : 0
            ])->where($this->managerId())->update($data);
        } else {
            return implode($validate->getError());
        }
    }

    //批量修改订单状态
    public function modify2()
    {
        return $this->where('id', 'IN', Request::post('ids'))
            ->where($this->managerId())
            ->update(['order_state_id' => Request::post('order_state_id')]);
    }

    //批量修改快递单号
    public function modify3()
    {
        $orderIds = explode("\r\n", Request::post('order_ids'));
        $expressNumbers = explode("\r\n", Request::post('express_numbers'));
        if (count($orderIds) != count($expressNumbers)) {
            return '您输入的快递单号个数和订单号个数不一致！';
        }
        foreach ($orderIds as $key => $value) {
            $this->where(['order_id' => $value])->where($this->managerId())->update([
                'order_state_id' => 3,
                'express_id' => Request::post('express_id'),
                'express_number' => $expressNumbers[$key]
            ]);
        }
        return 1;
    }

    //回收
    public function recycle()
    {
        return $this->where('id', 'IN', Request::post('id') ?: Request::post('ids'))
            ->where($this->managerId())
            ->update(['is_recycle' => 1]);
    }

    //还原
    public function recover()
    {
        return $this->where('id', 'IN', Request::post('id') ?: Request::post('ids'))
            ->where($this->managerId())
            ->update(['is_recycle' => 0]);
    }

    //删除
    public function remove()
    {
        try {
            $affectedRows = $this->where('id', 'IN', Request::post('id') ?: Request::post('ids'))
                ->where($this->managerId())
                ->delete();
            if ($affectedRows) {
                Db::execute('OPTIMIZE TABLE `' . $this->getTable() . '`');
            }
            return $affectedRows;
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //高级搜索
    private function map()
    {
        $map['where'] = '1=1';
        $map['value'] = [];
        if (Request::get('keyword')) {
            $map['where'] .= ' AND (';
            foreach (
                ['order_id', 'name', 'tel', 'province', 'city', 'county', 'town', 'address', 'email', 'ip', 'referrer',
                 'pay_id', 'express_number'] as $value
            ) {
                $map['where'] .= '`' . $value . '` LIKE :' . $value . ' OR ';
                $map['value'][$value] = '%' . Request::get('keyword') . '%';
            }
            $map['where'] = substr($map['where'], 0, -4) . ')';
        }
        if (Request::get('manager_id', -1) != -1) {
            $map['where'] .= ' AND `manager_id`=:manager_id';
            $map['value']['manager_id'] = Request::get('manager_id');
        }
        if (Request::get('user_id')) {
            $map['where'] .= ' AND `user_id`=:user_id';
            $map['value']['user_id'] = Request::get('user_id');
        }
        if (Request::get('product_id')) {
            $map['where'] .= ' AND `product_id`=:product_id';
            $map['value']['product_id'] = Request::get('product_id');
        }
        if (Request::get('payment_id')) {
            $map['where'] .= ' AND `payment_id`=:payment_id';
            $map['value']['payment_id'] = Request::get('payment_id');
            if (Request::get('payment_id') == 2) {
                if (Request::get('alipay_scene')) {
                    $map['where'] .= ' AND `pay_scene`=:alipay_scene';
                    $map['value']['alipay_scene'] = Request::get('alipay_scene');
                }
            } elseif (Request::get('payment_id') == 3) {
                if (Request::get('wechat_pay_scene')) {
                    $map['where'] .= ' AND `pay_scene`=:wechat_pay_scene';
                    $map['value']['wechat_pay_scene'] = Request::get('wechat_pay_scene');
                }
            }
        }
        if (Request::get('order_state_id')) {
            $map['where'] .= ' AND `order_state_id`=:order_state_id';
            $map['value']['order_state_id'] = Request::get('order_state_id');
        }
        if (Request::get('express_id')) {
            $map['where'] .= ' AND `express_id`=:express_id';
            $map['value']['express_id'] = Request::get('express_id');
        }
        if (Request::get('price1')) {
            $map['where'] .= ' AND `price`>=:price1';
            $map['value']['price1'] = Request::get('price1');
        }
        if (Request::get('price2')) {
            $map['where'] .= ' AND `price`<=:price2';
            $map['value']['price2'] = Request::get('price2');
        }
        if (Request::get('count1')) {
            $map['where'] .= ' AND `count`>=:count1';
            $map['value']['count1'] = Request::get('count1');
        }
        if (Request::get('count2')) {
            $map['where'] .= ' AND `count`<=:count2';
            $map['value']['count2'] = Request::get('count2');
        }
        if (Request::get('total1')) {
            $map['where'] .= ' AND `price`*`count`>=:total1';
            $map['value']['total1'] = Request::get('total1');
        }
        if (Request::get('total2')) {
            $map['where'] .= ' AND `price`*`count`<=:total2';
            $map['value']['total2'] = Request::get('total2');
        }
        if (Request::get('date1')) {
            $map['where'] .= ' AND `date`>=:date1';
            $map['value']['date1'] = strtotime(Request::get('date1') . ' 00:00:00');
        }
        if (Request::get('date2')) {
            $map['where'] .= ' AND `date`<=:date2';
            $map['value']['date2'] = strtotime(Request::get('date2') . ' 23:59:59');
        }
        if (Request::get('pay_date1')) {
            $map['where'] .= ' AND `pay_date`>=:pay_date1';
            $map['value']['pay_date1'] = strtotime(Request::get('pay_date1') . ' 00:00:00');
        }
        if (Request::get('pay_date2')) {
            $map['where'] .= ' AND `pay_date`<=:pay_date2';
            $map['value']['pay_date2'] = strtotime(Request::get('pay_date2') . ' 23:59:59');
        }
        if (Request::get('is_commission', -1) != -1) {
            $map['where'] .= ' AND `is_commission`=:is_commission';
            $map['value']['is_commission'] = Request::get('is_commission');
        }
        $map['where'] .= ' AND `is_recycle`=' . (Request::controller() == 'OrderRecycle' ? 1 : 0);
        $map['where'] .= ' AND ' . $this->managerId();
        return $map;
    }

    //管理权限
    private function managerId()
    {
        $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
        $sqlWhere = [
            1 => '`manager_id`=' . $session['id'],
            2 => '`manager_id` IN (' . $session['id'] . ',0)',
            3 => '1=1'
        ];
        return $session['level'] != 1 ? $sqlWhere[$session['order_permit']] : $sqlWhere[3];
    }
}
