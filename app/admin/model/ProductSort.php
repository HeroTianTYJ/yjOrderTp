<?php

namespace app\admin\model;

use app\admin\validate\ProductSort as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class ProductSort extends Model
{
    //查询所有
    public function all()
    {
        try {
            return $this->field('id,name,color,sort,date')
                ->where('name', 'LIKE', '%' . Request::get('keyword') . '%')
                ->order(['sort' => 'ASC'])
                ->paginate(Config::get('app.page_size'));
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（不分页）
    public function all2($ids = '')
    {
        try {
            $all = $this->field('id,name,color')->order(['sort' => 'ASC']);
            return $ids ? $all->where('id', 'IN', $ids)->select()->toArray() : $all->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,name,color,sort,date')->where(['id' => $id ?: Request::post('id')])->find();
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
            'color' => Request::post('color'),
            'sort' => $this->nextId(),
            'date' => time()
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if ($this->repeat()) {
                return '此商品分类已存在！';
            }
            return $this->insertGetId($data);
        } else {
            return implode($validate->getError());
        }
    }

    //修改
    public function modify()
    {
        $data = [
            'name' => Request::post('name'),
            'color' => Request::post('color')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if ($this->repeat(true)) {
                return '此商品分类已存在！';
            }
            return $this->where(['id' => Request::post('id')])->update($data);
        } else {
            return implode($validate->getError());
        }
    }

    //排序
    public function sort($id, $sort)
    {
        return $this->where(['id' => $id])->update(['sort' => $sort]);
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
            $one = $this->field('id')->where(['name' => Request::post('name')]);
            return $update ? $one->where('id', '<>', Request::post('id'))->find() : $one->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //自增ID
    private function nextId()
    {
        try {
            $query = Db::query('SHOW TABLE STATUS FROM `' . Config::get('database.connections.mysql.database') .
                '` LIKE \'' . $this->getTable() . '\'');
            return $query[0]['Auto_increment'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
