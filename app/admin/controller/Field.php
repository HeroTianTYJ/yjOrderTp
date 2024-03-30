<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Request;
use think\facade\View;

class Field extends Base
{
    public function index()
    {
        $fieldAll = (new model\Field())->all();
        if (Request::isAjax()) {
            foreach ($fieldAll as $key => $value) {
                $fieldAll[$key] = $this->listItem($value);
            }
            return $fieldAll->items() ? json_encode($fieldAll->items()) : '';
        }
        View::assign(['Total' => $fieldAll->total()]);
        return $this->view();
    }

    public function isDefault()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Field = new model\Field();
            $fieldOne = $Field->one();
            if (!$fieldOne) {
                return showTip('不存在此字段！', 0);
            }
            if ($fieldOne['is_default'] == 0) {
                return $Field->isDefault(1) ? showTip('设置默认字段成功！') : showTip('设置默认字段失败！', 0);
            } else {
                return $Field->isDefault(0) ? showTip('取消默认字段成功！') : showTip('取消默认字段失败！', 0);
            }
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        return $item;
    }
}
