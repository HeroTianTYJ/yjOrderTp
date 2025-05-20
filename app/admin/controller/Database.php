<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class Database extends Base
{
    public function index()
    {
        $commonInfo = (new model\Common())->info();
        if (Request::isAjax()) {
            $rows = $size = $dataFree = 0;
            foreach ($commonInfo as $key => $value) {
                $commonInfo[$key] = $this->listItem($value);
                $rows += $value['Rows'];
                $size += $commonInfo[$key]['size_number'];
                $dataFree += $value['Data_free'];
            }
            $tableTotal = count($commonInfo);
            if ($tableTotal) {
                $commonInfo[$tableTotal]['Name'] = $tableTotal . ' 个表';
                $commonInfo[$tableTotal]['Rows'] = $rows;
                $commonInfo[$tableTotal]['Auto_increment'] = '-';
                $commonInfo[$tableTotal]['Size'] = $size < 1024 ?
                    $size . ' KB' :
                    number_format($size / 1024, 1, '.', '') . ' MB';
                $commonInfo[$tableTotal]['Data_free'] = $dataFree;
                return $commonInfo ? json_encode($commonInfo) : '';
            } else {
                return '';
            }
        }
        View::assign(['Total' => count($commonInfo)]);
        return $this->view();
    }

    public function optimize()
    {
        if (Request::isAjax()) {
            $Common = new model\Common();
            foreach ($Common->info() as $value) {
                $Common->optimizeTable($value['Name']);
            }
            return showTip('数据表优化成功！');
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function repairAutoIncrement()
    {
        if (Request::isAjax()) {
            $Common = new model\Common();
            foreach ($Common->info() as $value) {
                if ($value['Name'] != Config::get('database.connections.mysql.prefix') . 'order') {  //此判断勿删，否则可能将无法登录后台
                    $Common->repairAutoIncrement($value['Name']);
                }
            }
            return showTip('AutoIncrement修复成功！');
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function schema()
    {
        if (Request::isAjax()) {
            return databaseSchema() ? showTip('数据表缓存更新成功！') : showTip('数据表缓存更新失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['Name'] = keyword($item['Name']);
        $item['size_number'] = number_format(($item['Index_length'] + $item['Data_length']) / 1024, 1, '.', '');
        $item['Size'] = $item['size_number'] < 1024 ?
            $item['size_number'] . ' KB' :
            number_format($item['size_number'] / 1024, 1, '.', '') . ' MB';
        return $item;
    }
}
