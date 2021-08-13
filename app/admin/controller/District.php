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
        $districtAll = $District->all($this->page($District->total()));
        foreach ($districtAll as $key => $value) {
            $districtAll[$key]['child'] = $District->one2($value['id']);
        }
        View::assign(['All' => $districtAll, 'Map' => $whole]);
        if (Request::get('parent_id', 0)) {
            View::assign(['ParentId' => $District->one(Request::get('parent_id'))['parent_id']]);
        }
        return $this->view();
    }

    public function add()
    {
        $District = new model\District();
        if (Request::get('parent_id') && !$District->one(Request::get('parent_id'))) {
            return $this->failed('不存在此行政区划！');
        }
        if ($this->whole(Request::get('parent_id'))['level'] > 4) {
            return $this->failed('仅支持4级行政区划！');
        }
        if (Request::isPost()) {
            $districtAdd = $District->add();
            if (is_numeric($districtAdd)) {
                return $districtAdd > 0 ?
                    $this->success(
                        Route::buildUrl(
                            '/' . parse_name(Request::controller()) . '/index',
                            ['parent_id' => Request::get('parent_id')]
                        ),
                        '行政区划添加成功！'
                    ) : $this->failed('行政区划添加失败！');
            } else {
                return $this->failed($districtAdd);
            }
        }
        View::assign(['Map' => $this->whole(Request::get('parent_id'))]);
        return $this->view();
    }

    public function multi()
    {
        $District = new model\District();
        if (Request::get('parent_id') && !$District->one(Request::get('parent_id'))) {
            return $this->failed('不存在此行政区划！');
        }
        $whole = $this->whole(Request::get('parent_id'));
        if ($whole['level'] > 4) {
            return $this->failed('仅支持4级行政区划！');
        }
        if (Request::isPost()) {
            $districtMulti = $District->multi();
            if (is_numeric($districtMulti)) {
                return $districtMulti > 0 ? $this->success(
                    Route::buildUrl(
                        '/' . parse_name(Request::controller()) . '/index',
                        ['parent_id' => Request::get('parent_id')]
                    ),
                    '行政区划批量添加成功！'
                ) : $this->failed('行政区划批量添加失败！');
            } else {
                return $this->failed($districtMulti);
            }
        }
        View::assign(['Map' => $whole]);
        return $this->view();
    }

    public function update()
    {
        if (Request::get('id')) {
            $District = new model\District();
            $districtOne = $District->one();
            if (!$districtOne) {
                return $this->failed('不存在此行政区划！');
            }
            if (Request::isPost()) {
                $districtModify = $District->modify($districtOne['parent_id']);
                return is_numeric($districtModify) ?
                    $this->success(
                        Route::buildUrl(
                            '/' . parse_name(Request::controller()) . '/index',
                            ['parent_id' => $districtOne['parent_id']]
                        ),
                        '行政区划修改成功！'
                    ) : $this->failed($districtModify);
            }
            View::assign([
                'One' => $districtOne,
                'Map' => $this->whole($districtOne['parent_id'])
            ]);
            return $this->view();
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function delete()
    {
        if (Request::get('id')) {
            $District = new model\District();
            if (!$District->one()) {
                return $this->failed('不存在此行政区划！');
            }
            if ($District->one2(Request::get('id'))) {
                return $this->failed('此行政区划下有子行政区划，无法删除！');
            }
            if (Request::isPost()) {
                return $District->remove() ?
                    $this->success(Request::post('prev'), '行政区划删除成功！') : $this->failed('行政区划删除失败！');
            }
            return $this->confirm('您真的要删除这条数据么？');
        } else {
            return $this->failed('非法操作！');
        }
    }

    private function whole($parentId = 0)
    {
        $name = '';
        if ($parentId) {
            $District = new model\District();
            $districtOne = $District->one($parentId);
            if ($districtOne) {
                $name .= $districtOne['name'];
                if ($districtOne['parent_id']) {
                    $name = $this->whole($districtOne['parent_id'])['name'] . ' - ' . $name;
                }
            }
        } else {
            $name = '一级区划';
        }
        return ['name' => $name, 'level' => $name == '' ? 1 : substr_count($name, ' - ') + 2];
    }
}
