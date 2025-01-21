<?php

namespace app\admin\model;

use app\admin\validate\Smtp as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class Smtp extends Model
{
    //查询所有
    public function all()
    {
        try {
            return $this->field('id,smtp,port,email,from_name')
                ->where('smtp|port|email|from_name', 'LIKE', '%' . Request::get('keyword') . '%')
                ->order(['id' => 'DESC'])
                ->paginate(Config::get('app.page_size'));
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,smtp,port,email,from_name')->where(['id' => $id ?: Request::post('id')])->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询运行中的服务器
    public function one2()
    {
        try {
            $firstRow = date('H') % $this->count();
            return $this->field('smtp,port,email,pass,from_name')
                ->order(['id' => 'DESC'])
                ->limit($firstRow, 1)
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
    public function one3($hour)
    {
        try {
            $firstRow = $hour % $this->count();
            return $this->field('id,smtp,port,email,from_name')
                ->order(['id' => 'DESC'])
                ->limit($firstRow, 1)
                ->select()[0];
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //添加
    public function add()
    {
        $data = [
            'smtp' => Request::post('smtp'),
            'port' => Request::post('port'),
            'email' => Request::post('email'),
            'pass' => Request::post('pass'),
            'from_name' => Request::post('from_name')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            return $this->insertGetId($data);
        } else {
            return $validate->getError();
        }
    }

    //修改
    public function modify()
    {
        $data = [
            'smtp' => Request::post('smtp'),
            'port' => Request::post('port'),
            'email' => Request::post('email'),
            'from_name' => Request::post('from_name')
        ];
        if (Request::post('pass')) {
            $data['pass'] = Request::post('pass');
        }
        $validate = new validate();
        if ($validate->remove('pass', ['require'])->check($data)) {
            return $this->where(['id' => Request::post('id')])->update($data);
        } else {
            return $validate->getError();
        }
    }

    //删除
    public function remove()
    {
        try {
            $affectedRows = $this->where('id', 'IN', Request::post('id') ?: Request::post('ids'))->delete();
            if ($affectedRows) {
                Db::execute('OPTIMIZE TABLE `' . $this->getTable() . '`');
            }
            return $affectedRows;
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
