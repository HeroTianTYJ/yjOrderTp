<?php

namespace app\admin\controller;

use app\admin\library\Html;
use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class Manager extends Base
{
    private array $level = [1 => ['red', '超级管理员'], 2 => ['green', '普通管理员']];
    private array $isActivation = [['green', '否'], ['red', '是']];
    private array $orderPermit = [1 => ['green', '自己订单'], 2 => ['blue', '自己订单 + 前台订单'], 3 => ['red', '所有订单']];
    private array $wechat = [['green', '否'], ['red', '是']];
    private array $qq = [['green', '否'], ['red', '是']];

    public function index()
    {
        $managerAll = (new model\Manager())->all();
        if (Request::isAjax()) {
            foreach ($managerAll as $key => $value) {
                $managerAll[$key] = $this->listItem($value);
            }
            return $managerAll->items() ? apiResponse('', 1, $managerAll->items()) : '';
        }
        View::assign(['Total' => $managerAll->total()]);
        Html::permitGroup(Request::get('permit_group_id'));
        Html::managerLevelSelect($this->level, Request::get('level_id'));
        Html::managerIsActivationSelect($this->isActivation, Request::get('is_activation', -1));
        Html::managerOrderPermitSelect($this->orderPermit, Request::get('order_permit_id'));
        Html::wechat($this->wechat, Request::get('wechat', -1));
        Html::qq($this->qq, Request::get('qq', -1));
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $managerAdd = (new model\Manager())->add();
                if (is_numeric($managerAdd)) {
                    return $managerAdd > 0 ? apiResponse('管理员添加成功！') : apiResponse('管理员添加失败！', 0);
                } else {
                    return apiResponse($managerAdd, 0);
                }
            }
            Html::permitGroup(0, 1);
            Html::managerLevelRadio($this->level, 2);
            Html::managerIsActivationRadio($this->isActivation, 1);
            Html::managerOrderPermitRadio($this->orderPermit, 1);
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Manager = new model\Manager();
            $managerOne = $Manager->one();
            if (!$managerOne) {
                return apiResponse('不存在此管理员！', 0);
            }
            if (Request::get('action') == 'do') {
                if (Config::get('app.demo') && Request::post('id') == 1) {
                    return apiResponse('演示站，无法修改创始人！', 0);
                }
                $managerModify = $Manager->modify();
                return is_numeric($managerModify) ?
                    apiResponse('管理员修改成功！', 1, $this->listItem($Manager->one())) :
                    apiResponse($managerModify, 0);
            }
            Html::permitGroup($managerOne['permit_group_id'], 1);
            Html::managerLevelRadio($this->level, $managerOne['level_id']);
            Html::managerIsActivationRadio($this->isActivation, $managerOne['is_activation']);
            Html::managerOrderPermitRadio($this->orderPermit, $managerOne['order_permit_id'] ?: 1);
            View::assign(['One' => $managerOne]);
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return apiResponse('演示站，管理员无法删除！', 0);
            }
            $Manager = new model\Manager();
            if (Request::post('id')) {
                $managerOne = $Manager->one();
                if (!$managerOne) {
                    return apiResponse('不存在此管理员！', 0);
                }
                if (Request::post('id') == 1) {
                    return apiResponse('无法删除创始人！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Manager->one($value)) {
                        return apiResponse('不存在您勾选的管理员！', 0);
                    }
                    if ($value == 1) {
                        return apiResponse('无法删除创始人！', 0);
                    }
                }
            }
            return $Manager->remove() ? apiResponse('管理员删除成功！') : apiResponse('管理员删除失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function isActivation()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Manager = new model\Manager();
            $managerOne = $Manager->one();
            if (!$managerOne) {
                return apiResponse('不存在此管理员！', 0);
            }
            if (Request::post('id') == 1) {
                return apiResponse('无法激活创始人！', 0);
            }
            if ($managerOne['is_activation'] == 0) {
                return $Manager->isActivation(1) ? apiResponse('管理员激活成功！') : apiResponse('管理员激活失败！', 0);
            } else {
                return $Manager->isActivation(0) ? apiResponse('管理员取消激活成功！') : apiResponse('管理员取消激活失败！', 0);
            }
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function wechatOpenId()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Manager = new model\Manager();
            $managerOne = $Manager->one();
            if (!$managerOne) {
                return apiResponse('不存在此管理员！', 0);
            }
            return $Manager->wechatOpenId() ? apiResponse('管理员微信解绑成功！') : apiResponse('管理员微信解绑失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function qqOpenId()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Manager = new model\Manager();
            $managerOne = $Manager->one();
            if (!$managerOne) {
                return apiResponse('不存在此管理员！', 0);
            }
            return $Manager->qqOpenId() ? apiResponse('管理员QQ解绑成功！') : apiResponse('管理员QQ解绑失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        $item['level'] = $item['id'] == 1 ?
            '<span class="red">创始人</span>' :
            '<span class="' . $this->level[$item['level_id']][0] . '">' . $this->level[$item['level_id']][1] . '</span>';
        $item['order_permit'] = $item['level_id'] == 1 ?
            '-' :
            '<span class="' . $this->orderPermit[$item['order_permit_id']][0] . '">' .
            $this->orderPermit[$item['order_permit_id']][1] . '</span>';
        if ($item['level_id'] == 2) {
            $permitGroupOne = (new model\PermitGroup())->one($item['permit_group_id']);
            $item['permit_group'] = $permitGroupOne ? $permitGroupOne['name'] : '此权限组已被删除';
        } else {
            $item['permit_group'] = '-';
        }
        $item['create_time'] = timeFormat($item['create_time']);
        return $item;
    }
}
