<?php

use think\facade\Config;

if (!defined('APPID')) {
    define('APPID', Config::get('system.wxpay_app_id'));
}
if (!defined('MCHID')) {
    define('MCHID', Config::get('system.wxpay_mchid'));
}
if (!defined('KEY')) {
    define('KEY', Config::get('system.wxpay_key'));
}
if (!defined('APPSECRET')) {
    define('APPSECRET', Config::get('system.wxpay_app_secret'));
}
/*if (!defined('SSLCERT_PATH')) {
    define('SSLCERT_PATH', '');
}
if (!defined('SSLKEY_PATH')) {
    define('SSLKEY_PATH', '');
}
if (!defined('CURL_PROXY_HOST')) {
    define('CURL_PROXY_HOST', '0.0.0.0');
}
if (!defined('CURL_PROXY_PORT')) {
    define('CURL_PROXY_PORT', 0);
}*/
