<?php

return [
    'version' => '36.0|2023-11-10',  //版本
    'page_size' => 20,  //默认每页条数

    'demo' => $_SERVER['HTTP_HOST'] == 'www.yjrj.top' && getUserIp() != '111.225.42.206',

    'error_message' => '页面错误！请稍后再试～',
    'show_error_msg' => false,
];
