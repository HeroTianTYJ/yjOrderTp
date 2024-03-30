<?php

namespace app\admin\model;

use app\admin\validate\Product as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class Product extends Model
{
    //按分类查询记录数
    public function totalCount($productSortId = 0)
    {
        return $this->where(['product_sort_id' => $productSortId])->count();
    }

    //按是否运作查询记录数
    public function totalCount2($isView = 0)
    {
        return $isView ? $this->where(['is_view' => 1])->count() : $this->count();
    }

    //查询所有
    public function all()
    {
        try {
            $map = [
                'where' => '`name` LIKE :name',
                'value' => [
                    'name' => '%' . Request::get('keyword') . '%'
                ]
            ];
            if (Request::get('product_sort_id')) {
                $map['where'] .= ' AND `product_sort_id`=:product_sort_id';
                $map['value']['product_sort_id'] = Request::get('product_sort_id');
            }
            return $this->field('id,name,product_sort_id,price,color,sort,is_view,is_default,date')
                ->where($map['where'], $map['value'])
                ->order(['sort' => 'ASC'])
                ->paginate(Config::get('app.page_size'));
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询分类下的商品
    public function all2($productSortId = 0)
    {
        try {
            return $this->field('id,name,price,color,is_default')
                ->where(['product_sort_id' => $productSortId])
                ->order(['sort' => 'ASC'])
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,name,product_sort_id,price,color,sort,is_view,is_default,date')
                ->where(['id' => $id ?: Request::post('id')])
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
            'name' => Request::post('name'),
            'product_sort_id' => Request::post('product_sort_id'),
            'price' => Request::post('price'),
            'color' => Request::post('color'),
            'sort' => $this->nextId(),
            'date' => time()
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if (!(new ProductSort())->one($data['product_sort_id'])) {
                return '您选择的商品分类不存在！';
            }
            if ($this->repeat()) {
                return '此商品已存在！';
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
            'product_sort_id' => Request::post('product_sort_id'),
            'price' => Request::post('price'),
            'color' => Request::post('color')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if (!(new ProductSort())->one($data['product_sort_id'])) {
                return '您选择的商品分类不存在！';
            }
            if ($this->repeat(true)) {
                return '此商品已存在！';
            }
            return $this->where(['id' => Request::post('id')])->update($data);
        } else {
            return $validate->getError();
        }
    }

    //设置默认
    public function isDefault()
    {
        $this->where(['is_default' => 1])->update(['is_default' => 0]);
        return $this->where(['id' => Request::post('id')])->update(['is_default' => 1]);
    }

    //确认和取消显示
    public function isView($isView)
    {
        return $this->where(['id' => Request::post('id')])->update(['is_view' => $isView]);
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

    //验证重复
    private function repeat($update = false)
    {
        try {
            $one = $this->field('id')->where([
                'name' => Request::post('name'),
                'product_sort_id' => Request::post('product_sort_id')
            ]);
            return $update ? $one->where('id', '<>', Request::post('id'))->find() : $one->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
