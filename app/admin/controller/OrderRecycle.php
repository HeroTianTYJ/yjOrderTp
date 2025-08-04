<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;

class OrderRecycle extends Order
{
    public function recover()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            $Order = new model\Order();
            if (Request::post('id')) {
                if (!$Order->one()) {
                    return apiResponse('不存在此订单！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Order->one($value)) {
                        return apiResponse('不存在您勾选的订单！', 0);
                    }
                }
            }
            return $Order->recover() ? apiResponse('订单还原成功！') : apiResponse('订单还原失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return apiResponse('演示站，订单无法删除！', 0);
            }
            $Order = new model\Order();
            if (Request::post('id')) {
                if (!$Order->one()) {
                    return apiResponse('不存在此订单！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Order->one($value)) {
                        return apiResponse('不存在您勾选的订单！', 0);
                    }
                }
            }
            return $Order->remove() ? apiResponse('订单删除成功！') : apiResponse('订单删除失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }
}
