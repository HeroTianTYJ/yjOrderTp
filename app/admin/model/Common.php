<?php

namespace app\admin\model;

use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class Common extends Model
{
    //数据表信息
    public function info()
    {
        try {
            $mysql = Config::get('database.connections.mysql');
            return Db::query('SHOW TABLE STATUS FROM `' . $mysql['database'] . '` LIKE \'' . $mysql['prefix'] . '%' .
                str_replace($mysql['prefix'], '', Request::get('keyword', '')) . '%\'');
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //优化数据表
    public function optimizeTable($table)
    {
        try {
            return Db::execute('OPTIMIZE TABLE `' . $table . '`');
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //修复AutoIncrement
    public function repairAutoIncrement($table)
    {
        try {
            return Db::execute('ALTER TABLE `' . $table . '` AUTO_INCREMENT=1');
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
