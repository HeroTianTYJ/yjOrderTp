<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;
use yjrj\QqLogin;
use yjrj\Wechat;

class Profile extends Base
{
    public function index()
    {
        $Manager = new model\Manager();
        if (Request::isAjax()) {
            if (Config::get('app.demo')) {
                return apiResponse('演示站，个人资料无法修改！', 0);
            }
            $managerModify = $Manager->modify2();
            return is_numeric($managerModify) ? apiResponse('个人资料修改成功！') : apiResponse($managerModify, 0);
        }
        View::assign([
            'One' => $Manager->one(Session::get(Config::get('system.session_key_admin') . '.manage_info.id'))
        ]);
        return $this->view();
    }

    public function loginRecord()
    {
        $loginRecordManagerAll = (new model\LoginRecordManager())->all(1);
        if (Request::isAjax()) {
            foreach ($loginRecordManagerAll as $key => $value) {
                $loginRecordManagerAll[$key] = $this->listItem($value);
            }
            return $loginRecordManagerAll->items() ?
                apiResponse('', 1, $loginRecordManagerAll->items()) : '';
        }
        View::assign(['Total' => $loginRecordManagerAll->total()]);
        return $this->view();
    }

    public function wechat()
    {
        if (in_array(device(), ['harmonyWechat', 'androidWechat', 'iphoneWechat', 'windowsWechat', 'macWechat'])) {
            $Wechat = new Wechat([
                'app_id' => Config::get('system.wechat_app_id'),
                'app_secret' => Config::get('system.wechat_app_secret')
            ]);
        } else {
            $Wechat = new Wechat([
                'app_id' => Config::get('system.wechat_open_app_id'),
                'app_secret' => Config::get('system.wechat_open_app_secret'),
                'is_mp' => false
            ]);
        }
        $Wechat->oauthRedirect(Config::get('url.web1') . 'callback.php/admin/wechatBind.html');
    }

    public function qq()
    {
        (new QqLogin([
            'app_id' => Config::get('system.qq_app_id'),
            'app_key' => Config::get('system.qq_app_key'),
            'redirect_uri' => Config::get('url.web1') . 'callback.php/admin/qqBind.html'
        ]))->oauthRedirect();
    }

    private function listItem($item)
    {
        $item['ip'] = keyword($item['ip']) . '<br>' . ipGeolocation($item['ip']);
        $item['create_time'] = timeFormat($item['create_time']);
        return $item;
    }
}
