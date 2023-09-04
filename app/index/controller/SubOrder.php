<?php

namespace app\index\controller;

use app\index\model;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use think\facade\Config;
use think\facade\Request;
use yjrj\QQWry;

class SubOrder extends Base
{
    public function index()
    {
        if (Request::isPost()) {
            $orderAdd = (new model\Order())->add();
            if (is_array($orderAdd)) {
                $this->sendmail($orderAdd);
                if (Request::post('payment_id') == 2) {
                    return $this->succeed(Config::get('url.web1') . Config::get('system.index_php') .
                        'pay/alipay/order_id/' . $orderAdd['order_id'] . '.html');
                } elseif (Request::post('payment_id') == 3) {
                    return $this->succeed(Config::get('url.web1') . Config::get('system.index_php') .
                        'pay/wechatPay/order_id/' . $orderAdd['order_id'] . '.html');
                }
                return $this->succeed('', $orderAdd['success'], 0, 2);
            } else {
                return $this->failed($orderAdd);
            }
        }
        return '';
    }

    private function sendmail($orderAdd)
    {
        $Smtp = new model\Smtp();
        if ($Smtp->count() > 0) {
            $smtpOne = $Smtp->one();
            if ($smtpOne) {
                try {
                    include ROOT_DIR . '/extend/PHPMailer/SMTP.php';
                    include ROOT_DIR . '/extend/PHPMailer/PHPMailer.php';
                    $PHPMailer = new PHPMailer();
                    $PHPMailer->CharSet = 'UTF-8';
                    $PHPMailer->IsSMTP();
                    $PHPMailer->SMTPAuth = true;
                    $PHPMailer->Port = $smtpOne[0]['port'];
                    $PHPMailer->Host = $smtpOne[0]['smtp'];
                    $PHPMailer->Username = $smtpOne[0]['user'];
                    $PHPMailer->Password = $smtpOne[0]['pass'];
                    $PHPMailer->From = $smtpOne[0]['email'];
                    $PHPMailer->FromName = $smtpOne[0]['email'];
                    foreach (array_unique(array_filter(explode(',', Config::get('system.admin_mail')))) as $value) {
                        $PHPMailer->AddAddress($value, $PHPMailer->FromName);
                    }
                    $PHPMailer->IsHTML();
                    $PHPMailer->Subject = $this->mail(Config::get('system.mail_order_subject'), $orderAdd);
                    $PHPMailer->Body = $this->mail(Config::get('system.mail_order_content'), $orderAdd);
                    $PHPMailer->Send();
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
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
            isset($orderAdd['ip']) ? $orderAdd['ip'] . ' ' . QQWry::getAddress($orderAdd['ip']) : '',
            isset($orderAdd['referrer']) ? '<a href="' . $orderAdd['referrer'] . '" target="_blank">' .
                $orderAdd['referrer'] . '</a>' : '直接进入',
            Config::get('payment.' . $orderAdd['payment_id'])
        ], $content);
    }
}
