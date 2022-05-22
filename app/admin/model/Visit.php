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
        return $this->where('date1', '>', strtotime(date('Y-m-d') . ' 00:00:00'))->count();
    }

    //查询所有
    public function all()
    {
        try {
            return $this->field('ip,url,count,date1,date2')
                ->where(
                    '`ip` LIKE :ip OR `url` LIKE :url',
                    ['ip' => '%' . Request::get('keyword') . '%', 'url' => '%' . Request::get('keyword') . '%']
                )
                ->order(['date2' => 'DESC'])
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
            return $this->field('ip,url,count,date1,date2')->order(['date2' => 'DESC'])->select()->toArray();
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
