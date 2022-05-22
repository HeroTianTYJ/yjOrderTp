<?php

namespace app\admin\model;

use Exception;
use think\facade\Config;
use think\facade\Request;
use think\Model;

class PermitManage extends Model
{
    //查询所有（主权限）
    public function all()
    {
        try {
            $all = $this->field('id,name,controller,action,is_default')->where(['parent_id' => 0]);
            if (Request::get('keyword')) {
                $all->where(function ($query) {
                    $query->where('name|controller|action', 'LIKE', '%' . Request::get('keyword') . '%');
                    $all = $this->field('parent_id')
                        ->where('name|controller|action', 'LIKE', '%' . Request::get('keyword') . '%')
                        ->select()
                        ->toArray();
                    if ($all) {
                        $query->whereOr('id', 'IN', arrToStr($all, 'parent_id'));
                    }
                });
            }
            return $all->order(['id' => 'ASC'])->paginate(Config::get('app.page_size'));
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（主权限，不分页）
    public function all2($ids = '')
    {
        try {
            $all = $this->field('id,name,controller,action')->order(['id' => 'ASC'])->where(['parent_id' => 0]);
            return $ids ? $all->where('id', 'IN', $ids)->select()->toArray() : $all->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（子权限）
    public function all3($parentId)
    {
        try {
            return $this->field('id,name,controller,action,is_default')
                ->where('name|controller|action', 'LIKE', '%' . Request::get('keyword') . '%')
                ->where(['parent_id' => $parentId])
                ->order(['id' => 'ASC'])
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
    public function all4($ids, $parentId)
    {
        try {
            return $this->field('name')
                ->where('id', 'IN', $ids)
                ->where(['parent_id' => $parentId])
                ->order(['id' => 'ASC'])
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（默认权限）
    public function all5()
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
            return $this->field('name,parent_id,is_default')->where(['id' => $id ?: Request::post('id')])->find();
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
