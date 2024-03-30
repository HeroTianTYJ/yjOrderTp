<?php

namespace app\admin\model;

use Exception;
use think\facade\Config;
use think\facade\Request;
use think\Model;

class Field extends Model
{
    //查询所有
    public function all()
    {
        try {
            return $this->field('id,name,is_default')
                ->where('name', 'LIKE', '%' . Request::get('keyword') . '%')
                ->order(['id' => 'ASC'])
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
            return $this->field('id,name,is_default')->order(['id' => 'ASC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（默认字段）
    public function all3()
    {
        try {
            return $this->field('id')->where(['is_default' => 1])->order(['id' => 'ASC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('is_default')->where(['id' => $id ?: Request::post('id')])->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //设置和取消默认
    public function isDefault($isDefault)
    {
        return $this->where(['id' => Request::post('id')])->update(['is_default' => $isDefault]);
    }
}
