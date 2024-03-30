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
                    return showTip('不存在此订单！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Order->one($value)) {
                        return showTip('不存在您勾选的订单！', 0);
                    }
                }
            }
            return $Order->recover() ? showTip('订单还原成功！') : showTip('订单还原失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return showTip('演示站，订单无法删除！', 0);
            }
            $Order = new model\Order();
            if (Request::post('id')) {
                if (!$Order->one()) {
                    return showTip('不存在此订单！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Order->one($value)) {
                        return showTip('不存在您勾选的订单！', 0);
                    }
                }
            }
            return $Order->remove() ? showTip('订单删除成功！') : showTip('订单删除失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }
}
