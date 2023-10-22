<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class OrderState extends Base
{
    public function index()
    {
        $orderStateAll = (new model\OrderState())->all();
        if (Request::isAjax()) {
            foreach ($orderStateAll as $key => $value) {
                $orderStateAll[$key] = $this->listItem($value);
            }
            return $orderStateAll->items() ? json_encode($orderStateAll->items()) : '';
        }
        View::assign(['Total' => $orderStateAll->total()]);
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $orderStateAdd = (new model\OrderState())->add();
                if (is_numeric($orderStateAdd)) {
                    return $orderStateAdd > 0 ? showTip('订单状态添加成功！') : showTip('订单状态添加失败！', 0);
                } else {
                    return showTip($orderStateAdd, 0);
                }
            }
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $OrderState = new model\OrderState();
            $orderStateOne = $OrderState->one();
            if (!$orderStateOne) {
                return showTip('不存在此订单状态！', 0);
            }
            if (Request::get('action') == 'do') {
                if (Config::get('app.demo') && Request::post('id') <= 6) {
                    return showTip('演示站，id<=6的订单状态无法修改！', 0);
                }
                $orderStateModify = $OrderState->modify();
                return is_numeric($orderStateModify) ?
                    showTip(['msg' => '订单状态修改成功！', 'data' => $this->listItem($OrderState->one())]) :
                    showTip($orderStateOne, 0);
            }
            View::assign(['One' => $orderStateOne]);
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return showTip('演示站，订单状态无法删除！', 0);
            }
            $OrderState = new model\OrderState();
            if (Request::post('id')) {
                if (!$OrderState->one()) {
                    return showTip('不存在此订单状态！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$OrderState->one($value)) {
                        return showTip('不存在您勾选的订单状态！', 0);
                    }
                }
            }
            return $OrderState->remove() ? showTip('订单状态删除成功！') : showTip('订单状态删除失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function isDefault()
    {
        if (Request::isAjax() && Request::post('id')) {
            if (Config::get('app.demo')) {
                return showTip('演示站，订单状态无法设置默认！', 0);
            }
            $OrderState = new model\OrderState();
            if (!$OrderState->one()) {
                return showTip('不存在此订单状态！', 0);
            }
            return $OrderState->isDefault() ? showTip('设置默认订单状态成功！') : showTip('设置默认订单状态失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function sort()
    {
        if (Request::isAjax()) {
            $OrderState = new model\OrderState();
            foreach (Request::post('sort') as $key => $value) {
                if (is_numeric($value)) {
                    $OrderState->sort($key, $value);
                }
            }
            return showTip('订单状态排序成功！');
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        $item['date'] = dateFormat($item['date']);
        return $item;
    }
}
