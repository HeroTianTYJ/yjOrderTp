<?php

namespace app\index\model;

use Exception;
use think\Model;

class ProductSort extends Model
{
    //查询所有（不分页）
    public function all($ids = 0)
    {
        try {
            $all = $this->field('id,name,color')->order(['sort' => 'ASC']);
            if ($ids) {
                $all = $all->where('id', 'IN', $ids);
            }
            return $all->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
