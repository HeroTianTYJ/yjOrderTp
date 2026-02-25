<?php

namespace app\admin\library;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\Route;
use think\facade\Session;
use think\facade\View;

class Html extends \app\common\library\Html
{
    public static function manager($id = 0)
    {
        self::selectDataset((new model\Manager())->all2(), 'Manager', $id);
    }

    public static function manager2($id = 0)
    {
        $html = '';
        $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
        if ($session['level_id'] == 1 || ($session['level_id'] == 2 && $session['order_permit_id'] != 1)) {
            $html .= '<option value="0" ' . ($id == 0 ? 'selected' : '') . '>终端客户</option>';
        }
        self::selectDataset((new model\Manager())->all2(), 'Manager', $id, $html);
    }

    public static function permitManage($ids = [])
    {
        $PermitManage = new model\PermitManage();
        $isDefault = arrayFieldToString($PermitManage->all5(), 'id');
        $ids = is_array($ids) ? $isDefault : $ids;
        $html = '';
        $permitManageAll = $PermitManage->all2();
        if (count($permitManageAll)) {
            $html .= '<table>';
            foreach ($permitManageAll as $value) {
                $html .= '<tr><td><div class="check-box"><label' . (in_array($value['id'], explode(',', $isDefault)) ?
                        ' class="red"' : '') . '><input type="checkbox" name="permit_manage_ids[]" ' .
                    (in_array($value['id'], explode(',', $ids)) ? 'checked' : '') . ' value="' . $value['id'] .
                    '">' . $value['name'] . '</label></div></td><td>';
                foreach ($PermitManage->all3($value['id']) as $v) {
                    $html .= '<div class="check-box"><label class="' . (in_array($v['id'], explode(',', $isDefault)) ?
                            ' red' : 'blue') . '"><input type="checkbox" name="permit_manage_ids[]" ' .
                        (in_array($v['id'], explode(',', $ids)) ? 'checked' : '') . ' value="' . $v['id'] . '">' .
                        $v['name'] . '</label></div>';
                }
                $html .= '</td></tr>';
            }
            $html .= '</table>';
        }
        View::assign(['PermitManage' => $html]);
    }

    public static function permitData($ids = [])
    {
        $PermitData = new model\PermitData();
        $isDefault = arrayFieldToString($PermitData->all5(), 'id');
        $ids = is_array($ids) ? $isDefault : $ids;
        $html = '';
        $permitDataAll = $PermitData->all2();
        if (count($permitDataAll)) {
            $html .= '<table>';
            foreach ($permitDataAll as $value) {
                $html .= '<tr><td><div class="check-box"><label' . (in_array($value['id'], explode(',', $isDefault)) ?
                        ' class="red"' : '') . '><input type="checkbox" name="permit_data_ids[]" ' .
                    (in_array($value['id'], explode(',', $ids)) ? 'checked' : '') . ' value="' . $value['id'] .
                    '">' . $value['name'] . '</label></div></td><td>';
                foreach ($PermitData->all3($value['id']) as $v) {
                    $html .= '<div class="check-box"><label class="' . (in_array($v['id'], explode(',', $isDefault)) ?
                            ' red' : 'blue') . '"><input type="checkbox" name="permit_data_ids[]" ' .
                        (in_array($v['id'], explode(',', $ids)) ? 'checked' : '') . ' value="' . $v['id'] . '">' .
                        $v['name'] . '</label></div>';
                }
                $html .= '</td></tr>';
            }
            $html .= '</table>';
        }
        View::assign(['PermitData' => $html]);
    }

    public static function permitGroup($id = 0, $isDefault = 0)
    {
        self::selectDataset((new model\PermitGroup())->all2(), 'PermitGroup', $id, '', $isDefault);
    }

    public static function managerLevelSelect($level = [], $id = 0)
    {
        self::selectArray($level, 'Level', $id);
    }

    public static function managerLevelRadio($level = [], $id = 0)
    {
        self::radioArray($level, 'level_id', 'Level', $id);
    }

    public static function managerIsActivationSelect($isActivation = [], $id = 0)
    {
        self::selectArray($isActivation, 'IsActivation', $id);
    }

    public static function managerIsActivationRadio($isActivation = [], $id = 0)
    {
        self::radioArray($isActivation, 'is_activation', 'IsActivation', $id);
    }

    public static function managerOrderPermitSelect($orderPermit = [], $id = 0)
    {
        self::selectArray($orderPermit, 'OrderPermit', $id);
    }

    public static function managerOrderPermitRadio($orderPermit = [], $id = 0)
    {
        self::radioArray($orderPermit, 'order_permit_id', 'OrderPermit', $id);
    }

    public static function qq($qq = [], $id = 0)
    {
        self::selectArray($qq, 'Qq', $id);
    }

    public static function wechat($wechat = [], $id = 0)
    {
        self::selectArray($wechat, 'Wechat', $id);
    }

    public static function validateFileExtension($extension = [], $id = 0)
    {
        self::radioArray($extension, 'extension', 'Extension', $id);
    }

    public static function alipayScene($id = 0)
    {
        self::selectArray(Config::get('pay_scene.alipay'), 'AlipayScene', $id);
    }

