<?php

namespace app\admin\model;

use app\admin\validate\Manager as valid;
use Exception;
use think\facade\Db;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use think\Model;

class Manager extends Model
{
    //查询总记录
    public function total()
    {
        return $this->where($this->map()['where'], $this->map()['value'])->count();
    }

    //查询所有
    public function all($firstRow)
    {
        try {
            return $this->field('id,name,level,is_activation,permit_group_id,order_permit,qqau,date')
                ->where($this->map()['where'], $this->map()['value'])
                ->order(['date' => 'DESC'])
                ->limit($firstRow, Config::get('app.page_size'))
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询所有（不分页）
    public function all2()
    {
        try {
            $map = [];
            $session = Session::get(Config::get('system.session_key'));
            if ($session['level'] == 2 && $session['order_permit'] != 3) {
                $map['id'] = $session['id'];
            }
            return $this->field('id,name')->where($map)->order(['date' => 'DESC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //登录
    public function login()
    {
        try {
            $data = [
                'name' => Request::post('name'),
                'pass' => Request::post('pass')
            ];
            $validate = new valid();
            if ($validate->scene('one')->check($data)) {
                $map['name'] = Request::post('name');
                return $this->field('id,name,pass,level,is_activation,permit_group_id,order_permit,qqau')
                    ->where($map)
                    ->find();
            } else {
                return $validate->getError();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //QQ登录
    public function qqLogin($qqau)
    {
        try {
            $map['qqau'] = $qqau;
            return $this->field('id,name,level,is_activation,permit_group_id,order_permit')->where($map)->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            $map['id'] = $id ? $id : Request::get('id');
            return $this->field('id,name,pass,level,is_activation,permit_group_id,order_permit,qqau,date')
                ->where($map)
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
            'pass' => Request::post('pass'),
            'repass' => Request::post('repass'),
            'level' => Request::post('level'),
            'is_activation' => Request::post('is_activation'),
            'date' => time()
        ];
        if (Request::post('level') == 1) {
            $data['permit_group_id'] = 0;
            $data['order_permit'] = 0;
        } else {
            if (!Request::post('permit_group_id')) {
                return '请先在权限组模块中添加一个权限组！';
            }
            $data['permit_group_id'] = Request::post('permit_group_id');
            $data['order_permit'] = Request::post('order_permit');
        }
        $validate = new valid();
        if ($validate->remove('admin_mail', true)->check($data)) {
            if ($this->repeat()) {
                return '此帐号已存在！';
            }
            $data['pass'] = passEncode(Request::post('pass'));
            unset($data['repass']);
            return $this->insertGetId($data);
        } else {
            return $validate->getError();
        }
    }

    //添加超级管理员
    public function add2($passKey)
    {
        $data = [
            'name' => Request::post('admin_name'),
            'pass' => Request::post('admin_pass'),
            'repass' => Request::post('admin_repass'),
            'admin_mail' => Request::post('admin_mail'),
            'level' => 1,
            'is_activation' => 1,
            'date' => time()
        ];
        $validate = new valid();
        if ($validate->only(['name', 'pass', 'repass', 'admin_mail'])->check($data)) {
            $data['pass'] = passEncode(Request::post('admin_pass'), $passKey);
            unset($data['repass']);
            unset($data['admin_mail']);
            return $this->insertGetId($data);
        } else {
            return $validate->getError();
        }
    }

    //修改
    public function modify()
    {
        $scene = ['name'];
        $data = [
            'name' => Request::post('name'),
            'level' => Request::post('level'),
            'is_activation' => Request::post('is_activation')
        ];
        if (Request::post('pass')) {
            $data['pass'] = Request::post('pass');
            $data['repass'] = Request::post('repass');
            $scene[] = 'pass';
            $scene[] = 'repass';
        }
        if (Request::get('id') != 1) {
            if (Request::post('level') == 1) {
                $data['permit_group_id'] = 0;
                $data['order_permit'] = 0;
            } else {
                if (!Request::post('permit_group_id')) {
                    return '请先在权限组模块中添加一个权限组！';
                }
                $data['permit_group_id'] = Request::post('permit_group_id');
                $data['order_permit'] = Request::post('order_permit');
            }
        }
        $validate = new valid();
        if ($validate->only($scene)->check($data)) {
            if (Request::post('pass')) {
                $data['pass'] = passEncode(Request::post('pass'));
            }
            if ($this->repeat(true)) {
                return '此帐号已存在！';
            }
            unset($data['repass']);
            return $this->where(['id' => Request::get('id')])->update($data);
        } else {
            return $validate->getError();
        }
    }
    public function modify2()
    {
        $session = Session::get(Config::get('system.session_key'));
        $object = $this->one($session['id']);
        $scene = [];
        if (Request::post('pass')) {
            if (passEncode(Request::post('old_pass')) != $object['pass']) {
                return '请输入正确的旧密码！';
            }
            $data['pass'] = Request::post('pass');
            $data['repass'] = Request::post('repass');
            $scene[] = 'pass';
            $scene[] = 'repass';
        }
        if (Request::post('qqau')) {
            $data['qqau'] = '';
        }
        if (empty($data)) {
            return '您没有做任何修改！';
        }
        $validate = new valid();
        if ($validate->only($scene)->check($data)) {
            $data['pass'] = passEncode(Request::post('pass'));
            unset($data['repass']);
            return $this->where(['id' => $session['id']])->update($data);
        } else {
            return $validate->getError();
        }
    }
    public function modify3()
    {
        if (!Request::post('reset_pass_key')) {
            return '密码重置密钥不得为空！';
        }
        if (Request::post('reset_pass_key') != Config::get('system.reset_pass_key')) {
            return '密码重置密钥不正确！';
        }
        $data = [
            'pass' => Request::post('pass'),
            'repass' => Request::post('repass')
        ];
        $validate = new valid();
        if ($validate->only(['pass', 'repass'])->check($data)) {
            $data['pass'] = passEncode(Request::post('pass'));
            unset($data['repass']);
            return $this->where(['id' => 1])->update($data);
        } else {
            return $validate->getError();
        }
    }

    //激活和取消激活
    public function isActivation($isActivation)
    {
        return $this->where(['id' => Request::get('id')])->update(['is_activation' => $isActivation]);
    }

    //绑定和解绑QQ
    public function qq($qqau = '', $id = 0)
    {
        return $this->where(['id' => $id ? $id : Request::get('id')])->update(['qqau' => $qqau]);
    }

    //删除
    public function remove()
    {
        try {
            $affectedRows = $this->where(['id' => Request::get('id')])->delete();
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
            $object = $this->field('id')->where(['name' => Request::post('name')]);
            return $update ? $object->where('id', '<>', Request::get('id'))->find() : $object->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //搜索
    private function map()
    {
        $map['where'] = '`name` LIKE :keyword';
        $map['value']['keyword'] = '%' . Request::get('keyword') . '%';
        if (Request::get('level')) {
            $map['where'] .= ' AND `level`=:level';
            $map['value']['level'] = Request::get('level');
        }
        if (Request::get('is_activation', -1) != -1) {
            $map['where'] .= ' AND `is_activation`=:is_activation';
            $map['value']['is_activation'] = Request::get('is_activation');
        }
        if (Request::get('order_permit')) {
            $map['where'] .= ' AND `order_permit`=:order_permit';
            $map['value']['order_permit'] = Request::get('order_permit');
        }
        if (Request::get('qq', -1) == 0) {
            $map['where'] .= ' AND `qqau`=\'\'';
        } elseif (Request::get('qq') == 1) {
            $map['where'] .= ' AND `qqau`<>\'\'';
        }
        if (Request::get('date1')) {
            $map['where'] .= ' AND `date`>=:date1';
            $map['value']['date1'] = strtotime(Request::get('date1') . ' 00:00:00');
        }
        if (Request::get('date2')) {
            $map['where'] .= ' AND `date`<=:date2';
            $map['value']['date2'] = strtotime(Request::get('date2') . ' 23:59:59');
        }
        return $map;
    }
}
