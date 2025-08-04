<?php

namespace app\admin\controller;

use app\admin\model;
use app\admin\library\Html;
use think\facade\Request;
use think\facade\View;

class PermitGroup extends Base
{
    public function index()
    {
        $permitGroupAll = (new model\PermitGroup())->all();
        if (Request::isAjax()) {
            foreach ($permitGroupAll as $key => $value) {
                $permitGroupAll[$key] = $this->listItem($value);
            }
            return $permitGroupAll->items() ? apiResponse('', 1, $permitGroupAll->items()) : '';
        }
        View::assign(['Total' => $permitGroupAll->total()]);
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $permitGroupAdd = (new model\PermitGroup())->add();
                if (is_numeric($permitGroupAdd)) {
                    return $permitGroupAdd > 0 ? apiResponse('权限组添加成功！') : apiResponse('权限组添加失败！', 0);
                } else {
                    return apiResponse($permitGroupAdd, 0);
                }
            }
            Html::permitManage();
            Html::permitData();
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $PermitGroup = new model\PermitGroup();
            $permitGroupOne = $PermitGroup->one();
            if (!$permitGroupOne) {
                return apiResponse('不存在此权限组！', 0);
            }
            if (Request::get('action') == 'do') {
                $permitGroupModify = $PermitGroup->modify($permitGroupOne['text_id_permit_manage_ids']);
                return is_numeric($permitGroupModify) ?
                    apiResponse('权限组修改成功！', 1, $this->listItem($PermitGroup->one())) :
                    apiResponse($permitGroupModify, 0);
            }
            Html::permitManage((new model\Text())->content($permitGroupOne['text_id_permit_manage_ids']));
            Html::permitData($permitGroupOne['permit_data_ids']);
            View::assign(['One' => $permitGroupOne]);
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function isDefault()
    {
        if (Request::isAjax() && Request::post('id')) {
            $PermitGroup = new model\PermitGroup();
            if (!$PermitGroup->one()) {
                return apiResponse('不存在此权限组！', 0);
            }
            return $PermitGroup->isDefault() ? apiResponse('设置默认权限组成功！') : apiResponse('设置默认权限组失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            $PermitGroup = new model\PermitGroup();
            $textId = [];
            if (Request::post('id')) {
                $permitGroupOne = $PermitGroup->one();
                if (!$permitGroupOne) {
                    return apiResponse('不存在此权限组！', 0);
                }
                $textId[] = $permitGroupOne['text_id_permit_manage_ids'];
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    $permitGroupOne = $PermitGroup->one($value);
                    if (!$permitGroupOne) {
                        return apiResponse('不存在您勾选的权限组！', 0);
                    }
                    $textId[] = $permitGroupOne['text_id_permit_manage_ids'];
                }
            }
            if ($PermitGroup->remove()) {
                (new model\Text())->remove($textId);
                return apiResponse('权限组删除成功！');
            } else {
                return apiResponse('权限组删除失败！', 0);
            }
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function a()
    {
        $PermitManage = new model\PermitManage();
        $html = '<meta charset="utf-8">';
        $permitManageAll = $PermitManage->all2();
        if (count($permitManageAll)) {
            //$html .= '<table cellspacing="0" style="border:1px solid #E5EDF0;">';
            $html .= '<table class="list">';
            foreach ($permitManageAll as $value) {
                //$html .= '<tr><td style="background:#E5EDF0;font-weight:bold;text-align:right;padding:5px ;' .
                //'width:104px;">'.$value['name'].'：</td><td>';
                $html .= '<tr><td>' . $value['name'] . '：</td><td>';
                $permitManageAll2 = $PermitManage->all3($value['id']);
                if (count($permitManageAll2)) {
                    foreach ($permitManageAll2 as $v) {
                        $html .= $v['name'] . '、';
                    }
                    $html = substr($html, 0, -3);
                }
                $html .= '</td></tr>';
            }
            $html .= '</table>';
        }
        return $html;
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);

        $permitManageStr = '';
        $permitManageIds = (new model\Text())->content($item['text_id_permit_manage_ids']);
        if ($permitManageIds) {
            $PermitManage = new model\PermitManage();
            foreach ($PermitManage->all2($permitManageIds) as $v) {
                $permitManageStr .= $v['name'] . '：<span class="blue">';
                foreach ($PermitManage->all4($permitManageIds, $v['id']) as $v2) {
                    $permitManageStr .= $v2['name'] . '、';
                }
                $permitManageStr = (substr($permitManageStr, -3) == '、' ?
                        substr($permitManageStr, 0, -3) : $permitManageStr) . '</span><br>';
            }
        }
        $item['permit_manage'] = $permitManageStr;

        $permitDataStr = '';
        if ($item['permit_data_ids']) {
            $PermitData = new model\PermitData();
            foreach ($PermitData->all2($item['permit_data_ids']) as $v) {
                $permitDataStr .= '' . $v['name'] . '：<span class="blue">';
                foreach ($PermitData->all4($item['permit_data_ids'], $v['id']) as $v2) {
                    $permitDataStr .= $v2['name'] . '、';
                }
                $permitDataStr = (substr($permitDataStr, -3) == '、' ?
                        substr($permitDataStr, 0, -3) : $permitDataStr) . '</span><br>';
            }
        }
        $item['permit_data'] = $permitDataStr;

        $item['date'] = dateFormat($item['date']);
        return $item;
    }
}
