<?php

namespace app\admin\model;

use Exception;
use think\facade\Db;
use think\Model;

class Text extends Model
{
    //查询一条
    public function one($id = 0)
    {
        try {
            return $this->field('content')->where(['id' => $id])->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询内容
    public function content($id = 0)
    {
        if ($id) {
            $one = $this->one($id);
            return $one ? $one['content'] : '';
        }
        return '';
    }

    //添加
    public function add($content = '')
    {
        return $this->insertGetId(['content' => $content]);
    }

    //修改
    public function modify($id = 0, $content = '')
    {
        return $this->where(['id' => $id])->update(['content' => $content]);
    }

    //删除
    public function remove($ids)
    {
        try {
            $affectedRows = $this->where('id', 'IN', $ids)->delete();
            if ($affectedRows) {
                Db::execute('OPTIMIZE TABLE `' . $this->getTable() . '`');
            }
            return $affectedRows;
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //增删改
    public function amr($content = '', $id = 0)
    {
        if ($content) {
            if ($id && $this->one($id)) {
                $this->modify($id, $content);
                return $id;
            } else {
                return $this->add($content);
            }
        } elseif ($id) {
            $this->remove($id);
        }
        return 0;
    }
}