    public static function wechatPayScene($id = 0)
    {
        self::selectArray(Config::get('pay_scene.wechat_pay'), 'WxpayScene', $id);
    }

    public static function orderStateRadio($id = 0)
    {
        $html = '';
        foreach ((new model\OrderState())->all2() as $value) {
            if ($id == 0) {
                $html .= '<div class="radio-box"><label style="color:' . $value['color'] .
                    ';"><input type="radio" name="order_state_id" value="' . $value['id'] . '" ' .
                    ($value['is_default'] ? 'checked' : '') . '>' . $value['name'] . '</label></div>';
            } else {
                $html .= '<div class="radio-box"><label style="color:' . $value['color'] .
                    ';"><input type="radio" name="order_state_id" value="' . $value['id'] . '" ' .
                    ($value['id'] == $id ? 'checked' : '') . '>' . $value['name'] . '</label></div>';
            }
        }
        View::assign(['OrderState' => $html]);
    }

    public static function typeSelect($type = [], $id = 0)
    {
        self::selectArray($type, 'Type', $id);
    }

    public static function typeRadio($type = [], $id = 0)
    {
        self::radioArray($type, 'type_id', 'Type', $id);
    }

    public static function product($id = 0, $flag = 0, $price = false)
    {
        $html = '';
        $productSortAll = (new model\ProductSort())->all2();
        if (count($productSortAll)) {
            $Product = new model\Product();
            foreach ($productSortAll as $value) {
                $html .= '<optgroup label="' . $value['name'] . '">';
                foreach ($Product->all2($value['id']) as $v) {
                    if ($id == 0) {
                        $html .= '<option value="' . $v['id'] . '" ' . ($v['is_default'] && $flag == 1 ?
                                'selected' : '') . ($price ? ' price="' . $v['price'] . '"' : '') . '>' . $v['name'] .
                            ($price ? '（' . $v['price'] . '元' . '）' : '') . '</option>';
                    } else {
                        $html .= '<option value="' . $v['id'] . '" ' . ($v['id'] == $id ? 'selected' : '') .
                            ($price ? ' price="' . $v['price'] . '"' : '') . '>' . $v['name'] . ($price ? '（' .
                                $v['price'] . '元' . '）' : '') . '</option>';
                    }
                }
                $html .= '</optgroup>';
            }
        }
        View::assign(['Product' => $html]);
    }

    public static function productSort($id = 0)
    {
        self::selectDataset((new model\ProductSort())->all2(), 'ProductSort', $id);
    }

    public static function orderPaymentSelect($id = 0)
    {
        self::selectArray(Config::get('payment'), 'Payment', $id);
    }

    public static function orderPaymentRadio($id = 0)
    {
        self::radioArray(Config::get('payment'), 'payment_id', 'Payment', $id);
    }

    public static function express($id = 0)
    {
        self::selectDataset((new model\Express())->all2(), 'Express', $id);
    }

    public static function template($template = [], $id = 0)
    {
        $html = '';
        foreach ($template as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . ' view="' .
                Route::buildUrl('/' . parse_name(Request::controller()) . '/templateView', ['id' => $key]) . '">' .
                $value . '</option>';
        }
        View::assign(['Template' => $html]);
    }

    public static function template2($id = 0, $isDefault = 0)
    {
        self::selectDataset((new model\Template())->all2(), 'Template', $id, '', $isDefault);
    }

    public static function templateStyle($id = 0)
    {
        self::selectDataset((new model\TemplateStyle())->all2(), 'TemplateStyle', $id);
    }

    public static function field($ids = [])
    {
        $html = '';
        $Field = new model\Field();
        $isDefault = arrayFieldToString($Field->all3(), 'id');
        $ids = is_array($ids) ? $isDefault : $ids;
        foreach ($Field->all2() as $value) {
            $html .= '<div class="check-box"><label' . (in_array($value['id'], explode(',', $isDefault)) ?
                    ' class="red"' : '') . '><input type="checkbox" name="field_ids[]" ' .
                (in_array($value['id'], explode(',', $ids)) ? 'checked' : '') . ' value="' . $value['id'] . '">' .
                $value['name'] . '</label></div>';
        }
        View::assign(['Field' => $html]);
    }

    public static function payment($ids = '', $default = 0)
    {
        $html = $html2 = '';
        foreach (Config::get('payment') as $key => $value) {
            $html .= '<div class="check-box"><label><input type="checkbox" name="payment_ids[]" value="' . $key . '" ' .
                (in_array($key, explode(',', $ids)) ? 'checked' : '') . '>' . $value . '</label></div>';
            $html2 .= '<option value="' . $key . '" ' . ($default == $key ? 'selected' : '') . '>' . $value .
                '</option>';
        }
        View::assign(['Payment' => $html, 'PaymentDefault' => $html2]);
    }

    public static function orderState($id = 0)
    {
        self::selectDataset((new model\OrderState())->all2(), 'OrderState', $id);
    }

    public static function loginWay($id = 0)
    {
        self::selectArray(Config::get('login_way.name'), 'LoginWay', $id);
    }
}
