<?php

namespace app\index\model;

use Exception;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class Visit extends Model
{
    //查询所有（不分页）
    public function all()
    {
        try {
            return $this->field('ip,url,count,create_time,last_visit_time')->order(['last_visit_time' => 'DESC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询昨天是否有记录
    public function yesterday()
    {
        try {
            return $this->field('id')->where('create_time', '<', date('Y-m-d') . ' 00:00:00')->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one()
    {
        try {
            return $this->field('id')
                ->where(['ip' => getUserIp(), 'url' => strip_tags(Request::post('url', '', null))])
                ->where('create_time', '>=', date('Y-m-d') . ' 00:00:00')
                ->where('last_visit_time', '<=', date('Y-m-d') . ' 23:59:59')
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //添加
    public function add()
    {
        $data = [
            'ip' => getUserIp(),
            'url' => strip_tags(Request::post('url', '', null)),
            'count' => 1,
            'create_time' => now(),
            'last_visit_time' => now()
        ];
        return $this->insertGetId($data);
    }

    //修改
    public function modify($id)
    {
        $this->where(['id' => $id])->inc('count')->update();
        return $this->where(['id' => $id])->update(['last_visit_time' => now()]);
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
