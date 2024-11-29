<?php

namespace app\admin\model;

use app\admin\validate\District as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class District extends Model
{
    //查询所有
    public function all()
    {
        try {
            return $this->field('id,name')
                ->where(
                    '`name` LIKE :name AND `parent_id`=:parent_id',
                    ['name' => '%' . Request::get('keyword') . '%', 'parent_id' => Request::get('parent_id', 0)]
                )
                ->order(['id' => 'ASC'])
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
            return $this->field('id,name,parent_id')->where(['id' => $id ?: Request::post('id')])->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
    public function one2($parentId)
    {
        try {
            return $this->field('id')->where(['parent_id' => $parentId])->find();
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
            'parent_id' => Request::post('parent_id')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if ($this->repeat(Request::post('parent_id'))) {
                return '此行政区划已存在！';
            }
            return $this->insertGetId($data);
        } else {
            return implode($validate->getError());
        }
    }

    //批量添加
    public function multi()
    {
        $validate = new validate();
        foreach (explode("\r\n", Request::post('multi')) as $value) {
            $data = [
                'name' => $value,
                'parent_id' => Request::post('parent_id')
            ];
            if ($validate->check($data)) {
                if ($this->repeat(Request::post('parent_id'), $value)) {
                    return '“' . $value . '”行政区划已存在！';
                }
                $this->insertGetId($data);
            } else {
                return implode($validate->getError());
            }
        }
        return 1;
    }

    //修改
    public function modify($parentId = 0)
    {
        $data = [
            'name' => Request::post('name')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if ($this->repeat($parentId, '', true)) {
                return '此行政区划已存在！';
            }
            return $this->where(['id' => Request::post('id')])->update($data);
        } else {
            return implode($validate->getError());
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
    private function repeat($parentId = 0, $name = '', $update = false)
    {
        try {
            $one = $this->field('id')->where(['name' => $name ?: Request::post('name'), 'parent_id' => $parentId]);
            return $update ? $one->where('id', '<>', Request::post('id'))->find() : $one->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
