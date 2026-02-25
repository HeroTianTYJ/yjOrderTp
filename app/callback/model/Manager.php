<?php

namespace app\callback\model;

use Exception;
use think\Model;

class Manager extends Model
{
    //第三方账号登录
    public function oauthLogin($type = '', $openId = '', $unionId = '')
    {
        try {
            $one = $this->field('id,name,level_id,is_activation,permit_group_id,order_permit_id')
                ->where([$type . '_open_id' => $openId]);
            return $unionId ? $one->whereOr([$type . '_union_id' => $unionId])->find() : $one->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //第三方账号绑定
    public function oauthBind($type = '', $id = 0, $openId = '', $unionId = '')
    {
        return $this->where(['id' => $id])->update([$type . '_open_id' => $openId, $type . '_union_id' => $unionId]);
    }
}
