<?php

namespace app\admin\model;

use app\admin\validate\Manager as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\Model;

class Manager extends Model
{
    //查询总记录
    public function totalCount($type = 0)
    {
        $map = [];
        if (in_array($type, [1, 2])) {
            $map['level_id'] = $type;
        } elseif ($type == 3) {
            $map['is_activation'] = 0;
        }
        $total = $this->where($map);
        return $type == 1 ? $total->where('id', '<>', 1)->count() : $total->count();
    }

    //查询所有
    public function all()
    {
        try {
            $map['where'] = '`name` LIKE :name';
            $map['value']['name'] = '%' . Request::get('keyword') . '%';
            if (Request::get('level_id')) {
                $map['where'] .= ' AND `level_id`=:level_id';
                $map['value']['level_id'] = Request::get('level_id');
            }
            if (Request::get('is_activation', -1) != -1) {
                $map['where'] .= ' AND `is_activation`=:is_activation';
                $map['value']['is_activation'] = Request::get('is_activation');
            }
            if (Request::get('permit_group_id')) {
                $map['where'] .= ' AND `permit_group_id`=:permit_group_id';
                $map['value']['permit_group_id'] = Request::get('permit_group_id');
            }
            if (Request::get('order_permit_id')) {
                $map['where'] .= ' AND `order_permit_id`=:order_permit_id';
                $map['value']['order_permit_id'] = Request::get('order_permit_id');
            }
            if (Request::get('wechat', -1) == 0) {
                $map['where'] .= ' AND `wechat_open_id`=\'\' AND `wechat_union_id`=\'\'';
            } elseif (Request::get('wechat') == 1) {
                $map['where'] .= ' AND (`wechat_open_id`<>\'\' OR `wechat_union_id`<>\'\')';
            }
            if (Request::get('qq', -1) == 0) {
                $map['where'] .= ' AND `qq_open_id`=\'\' AND `qq_union_id`=\'\'';
            } elseif (Request::get('qq') == 1) {
                $map['where'] .= ' AND (`qq_open_id`<>\'\' OR `qq_union_id`<>\'\')';
            }
            if (Request::get('create_time1')) {
                $map['where'] .= ' AND `create_time`>=:create_time1';
                $map['value']['create_time1'] = Request::get('create_time1') . ' 00:00:00';
            }
            if (Request::get('create_time2')) {
                $map['where'] .= ' AND `create_time`<=:create_time2';
                $map['value']['create_time2'] = Request::get('create_time2') . ' 23:59:59';
            }
            return $this->field('id,name,level_id,is_activation,permit_group_id,order_permit_id,wechat_open_id,' .
                'wechat_union_id,qq_open_id,qq_union_id,create_time')
                ->where($map['where'], $map['value'])
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
            return $this->field('id,name')->order(['create_time' => 'DESC'])->select()->toArray();
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
            $validate = new validate();
            if ($validate->only(['name', 'pass'])->check($data)) {
                return $this->field('id,name,pass,level_id,is_activation,permit_group_id,order_permit_id,' .
                    'wechat_open_id,wechat_union_id,qq_open_id,qq_union_id')
                    ->where(['name' => Request::post('name')])
                    ->find();
            } else {
                return $validate->getError();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,name,pass,level_id,is_activation,permit_group_id,order_permit_id,wechat_open_id,' .
                'wechat_union_id,qq_open_id,qq_union_id,create_time')
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
            'pass' => Request::post('pass'),
            'repass' => Request::post('repass'),
            'level_id' => Request::post('level_id'),
            'is_activation' => Request::post('is_activation'),
            'create_time' => now()
        ];
        if (Request::post('level_id') == 1) {
            $data['permit_group_id'] = $data['order_permit_id'] = 0;
        } elseif (Request::post('level_id') == 2) {
            if (!Request::post('permit_group_id')) {
                return '请先在权限组模块中添加一个权限组！';
            }
            if (!(new PermitGroup())->one(Request::post('permit_group_id'))) {
                return '您选择的权限组不存在！';
            }
            $data['permit_group_id'] = Request::post('permit_group_id');
            $data['order_permit_id'] = Request::post('order_permit_id');
        }
        $validate = new validate();
        if ($validate->remove('admin_mail', true)->check($data)) {
            if ($this->repeat()) {
                return '此账号已存在！';
            }
            $data['pass'] = passEncode(Request::post('pass'));
            unset($data['repass']);
            return $this->insertGetId($data);
        } else {
            return $validate->getError();
        }
    }

