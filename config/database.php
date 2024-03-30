<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type' => 'mysql',  //数据库类型
            'hostname' => '127.0.0.1',  //数据库服务器
            'hostport' => '3306',  //服务器端口
            'database' => 'yjorder',  //数据库名
            'username' => 'root',  //数据库用户名
            'password' => 'tyjtyjtyj',  //数据库密码
            'prefix' => 'yjorder_',  //数据表前缀
            'charset' => 'UTF8MB4',  //字符集
            'fields_cache' => true  //开启字段缓存
        ]
    ]
];
