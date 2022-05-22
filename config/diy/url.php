<?php

return [
    'prev' => $_SERVER['HTTP_REFERER'] ?? '',  //上一页地址
    'web1' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' .
        $_SERVER['HTTP_HOST'] . substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1),  //站点URL
    'web2' => '//' . $_SERVER['HTTP_HOST'] .
        substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1),  //站点URL
    'web3' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' .
        $_SERVER['HTTP_HOST'] . '/'  //站点URL
];
