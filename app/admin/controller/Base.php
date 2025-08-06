<?php

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Request;
use think\facade\Route;
use think\facade\Session;
use think\facade\View;

class Base extends \app\common\controller\Base
{
    //初始化
    protected function initialize()
    {
        parent::initialize();
        $this->checkLogin();
        $this->checkPermit(Request::controller(), Request::action());
        $this->publicAssign();
    }

    //公共变量
    private function publicAssign()
    {
        $data = [];
        if (Session::has(Config::get('system.session_key_admin') . '.manage_info')) {
            $data['Navigator'] = $this->navigator();
        }
        View::assign($data);
    }

    //登录验证
    private function checkLogin()
    {
        $manageInfo = Config::get('system.session_key_admin') . '.manage_info';
        if (Session::has($manageInfo)) {
            $session = Session::get($manageInfo);
            if (
                isset($session['id'], $session['name'], $session['level_id'], $session['permit_group']) &&
                isset($session['permit_manage'], $session['permit_data'], $session['order_permit_id']) &&
                is_numeric($session['id']) && $session['name'] && is_numeric($session['level_id']) &&
                $session['permit_group'] && is_array($session['permit_manage']) && is_array($session['permit_data']) &&
                is_numeric($session['order_permit_id'])
            ) {
                if (Request::controller() == 'Login' && Request::action() != 'logout') {
                    $this->succeed(Route::buildUrl('/index/index'));
                }
            } elseif (Request::controller() != 'Login') {
                $this->error('登录信息不合法，请重新登录！', 5, Route::buildUrl('/login/index'));
            }
        } else {
            if (Request::controller() == 'Index') {
                $this->succeed(Route::buildUrl('/login/index'));
            } elseif (!in_array(Request::controller(), ['Login', 'Reset'])) {
                $this->error('非法登录！', 5, Route::buildUrl('/login/index'));
            }
        }
    }

    //权限验证
    private function checkPermit($controller, $action)
    {
        $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
        if ($session && $session['level_id'] != 1) {
            $currentPermitManageId = Config::get('permit_manage.' . $controller . '.' . strtolower($action), 0);
            if ($currentPermitManageId && !in_array($currentPermitManageId, $session['permit_manage'])) {
                if (Request::isAjax()) {
                    exit(apiResponse('权限不足！', 0));
                } else {
                    strtolower($action) == 'index' ? $this->error('权限不足！', 0) : $this->error('权限不足！');
                }
            }
        }
    }

    //模板引入方法重写
    protected function view($template = '', $code = 200)
    {
        $run = '';
        if (Config::get('app.demo')) {
            $run .= '<script type="text/javascript" src="static/index/js/visit.js?' . staticCache() . '"></script>
<script type="text/javascript" src="https://hm.baidu.com/hm.js?c6bb5df23fe4ee546e63006da2093d81" async></script>';
        }
        View::assign(['Run' => $run]);
        return parent::view($template, $code);
    }

