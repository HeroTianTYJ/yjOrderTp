<?php

return [
    'session_key_admin' => '9ERniVGJIzC8V5e5eRHvuDR2kY7FYPsj3LycsiMu',  //主后台session key
    'pass_key' => 'uptfJ33SjUtjDj8WOaAqsOuTB433shHBxeILGEvu',  //密码的盐
    'reset_pass_key' => 'vjSNiDraUS1levQmYVT5DxT66566M8DoXT2xj1Ve',  //重置密码的密钥

    'openid' => 'odh0BtM-3KOuH9rbql8Rl4S_i3lY',  //OpenID
    'web_name' => '昱杰订单管理系统',  //站点名称
    'admin_mail' => 'tianyingjie@qq.com',  //管理员邮箱
    'www' => '0',  //是否强制www
    'https' => '0',  //是否强制https
    'manager_enter' => 'admin.php',  //后台入口
    'index_php' => 'index.php/',  //隐藏index.php
    'copyright_backend' => '<a href="https://www.yjrj.top/" target="_blank">昱杰软件</a> © 版权所有',  //登录页版权
    'order_time' => '0',  //防刷单间隔
    'order_search' => '0',  //订单查询
    'order_search_step' => '0',  //跨模板查询
    'alipay_app_id' => '',  //支付宝APPID
    'alipay_merchant_private_key' => '',  //支付宝应用私钥
    'alipay_public_key' => '',  //支付宝公钥
    'wechat_pay_app_id' => '',  //微信支付AppID
    'wechat_pay_app_secret' => '',  //微信支付AppSecret
    'wechat_pay_mch_id' => '',  //微信支付商户号
    'wechat_pay_key' => '',  //微信支付商户密钥
    'wechat_pay_cert_serial_number' => '',  //微信支付证书序列号
    'wechat_pay_cert_private_key' => '',  //微信支付证书私钥
    'qq_app_id' => '',  //QQ互联AppID
    'qq_app_key' => '',  //QQ互联AppKey
    'wechat_app_id' => '',  //微信公众平台AppID
    'wechat_app_secret' => '',  //微信公众平台AppSecret
    'wechat_open_app_id' => '',  //微信开放平台AppID
    'wechat_open_app_secret' => '',  //微信开放平台AppSecret
    'mail_order_subject' => '您有一笔新订单，请及时发货',  //订单提醒邮件标题
    'mail_order_content' => '<p>订单详情</p>
<p>订 单 号：{order_id}</p>
<p>订购商品：{product_name}</p>
<p>成交单价：{product_price}元</p>
<p>订购数量：{product_count}</p>
<p>成交总价：{product_total}元</p>
<p>姓　　名：{name}</p>
<p>联系电话：{tel}</p>
<p>详细地址：{province} {city} {county} {town} {address}</p>
<p>备　　注：{note}</p>
<p>　下单IP：{ip}</p>
<p>支付方式：{payment}</p>',  //订单提醒邮件内容
    'mail_pay_subject' => '您的订单尚未支付，请及时支付',  //支付提醒邮件标题
    'mail_pay_content' => '<p>您的订单尚未支付，请及时支付，支付链接：<a href="{alipay_url}" target="_blank">支付宝</a> <a href="{wxpay_url}" target="_blank">微信支付</a>。</p>
<p>订单详情</p>
<p>订 单 号：{order_id}</p>
<p>订购商品：{product_name}</p>
<p>商品单价：{product_price}元</p>
<p>订购数量：{product_count}</p>
<p>商品总价：{product_total}元</p>
<p>姓　　名：{name}</p>
<p>联系电话：{tel}</p>
<p>详细地址：{province} {city} {county} {town} {address}</p>
<p>备　　注：{note}</p>
<p>　下单IP：{ip}</p>
<p>支付方式：{payment}</p>
<p>订单状态：{order_state}</p>
<p>下单时间：{date}</p>',  //支付提醒邮件内容
    'mail_send_subject' => '您的订单已发货，请注意查收',  //发货提醒邮件标题
    'mail_send_content' => '<p>您的物流信息为：快递公司 {express_name}　快递单号 {express_id}。您可以<a href="{express_url}" target="_blank">点击此处</a>查询物流详情。</p>
<p>订单详情</p>
<p>订 单 号：{order_id}</p>
<p>订购商品：{product_name}</p>
<p>商品单价：{product_price}元</p>
<p>订购数量：{product_count}</p>
<p>商品总价：{product_total}元</p>
<p>姓　　名：{name}</p>
<p>联系电话：{tel}</p>
<p>详细地址：{province} {city} {county} {town} {address}</p>
<p>备　　注：{note}</p>
<p>　下单IP：{ip}</p>
<p>支付方式：{payment}</p>
<p>支付订单号：{pay_id}</p>
<p>支付场景：{pay_scene}</p>
<p>支付时间：{pay_date}</p>
<p>订单状态：{order_state}</p>
<p>下单时间：{date}</p>',  //发货提醒邮件内容
];
