<?php

namespace app\index\controller;

use app\common\controller\Auth;
use app\index\model;
use Exception;
use PHPMailer\PHPMailer;
use think\facade\Config;

class SubOrder extends Base
{
    public function index()
    {
        return (new Auth())->subOrder();
    }

    public function sendmail($orderAdd)
    {
        try {
            $Smtp = new model\Smtp();
            if ($Smtp->count() > 0) {
                $smtpOne = $Smtp->one();
                if ($smtpOne) {
                    $PHPMailer = new PHPMailer();
                    $PHPMailer->Host = $smtpOne[0]['smtp'];
                    $PHPMailer->Port = $smtpOne[0]['port'];
                    $PHPMailer->Username = $PHPMailer->From = $smtpOne[0]['email'];
                    $PHPMailer->Password = $smtpOne[0]['pass'];
                    $PHPMailer->FromName = $smtpOne[0]['from_name'];
                    foreach (array_unique(array_filter(explode(',', Config::get('system.admin_mail')))) as $value) {
                        $PHPMailer->AddAddress($value, $PHPMailer->FromName);
                    }
                    $PHPMailer->Subject = $this->mail(Config::get('system.mail_order_subject'), $orderAdd);
                    $PHPMailer->Body = $this->mail(Config::get('system.mail_order_content'), $orderAdd);
                    $PHPMailer->Send();
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function mail($content, $orderAdd)
    {
        return str_replace([
            '{order_id}',
            '{product_name}',
            '{product_price}',
            '{product_count}',
            '{product_total}',
            '{name}',
            '{tel}',
            '{province}',
            '{city}',
            '{county}',
            '{town}',
            '{address}',
            '{note}',
            '{email}',
            '{ip}',
            '{referrer}',
            '{payment}'
        ], [
            $orderAdd['order_id'] ?? '',
            $orderAdd['product'] ?? '',
            $orderAdd['price'] ?? '',
            $orderAdd['count'] ?? '',
            isset($orderAdd['price']) ? number_format($orderAdd['price'] * $orderAdd['count'], 2, '.', '') : '0.00',
            $orderAdd['name'] ?? '',
            $orderAdd['tel'] ?? '',
            $orderAdd['province'] ?? '',
            $orderAdd['city'] ?? '',
            $orderAdd['county'] ?? '',
            $orderAdd['town'] ?? '',
            $orderAdd['address'] ?? '',
            $orderAdd['note'] ?? '',
            $orderAdd['email'] ?? '',
            isset($orderAdd['ip']) ? $orderAdd['ip'] . ' ' . ipGeolocation($orderAdd['ip']) : '',
            isset($orderAdd['referrer']) ? '<a href="' . $orderAdd['referrer'] . '" target="_blank">' .
                $orderAdd['referrer'] . '</a>' : '直接进入',
            Config::get('payment.' . $orderAdd['payment_id'])
        ], $content);
    }
}
