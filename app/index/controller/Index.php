<?php

namespace app\index\controller;

use app\common\controller\Template;
use app\index\model;
use think\facade\Request;
use think\facade\Route;

class Index extends Base
{
    public function index()
    {
        if (!(new model\Template())->one(Request::param('id'))) {
            return $this->failed('不存在此下单模板！');
        }
        return (new Template())->html(Request::param('id'));
    }

    public function t()
    {
        return '<img src="' . Route::buildUrl('/common/qrcode', ['data' => 'weixin://wxpay/bizpayurl?pr=nRBKLMpzz']) .
            '" alt="">';
    }
}
