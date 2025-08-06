<?php

namespace app\admin\model;

use app\admin\validate\PermitGroup as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class PermitGroup extends Model
{
    //查询所有
    public function all()
    {
        try {
            return $this->field('id,name,text_id_permit_manage_ids,permit_data_ids,is_default,create_time')
                ->where('name', 'LIKE', '%' . Request::get('keyword') . '%')
                ->order(['create_time' => 'DESC'])
                ->paginate(Config::get('app.page_size'));
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（不分页）
    public function all2()
    {
        try {
            return $this->field('id,name,is_default')->order(['create_time' => 'DESC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,name,text_id_permit_manage_ids,permit_data_ids,is_default,create_time')
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
            'create_time' => now()
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            $permitManageIds = Request::post('permit_manage_ids', []);
            asort($permitManageIds);
            $PermitManage = new PermitManage();
            foreach ($permitManageIds as $value) {
                if (!$PermitManage->one($value)) {
                    return '您勾选的管理权限不存在！';
                }
            }
            $data['text_id_permit_manage_ids'] = (new Text())->amr(implode(',', $permitManageIds));

            $permitDataIds = Request::post('permit_data_ids', []);
            asort($permitDataIds);
            $PermitData = new PermitData();
            foreach ($permitDataIds as $value) {
                if (!$PermitData->one($value)) {
                    return '您勾选的数据权限不存在！';
                }
            }
            $data['permit_data_ids'] = implode(',', $permitDataIds);

            if ($this->repeat()) {
                return '此权限组已存在！';
            }
            return $this->insertGetId($data);
        } else {
            return $validate->getError();
        }
    }

    //修改
    public function modify($textIdPermitManageIds = 0)
    {
        $data = [
            'name' => Request::post('name')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            $permitManageIds = Request::post('permit_manage_ids', []);
            asort($permitManageIds);
            $PermitManage = new PermitManage();
            foreach ($permitManageIds as $value) {
                if (!$PermitManage->one($value)) {
                    return '您勾选的管理权限不存在！';
                }
            }
            $data['text_id_permit_manage_ids'] =
                (new Text())->amr(implode(',', $permitManageIds), $textIdPermitManageIds);

            $permitDataIds = Request::post('permit_data_ids', []);
            asort($permitDataIds);
            $PermitData = new PermitData();
            foreach ($permitDataIds as $value) {
                if (!$PermitData->one($value)) {
                    return '您勾选的数据权限不存在！';
                }
            }
            $data['permit_data_ids'] = implode(',', $permitDataIds);

            if ($this->repeat(true)) {
                return '此权限组已存在！';
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
    private function repeat($update = false)
    {
        try {
            $one = $this->field('id')->where(['name' => Request::post('name')]);
            return $update ? $one->where('id', '<>', Request::post('id'))->find() : $one->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
