<?php

namespace app\callback\controller;

use app\admin\controller\Login;
use app\callback\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use yjrj\QqLogin;
use yjrj\Wechat;

class Admin extends Login
{
    protected function initialize()
    {
        $this->loadConfig();
        $this->requestFilter();
    }

    public function wechatLogin()
    {
        if (Request::get('code')) {
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
            $Manager = new model\Manager();
            $userInfo = $Wechat->getUserInfo();
            $managerWechatLogin = isset($userInfo['unionid']) ?
                $Manager->wechatLogin($userInfo['unionid']) :
                $Manager->wechatLogin2($userInfo['openid']);
            if ($managerWechatLogin) {
                $loginDo = $this->loginDo($managerWechatLogin);
                if ($loginDo != '1') {
                    return $loginDo;
                }
                return $this->succeed(Config::get('url.web1') . Config::get('system.manager_enter'), '登录成功，跳转中。。。');
            } else {
                $loginInfo = ['type' => 'wechat', 'openid' => $userInfo['openid']];
                if (isset($userInfo['unionid'])) {
                    $loginInfo['unionid'] = $userInfo['unionid'];
                }
                Session::set(Config::get('system.session_key_admin') . '.login_info', $loginInfo);
                return $this->succeed(
                    Config::get('url.web1') . Config::get('system.manager_enter'),
                    '您的微信号尚未绑定管理员账号，即将跳转到绑定页面。。。'
                );
            }
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function wechatBind()
    {
        if (Request::get('code')) {
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
            $Manager = new model\Manager();
            $userInfo = $Wechat->getUserInfo();
            if (
                isset($userInfo['unionid']) ?
                    $Manager->wechatLogin($userInfo['unionid']) :
                    $Manager->wechatLogin2($userInfo['openid'])
            ) {
                return $this->failed('此微信已绑定了其它账号，无法再绑定此账号！', 0);
            }
            if (
                $Manager->wechatOpenId(
                    $userInfo['openid'],
                    $userInfo['unionid'] ?? '',
                    Session::get(Config::get('system.session_key_admin') . '.manage_info.id')
                )
            ) {
                return in_array(device(), ['harmonyWechat', 'androidWechat', 'iphoneWechat', 'windowsWechat', 'macWechat']) ?
                    $this->succeed(Config::get('url.web1') . Config::get('system.manager_enter') .
                        '/profile/index.html') :
                    '<script type="text/javascript">window.opener.location.reload();window.close();</script>';
            } else {
                return $this->failed('微信绑定失败，请重试！', 0);
            }
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function qqLogin()
    {
        if (Request::get('state') && Request::get('code')) {
            $openid = (new QqLogin([
                'app_id' => Config::get('system.qq_app_id'),
                'app_key' => Config::get('system.qq_app_key'),
                'redirect_uri' => Config::get('url.web1') . 'callback.php/admin/qqLogin.html'
            ]))->getOpenid();
            $managerQqLogin = (new model\Manager())->qqLogin($openid);
            if ($managerQqLogin) {
                $loginDo = $this->loginDo($managerQqLogin);
                if ($loginDo != '1') {
                    return $loginDo;
                }
                return $this->succeed(Config::get('url.web1') . Config::get('system.manager_enter'), '登录成功，跳转中。。。');
            } else {
                Session::set(
                    Config::get('system.session_key_admin') . '.login_info',
                    ['type' => 'qq', 'openid' => $openid]
                );
                return $this->succeed(
                    Config::get('url.web1') . Config::get('system.manager_enter'),
                    '您的QQ号尚未绑定管理员账号，即将跳转到绑定页面。。。'
                );
            }
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function qqBind()
    {
        if (Request::get('state') && Request::get('code')) {
            $Manager = new model\Manager();
            $openId = (new QqLogin([
                'app_id' => Config::get('system.qq_app_id'),
                'app_key' => Config::get('system.qq_app_key'),
                'redirect_uri' => Config::get('url.web1') . 'callback.php/admin/qqBind.html'
            ]))->getOpenid();
            if ($Manager->qqLogin($openId)) {
                return $this->failed('此QQ已绑定了其它账号，无法再绑定此账号！', 0);
            }
            return $Manager->qqOpenId(
                $openId,
                Session::get(Config::get('system.session_key_admin') . '.manage_info.id')
            ) ?
                '<script type="text/javascript">window.opener.location.reload();window.close();</script>' :
                $this->failed('QQ绑定失败，请重试！', 0);
        } else {
            return $this->failed('非法操作！');
        }
    }
}
