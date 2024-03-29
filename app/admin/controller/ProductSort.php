<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class ProductSort extends Base
{
    public function index()
    {
        $productSortAll = (new model\ProductSort())->all();
        if (Request::isAjax()) {
            foreach ($productSortAll as $key => $value) {
                $productSortAll[$key] = $this->listItem($value);
            }
            return $productSortAll->items() ? json_encode($productSortAll->items()) : '';
        }
        View::assign(['Total' => $productSortAll->total()]);
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $productSortAdd = (new model\ProductSort())->add();
                if (is_numeric($productSortAdd)) {
                    return $productSortAdd > 0 ? showTip('商品分类添加成功！') : showTip('商品分类添加失败！', 0);
                } else {
                    return showTip($productSortAdd, 0);
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
            $ProductSort = new model\ProductSort();
            $productSortOne = $ProductSort->one();
            if (!$productSortOne) {
                return showTip('不存在此商品分类！', 0);
            }
            if (Request::get('action') == 'do') {
                if (Config::get('app.demo') && Request::post('id') <= 2) {
                    return showTip('演示站，id<=2的商品分类无法修改！', 0);
                }
                $productSortModify = $ProductSort->modify();
                return is_numeric($productSortModify) ?
                    showTip(['msg' => '商品分类修改成功！', 'data' => $this->listItem($ProductSort->one())]) :
                    showTip($productSortModify, 0);
            }
            View::assign(['One' => $productSortOne]);
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return showTip('演示站，商品分类无法删除！', 0);
            }
            $ProductSort = new model\ProductSort();
            if (Request::post('id')) {
                if (!$ProductSort->one()) {
                    return showTip('不存在此商品分类！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$ProductSort->one($value)) {
                        return showTip('不存在您勾选的商品分类！', 0);
                    }
                }
            }
            return $ProductSort->remove() ? showTip('商品分类删除成功！') : showTip('商品分类删除失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function sort()
    {
        if (Request::isAjax()) {
            $ProductSort = new model\ProductSort();
            foreach (Request::post('sort') as $key => $value) {
                if (is_numeric($value)) {
                    $ProductSort->sort($key, $value);
                }
            }
            return showTip('商品分类排序成功！');
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        $item['count'] = (new model\Product())->totalCount($item['id']);
        $item['date'] = dateFormat($item['date']);
        return $item;
    }
}
