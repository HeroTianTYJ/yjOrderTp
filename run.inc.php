<?php

session_cache_limiter('private_no_cache');
header('Cache-Control:private');

if (strstr($_SERVER['PHP_SELF'], 'run.inc.php')) {
    exit;
}
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    exit('<meta charset="utf-8">本系统需运行在PHP8.0或以上版本中，建议您升级PHP版本，使运行环境更安全。如确实不能升级，请' .
        '<a href="https://www.yjrj.top/3" target="_blank">点击此处</a>下载本系统的PHP7.4版支持包，下载后，删除本系统根目录中的vendor目录及' .
        'run.inc.php文件，并将支持包解压到本系统根目录，然后刷新本页面即可。');
}

const ROOT_DIR = __DIR__;
require ROOT_DIR . '/vendor/autoload.php';
$http = (new think\App())->http;
$response = $http->name(APP_NAME)->run();
$response->send();
$http->end($response);
