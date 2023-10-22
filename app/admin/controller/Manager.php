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
            return $managerAll->items() ? json_encode($managerAll->items()) : '';
        }
        View::assign(['Total' => $managerAll->total()]);
        Html::permitGroup(Request::get('permit_group_id'));
        Html::managerLevelSelect($this->level, Request::get('level'));
        Html::managerIsActivationSelect($this->isActivation, Request::get('is_activation', -1));
        Html::managerOrderPermitSelect($this->orderPermit, Request::get('order_permit'));
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
                    $level = $this->level[Request::post('level')][1];
                    return $managerAdd > 0 ? showTip($level . '添加成功！') : showTip($level . '添加失败！', 0);
                } else {
                    return showTip($managerAdd, 0);
                }
            }
            Html::permitGroup(0, 1);
            Html::managerLevelRadio($this->level, 2);
            Html::managerIsActivationRadio($this->isActivation, 1);
            Html::managerOrderPermitRadio($this->orderPermit, 1);
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Manager = new model\Manager();
            $managerOne = $Manager->one();
            if (!$managerOne) {
                return showTip('不存在此管理员！', 0);
            }
            if (Request::get('action') == 'do') {
                if (Config::get('app.demo') && Request::post('id') == 1) {
                    return showTip('演示站，无法修改创始人！', 0);
                }
                $managerModify = $Manager->modify();
                if (is_numeric($managerModify)) {
                    return showTip([
                        'msg' => $this->level[Request::post('level', 1)][1] . '修改成功！',
                        'data' => $this->listItem($Manager->one())
                    ]);
                } else {
                    return showTip($managerModify, 0);
                }
            }
            Html::permitGroup($managerOne['permit_group_id'], 1);
            Html::managerLevelRadio($this->level, $managerOne['level']);
            Html::managerIsActivationRadio($this->isActivation, $managerOne['is_activation']);
            Html::managerOrderPermitRadio($this->orderPermit, $managerOne['order_permit'] ?: 1);
            View::assign(['One' => $managerOne]);
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return showTip('演示站，管理员无法删除！', 0);
            }
            $Manager = new model\Manager();
            $level = '';
            if (Request::post('id')) {
                $managerOne = $Manager->one();
                if (!$managerOne) {
                    return showTip('不存在此管理员！', 0);
                }
                if (Request::post('id') == 1) {
                    return showTip('无法删除创始人！', 0);
                }
                $level = $this->level[$managerOne['level']][1];
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Manager->one($value)) {
                        return showTip('不存在您勾选的管理员！', 0);
                    }
                    if ($value == 1) {
                        return showTip('无法删除创始人！', 0);
                    }
                }
                $level = '管理员';
            }
            return $Manager->remove() ? showTip($level . '删除成功！') : showTip($level . '删除失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function isActivation()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Manager = new model\Manager();
            $managerOne = $Manager->one();
            if (!$managerOne) {
                return showTip('不存在此管理员！', 0);
            }
            if (Request::post('id') == 1) {
                return showTip('无法激活创始人！', 0);
            }
            $level = $this->level[$managerOne['level']][1];
            if ($managerOne['is_activation'] == 0) {
                return $Manager->isActivation(1) ? showTip($level . '激活成功！') : showTip($level . '激活失败！', 0);
            } else {
                return $Manager->isActivation(0) ? showTip($level . '取消激活成功！') : showTip($level . '取消激活失败！', 0);
            }
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function wechatOpenId()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Manager = new model\Manager();
            $managerOne = $Manager->one();
            if (!$managerOne) {
                return showTip('不存在此管理员！', 0);
            }
            $level = $this->level[$managerOne['level']][1];
            return $Manager->wechatOpenId() ? showTip($level . '微信解绑成功！') : showTip($level . '微信解绑失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function qqOpenId()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Manager = new model\Manager();
            $managerOne = $Manager->one();
            if (!$managerOne) {
                return showTip('不存在此管理员！', 0);
            }
            $level = $this->level[$managerOne['level']][1];
            return $Manager->qqOpenId() ? showTip($level . 'QQ解绑成功！') : showTip($level . 'QQ解绑失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        $item['level_name'] = $item['id'] == 1 ?
            '<span class="red">创始人</span>' :
            '<span class="' . $this->level[$item['level']][0] . '">' . $this->level[$item['level']][1] . '</span>';
        $item['order_permit'] = $item['level'] == 1 ?
            '-' :
            '<span class="' . $this->orderPermit[$item['order_permit']][0] . '">' .
            $this->orderPermit[$item['order_permit']][1] . '</span>';
        if ($item['level'] == 2) {
            $permitGroupOne = (new model\PermitGroup())->one($item['permit_group_id']);
            $item['permit_group'] = $permitGroupOne ? $permitGroupOne['name'] : '此权限组已被删除';
        } else {
            $item['permit_group'] = '-';
        }
        $item['date'] = dateFormat($item['date']);
        return $item;
    }
}
