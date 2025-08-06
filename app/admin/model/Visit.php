<?php

namespace app\admin\model;

use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class Visit extends Model
{
    //查询总记录
    public function totalCount()
    {
        return $this->where('create_time', '>', date('Y-m-d') . ' 00:00:00')->count();
    }

    //查询所有
    public function all()
    {
        try {
            return $this->field('ip,url,count,create_time,last_visit_time')
                ->where(
                    '`ip` LIKE :ip OR `url` LIKE :url',
                    ['ip' => '%' . Request::get('keyword') . '%', 'url' => '%' . Request::get('keyword') . '%']
                )
                ->order(['last_visit_time' => 'DESC'])
                ->paginate(Config::get('app.page_size'));
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（不分页）
    public function all2()
    {
        try {
            return $this->field('ip,url,count,create_time,last_visit_time')->order(['last_visit_time' => 'DESC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //清空表
    public function truncate()
    {
        try {
            return Db::execute('TRUNCATE `' . $this->getTable() . '`');
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
