<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Request;
use think\facade\Route;
use think\facade\View;

class District extends Base
{
    public function index()
    {
        $District = new model\District();
        if (Request::get('parent_id') && !$District->one(Request::get('parent_id'))) {
            return $this->failed('不存在此行政区划！');
        }
        $whole = $this->whole(Request::get('parent_id'));
        if ($whole['level'] > 4) {
            return $this->failed('仅支持4级行政区划！');
        }
        $districtAll = $District->all();
        if (Request::isAjax()) {
            foreach ($districtAll as $key => $value) {
                $districtAll[$key] = $this->listItem($value);
            }
            return $districtAll->items() ? apiResponse('', 1, $districtAll->items()) : '';
        }
        View::assign([
            'Total' => $districtAll->total(),
            'Whole' => $whole
        ]);
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            $District = new model\District();
            if (Request::get('parent_id') && !$District->one(Request::get('parent_id'))) {
                return apiResponse('不存在此行政区划！', 0);
            }
            if ($this->whole(Request::get('parent_id'))['level'] > 4) {
                return apiResponse('仅支持4级行政区划！', 0);
            }
            if (Request::get('action') == 'do') {
                $districtAdd = $District->add();
                if (is_numeric($districtAdd)) {
                    return $districtAdd > 0 ? apiResponse('行政区划添加成功！') : apiResponse('行政区划添加失败！', 0);
                } else {
                    return apiResponse($districtAdd, 0);
                }
            }
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function multi()
    {
        if (Request::isAjax()) {
            $District = new model\District();
            if (Request::get('parent_id') && !$District->one(Request::get('parent_id'))) {
                return apiResponse('不存在此行政区划！', 0);
            }
            if ($this->whole(Request::get('parent_id'))['level'] > 4) {
                return apiResponse('仅支持4级行政区划！', 0);
            }
            if (Request::get('action') == 'do') {
                $districtMulti = $District->multi();
                if (is_numeric($districtMulti)) {
                    return $districtMulti > 0 ? apiResponse('行政区划批量添加成功！') : apiResponse('行政区划批量添加失败！', 0);
                } else {
                    return apiResponse($districtMulti, 0);
                }
            }
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $District = new model\District();
            $districtOne = $District->one();
            if (!$districtOne) {
                return apiResponse('不存在此行政区划！', 0);
            }
            if (Request::get('action') == 'do') {
                $districtModify = $District->modify($districtOne['parent_id']);
                return is_numeric($districtModify) ?
                    apiResponse('行政区划修改成功！', 1, $this->listItem($District->one())) :
                    apiResponse($districtModify, 0);
            }
            View::assign(['One' => $districtOne]);
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            $District = new model\District();
            if (Request::post('id')) {
                if (!$District->one()) {
                    return apiResponse('不存在此行政区划！', 0);
                }
                if ($District->one2(Request::post('id'))) {
                    return apiResponse('此行政区划下有子行政区划，无法删除！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$District->one($value)) {
                        return apiResponse('不存在您勾选的行政区划！', 0);
                    }
                    if ($District->one2($value)) {
                        return apiResponse('您勾选的行政区划下有子行政区划，无法删除！', 0);
                    }
                }
            }
            return $District->remove() ? apiResponse('行政区划删除成功！') : apiResponse('行政区划删除失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    private function whole($parentId = 0)
    {
        $name = '';
        $District = new model\District();
        $districtOne = $District->one($parentId);
        if ($districtOne) {
            $name .= '<a href="' . Route::buildUrl(
                '/' . parse_name(Request::controller()) . '/index',
                ['parent_id' => $parentId]
            ) . '">' . $districtOne['name'] . '</a>';
            if ($districtOne['parent_id']) {
                $name = $this->whole($districtOne['parent_id'])['name'] . ' - ' . $name;
            }
        }
        return ['name' => $name, 'level' => $name == '' ? 1 : substr_count($name, ' - ') + 2];
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        return $item;
    }
}
