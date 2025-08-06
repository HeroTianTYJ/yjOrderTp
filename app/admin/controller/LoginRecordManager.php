<?php

namespace app\admin\controller;

use app\admin\model;
use app\admin\library\Html;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;
use yjrj\QQWry;

class LoginRecordManager extends Base
{
    public function index()
    {
        $loginRecordManagerAll = (new model\LoginRecordManager())->all();
        if (Request::isAjax()) {
            foreach ($loginRecordManagerAll as $key => $value) {
                $loginRecordManagerAll[$key] = $this->listItem($value);
            }
            return $loginRecordManagerAll->items() ?
                apiResponse('', 1, $loginRecordManagerAll->items()) : '';
        }
        View::assign(['Total' => $loginRecordManagerAll->total()]);
        Html::manager(Request::get('manager_id'));
        return $this->view();
    }

    public function output()
    {
        if (Request::isAjax()) {
            $output = '"管理员","登录IP","登录时间",';
            $LoginRecordManager = new model\LoginRecordManager();
            $loginRecordManagerAll = $LoginRecordManager->all2();
            if (count($loginRecordManagerAll)) {
                $Manager = new model\Manager();
                foreach ($loginRecordManagerAll as $value) {
                    $managerOne = $Manager->one($value['manager_id']);
                    $output .= "\r\n" . '"' . ($managerOne ? $managerOne['name'] : '此管理员已被删除') . '","' .
                        $value['ip'] . ' -- ' . QQWry::getAddress($value['ip']) . '","' . timeFormat($value['create_time']) .
                        '",';
                }
            }
            $output = mb_convert_encoding($output, 'GBK', 'UTF-8');
            $file = Config::get('dir.output') . 'login_manager_' . date('YmdHis') . '.csv';
            if (file_put_contents(ROOT_DIR . '/' . $file, $output)) {
                $LoginRecordManager->truncate();
                return apiResponse('登录记录导出成功！');
            } else {
                return apiResponse('登录记录导出失败，请检查' . Config::get('dir.output') . '目录权限！', 0);
            }
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['ip'] = keyword($item['ip']) . '<br>' . QQWry::getAddress($item['ip']);
        $managerOne = (new model\Manager())->one($item['manager_id']);
        $item['manager'] = $managerOne ? $managerOne['name'] : '此管理员已被删除';
        $item['create_time'] = timeFormat($item['create_time']);
        return $item;
    }
}
