<?php

namespace app\admin\validate;

use app\common\validate\Base;

class System extends Base
{
    protected $rule = [
        'web_name' => 'require',
        'admin_mail' => 'require',
        'www' => 'require|between:0,1',
        'https' => 'require|between:0,1',
        'manager_enter' => 'require',
        'index_php' => 'require|between:0,1',
        'order_time' => 'number',
        'order_search' => 'require|between:0,1',
        'order_search_step' => 'require|between:0,1',
        'mail_order_subject' => 'require',
        'mail_order_content' => 'require',
        'mail_pay_subject' => 'require',
        'mail_pay_content' => 'require',
        'mail_send_subject' => 'require',
        'mail_send_content' => 'require',
    ];
    protected $message = [
        'web_name' => '站点名称不得为空！',
        'admin_mail' => '管理员邮箱不得为空！',
        'www' => '强制www设置不合法！',
        'https' => '强制https设置不合法！',
        'manager_enter' => '后台入口不得为空！',
        'index_php' => '隐藏index.php设置不合法！',
        'order_time' => '防刷单间隔必须是数字！',
        'order_search' => '订单查询设置不合法！',
        'order_search_step' => '跨模板查询设置不合法！',
        'mail_order_subject' => '订单提醒邮件标题不得为空！',
        'mail_order_content' => '订单提醒邮件内容不得为空！',
        'mail_pay_subject' => '支付提醒邮件标题不得为空！',
        'mail_pay_content' => '支付提醒邮件内容不得为空！',
        'mail_send_subject' => '发货提醒邮件标题不得为空！',
        'mail_send_content' => '发货提醒邮件内容不得为空！',
    ];
}
