<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;
use yjrj\QQWry;

class Visit extends Base
{
    public function index()
    {
        $visitAll = (new model\Visit())->all();
        if (Request::isAjax()) {
            foreach ($visitAll as $key => $value) {
                $visitAll[$key] = $this->listItem($value);
            }
            return $visitAll->items() ? apiResponse('', 1, $visitAll->items()) : '';
        }
        View::assign(['Total' => $visitAll->total()]);
        return $this->view();
    }

    public function output()
    {
        if (Request::isAjax()) {
            $output = '"IP","访问页面","当日访问次数","第一次","最后一次",';
            $Visit = new model\Visit();
            $visitAll = $Visit->all2();
            foreach ($visitAll as $value) {
                $output .= "\r\n" . '"' . $value['ip'] . ' -- ' . QQWry::getAddress($value['ip']) . '","' .
                    $value['url'] . '","' . $value['count'] . '","' . dateFormat($value['date1']) . '","' .
                    dateFormat($value['date2']) . '",';
            }
            $output = mb_convert_encoding($output, 'GBK', 'UTF-8');
            $file = Config::get('dir.output') . 'visit_' . date('YmdHis') . '.csv';
            if (file_put_contents(ROOT_DIR . '/' . $file, $output)) {
                $Visit->truncate();
                return apiResponse('访问统计导出成功！');
            } else {
                return apiResponse('访问统计导出失败，请检查' . Config::get('dir.output') . '目录权限！', 0);
            }
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function js()
    {
        if (Request::isAjax()) {
            $js = ROOT_DIR . '/static/index/js/visit.js';
            return file_put_contents(
                $js,
                preg_replace(
                    '/url: \'.*\',/U',
                    'url: \'' . Config::get('url.web2') . Config::get('system.index_php') . 'common/visit.html\',',
                    file_get_contents($js)
                )
            ) ? apiResponse('文件更新成功！') : apiResponse('文件更新失败，请检查static/index/js目录权限！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['truncate_url'] = keyword(truncate($item['url'], 0, 28));
        $item['ip'] = keyword($item['ip']) . '<br>' . QQWry::getAddress($item['ip']);
        $item['date1'] = dateFormat($item['date1']);
        $item['date2'] = dateFormat($item['date2']);
        return $item;
    }
}
