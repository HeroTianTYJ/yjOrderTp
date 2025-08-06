<?php

namespace app\admin\controller;

use app\admin\library\Html;
use app\admin\library\Json;
use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class Template extends Base
{
    private array $template = ['手机版1', '手机版2', '手机版3', '手机版4', '电脑版'];

    public function index()
    {
        $templateAll = (new model\Template())->all();
        if (Request::isAjax()) {
            foreach ($templateAll as $key => $value) {
                $templateAll[$key] = $this->listItem($value);
            }
            return $templateAll->items() ? apiResponse('', 1, $templateAll->items()) : '';
        }
        View::assign(['Total' => $templateAll->total()]);
        return $this->view();
    }

    public function add()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $templateAdd = (new model\Template())->add();
                if (is_numeric($templateAdd)) {
                    return $templateAdd > 0 ? apiResponse('模板添加成功！') : apiResponse('模板添加失败！', 0);
                } else {
                    return apiResponse($templateAdd, 0);
                }
            }
            Html::template($this->template);
            Html::manager();
            Html::templateStyle();
            Html::productSort();
            Html::field();
            Html::payment();
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Template = new model\Template();
            $templateOne = $Template->one();
            if (!$templateOne) {
                return apiResponse('不存在此模板！', 0);
            }
            if (Request::get('action') == 'do') {
                if (Config::get('app.demo') && Request::post('id') <= 5) {
                    return apiResponse('演示站，id<=5的模板无法修改！', 0);
                }
                $templateModify = $Template->modify();
                return is_numeric($templateModify) ?
                    apiResponse('模板修改成功！', 1, $this->listItem($Template->one())) :
                    apiResponse($templateModify, 0);
            }
            Html::template($this->template, $templateOne['template_id']);
            Html::manager($templateOne['manager_id']);
            Html::templateStyle($templateOne['template_style_id']);
            Html::productSort($templateOne['product_sort_ids']);
            Html::field($templateOne['field_ids']);
            Html::payment($templateOne['payment_ids'], $templateOne['payment_default_id']);
            View::assign(['One' => $templateOne]);
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function code()
    {
        if (Request::isAjax() && Request::post('id')) {
            $templateOne = (new model\Template())->one();
            if (!$templateOne) {
                return apiResponse('不存在此模板！', 0);
            }
            View::assign(['One' => $templateOne]);
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function templateView()
    {
        return (new \app\common\controller\Template())->html(Request::get('id'), 1);
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return apiResponse('演示站，模板无法删除！', 0);
            }
            $Template = new model\Template();
            if (Request::post('id')) {
                if (!$Template->one()) {
                    return apiResponse('不存在此模板！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Template->one($value)) {
                        return apiResponse('不存在您勾选的模板！', 0);
                    }
                }
            }
            return $Template->remove() ? apiResponse('模板删除成功！') : apiResponse('模板删除失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function ajaxProduct()
    {
        if (Request::isAjax()) {
            return Json::product(Request::post('product_ids1'), Request::post('product_sort_id'));
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function ajaxProduct2()
    {
        if (Request::isAjax()) {
            return Json::product2(Request::post('product_ids2'));
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function isDefault()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Template = new model\Template();
            if (!$Template->one()) {
                return apiResponse('不存在此模板！', 0);
            }
            return $Template->isDefault() ? apiResponse('设置默认模板成功！') : apiResponse('设置默认模板失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        if ($item['manager_id']) {
            $managerOne = (new model\Manager())->one($item['manager_id']);
            $item['manager'] = $managerOne ? $managerOne['name'] : '此管理员已被删除';
        } else {
            $item['manager'] = '-';
        }
        $item['template'] = $this->template[$item['template_id']];
        $item['is_show_search'] = $item['is_show_search'] ? '开启' : '关闭';
        $item['is_show_send'] = $item['is_show_send'] ? '开启' : '关闭';
        $item['is_captcha'] = $item['is_captcha'] ? '开启' : '关闭';
        $item['create_time'] = timeFormat($item['create_time']);
        $item['url'] = Config::get('url.web1') . Config::get('system.index_php') . 'id/' . $item['id'] . '.html';
        return $item;
    }
}
