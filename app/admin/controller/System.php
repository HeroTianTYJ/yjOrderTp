<?php

namespace app\admin\controller;

use extend\QQWry;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;
use wechat\WechatExtra;
use app\admin\model;

class System extends Base
{
    public function index()
    {
        if (Request::isPost()) {
            if (Config::get('app.demo')) {
                return $this->failed('演示站，数据无法修改！');
            }
            $System = new model\System();
            $object = $System->form();
            if (is_numeric($object)) {
                $output = "<?php

return [
    'openid' => '" . str_replace("'", "\'", Request::post('openid')) . "',  //OpenID
    'web_name' => '" . str_replace("'", "\'", Request::post('web_name')) . "',  //站点名称
    'session_key' => '" . Config::get('system.session_key') . "',  //网站session key
    'pass_key' => '" . Config::get('system.pass_key') . "',  //密码的盐
    'reset_pass_key' => '" . Config::get('system.reset_pass_key') . "',  //重置密码的密钥
    'admin_mail' => '" . str_replace("'", "\'", Request::post('admin_mail')) . "',  //管理员邮箱
    'www' => '" . str_replace("'", "\'", Request::post('www', 0)) . "',  //是否强制www
    'https' => '" . str_replace("'", "\'", Request::post('https', 0)) . "',  //是否强制https
    'manage_enter' => '" . str_replace("'", "\'", Request::post('manage_enter')) . "',  //后台入口
    'manage_skin' => '" . str_replace("'", "\'", Request::post('manage_skin')) . "',  //后台皮肤
    'index_php' => '" . str_replace("'", "\'", Request::post('index_php')) . "',  //隐藏index.php
    'copyright_top' => '" . str_replace("'", "\'", Request::post('copyright_top')) . "',  //左上角版权
    'copyright_start' => '" . str_replace("'", "\'", Request::post('copyright_start', '', 'stripslashes')) .
                    "',  //起始页版权
    'copyright_footer' => '" . str_replace("'", "\'", Request::post('copyright_footer', '', 'stripslashes')) .
                    "',  //页面底部版权
    'order_db' => '" . str_replace("'", "\'", Request::post('order_db')) . "',  //订单入库
    'order_time' => '" . str_replace("'", "\'", Request::post('order_time')) . "',  //防刷单间隔
    'order_search' => '" . str_replace("'", "\'", Request::post('order_search')) . "',  //订单查询
    'order_search_step' => '" . str_replace("'", "\'", Request::post('order_search_step')) . "',  //跨模板查询
    'alipay_appid' => '" . str_replace("'", "\'", Request::post('alipay_appid')) . "',  //支付宝APPID
    'alipay_merchant_private_key' => '" . str_replace("'", "\'", Request::post('alipay_merchant_private_key')) .
                    "',  //支付宝应用私钥
    'alipay_public_key' => '" . str_replace("'", "\'", Request::post('alipay_public_key')) . "',  //支付宝公钥
    'wxpay_appid' => '" . str_replace("'", "\'", Request::post('wxpay_appid')) . "',  //微信支付APPID
    'wxpay_appsecret' => '" . str_replace("'", "\'", Request::post('wxpay_appsecret')) . "',  //微信支付APPSECRET
    'wxpay_mchid' => '" . str_replace("'", "\'", Request::post('wxpay_mchid')) . "',  //微信支付MCHID
    'wxpay_key' => '" . str_replace("'", "\'", Request::post('wxpay_key')) . "',  //微信支付KEY
    'qq_appid' => '" . str_replace("'", "\'", Request::post('qq_appid')) . "',  //QQ互联APP ID
    'qq_appkey' => '" . str_replace("'", "\'", Request::post('qq_appkey')) . "',  //QQ互联APP KEY
    'mail_order_subject' => '" . str_replace("'", "\'", Request::post('mail_order_subject')) . "',  //订单提醒邮件标题
    'mail_order_content' => '" . str_replace("'", "\'", Request::post('mail_order_content', '', 'stripslashes')) .
                    "',  //订单提醒邮件内容
    'mail_pay_subject' => '" . str_replace("'", "\'", Request::post('mail_pay_subject')) . "',  //支付提醒邮件标题
    'mail_pay_content' => '" . str_replace("'", "\'", Request::post('mail_pay_content', '', 'stripslashes')) .
                    "',  //支付提醒邮件内容
    'mail_send_subject' => '" . str_replace("'", "\'", Request::post('mail_send_subject')) . "',  //发货提醒邮件标题
    'mail_send_content' => '" . str_replace("'", "\'", Request::post('mail_send_content', '', 'stripslashes')) .
                    "',  //发货提醒邮件内容
    'install_time' => '" . Config::get('system.install_time') . "',  //安装时间
];
";

                if (file_put_contents(ROOT_PATH . '/' . Config::get('app.config_path') . '/system.php', $output)) {
                    if (
                        Request::post('www', 0) != Config::get('system.www') ||
                        Request::post('https', 0) != Config::get('system.https')
                    ) {
                        file_put_contents(ROOT_PATH . '/.htaccess', '<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On' . (Request::post('www', 0) ? '
  RewriteCond %{HTTP_HOST} !^www\.(.*)
  RewriteRule (.*) http://www.%{HTTP_HOST}/$1 [R=301,L]' : '') . (Request::post('https', 0) ? '
  RewriteCond %{SERVER_PORT} !^443$
  RewriteRule ^.*$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R]' : '') . '
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php' . (strstr(php_uname('s'), 'Windows') ? ' [L,E=PATH_INFO:$1]' : '/$1 [QSA,PT,L]') . '
</IfModule>');
                    }

                    if (Request::post('manage_enter') != Config::get('system.manage_enter')) {
                        return
                            rename(
                                ROOT_PATH . '/' . Config::get('system.manage_enter'),
                                ROOT_PATH . '/' . Request::post('manage_enter')
                            ) ? $this->success(
                                Config::get('app.web_url') . Request::post('manage_enter'),
                                '后台入口修改成功，即将跳转到新入口！',
                                0,
                                1
                            ) : $this->failed('后台入口重命名失败，请检查系统根目录权限！');
                    } else {
                        return $this->success(Config::get('app.prev_url'), '系统设置更新成功！');
                    }
                } else {
                    return $this->failed('系统设置更新失败，请检查' . Config::get('app.config_path') . '目录权限！');
                }
            } else {
                return $this->failed($object);
            }
        }
        $this->skin(Config::get('system.manage_skin'));
        include ROOT_PATH . '/extend/QQWry.php';
        View::assign(['IpVersion' => QQWry::getInstance()->getVersion()]);
        return $this->view();
    }

    public function wechatIp()
    {
        include ROOT_PATH . '/extend/wechat/WechatExtra.php';
        $Wechat = new WechatExtra([
            'appid' => Config::get('system.wechat_appid'),
            'appsecret' => Config::get('system.wechat_appsecret')
        ]);
        $Wechat->checkAuth();
        echo '<meta charset="utf-8">';
        if ($Wechat->errMsg == 'no access') {
            echo 'IP白名单配置正常，无需再配置';
        } elseif (preg_match('/invalid ip (.*) ipv6 (.*), not in whitelist rid:/', $Wechat->errMsg, $ip)) {
            echo 'IPv4地址：' . $ip[1] . '<br>IPv6地址：' . $ip[2] . '（如果IPv6地址前包含::ffff:，则无需配置IPv6地址）';
        } else {
            echo '发生其它错误，错误信息：' . $Wechat->errMsg . '，如有疑问请联系客服处理';
        }
    }

    private function skin($id = 0)
    {
        $html = '';
        foreach (
            [
                'default' => '黑色', 'blue' => '蓝色', 'green' => '绿色', 'red' => '红色', 'yellow' => '黄色', 'orange' => '橙色'
            ] as $key => $value
        ) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . '>' . $value . '</option>';
        }
        View::assign(['Skin' => $html]);
    }
}
