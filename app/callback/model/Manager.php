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
            return $this->field('id,name,level,is_activation,permit_group_id,order_permit,distributor_code')
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
            return $this->field('id,name,level,is_activation,permit_group_id,order_permit,distributor_code')
                ->where(['wechat_open_id' => $wechatOpenId])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //QQ登录
    public function qqLogin($qqOpenId)
    {
        try {
            return $this->field('id,name,level,is_activation,permit_group_id,order_permit,distributor_code')
                ->where(['qq_open_id' => $qqOpenId])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //绑定和解绑微信
    public function wechatOpenId($wechatOpenId = '', $wechatUnionId = '', $id = 0)
    {
        return $this->where(['id' => $id])
            ->update(['wechat_open_id' => $wechatOpenId, 'wechat_union_id' => $wechatUnionId]);
    }

    //绑定和解绑QQ
    public function qqOpenId($qqOpenId = '', $id = 0)
    {
        return $this->where(['id' => $id])->update(['qq_open_id' => $qqOpenId]);
    }
}
