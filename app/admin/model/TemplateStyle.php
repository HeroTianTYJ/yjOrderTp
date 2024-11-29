<?php

namespace app\admin\model;

use app\admin\validate\TemplateStyle as validate;
use Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\Model;

class TemplateStyle extends Model
{
    //查询所有
    public function all()
    {
        try {
            return $this->field('id,bg_color,border_color,button_color,select_current_bg_color,date')
                ->where(
                    'bg_color|border_color|button_color|select_current_bg_color',
                    'LIKE',
                    '%' . Request::get('keyword') . '%'
                )
                ->order(['id' => 'DESC', 'date' => 'DESC'])
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
            return $this->field('id,CONCAT(`id`,\'号皮肤样式\') name')->order(['id' => 'ASC'])->select()->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('id,bg_color,border_color,button_color,select_current_bg_color,date')
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
            'bg_color' => Request::post('bg_color'),
            'border_color' => Request::post('border_color'),
            'button_color' => Request::post('button_color'),
            'select_current_bg_color' => Request::post('select_current_bg_color'),
            'date' => time()
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            return $this->insertGetId($data);
        } else {
            return implode($validate->getError());
        }
    }

    //修改
    public function modify()
    {
        $data = [
            'bg_color' => Request::post('bg_color'),
            'border_color' => Request::post('border_color'),
            'button_color' => Request::post('button_color'),
            'select_current_bg_color' => Request::post('select_current_bg_color')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            return $this->where(['id' => Request::post('id')])->update($data);
        } else {
            return implode($validate->getError());
        }
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
}