    //添加创始人
    public function add2($passKey)
    {
        $data = [
            'name' => Request::post('admin_name'),
            'pass' => Request::post('admin_pass'),
            'repass' => Request::post('admin_repass'),
            'admin_mail' => Request::post('admin_mail'),
            'level_id' => 1,
            'is_activation' => 1,
            'create_time' => now()
        ];
        $validate = new validate();
        if ($validate->only(['name', 'pass', 'repass', 'admin_mail', 'level_id', 'is_activation'])->check($data)) {
            $data['pass'] = passEncode(Request::post('admin_pass'), $passKey);
            unset($data['repass'], $data['admin_mail']);
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
        $scene = ['name'];
        if (Request::post('id') != 1) {
            $data['level_id'] = Request::post('level_id');
            $data['is_activation'] = Request::post('is_activation');
            $scene[] = 'level_id';
            $scene[] = 'is_activation';
            if (Request::post('level_id') == 1) {
                $data['permit_group_id'] = $data['order_permit_id'] = 0;
            } elseif (Request::post('level_id') == 2) {
                if (!Request::post('permit_group_id')) {
                    return '请先在权限组模块中添加一个权限组！';
                }
                if (!(new PermitGroup())->one(Request::post('permit_group_id'))) {
                    return '您选择的权限组不存在！';
                }
                $data['permit_group_id'] = Request::post('permit_group_id');
                $data['order_permit_id'] = Request::post('order_permit_id');
                $scene[] = 'order_permit_id';
            }
        }
        if (Request::post('pass')) {
            $data['pass'] = Request::post('pass');
            $data['repass'] = Request::post('repass');
            $scene[] = 'pass';
            $scene[] = 'repass';
        }
        $validate = new validate();
        if ($validate->only($scene)->check($data)) {
            if ($this->repeat(true)) {
                return '此账号已存在！';
            }
            if (Request::post('pass')) {
                $data['pass'] = passEncode(Request::post('pass'));
                unset($data['repass']);
            }
            return $this->where(['id' => Request::post('id')])->update($data);
        } else {
            return $validate->getError();
        }
    }
    public function modify2()
    {
        $scene = [''];
        $data = [];
        $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
        $one = $this->one($session['id']);
        if (Request::post('pass')) {
            if (passEncode(Request::post('old_pass')) != $one['pass']) {
                return '请输入正确的旧密码！';
            }
            $data['pass'] = Request::post('pass');
            $data['repass'] = Request::post('repass');
            $scene[] = 'pass';
            $scene[] = 'repass';
        }
        if (Request::post('wechat_open_id')) {
            $data['wechat_open_id'] = $data['wechat_union_id'] = '';
        }
        if (Request::post('qq_open_id')) {
            $data['qq_open_id'] = $data['qq_union_id'] = '';
        }
        $validate = new validate();
        if ($validate->only($scene)->check($data)) {
            if (Request::post('pass')) {
                $data['pass'] = passEncode(Request::post('pass'));
                unset($data['repass']);
            }
            return $this->where(['id' => $session['id']])->update($data);
        } else {
            return $validate->getError();
        }
    }
    public function modify3()
    {
        if (!Request::post('reset_pass_key')) {
            return '重置密钥不得为空！';
        }
        if (Request::post('reset_pass_key') != Config::get('system.reset_pass_key')) {
            return '重置密钥不正确！';
        }
        $data = [
            'pass' => Request::post('pass'),
            'repass' => Request::post('repass')
        ];
        $validate = new validate();
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
        return $this->where(['id' => Request::post('id')])->update(['is_activation' => $isActivation]);
    }

    //绑定和解绑微信
    public function wechatOpenId($id = 0, $wechatOpenId = '', $wechatUnionId = '')
    {
        return $this->where(['id' => $id ?: Request::post('id')])
            ->update(['wechat_open_id' => $wechatOpenId, 'wechat_union_id' => $wechatUnionId]);
    }

    //绑定和解绑QQ
    public function qqOpenId($id = 0, $qqOpenId = '', $qqUnionId = '')
    {
        return $this->where(['id' => $id ?: Request::post('id')])
            ->update(['qq_open_id' => $qqOpenId, 'qq_union_id' => $qqUnionId]);
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
