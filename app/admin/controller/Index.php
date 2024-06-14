<?php

namespace app\admin\controller;

use app\admin\model;
use app\common\controller\Auth;
use think\facade\Config;
use think\facade\Session;
use think\facade\View;
use yjrj\QQWry;

class Index extends Base
{
    public function index()
    {
        $data = [];
        $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
        if (isDataPermission('system', 'version_code')) {
            $data['系统信息'][] = ['版本号', 'V' . explode('|', Config::get('app.version'))[0]];
        }
        if (isDataPermission('system', 'version_date')) {
            $data['系统信息'][] = ['更新时间', explode('|', Config::get('app.version'))[1]];
        }
        if (isDataPermission('profile', 'level')) {
            $level = '';
            if ($session['id'] == 1) {
                $level = '创始人';
            } elseif ($session['level'] == 1) {
                $level = '超级管理员';
            } elseif ($session['level'] == 2) {
                $level = '普通管理员';
            }
            $data['个人信息'][] = ['身份', $level];
        }
        if (isDataPermission('profile', 'permit_group')) {
            $data['个人信息'][] = ['权限组', $session['permit_group']];
        }
        if (permitDataIntersect([['profile', 'login_count'], ['profile', 'login_date'], ['profile', 'login_ip']])) {
            $LoginRecordManager = new model\LoginRecordManager();
            $loginRecordManagerTotalCount = $LoginRecordManager->totalCount();
            if (isDataPermission('profile', 'login_count')) {
                $data['个人信息'][] = ['登录次数', $loginRecordManagerTotalCount];
            }
            if (isDataPermission('profile', 'login_date')) {
                $data['个人信息'][] = [
                    '上次登录时间',
                    $loginRecordManagerTotalCount > 1 ? dateFormat($LoginRecordManager->one()['date']) : '首次登录'
                ];
            }
            if (isDataPermission('profile', 'login_ip')) {
                if ($loginRecordManagerTotalCount > 1) {
                    $loginRecordManagerOne = $LoginRecordManager->one();
                    $ip = $loginRecordManagerOne['ip'] . ' - ' .
                        QQWry::getAddress($loginRecordManagerOne['ip']);
                } else {
                    $ip = '首次登录';
                }
                $data['个人信息'][] = ['上次登录IP', $ip];
            }
        }
        if (
            permitDataIntersect([['order', 'total'], ['order', 'arrearage'], ['order', 'undelivered'],
                ['order', 'delivered'], ['order', 'received'], ['order', 'after_sale'], ['order', 'closed'],
                ['order', 'count']])
        ) {
            $Order = new model\Order();
            if (isDataPermission('order', 'total')) {
                $data['订单'][] = ['总数', $Order->totalCount()];
            }
            if (isDataPermission('order', 'arrearage')) {
                $data['订单'][] = ['待支付', $Order->totalCount(1)];
            }
            if (isDataPermission('order', 'undelivered')) {
                $data['订单'][] = ['待发货', $Order->totalCount(2)];
            }
            if (isDataPermission('order', 'delivered')) {
                $data['订单'][] = ['已发货', $Order->totalCount(3)];
            }
            if (isDataPermission('order', 'received')) {
                $data['订单'][] = ['已签收', $Order->totalCount(4)];
            }
            if (isDataPermission('order', 'after_sale')) {
                $data['订单'][] = ['售后中', $Order->totalCount(5)];
            }
            if (isDataPermission('order', 'closed')) {
                $data['订单'][] = ['交易关闭', $Order->totalCount(6)];
            }
            if (isDataPermission('order', 'count')) {
                $authValidate = (new Auth())->validate('orderCount');
                $url = 'https://www.yjrj.cn/mp/member.php?keyword=' . md5(passEncode($_SERVER['HTTP_HOST']));
                $data['订单'][] = ['剩余订单量', $authValidate['state'] == 1 ?
                    $authValidate['content'] . '（<a href="' . $url . '" target="_blank">充值</a>）' :
                    '<a href="' . $url . '" target="_blank">查询失败</a>（<span class="iconfont icon-question" ' .
                    'title="失败原因：' . $authValidate['content'] . '具体请点击链接并微信扫码登录后核实"></span>）'];
            }
        }
        if (permitDataIntersect([['product', 'total'], ['product', 'view_total']])) {
            $Product = new model\Product();
            if (isDataPermission('product', 'total')) {
                $data['商品'][] = ['总数', $Product->totalCount2()];
            }
            if (isDataPermission('product', 'view_total')) {
                $data['商品'][] = [
                    '运作商品',
                    $Product->totalCount2(1) . '（<span class="iconfont icon-question" title="此数据为已上架的商品数"></span>）'
                ];
            }
        }
        if (isDataPermission('data', 'web_pv')) {
            $data['数据'][] = ['今日网站PV', (new model\Visit())->totalCount()];
        }
        if (isDataPermission('data', 'file')) {
            $data['数据'][] = [
                '文件',
                $this->dataCache()['file_count'] .
                '（已缓存 <span class="iconfont icon-question" title="此数据为缓存数据，只在每天第一次登录后台时做更新"></span>）'
            ];
        }
        if (
            permitDataIntersect([['manager', 'total'], ['manager', 'founder'], ['manager', 'super'],
                ['manager', 'general'], ['manager', 'wait_activation']])
        ) {
            $Manager = new model\Manager();
            if (isDataPermission('manager', 'total')) {
                $data['管理员'][] = ['总数', $Manager->totalCount()];
            }
            if (isDataPermission('manager', 'founder')) {
                $data['管理员'][] = ['创始人', 1];
            }
            if (isDataPermission('manager', 'super')) {
                $data['管理员'][] = ['超级管理员', $Manager->totalCount(1)];
            }
            if (isDataPermission('manager', 'general')) {
                $data['管理员'][] = ['普通管理员', $Manager->totalCount(2)];
            }
            if (isDataPermission('manager', 'wait_activation')) {
                $data['管理员'][] = ['待激活', $Manager->totalCount(3)];
            }
        }
        foreach ($data as $key => $value) {
            $data[$key]['width'] = floor((100 / count($value) * 100)) / 100;
        }
        View::assign(['Data' => $data]);
        return $this->view();
    }

    private function dataCache()
    {
        if (Config::get('data.cache') > strtotime(date('Y-m-d'))) {
            return Config::get('data');
        } else {
            $dirOutput = ROOT_DIR . '/' . Config::get('dir.output');
            $fileCount = is_dir($dirOutput) ? count(scandir($dirOutput)) - 2 : 0;
            file_put_contents(
                ROOT_DIR . '/app/admin/config/data.php',
                "<?php

return ['cache' => " . time() . ", 'file_count' => " . $fileCount . '];
'
            );
            return [
                'file_count' => $fileCount
            ];
        }
    }
}
