<?php

namespace app\index\controller;

use app\index\model;
use qrCode\QrCode;
use think\captcha\facade\Captcha;
use think\facade\Config;
use think\facade\Request;

class Common extends Base
{
    //访问统计
    public function visit()
    {
        if (Request::isPost()) {
            $Visit = new model\Visit();
            if ($Visit->yesterday()) {
                $output = '"IP","访问页面","当日次数","第一次","最后一次",';
                foreach ($Visit->all() as $value) {
                    $output .= "\r\n" . '"' . $value['ip'] . ' -- ' . ipGeolocation($value['ip']) . '","' .
                        $value['url'] . '","' . $value['count'] . '","' . timeFormat($value['create_time']) . '","' .
                        timeFormat($value['last_visit_time']) . '",';
                }
                if (
                    file_put_contents(
                        ROOT_DIR . '/' . Config::get('dir.output') . 'visit_' . date('YmdHis') . '.csv',
                        mb_convert_encoding($output, 'GBK', 'UTF-8')
                    )
                ) {
                    $Visit->truncate();
                }
            }
            $visitOne = $Visit->one();
            $visitOne ? $Visit->modify($visitOne['id']) : $Visit->add();
        }
    }

    //二维码
    public function qrcode()
    {
        ob_start();
        ob_clean();
        QrCode::png(Request::get('data'), 8);
    }

    //验证码
    public function captcha()
    {
        return Captcha::create(1);
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
