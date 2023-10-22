<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class TemplateStyle extends Base
{
    public function index()
    {
        $templateStyleAll = (new model\TemplateStyle())->all();
        if (Request::isAjax()) {
            foreach ($templateStyleAll as $key => $value) {
                $templateStyleAll[$key] = $this->listItem($value);
            }
            return $templateStyleAll->items() ? json_encode($templateStyleAll->items()) : '';
        }
        View::assign(['Total' => $templateStyleAll->total()]);
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $templateStyleAdd = (new model\TemplateStyle())->add();
                if (is_numeric($templateStyleAdd)) {
                    return $templateStyleAdd > 0 ? showTip('模板样式添加成功！') : showTip('模板样式添加失败！', 0);
                } else {
                    return showTip($templateStyleAdd, 0);
                }
            }
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $TemplateStyle = new model\TemplateStyle();
            $templateStyleOne = $TemplateStyle->one();
            if (!$templateStyleOne) {
                return showTip('不存在此模板样式！', 0);
            }
            if (Request::get('action') == 'do') {
                $templateStyleModify = $TemplateStyle->modify();
                return is_numeric($templateStyleModify) ?
                    showTip(['msg' => '模板样式修改成功！', 'data' => $this->listItem($TemplateStyle->one())]) :
                    showTip($templateStyleModify, 0);
            }
            View::assign(['One' => $templateStyleOne]);
            return $this->view();
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            $TemplateStyle = new model\TemplateStyle();
            if (Request::post('id')) {
                if (!$TemplateStyle->one()) {
                    return showTip('不存在此模板样式！', 0);
                }
                if (Config::get('app.demo') && Request::post('id') <= 12) {
                    return showTip('演示站，id<=12的模板样式无法删除！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$TemplateStyle->one($value)) {
                        return showTip('不存在您勾选的模板样式！', 0);
                    }
                    if (Config::get('app.demo') && $value <= 12) {
                        return showTip('演示站，id<=12的模板样式无法删除！', 0);
                    }
                }
            }
            return $TemplateStyle->remove() ? showTip('模板样式删除成功！') : showTip('模板样式删除失败！', 0);
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['keyword_bg_color'] = keyword($item['bg_color']);
        $item['keyword_border_color'] = keyword($item['border_color']);
        $item['keyword_button_color'] = keyword($item['button_color']);
        $item['keyword_select_current_bg_color'] = keyword($item['select_current_bg_color']);
        $item['date'] = dateFormat($item['date']);
        return $item;
    }
}
