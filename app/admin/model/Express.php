<?php

namespace app\admin\model;

use app\admin\validate\Express as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class Express extends Model
{
    //查询所有
    public function all()
    {
        try {
            return $this->field('id,name,code')
                ->where('name|code', 'LIKE', '%' . Request::get('keyword') . '%')
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
            return $this->field('id,name')->order(['id' => 'ASC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,name,code')->where(['id' => $id ?: Request::post('id')])->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //添加
    public function add()
    {
        $data = [
            'name' => Request::post('name'),
            'code' => Request::post('code')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if ($this->repeat()) {
                return '此快递公司已存在！';
            }
            return $this->insertGetId($data);
        } else {
            return $validate->getError();
        }
    }

    //修改
    public function modify()
    {
        $data = [
            'name' => Request::post('name'),
            'code' => Request::post('code')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if ($this->repeat(true)) {
                return '此快递公司已存在！';
            }
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

    //验证重复
    private function repeat($update = false)
    {
        try {
            $one = $this->field('id')->where(
                '`name`=:name OR `code`=:code',
                ['name' => Request::post('name'), 'code' => Request::post('code')]
            );
            return $update ? $one->where('id', '<>', Request::post('id'))->find() : $one->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
