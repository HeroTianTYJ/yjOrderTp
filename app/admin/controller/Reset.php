<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Request;
use think\facade\Route;

class Reset extends Base
{
    public function index()
    {
        if (Request::isPost()) {
            $Manager = new model\Manager();
            $managerModify = $Manager->modify3();
            if (is_numeric($managerModify)) {
                return $this->succeed(
                    '',
                    '创始人登录密码重置成功，登录帐号为：' . $Manager->one(1)['name'] . '，<a href="' . Route::buildUrl('/login/index') .
                    '">点击此处</a>跳转到后台登录页！',
                    0
                );
            } else {
                return $this->failed($managerModify);
            }
        }
        return $this->view();
    }
}
