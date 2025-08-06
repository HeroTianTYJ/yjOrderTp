<?php

namespace app\common\controller;

use app\index\model;
use think\facade\Config;

class Template
{
    public function html($id, $demo = 0)
    {
        if ($demo) {
            $templateOne = [
                'name' => '填写订单',
                'template_id' => $id,
                'template_style_id' => 1,
                'product_type' => 1,
                'product_sort_ids' => '',
                'product_ids' => '',
                'product_default' => '',
                'product_view_type' => 0,
                'field_ids' => '1,2,3,4,5,6,7,8',
                'payment_ids' => '1,2,3',
                'payment_default_id' => 1,
                'is_show_search' => 1,
                'is_show_send' => 1,
                'is_captcha' => 1
            ];
            if (in_array($id, [1, 2])) {
                $templateOne['template_style_id'] = 11;
            } elseif ($id == 3) {
                $templateOne['template_style_id'] = 12;
            }
        } else {
            $templateOne = (new model\Template())->one($id);
        }

        $templateStyleOne = (new model\TemplateStyle())->one($templateOne['template_style_id']);

        $productHtml = '';
        if ($demo == 0) {
            $Product = new model\Product();
            if ($templateOne['product_type'] == 0) {
                $productAll = $Product->all($templateOne['product_ids']);
                if ($templateOne['product_view_type'] == 0) {
                    foreach ($productAll as $value) {
                        $productHtml .= '<label style="color:' . $value['color'] .
                            ';"><input type="radio" name="product_id" value="' . $value['id'] . '" ' .
                            ($value['id'] == $templateOne['product_default'] ? 'checked' : '') . ' price="' .
                            $value['price'] . '">' . $value['name'] . '（' . $value['price'] . '元）</label><br>';
                    }
                } elseif ($templateOne['product_view_type'] == 1) {
                    $productHtml .= '<select name="product_id">';
                    foreach ($productAll as $value) {
                        $productHtml .= '<option value="' . $value['id'] . '" ' .
                            ($value['id'] == $templateOne['product_default'] ? 'selected' : '') . ' style="color:' .
                            $value['color'] . ';" price="' . $value['price'] . '">' . $value['name'] . '（' .
                            $value['price'] . '元）</option>';
                    }
                    $productHtml .= '</select>';
                }
            } elseif ($templateOne['product_type'] == 1) {
                $productSortAll = (new model\ProductSort())->all($templateOne['product_sort_ids']);
                if ($templateOne['product_view_type'] == 0) {
                    foreach ($productSortAll as $value) {
                        $productHtml .= '<span style="color:' . $value['color'] . ';">' . $value['name'] .
                            '</span><br>';
                        $productAll = $Product->all($templateOne['product_ids'], $value['id']);
                        foreach ($productAll as $v) {
                            $productHtml .= '<label style="color:' . $v['color'] .
                                ';"><input type="radio" name="product_id" value="' . $v['id'] . '" ' .
                                ($v['id'] == $templateOne['product_default'] ? 'checked' : '') . ' price="' .
                                $v['price'] . '">' . $v['name'] . '（' . $v['price'] . '元）</label><br>';
                        }
                    }
                } elseif ($templateOne['product_view_type'] == 1) {
                    $productHtml .= '<select name="product_id">';
                    foreach ($productSortAll as $value) {
                        $productHtml .= '<optgroup label="' . $value['name'] . '" style="color:' . $value['color'] .
                            ';">';
                        $productAll = $Product->all($templateOne['product_ids'], $value['id']);
                        foreach ($productAll as $v) {
                            $productHtml .= '<option value="' . $v['id'] . '" ' .
                                ($v['id'] == $templateOne['product_default'] ? 'selected' : '') . ' style="color:' .
                                ($v['color'] ?: '#000') . ';" price="' . $v['price'] . '">' . $v['name'] . '（' .
                                $v['price'] . '元）</option>';
                        }
                        $productHtml .= '</optgroup>';
                    }
                    $productHtml .= '</select>';
                }
            }
        } else {
            $productHtml = '<select name="product_id"></select>';
        }

        $payHtml = '';
        foreach (Config::get('payment') as $key => $value) {
            if (in_array($key, explode(',', $templateOne['payment_ids']))) {
                $payHtml .= '<label><input type="radio" name="payment_id" value="' . $key . '" ' .
                    ($key == $templateOne['payment_default_id'] ? 'checked' : '') . '>' . $value . '</label> ';
            }
        }

        $fieldTemp = explode(',', $templateOne['field_ids']);
        $district = '';
        if (in_array($templateOne['template_id'], [0, 1, 2, 3])) {
            $districtArr = [
                '<div class="district1"><input type="hidden" name="province"><input type="hidden" name="city">' .
                '<input type="hidden" name="county"><input type="hidden" name="town"><dd><span class="left">省　　份：' .
                '</span><span class="right"><select class="province" lay-filter="province"><option value="">请选择省份' .
                '</option></select></span></dd><dd><span class="left">城　　市：</span><span class="right">' .
                '<select class="city" lay-filter="city"><option value="">请选择城市</option></select></span></dd><dd>' .
                '<span class="left">区 / 县 ：</span><span class="right"><select class="county" lay-filter="county">' .
                '<option value="">请选择区/县</option></select></span></dd><dd><span class="left">乡镇/街道：</span>' .
                '<span class="right"><select class="town" lay-filter="town"><option value="">若不清楚，可不选</option>' .
                '</select></span></dd></div>',
                '<div class="district2"><dd><span class="left">省　　份：</span><span class="right">' .
                '<input type="text" name="province2" class="text"></span></dd><dd><span class="left">城　　市：</span>' .
                '<span class="right"><input type="text" name="city2" class="text"></span></dd><dd><span class="left">' .
                '区 / 县 ：</span><span class="right"><input type="text" name="county2" class="text"></span></dd><dd>' .
                '<span class="left">乡镇/街道：</span><span class="right">' .
                '<input type="text" name="town2" class="text" placeholder="若不清楚，可留空"></span></dd></div>'
            ];
            if (in_array(4, $fieldTemp) && in_array(5, $fieldTemp)) {
                $district .= '<dd><span class="left">所在地区：</span><span class="right">' .
                    '<label><input type="radio" name="district_type" value="0" checked>选择填写</label> ' .
                    '<label><input type="radio" name="district_type" value="1">手动填写</label></span></dd>' .
                    $districtArr[0] . $districtArr[1];
            } elseif (in_array(4, $fieldTemp)) {
                $district .= '<dd><span class="left">所在地区：</span><span class="right">' .
                    '<input type="radio" name="district_type" value="0" checked style="display:none;"></span></dd>' .
                    $districtArr[0];
            } elseif (in_array(5, $fieldTemp)) {
                $district .= '<dd><span class="left">所在地区：</span><span class="right">' .
                    '<input type="radio" name="district_type" value="1" checked style="display:none;"></span></dd>' .
                    $districtArr[1];
            }
            return '<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0">
<title>' . $templateOne['name'] . '</title>
<base href="' . Config::get('url.web1') . '">
<script type="text/javascript" src="static/library/jquery/jquery.js?' . staticCache() . '"></script>
</head>

<body>
<script type="text/javascript" src="static/library/jquery/cookie.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/library/valid_form.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/yj.admin.ui/js/address.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/library/lay_ui/js/lay_ui.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/index/js/order.js?' . staticCache() . '"></script>
<link rel="stylesheet" type="text/css" href="static/library/lay_ui/css/lay_ui.css?' . staticCache() . '">
<link rel="stylesheet" type="text/css" href="static/index/css/template' .
                (in_array($templateOne['template_id'], [0, 1]) ? 1 : $templateOne['template_id']) . '.css?' . staticCache() .
                '">
<script type="text/javascript">let CONFIG={"DISTRICT":"' . Config::get('url.web1') . Config::get('system.index_php') .
                'common/district"};</script>
<style>.order{background:' . $templateStyleOne['bg_color'] . ';border:1px solid ' .
                $templateStyleOne['border_color'] . ';}.order .buy{border-bottom:1px solid ' .
                $templateStyleOne['border_color'] . ';}.order dl dd.submit input{background:' .
                $templateStyleOne['button_color'] . ';}.order dl dd .form-select dl dd.this{background:' .
                $templateStyleOne['select_current_bg_color'] . ' !important;}</style>

<div class="order">
  ' . ($templateOne['template_id'] != 1 ? '<div class="buy"></div>' : '<div class="buy2">在线快速订购</div>') . '
  
  <form method="post" action="' . Config::get('url.web1') . Config::get('system.index_php') .
                'sub_order.html" target="_parent" class="form layui-form">
    <input type="hidden" name="template_id" value="' . $id . '"><input type="hidden" name="price">' .
                '<input type="hidden" name="referrer">
    <dl><dd class="center">请认真填写订单信息，以便您尽快收到货物</dd><dd class="pro"><span class="left">订购商品：</span>' .
                '<span class="right">' . $productHtml . '</span></dd>' . (in_array(1, $fieldTemp) ?
                    '<dd><span class="left">订购数量：</span><span class="right">' .
                    '<input type="text" name="count" value="1" class="text"></span></dd>' :
                '<input type="hidden" name="count" value="1">') . '<dd><span class="left">总价合计：</span>' .
                '<span class="right"><span class="total"></span></span></dd>' . (in_array(2, $fieldTemp) ?
                    '<dd><span class="left">姓　　名：</span><span class="right">' .
                    '<input type="text" name="name" class="text"></span></dd>' : '') . (in_array(3, $fieldTemp) ?
                    '<dd><span class="left">联系电话：</span><span class="right">' .
                    '<input type="text" name="tel" class="text"></span></dd>' : '') . $district .
                (in_array(6, $fieldTemp) ? '<dd><span class="left">详细地址：</span><span class="right">' .
                    '<input type="text" name="address" class="text"></span></dd>' : '') . (in_array(7, $fieldTemp) ?
                    '<dd class="textarea"><span class="left">备　　注：</span><span class="right"><textarea name="note">' .
                    '</textarea></span></dd>' : '') . (in_array(8, $fieldTemp) ? '<dd><span class="left">电子邮箱：</span>' .
                    '<span class="right"><input type="text" name="email" placeholder="选填，可接收发货信息" class="text">' .
                    '</span></dd>' : '') . ($payHtml ? '<dd class="pay"><span class="left">支付方式：</span>' .
                    '<span class="right">' . $payHtml . '</span></dd>' :
                    '<input type="hidden" name="payment_id" value="' . $templateOne['payment_default_id'] . '">') .
                ($templateOne['is_captcha'] ? '<dd><span class="left">验 证 码：</span><span class="right">' .
                    '<input type="text" name="captcha" class="text2"></span></dd><dd class="captcha">' .
                    '<span class="left"></span><span class="right"><img src="' . Config::get('url.web1') .
                    Config::get('system.index_php') . 'common/captcha" alt="验证码" onClick="this.src=\'' .
                    Config::get('url.web1') . Config::get('system.index_php') .
                    'common/captcha?tm=\'+Math.random();" title="看不清？换一张"></span></dd>' : '') .
                '<dd class="submit"><input type="submit" value="立即提交订单"></dd></dl>
  </form>
  
  ' . ($templateOne['is_show_search'] ? '<form method="get" action="' . Config::get('url.web1') .
                    Config::get('system.index_php') . 'order/search.html" target="_blank" class="search">
    <dl>
      <input type="hidden" name="template_id" value="' . $id . '">
      <dd class="center">订单查询</dd>
      <dd><span class="left">查询方式：</span><span class="right">' .
                    '<label><input type="radio" name="field" value="1" checked>订单号</label> ' .
                    '<label><input type="radio" name="field" value="2">姓名</label> ' .
                    '<label><input type="radio" name="field" value="3">联系电话</label></span></dd>
      <dd><span class="left">关 键 词：</span><span class="right"><input type="text" name="keyword" class="text"></span>' .
                    '</dd>
      <dd class="submit"><input type="submit" value="查询订单"></dd>
    </dl>
  </form>' : '') . '
  
  ' . ($templateOne['is_show_send'] ? '<div class="new"></div>' : '') . '
</div>

<script type="text/javascript" src="static/index/js/visit.js?' . staticCache() . '"></script>
<div class="tip"></div>
</body>
</html>';
        } else {
            $districtArr = [
                '<div class="district1"><input type="hidden" name="province"><input type="hidden" name="city">' .
                '<input type="hidden" name="county"><input type="hidden" name="town"><dd>省　　份：' .
                '<select class="province" lay-filter="province"><option value="">请选择省份</option></select></dd><dd>' .
                '城　　市：<select class="city" lay-filter="city"><option value="">请选择城市</option></select></dd><dd>区 / 县 ：' .
                '<select class="county" lay-filter="county"><option value="">请选择区/县</option></select></dd>' .
                '<dd class="town-dd">乡镇/街道：<select class="town" lay-filter="town"><option value="">若不清楚，可不选' .
                '</option></select></dd></div>',
                '<div class="district2"><dd>省　　份：<input type="text" name="province2" class="text"></dd><dd>城　　市：' .
                '<input type="text" name="city2" class="text"></dd><dd>区 / 县 ：' .
                '<input type="text" name="county2" class="text"></dd><dd>乡镇/街道：' .
                '<input type="text" name="town2" class="text" style="width:435px;" placeholder="若不清楚，可留空"></dd></div>'
            ];
            if (in_array(4, $fieldTemp) && in_array(5, $fieldTemp)) {
                $district = '<dd>所在地区：<label><input type="radio" name="district_type" value="0" checked>选择填写' .
                    '</label> <label><input type="radio" name="district_type" value="1">手动填写</label></dd>' .
                    $districtArr[0] . $districtArr[1];
            } elseif (in_array(4, $fieldTemp)) {
                $district =
                    '<dd>所在地区：<input type="radio" name="district_type" value="0" checked style="display:none;"></dd>' .
                    $districtArr[0];
            } elseif (in_array(5, $fieldTemp)) {
                $district =
                    '<dd>所在地区：<input type="radio" name="district_type" value="1" checked style="display:none;"></dd>' .
                    $districtArr[1];
            }
            return '<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<title>' . $templateOne['name'] . '</title>
<base href="' . Config::get('url.web1') . '">
<script type="text/javascript" src="static/library/jquery/jquery.js?' . staticCache() . '"></script>
</head>

<body>
<script type="text/javascript" src="static/library/jquery/cookie.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/library/valid_form.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/yj.admin.ui/js/address.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/library/lay_ui/js/lay_ui.js?' . staticCache() . '"></script>
<script type="text/javascript" src="static/index/js/order.js?' . staticCache() . '"></script>
<link rel="stylesheet" type="text/css" href="static/library/lay_ui/css/lay_ui.css?' . staticCache() . '">
<link rel="stylesheet" type="text/css" href="static/index/css/template4.css?' . staticCache() . '">
<script type="text/javascript">let CONFIG={"DISTRICT":"' . Config::get('url.web1') . Config::get('system.index_php') .
                'common/district"};</script>
<style>.order{width:' . ($templateOne['is_show_send'] ? 950 : 614) . 'px;background:' .
                $templateStyleOne['bg_color'] . ';border:1px solid ' . $templateStyleOne['border_color'] .
                ';}.order div.left{border-right:1px solid ' . $templateStyleOne['border_color'] .
                ';}.order div.left dl dd.submit input{background:' . $templateStyleOne['button_color'] .
                ';}.order div.left .buy,.order div.right .send{border-bottom:1px solid ' .
                $templateStyleOne['border_color'] .
                ';}.order dl dd .form-select dl dd.this{background:' .
                $templateStyleOne['select_current_bg_color'] . ' !important;}</style>

<div class="order">
  <div class="left">
    <div class="buy"></div>
    
    <form method="post" action="' . Config::get('url.web1') . Config::get('system.index_php') .
                'sub_order.html" target="_parent" class="form layui-form">
      <input type="hidden" name="template_id" value="' . $id . '"><input type="hidden" name="price">' .
                '<input type="hidden" name="referrer"><dl><dd>请认真填写订单信息，以便您尽快收到货物</dd><dd class="pro">' .
                '<span class="protext">订购商品：</span><span class="prolist">' . $productHtml . '</span></dd>' .
                (in_array(1, $fieldTemp) ? '<dd>订购数量：<input type="text" name="count" value="1" class="text"></dd>' :
                    '<input type="hidden" name="count" value="1">') . '<dd>总价合计：<span class="total"></span></dd>' .
                (in_array(2, $fieldTemp) ? '<dd>姓　　名：<input type="text" name="name" class="text"></dd>' : '') .
                (in_array(3, $fieldTemp) ? '<dd>联系电话：<input type="text" name="tel" class="text"></dd>' : '') .
                $district . (in_array(6, $fieldTemp) ? '<dd>详细地址：<input type="text" name="address" class="text"></dd>' :
                    '') . (in_array(7, $fieldTemp) ? '<dd class="textarea"><span>备　　注：</span><textarea name="note">' .
                    '</textarea></dd>' : '') . (in_array(8, $fieldTemp) ? '<dd>电子邮箱：' .
                    '<input type="text" name="email" placeholder="选填，可接收发货信息" class="text"></dd>' : '') .
                ($payHtml ? '<dd>支付方式：' . $payHtml . '</dd>' : '<input type="hidden" name="payment_id" value="' .
                    $templateOne['payment_default_id'] . '">') . ($templateOne['is_captcha'] ? '<dd class="captcha">' .
                    '<span>验 证 码：<input type="text" name="captcha" class="text2"></span> <img src="' .
                    Config::get('url.web1') . Config::get('system.index_php') .
                    'common/captcha" alt="验证码" onClick="this.src=\'' . Config::get('url.web1') .
                    Config::get('system.index_php') . 'common/captcha?tm=\'+Math.random();" title="看不清？换一张"></dd>' :
                    '') . '<dd class="submit"><input type="submit" value="立即提交订单"></dd></dl>
    </form>
    
  ' . ($templateOne['is_show_search'] ? '<form method="get" action="' . Config::get('url.web1') .
                    Config::get('system.index_php') . 'order/search.html" target="_blank" class="search">
      <dl>
        <input type="hidden" name="template_id" value="' . $id . '">
        <dd>订单查询</dd>
        <dd>查询方式：<label><input type="radio" name="field" value="1" checked>订单号</label> ' .
                    '<label><input type="radio" name="field" value="2">姓名</label> ' .
                    '<label><input type="radio" name="field" value="3">联系电话</label></dd>
        <dd>关 键 词：<input type="text" name="keyword" class="text"></dd>
        <dd class="submit"><input type="submit" value="查询订单"></dd>
      </dl>
    </form>' : '') . '
  </div>
  
  ' . ($templateOne['is_show_send'] ? '<div class="right">
    <div class="send"></div><div class="list"><div class="list1"></div><div class="list2"></div></div>
  </div>' : '') . '
  
  <p style="clear:both"></p>
</div>

<script type="text/javascript" src="static/index/js/visit.js?' . staticCache() . '"></script>
<div class="tip"></div>
</body>
</html>';
        }
    }
}
