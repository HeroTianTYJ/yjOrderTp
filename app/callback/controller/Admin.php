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
        if (Request::get('code') && Request::get('state')) {
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
            $userInfo = $Wechat->getUserInfo(Request::get('code'), Request::get('state'));
            if ($Wechat->errMsg) {
                return $this->failed('微信登录失败，请重试，错误信息：' . $Wechat->errMsg);
            }
            $Manager = new model\Manager();
            $managerOauthLogin = $Manager->oauthLogin('wechat', $userInfo['openid'], $userInfo['unionid'] ?? '');
            if ($managerOauthLogin) {
                $loginDo = $this->loginDo($managerOauthLogin, Config::get('login_way.id.wechat'));
                if ($loginDo != '1') {
                    return $loginDo;
                }
                return $this->succeed(Config::get('url.web1') . Config::get('system.manager_enter'), '登录成功，跳转中。。。');
            } else {
                Session::set(
                    Config::get('system.session_key_admin') . '.login_info',
                    ['type' => 'wechat', 'openid' => $userInfo['openid'], 'unionid' => $userInfo['unionid'] ?? '']
                );
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
        if (Request::get('code') && Request::get('state')) {
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
            $userInfo = $Wechat->getUserInfo(Request::get('code'), Request::get('state'));
            if ($Wechat->errMsg) {
                return $this->failed('微信绑定失败，请重试，错误信息：' . $Wechat->errMsg, 0);
            }
            $Manager = new model\Manager();
            if ($Manager->oauthLogin('wechat', $userInfo['openid'], $userInfo['unionid'] ?? '')) {
                return $this->failed('此微信号已绑定了其它账号，无法再绑定此账号！', 0);
            }
            if (
                $Manager->oauthBind(
                    'wechat',
                    Session::get(Config::get('system.session_key_admin') . '.manage_info.id'),
                    $userInfo['openid'],
                    $userInfo['unionid'] ?? ''
                )
            ) {
                return in_array(
                    device(),
                    ['harmonyWechat', 'androidWechat', 'iphoneWechat', 'windowsWechat', 'macWechat']
                ) ?
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
        if (Request::get('code') && Request::get('state')) {
            $QqLogin = new QqLogin([
                'app_id' => Config::get('system.qq_app_id'),
                'app_key' => Config::get('system.qq_app_key'),
                'redirect_uri' => Config::get('url.web1') . 'callback.php/admin/qqLogin.html',
                'proxy_uri' => Config::get('system.qq_proxy_uri')
            ]);
            $userInfo = $QqLogin->getUserInfo(Request::get('code'), Request::get('state'));
            if ($QqLogin->error) {
                return $this->failed('QQ登录失败，请重试，错误信息：' . $QqLogin->error);
            }
            $Manager = new model\Manager();
            $managerOauthLogin = $Manager->oauthLogin('qq', $userInfo['openid'], $userInfo['unionid']);
            if ($managerOauthLogin) {
                $loginDo = $this->loginDo($managerOauthLogin, Config::get('login_way.id.qq'));
                if ($loginDo != '1') {
                    return $loginDo;
                }
                return $this->succeed(Config::get('url.web1') . Config::get('system.manager_enter'), '登录成功，跳转中。。。');
            } else {
                Session::set(
                    Config::get('system.session_key_admin') . '.login_info',
                    ['type' => 'qq', 'openid' => $userInfo['openid'], 'unionid' => $userInfo['unionid']]
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
        if (Request::get('code') && Request::get('state')) {
            $QqLogin = new QqLogin([
                'app_id' => Config::get('system.qq_app_id'),
                'app_key' => Config::get('system.qq_app_key'),
                'redirect_uri' => Config::get('url.web1') . 'callback.php/admin/qqBind.html',
                'proxy_uri' => Config::get('system.qq_proxy_uri')
            ]);
            $userInfo = $QqLogin->getUserInfo(Request::get('code'), Request::get('state'));
            if ($QqLogin->error) {
                return $this->failed('QQ绑定失败，请重试，错误信息：' . $QqLogin->error, 0);
            }
            $Manager = new model\Manager();
            if ($Manager->oauthLogin('qq', $userInfo['openid'], $userInfo['unionid'])) {
                return $this->failed('此QQ号已绑定了其它账号，无法再绑定此账号！', 0);
            }
            return $Manager->oauthBind(
                'qq',
                Session::get(Config::get('system.session_key_admin') . '.manage_info.id'),
                $userInfo['openid'],
                $userInfo['unionid']
            ) ?
                '<script type="text/javascript">window.opener.location.reload();window.close();</script>' :
                $this->failed('QQ绑定失败，请重试！', 0);
        } else {
            return $this->failed('非法操作！');
        }
    }
}
