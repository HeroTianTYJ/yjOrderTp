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
    protected function succeed($url = '', $tip = '', $second = 3, $type = 0)
    {
        $url = htmlspecialchars_decode($url);
        if ($tip == '') {
            header('Location:' . $url);
            exit;
        } else {
            if (in_array($type, [0, 2])) {
                View::assign([
                    'Refresh' => $second,
                    'Url' => $url,
                    'Kind' => 'succeed',
                    'Tip' => $tip,
                    'Type' => $type,
                    'A' => '如果您的浏览器没有自动跳转，请点击这里'
                ]);
                return $this->view('../../common/view/public/tip');
            } elseif ($type == 1) {
                exit('<script type="text/javascript">alert(\'' . $tip . '\');parent.location.href=\'' . $url .
                    '\';</script>');
            } else {
                return $this->succeed($url, $tip, $second);
            }
        }
    }

    //错误提示
    protected function failed($tip = '', $second = 5, $type = 0, $url = '')
    {
        View::assign([
            'Refresh' => $second,
            'Url' => htmlspecialchars_decode($url),
            'Kind' => 'failed',
            'Tip' => $tip,
            'Type' => $type,
            'A' => $type ? '如果您的浏览器没有自动跳转，请点击这里' : '点击这里返回上一页'
        ]);
        return $this->view('../../common/view/public/tip', 404);
    }
    protected function error($tip = '', $second = 5, $type = 0, $url = '')
    {
        $a = $type ? '如果您的浏览器没有自动跳转，请点击这里' : '点击这里返回上一页';
        $html = '<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0">
<title>' . Config::get('system.web_name') . '-提示</title>
<base href="' . Config::get('url.web1') . '">
<script type="text/javascript" src="static/vendor/jquery.js?' . staticCache() . '"></script>
<link rel="stylesheet" type="text/css" href="static/common/iconfont/iconfont.css?' . staticCache() . '">
<link rel="stylesheet" type="text/css" href="static/common/iconfont/iconfont.extra.css?' . staticCache() . '">
<link rel="stylesheet" type="text/css" href="static/common/css/Tip.css?' . staticCache() . '">
<script type="text/javascript">
$(function () {
  height();
  $(window).on({resize: height});
  function height () {
    $(\'.tip\').height($(window).height() - 100);
  }
});
</script>
</head>
<body>
<div class="tip"><p class="iconfont icon-tip-failed"></p><p>' . $tip . '</p>';
        if ($type == 0) {
            $html .= '<p class="location"><a href="javascript:history.go(-1)">' . $a . '</a></p>
<script type="text/javascript">setTimeout(\'history.go(-1)\',' . $second . '*1000)</script>';
        } elseif ($type == 1) {
            $html .= '<p class="location"><a href="' . htmlspecialchars_decode($url . '') . '">' . $a . '</a></p>
<script type="text/javascript">setTimeout("location.href=\'' .
                htmlspecialchars_decode($url . '') . '\'",' . $second . '*1000)</script>';
        }
        $html .= '</div></body></html>';
        exit($html);
    }

    //模板引入方法重写
    protected function view($template = '', $code = 200)
    {
        return Response::create($template, 'view', $code);
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
