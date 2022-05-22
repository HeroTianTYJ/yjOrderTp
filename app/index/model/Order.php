<?php

namespace app\index\model;

use app\index\validate\Order as validate;
use Exception;
use think\captcha\facade\Captcha;
use think\facade\Config;
use think\facade\Request;
use think\Model;

class Order extends Model
{
    //前台查单
    public function all()
    {
        try {
            $map['is_recycle'] = 0;
            if (Config::get('system.order_search_step') == '0') {
                $map['template_id'] = Request::get('template_id');
            }
            if (Request::get('field') == 1) {
                $map['order_id'] = Request::get('keyword');
            } elseif (Request::get('field') == 2) {
                $map['name'] = Request::get('keyword');
            } elseif (Request::get('field') == 3) {
                $map['tel'] = Request::get('keyword');
            }
            return $this->field('id,order_id,manager_id,template_id,product_id,price,count,name,tel,province,city,' .
                'county,town,address,note,email,ip,referrer,payment_id,pay_id,pay_scene,pay_date,order_state_id,' .
                'express_id,express_number,date')
                ->where($map)
                ->order(['date' => 'DESC'])
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询支付信息
    public function one($orderId = 0)
    {
        try {
            return $this->field('template_id,product_id,price,count,order_state_id')
                ->where(['order_id' => $orderId ?: Request::param('order_id'), 'is_recycle' => 0])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //添加
    public function add()
    {
        $templateOne = (new Template())->one(Request::post('template_id'));
        if (!$templateOne) {
            return '此下单模板已被删除！';
        }
        $scene = ['count', 'payment_id'];
        $data = [
            'order_id' => time() . rand(100, 999),
            'manager_id' => $templateOne['manager_id'],
            'template_id' => Request::post('template_id'),
            'product_id' => Request::post('product_id'),
            'referrer' => Request::post('referrer'),
            'payment_id' => Request::post('payment_id', 1),
            'ip' => getUserIp(),
            'date' => time()
        ];
        if ($data['payment_id'] == 1) {
            $data['order_state_id'] = 2;
        } elseif (in_array($data['payment_id'], [2, 3])) {
            $data['order_state_id'] = 1;
        }
        $data['success'] = str_replace('{order_id}', $data['order_id'], $templateOne['success']);
        $fieldTemp = explode(',', $templateOne['field_ids']);
        $data['count'] = in_array(1, $fieldTemp) ? Request::post('count') : 1;
        if (in_array(2, $fieldTemp) || Request::post('name')) {
            $data['name'] = Request::post('name');
            $scene[] = 'name';
        }
        if (in_array(3, $fieldTemp) || Request::post('tel')) {
            $data['tel'] = Request::post('tel');
            $scene[] = 'tel';
        }
        if (in_array(4, $fieldTemp) && in_array(5, $fieldTemp)) {
            if (Request::post('district_type') == 0) {
                $data['province'] = Request::post('province');
                $data['city'] = Request::post('city');
                $data['county'] = Request::post('county');
                $data['town'] = Request::post('town');
                $scene[] = 'province';
                $scene[] = 'city';
                $scene[] = 'county';
                $scene[] = 'town';
            } elseif (Request::post('district_type') == 1) {
                $data['province'] = $data['province2'] = Request::post('province2');
                $data['city'] = $data['city2'] = Request::post('city2');
                $data['county'] = $data['county2'] = Request::post('county2');
                $data['town'] = $data['town2'] = Request::post('town2');
                $scene[] = 'province2';
                $scene[] = 'city2';
                $scene[] = 'county2';
                $scene[] = 'town2';
            }
        } elseif (in_array(4, $fieldTemp)) {
            $data['province'] = Request::post('province');
            $data['city'] = Request::post('city');
            $data['county'] = Request::post('county');
            $data['town'] = Request::post('town');
            $scene[] = 'province';
            $scene[] = 'city';
            $scene[] = 'county';
            $scene[] = 'town';
        } elseif (in_array(5, $fieldTemp)) {
            $data['province'] = $data['province2'] = Request::post('province2');
            $data['city'] = $data['city2'] = Request::post('city2');
            $data['county'] = $data['county2'] = Request::post('county2');
            $data['town'] = $data['town2'] = Request::post('town2');
            $scene[] = 'province2';
            $scene[] = 'city2';
            $scene[] = 'county2';
            $scene[] = 'town2';
        }
        if (in_array(6, $fieldTemp) || Request::post('address')) {
            $data['address'] = Request::post('address');
            $scene[] = 'address';
        }
        if (in_array(7, $fieldTemp) || Request::post('note')) {
            $data['note'] = Request::post('note');
            $scene[] = 'note';
        }
        if (in_array(8, $fieldTemp) || Request::post('email')) {
            $data['email'] = Request::post('email');
            $scene[] = 'email';
        }

        $productOne = (new Product())->one(Request::post('product_id'));
        if ($productOne) {
            $data['product'] = $productOne['name'];
            $data['price'] = $productOne['price'];
        } else {
            return '此商品已被删除，无法下单！';
        }

        $validate = new validate();
        if ($validate->only($scene)->check($data)) {
            if ($templateOne['is_captcha'] && !Captcha::check(Request::post('captcha'))) {
                return '验证码不正确！';
            }
            if ($this->repeat()) {
                return $templateOne['often'];
            }
            $data2 = $data;
            unset(
                $data2['province2'],
                $data2['city2'],
                $data2['county2'],
                $data2['town2'],
                $data2['success'],
                $data2['product']
            );
            $id = $this->insertGetId($data2);
            return $id ? $data : 0;
        } else {
            return $validate->getError();
        }
    }

    //修改支付状态
    public function modify($orderId, $pay, $payId = 0, $payScene = '', $payDate = '')
    {
        $data = [
            'payment_id' => $pay,
            'pay_id' => $payId,
            'pay_scene' => $payScene,
            'pay_date' => $payDate
        ];
        return $this->where(['order_id' => $orderId])->update($data);
    }

    //验证重复
    private function repeat()
    {
        try {
            return $this->field('id')->where('`ip`=:ip AND `is_recycle`=0 AND `date`>=:date', [
                'ip' => getUserIp(),
                'date' => time() - Config::get('system.order_time') * 60
            ])->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
