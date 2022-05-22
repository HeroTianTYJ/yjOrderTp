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
            return $this->field('id,name,permit_manage_ids,permit_data_ids,is_default,date')
                ->where('name', 'LIKE', '%' . Request::get('keyword') . '%')
                ->order(['date' => 'DESC'])
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
            return $this->field('id,name,is_default')->order(['date' => 'DESC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,name,permit_manage_ids,permit_data_ids,is_default,date')
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
            'date' => time()
        ];
        $permitManageIds = Request::post('permit_manage_ids');
        if ($permitManageIds) {
            asort($permitManageIds);
            $data['permit_manage_ids'] = implode(',', $permitManageIds);
        } else {
            $data['permit_manage_ids'] = '';
        }
        $permitDataIds = Request::post('permit_data_ids');
        if ($permitDataIds) {
            asort($permitDataIds);
            $data['permit_data_ids'] = implode(',', $permitDataIds);
        } else {
            $data['permit_data_ids'] = '';
        }
        $validate = new validate();
        if ($validate->check($data)) {
            if ($this->repeat()) {
                return '此权限组已存在！';
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
            'name' => Request::post('name')
        ];
        $permitManageIds = Request::post('permit_manage_ids');
        if ($permitManageIds) {
            asort($permitManageIds);
            $data['permit_manage_ids'] = implode(',', $permitManageIds);
        } else {
            $data['permit_manage_ids'] = '';
        }
        $permitDataIds = Request::post('permit_data_ids');
        if ($permitDataIds) {
            asort($permitDataIds);
            $data['permit_data_ids'] = implode(',', $permitDataIds);
        } else {
            $data['permit_data_ids'] = '';
        }
        $validate = new validate();
        if ($validate->check($data)) {
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
