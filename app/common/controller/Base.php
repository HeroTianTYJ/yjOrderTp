<?php

namespace app\common\controller;

use think\facade\Config;
use think\facade\Request;
use think\facade\View;
use think\Response;

class Base
{
    public function __construct()
    {
        $this->initialize();
    }

    //初始化
    protected function initialize()
    {
        $this->loadConfig();
        $this->requestFilter();
    }

    //成功提示
    protected function succeed($locationUrl = '', $tipContent = '', $locationSecond = 3, $type = 0)
    {
        if ($tipContent == '') {
            header('Location:' . $locationUrl);
            exit;
        } else {
            if ($type == 0) {
                View::assign([
                    'Type' => 'succeed',
                    'TipContent' => $tipContent,
                    'LocationUrl' => $locationUrl,
                    'LocationContent' => '如果您的浏览器没有自动跳转，请点击这里',
                    'LocationSecond' => $locationSecond
                ]);
                return $this->view('../../common/view/public/tip');
            } elseif ($type == 1) {
                exit('<script type="text/javascript">alert(\'' . $tipContent . '\');parent.location.href=\'' .
                    $locationUrl . '\';</script>');
            } else {
                return $this->succeed($locationUrl, $tipContent, $locationSecond);
            }
        }
    }

    //错误提示
    protected function failed($tipContent = '', $locationSecond = 5, $locationUrl = '')
    {
        View::assign([
            'Type' => 'failed',
            'TipContent' => $tipContent,
            'LocationUrl' => $locationUrl,
            'LocationContent' => $locationUrl ? '如果您的浏览器没有自动跳转，请点击这里' : '点击这里返回上一页',
            'LocationSecond' => $locationSecond
        ]);
        return Response::create('../../common/view/public/tip', 'view', 404);
    }
    protected function error($tipContent = '', $locationSecond = 5, $locationUrl = '')
    {
        $html = '<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0">
<title>' . Config::get('system.web_name') . '-提示</title>
<base href="' . Config::get('url.web1') . '">
<script type="text/javascript" src="static/library/jquery/jquery.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/yj.admin.ui/js/tip.js?' . staticCache() . '"></script>
<link rel="stylesheet" type="text/css" href="static/yj.admin.ui/iconfont/iconfont.css?' . staticCache() . '">
<link rel="stylesheet" type="text/css" href="static/yj.admin.ui/iconfont/iconfont_extra.css?' . staticCache() . '">
<link rel="stylesheet" type="text/css" href="static/yj.admin.ui/css/tip.css?' . staticCache() . '">
<script type="text/javascript">
let CONFIG = {
  TYPE: \'failed\',
  TIP_CONTENT: \'\',
  LOCATION_URL: \'' . $locationUrl . '\',
  LOCATION_CONTENT: \'' . ($locationUrl ? '如果您的浏览器没有自动跳转，请点击这里' : '点击这里返回上一页') . '\',
  LOCATION_SECOND: ' . $locationSecond . '
};
</script>
</head>

<body>
<div class="tip">
  <p class="iconfont color"></p>
  <p class="tip_content">' . $tipContent . '</p>
  <p class="location"><a></a></p>
</div>
</body>
</html>';
        exit($html);
    }

    //模板引入方法重写
    protected function view($template = '')
    {
        return View::fetch($template);
    }

    //加载自定义配置文件
    protected function loadConfig()
    {
        foreach (['dir', 'file', 'pay_scene', 'payment', 'static', 'system', 'url'] as $value) {
            if (!Config::get($value)) {
                Config::load('diy/' . $value, $value);
            }
        }
    }

    //转义请求变量
    protected function requestFilter()
    {
        Request::filter('htmlspecialchars');
    }
}
