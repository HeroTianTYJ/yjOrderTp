<?php

namespace app\index\model;

use Exception;
use think\Model;

class TemplateStyle extends Model
{
    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('bg_color,border_color,button_color,select_current_bg_color')
                ->where(['id' => $id])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
