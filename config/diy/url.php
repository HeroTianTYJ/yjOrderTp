<?php

use think\facade\Request;

return [
    'prev' => Request::server('HTTP_REFERER') ?? '',  //上一页地址
    'web1' => (Request::server('HTTPS') == 'on' ? 'https' : 'http') . '://' . Request::server('HTTP_HOST') .
        substr(Request::server('SCRIPT_NAME'), 0, strrpos(Request::server('SCRIPT_NAME'), '/') + 1),  //站点URL
    'web2' => '//' . Request::server('HTTP_HOST') .
        substr(Request::server('SCRIPT_NAME'), 0, strrpos(Request::server('SCRIPT_NAME'), '/') + 1),  //站点URL
    'web3' => (Request::server('HTTPS') == 'on' ? 'https' : 'http') . '://' . Request::server('HTTP_HOST') .
        '/'  //站点URL
];
