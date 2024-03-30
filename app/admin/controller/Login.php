<?php

namespace app\admin\controller;

use app\admin\model;
use app\common\controller\Auth;
use think\facade\Config;
use think\facade\Request;
use think\facade\Route;
use think\facade\Session;
use yjrj\QqLogin;
use yjrj\Wechat;

class Login extends Base
{
    public function index()
    {
        if (Request::isPost()) {
            $managerLogin = (new model\Manager())->login();
            if (is_object($managerLogin)) {
                if (passEncode(Request::post('pass')) != $managerLogin['pass']) {
                    return showTip('帐号或密码不正确！', 0);
                }
                $loginDo = $this->loginDo($managerLogin);
                if ($loginDo != '1') {
                    return $loginDo;
                }
                return showTip('登录成功，跳转中。。。');
            } elseif (is_string($managerLogin)) {
                return showTip($managerLogin, 0);
            } else {
                return showTip('帐号或密码不正确！', 0);
            }
        }
        return $this->view();
    }

    public function wechat()
    {
        if (in_array(device(), ['androidWechat', 'iphoneWechat', 'windowsWechat', 'macWechat'])) {
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
        $Wechat->oauthRedirect(Config::get('url.web1') . 'callback.php/admin/wechatLogin.html');
    }

    public function qq()
    {
        (new QqLogin([
            'app_id' => Config::get('system.qq_app_id'),
            'app_key' => Config::get('system.qq_app_key'),
            'redirect_uri' => Config::get('url.web1') . 'callback.php/admin/qqLogin.html'
        ]))->login();
    }

    public function logout()
    {
        Session::delete(Config::get('system.session_key_admin'));
        return $this->succeed(Route::buildUrl('/' . parse_name(Request::controller()) . '/index'), '您已退出登录！', 1);
    }

    public function bind()
    {
        $validate = (new Auth())->validate('login');
        return $validate['state'] == 0 ?
            $this->failed($validate['content'], 0) :
            $this->succeed(Route::buildUrl('/' . parse_name(Request::controller()) . '/index'), '绑定成功，即将跳转到登录页面！');
    }

    public function updateOpenid()
    {
        if (Config::get('app.demo')) {
            return 0;
        }
        $file = ROOT_DIR . '/config/diy/system.php';
        return file_put_contents($file, preg_replace(
            "/'openid'\s*=>\s*'.*'/U",
            "'openid' => '" . Request::post('openid') . "'",
            file_get_contents($file)
        )) ? 1 : 0;
    }

    protected function loginDo($managerLogin)
    {
        return (new Auth())->login($managerLogin);
    }
}
