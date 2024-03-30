<?php

namespace app\index\model;

use Exception;
use think\Model;

class Smtp extends Model
{
    //查询运行中的服务器
    public function one()
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
}
