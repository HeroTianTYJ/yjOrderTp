<?php

namespace app\index\model;

use Exception;
use think\Model;

class OrderState extends Model
{
    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('name,color')->where(['id' => $id])->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
