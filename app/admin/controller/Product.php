<?php

namespace app\admin\controller;

use app\admin\model;
use app\admin\library\Html;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class Product extends Base
{
    public function index()
    {
        $productAll = (new model\Product())->all();
        if (Request::isAjax()) {
            foreach ($productAll as $key => $value) {
                $productAll[$key] = $this->listItem($value);
            }
            return $productAll->items() ? json_encode($productAll->items()) : '';
        }
        View::assign(['Total' => $productAll->total()]);
        Html::productSort(Request::get('product_sort_id'));
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $productAdd = (new model\Product())->add();
                if (is_numeric($productAdd)) {
                    return $productAdd > 0 ? showTip('商品添加成功！') : showTip('商品添加失败！', 0);
                } else {
                    return showTip($productAdd, 0);
                }
            }
            Html::productSort();
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Product = new model\Product();
            $productOne = $Product->one();
            if (!$productOne) {
                return showTip('不存在此商品！', 0);
            }
            if (Request::get('action') == 'do') {
                if (Config::get('app.demo') && Request::post('id') <= 5) {
                    return showTip('演示站，id<=5的商品无法修改！', 0);
                }
                $productModify = $Product->modify();
                if (is_numeric($productModify)) {
                    return showTip(['msg' => '商品修改成功！', 'data' => $this->listItem($Product->one())]);
                } else {
                    return showTip($productModify, 0);
                }
            }
            Html::productSort($productOne['product_sort_id']);
            View::assign(['One' => $productOne]);
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return showTip('演示站，商品无法删除！', 0);
            }
            $Product = new model\Product();
            if (Request::post('id')) {
                if (!$Product->one()) {
                    return showTip('不存在此商品！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Product->one($value)) {
                        return showTip('不存在您勾选的商品！', 0);
                    }
                }
            }
            return $Product->remove() ? showTip('商品删除成功！') : showTip('商品删除失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function isView()
    {
        if (Request::isAjax() && Request::post('id')) {
            if (Config::get('app.demo')) {
                return showTip('演示站，商品无法设置上下架！', 0);
            }
            $Product = new model\Product();
            $productOne = $Product->one();
            if (!$productOne) {
                return showTip('不存在此商品！', 0);
            }
            if ($productOne['is_view'] == 0) {
                return $Product->isView(1) ? showTip('商品上架成功！') : showTip('商品上架失败！', 0);
            } else {
                return $Product->isView(0) ? showTip('商品下架成功！') : showTip('商品下架失败！', 0);
            }
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function isDefault()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Product = new model\Product();
            if (!$Product->one()) {
                return showTip('不存在此商品！', 0);
            }
            return $Product->isDefault() ? showTip('设置默认商品成功！') : showTip('设置默认商品失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function sort()
    {
        if (Request::isAjax()) {
            $Product = new model\Product();
            foreach (Request::post('sort') as $key => $value) {
                if (is_numeric($value)) {
                    $Product->sort($key, $value);
                }
            }
            return showTip('商品排序成功！');
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        $productSortOne = (new model\ProductSort())->one($item['product_sort_id']);
        $item['product_sort'] = $productSortOne ?
            '<span style="color:' . $productSortOne['color'] . ';">' . $productSortOne['name'] . '</span>' :
            '此分类已被删除';
        $item['date'] = dateFormat($item['date']);
        return $item;
    }
}
