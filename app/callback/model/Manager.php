<?php

namespace app\callback\model;

use Exception;
use think\Model;

class Manager extends Model
{
    //微信登录
    public function wechatLogin($wechatUnionId = '')
    {
        try {
            return $this->field('id,name,level_id,is_activation,permit_group_id,order_permit_id')
                ->where(['wechat_union_id' => $wechatUnionId])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
    public function wechatLogin2($wechatOpenId = '')
    {
        try {
            return $this->field('id,name,level_id,is_activation,permit_group_id,order_permit_id')
                ->where(['wechat_open_id' => $wechatOpenId])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }


    //QQ登录
    public function qqLogin($qqUnionId = '')
    {
        try {
            return $this->field('id,name,level_id,is_activation,permit_group_id,order_permit_id')
                ->where(['qq_union_id' => $qqUnionId])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
    public function qqLogin2($qqOpenId = '')
    {
        try {
            return $this->field('id,name,level_id,is_activation,permit_group_id,order_permit_id')
                ->where(['qq_open_id' => $qqOpenId])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //绑定微信
    public function wechatOpenId($id = 0, $wechatOpenId = '', $wechatUnionId = '')
    {
        return $this->where(['id' => $id])
            ->update(['wechat_open_id' => $wechatOpenId, 'wechat_union_id' => $wechatUnionId]);
    }

    //绑定QQ
    public function qqOpenId($id = 0, $qqOpenId = '', $qqUnionId = '')
    {
        return $this->where(['id' => $id])->update(['qq_open_id' => $qqOpenId, 'qq_union_id' => $qqUnionId]);
    }
}
