<?php

namespace app\admin\validate;

use think\Validate;

class System extends Validate
{
    protected $rule = [
        'web_name' => 'require',
        'admin_mail' => 'require',
        'manager_enter' => 'require',
        'order_time' => 'number',
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
        'manager_enter' => '后台入口不得为空！',
        'order_time' => '防刷单间隔必须是数字！',
        'mail_order_subject' => '订单提醒邮件标题不得为空！',
        'mail_order_content' => '订单提醒邮件内容不得为空！',
        'mail_pay_subject' => '支付提醒邮件标题不得为空！',
        'mail_pay_content' => '支付提醒邮件内容不得为空！',
        'mail_send_subject' => '发货提醒邮件标题不得为空！',
        'mail_send_content' => '发货提醒邮件内容不得为空！',
    ];
}
