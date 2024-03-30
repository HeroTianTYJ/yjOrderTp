<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Request;
use think\facade\View;

class Express extends Base
{
    public function index()
    {
        $expressAll = (new model\Express())->all();
        if (Request::isAjax()) {
            foreach ($expressAll as $key => $value) {
                $expressAll[$key] = $this->listItem($value);
            }
            return $expressAll->items() ? json_encode($expressAll->items()) : '';
        }
        View::assign(['Total' => $expressAll->total()]);
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $expressAdd = (new model\Express())->add();
                if (is_numeric($expressAdd)) {
                    return $expressAdd > 0 ? showTip('快递公司添加成功！') : showTip('快递公司添加失败！', 0);
                } else {
                    return showTip($expressAdd, 0);
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
            $Express = new model\Express();
            $expressOne = $Express->one();
            if (!$expressOne) {
                return showTip('不存在此快递公司！', 0);
            }
            if (Request::get('action') == 'do') {
                $expressModify = $Express->modify();
                return is_numeric($expressModify) ?
                    showTip(['msg' => '快递公司修改成功！', 'data' => $this->listItem($Express->one())]) :
                    showTip($expressModify, 0);
            }
            View::assign(['One' => $expressOne]);
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            $Express = new model\Express();
            if (Request::post('id')) {
                if (!$Express->one()) {
                    return showTip('不存在此快递公司！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Express->one($value)) {
                        return showTip('不存在您勾选的快递公司！', 0);
                    }
                }
            }
            return $Express->remove() ? showTip('快递公司删除成功！') : showTip('快递公司删除失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        $item['code'] = keyword($item['code']);
        return $item;
    }
}
