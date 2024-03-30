<?php

namespace app\admin\model;

use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\Model;

class LoginRecordManager extends Model
{
    //查询总记录
    public function totalCount()
    {
        return $this->where(['manager_id' => Session::get(Config::get('system.session_key_admin') . '.manage_info.id')])
            ->count();
    }

    //查询所有
    public function all($type = 0)
    {
        try {
            $map['where'] = '`ip` LIKE :ip';
            $map['value']['ip'] = '%' . Request::get('keyword') . '%';
            if ($type) {
                $map['where'] .= ' AND `manager_id`=:manager_id';
                $map['value']['manager_id'] = Session::get(Config::get('system.session_key_admin') . '.manage_info.id');
            } else {
                if (Request::get('manager_id')) {
                    $map['where'] .= ' AND `manager_id`=:manager_id';
                    $map['value']['manager_id'] = Request::get('manager_id');
                }
            }
            return $this->field('id,manager_id,ip,date')
                ->where($map['where'], $map['value'])
                ->order(['date' => 'DESC'])
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
            return $this->field('manager_id,ip,date')->order(['date' => 'DESC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one()
    {
        try {
            return $this->field('ip,date')
                   ->where(['manager_id' => Session::get(Config::get('system.session_key_admin') . '.manage_info.id')])
                   ->order(['date' => 'DESC'])
                   ->limit(1, 1)
                   ->select()[0];
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //添加
    public function add($managerId)
    {
        return $this->insertGetId(['manager_id' => $managerId, 'ip' => getUserIp(), 'date' => time()]);
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
