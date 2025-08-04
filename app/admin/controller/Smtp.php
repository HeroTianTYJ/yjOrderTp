<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class Smtp extends Base
{
    public function index()
    {
        $smtpAll = (new model\Smtp())->all();
        if (Request::isAjax()) {
            foreach ($smtpAll as $key => $value) {
                $smtpAll[$key] = $this->listItem($value);
            }
            return $smtpAll->items() ? apiResponse('', 1, $smtpAll->items()) : '';
        }
        View::assign(['Total' => $smtpAll->total()]);
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                if (Config::get('app.demo')) {
                    return apiResponse('演示站，SMTP服务器无法添加！', 0);
                }
                $smtpAdd = (new model\Smtp())->add();
                if (is_numeric($smtpAdd)) {
                    return $smtpAdd > 0 ? apiResponse('SMTP服务器添加成功！') : apiResponse('SMTP服务器添加失败！', 0);
                } else {
                    return apiResponse($smtpAdd, 0);
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
            $Smtp = new model\Smtp();
            $smtpOne = $Smtp->one();
            if (!$smtpOne) {
                return apiResponse('不存在此SMTP服务器！', 0);
            }
            if (Request::get('action') == 'do') {
                if (Config::get('app.demo')) {
                    return apiResponse('演示站，SMTP服务器无法修改！', 0);
                }
                $smtpModify = $Smtp->modify();
                return is_numeric($smtpModify) ?
                    apiResponse('SMTP服务器修改成功！', 1, $this->listItem($Smtp->one())) :
                    apiResponse($smtpModify, 0);
            }
            View::assign(['One' => $smtpOne]);
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return apiResponse('演示站，SMTP服务器无法删除！', 0);
            }
            $Smtp = new model\Smtp();
            if (Request::post('id')) {
                if (!$Smtp->one()) {
                    return apiResponse('不存在此SMTP服务器！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Smtp->one($value)) {
                        return apiResponse('不存在您勾选的SMTP服务器！', 0);
                    }
                }
            }
            return $Smtp->remove() ? apiResponse('SMTP服务器删除成功！') : apiResponse('SMTP服务器删除失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function state()
    {
        $Smtp = new model\Smtp();
        if (Request::isAjax()) {
            $smtpAll = [];
            if ($Smtp->count() > 0) {
                foreach (range(0, 23) as $key => $value) {
                    $smtpAll[$key] = $Smtp->one3($value);
                    $hour = ($value < 10 ? '0' : '') . $value;
                    $smtpAll[$key]['hour'] = $hour . ':00 ～ ' . $hour . ':59';
                    $smtpAll[$key]['current'] = $value == date('H');
                }
            }
            return $smtpAll ? apiResponse('', 1, $smtpAll) : '';
        }
        View::assign(['Total' => $Smtp->count() == 0 ? 0 : 24]);
        return $this->view();
    }

    private function listItem($item)
    {
        $item['smtp'] = keyword($item['smtp']);
        $item['port'] = keyword($item['port']);
        $item['email'] = keyword($item['email']);
        $item['from_name'] = keyword($item['from_name']);
        return $item;
    }
}