    //后台导航
    private function navigator()
    {
        $navigator = [
            'index' => [
                'one' => '首页',
                'two' => []
            ]
        ];
        if (permitIntersect(['Order', 'OrderRecycle', 'OrderStatistic', 'OrderState', 'Express'])) {
            $navigator['order']['one'] = '订单';
            if (isPermission('index', 'Order')) {
                $navigator['order']['two']['Order/index'] = '订单管理';
            }
            if (isPermission('index', 'OrderRecycle')) {
                $navigator['order']['two']['OrderRecycle/index'] = '订单回收站';
            }
            if (isPermission('index', 'OrderStatistic')) {
                $navigator['order']['two']['OrderStatistic/index'] = '订单统计';
            }
            if (isPermission('index', 'OrderState')) {
                $navigator['order']['two']['OrderState/index'] = '订单状态';
            }
            if (isPermission('index', 'Express')) {
                $navigator['order']['two']['Express/index'] = '快递公司';
            }
        }
        if (permitIntersect(['Product', 'ProductSort'])) {
            $navigator['product']['one'] = '商品';
            if (isPermission('index', 'Product')) {
                $navigator['product']['two']['Product/index'] = '商品管理';
            }
            if (isPermission('index', 'ProductSort')) {
                $navigator['product']['two']['ProductSort/index'] = '商品分类';
            }
        }
        if (permitIntersect(['Template', 'TemplateStyle', 'Field'])) {
            $navigator['template']['one'] = '模板';
            if (isPermission('index', 'Template')) {
                $navigator['template']['two']['Template/index'] = '模板管理';
            }
            if (isPermission('index', 'TemplateStyle')) {
                $navigator['template']['two']['TemplateStyle/index'] = '模板样式';
            }
            if (isPermission('index', 'Field')) {
                $navigator['template']['two']['Field/index'] = '下单字段';
            }
        }
        $navigator['web'] = [
            'one' => '单页',
            'two' => [],
            'url' => 'https://www.yjrj.cn/web.php'
        ];
        if (permitIntersect(['Visit', 'File', 'District'])) {
            $navigator['data']['one'] = '数据';
            if (isPermission('index', 'Visit')) {
                $navigator['data']['two']['Visit/index'] = '访问统计';
            }
            if (isPermission('index', 'File')) {
                $navigator['data']['two']['File/index'] = '文件管理';
            }
            if (isPermission('index', 'District')) {
                $navigator['data']['two']['District/index'] = '行政区划';
            }
        }
        if (permitIntersect(['Manager', 'LoginRecordManager', 'PermitGroup', 'PermitManage', 'PermitData'])) {
            $navigator['manage']['one'] = '管理';
            if (isPermission('index', 'Manager')) {
                $navigator['manage']['two']['Manager/index'] = '管理员';
            }
            if (isPermission('index', 'LoginRecordManager')) {
                $navigator['manage']['two']['LoginRecordManager/index'] = '登录记录';
            }
            if (isPermission('index', 'PermitGroup')) {
                $navigator['manage']['two']['PermitGroup/index'] = '权限组';
            }
            if (isPermission('index', 'PermitManage')) {
                $navigator['manage']['two']['PermitManage/index'] = '管理权限';
            }
            if (isPermission('index', 'PermitData')) {
                $navigator['manage']['two']['PermitData/index'] = '数据权限';
            }
        }
        if (permitIntersect(['System', 'ValidateFile', 'Smtp'])) {
            $navigator['setting']['one'] = '系统';
            if (isPermission('index', 'System')) {
                $navigator['setting']['two']['System/index'] = '系统设置';
            }
            if (isPermission('index', 'ValidateFile')) {
                $navigator['setting']['two']['ValidateFile/index'] = '生成验证文件';
            }
            if (isPermission('index', 'Smtp')) {
                $navigator['setting']['two']['Smtp/index'] = 'SMTP服务器';
            }
        }
        if (permitIntersect(['Database', 'DatabaseBackup'])) {
            $navigator['database']['one'] = '数据库';
            if (isPermission('index', 'Database')) {
                $navigator['database']['two']['Database/index'] = '数据表状态';
            }
            if (isPermission('index', 'DatabaseBackup')) {
                $navigator['database']['two']['DatabaseBackup/index'] = '数据库备份';
            }
        }
        $navigator['profile'] = [
            'one' => '',
            'two' => [
                'Profile/index' => ['个人资料'],
                'Profile/loginRecord' => ['登录记录']
            ]
        ];
        foreach ($navigator as $key => $value) {
            $navigator[$key]['active'] = $value['two'] ?
                (in_array(Request::controller() . '/' . Request::action(), keyToArray($value['two'])) ||
                    in_array(Request::controller() . '/index', keyToArray($value['two']))) :
                parse_name(Request::controller()) == $key;
        }
        return $navigator;
    }
}
