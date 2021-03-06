<?php

namespace app\home\controller;

use extend\QQWry;
use qqlogin\QC;
use think\captcha\facade\Captcha;
use think\facade\Request;
use think\facade\Config;
use app\home\model;

class Common extends Base
{
    //访问统计
    public function visit()
    {
        $Visit = new model\Visit();
        if ($Visit->yesterday()) {
            $output = '"IP","访问页面","当日次数","第一次","最后一次",';
            $object = $Visit->all();
            if ($object) {
                include ROOT_PATH . '/extend/QQWry.php';
                $QQWry = QQWry::getInstance();
                foreach ($object as $value) {
                    $output .= "\r\n" . '"' . $value['ip'] . ' -- ' . $QQWry->getAddr($value['ip']) . '","' .
                        $value['url'] . '","' . $value['count'] . '","' . dateFormat($value['date1']) . '","' .
                        dateFormat($value['date2']) . '",';
                }
            }
            $output = mb_convert_encoding($output, 'GBK', 'UTF-8');
            $file = Config::get('app.output_dir') . 'visit_' . date('YmdHis') . '.csv';
            if (file_put_contents(ROOT_PATH . '/' . $file, $output)) {
                $Visit->truncate();
            }
        }
        $object = $Visit->one();
        $object ? $Visit->modify($object['id']) : $Visit->add();
        exit;
    }

    //QQ登录
    public function qq()
    {
        $QC = new QC('', '', Config::get('app.web_url') . Config::get('system.index_php') . 'common/qqReturn');
        $QC->qqLogin();
    }

    //QQ登录返回
    public function qqReturn()
    {
        $QC = new QC('', '', Config::get('app.web_url') . Config::get('system.index_php') . 'common/qqReturn');
        $callback = $QC->qqCallback();
        $openid = $QC->getOpenid();
        if ($callback && $openid) {
            echo '<script type="text/javascript" src="' . Config::get('app.web_url') . 'public/base/jquery.js?' .
                staticCache() . '"></script>
<script type="text/javascript">
$(function(){
  $(".info2",window.opener.document).hide();
  $(".submit img",window.opener.document).hide();
  $(".info1",window.opener.document).show();
  $(".submit input",window.opener.document).show();
  window.close();
});
</script>';
        }
    }

    //验证码
    public function captcha()
    {
        if (Request::get('id')) {
            $captcha = Config::get('captcha');
            return isset($captcha[Request::get('id')]) ? Captcha::create(Request::get('id')) : '';
        }
        return '';
    }
    public function captcha2()
    {
        return Captcha::create();
    }

    //地区联动
    public function district()
    {
        return json_encode((new model\District())->all());
    }
}
