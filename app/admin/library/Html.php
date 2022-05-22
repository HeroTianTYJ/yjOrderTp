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
        $html = '';
        foreach ((new model\Manager())->all2() as $value) {
            $html .= '<option value="' . $value['id'] . '" ' . ($value['id'] == $id ? 'selected' : '') . '>' .
                $value['name'] . '</option>';
        }
        View::assign(['Manager' => $html]);
    }

    public static function manager2($id = 0)
    {
        $html = '';
        $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
        if ($session['level'] == 1 || ($session['level'] == 2 && $session['order_permit'] != 1)) {
            $html .= '<option value="0" ' . ($id == 0 ? 'selected' : '') . '>终端客户</option>';
        }
        foreach ((new model\Manager())->all2() as $value) {
            $html .= '<option value="' . $value['id'] . '" ' . ($value['id'] == $id ? 'selected' : '') . '>' .
                $value['name'] . '</option>';
        }
        return $html;
    }

    public static function permitManage($ids = [])
    {
        $PermitManage = new model\PermitManage();
        $isDefault = arrToStr($PermitManage->all5(), 'id');
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
        $isDefault = arrToStr($PermitData->all5(), 'id');
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

    public static function permitGroup($id = 0, $flag = 0)
    {
        $html = '';
        foreach ((new model\PermitGroup())->all2() as $value) {
            if ($id == 0) {
                $html .= '<option value="' . $value['id'] . '" ' . ($value['is_default'] && $flag ? 'selected' : '') .
                    '>' . $value['name'] . '</option>';
            } else {
                $html .= '<option value="' . $value['id'] . '" ' . ($value['id'] == $id ? 'selected' : '') . '>' .
                    $value['name'] . '</option>';
            }
        }
        View::assign(['PermitGroup' => $html]);
    }

    public static function managerLevelSelect($level = [], $id = 0)
    {
        $html = '';
        foreach ($level as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . ' style="color:' . $value[0] .
                ';">' . $value[1] . '</option>';
        }
        View::assign(['Level' => $html]);
    }

    public static function managerLevelRadio($level = [], $id = 0)
    {
        $html = '';
        foreach ($level as $key => $value) {
            $html .= '<div class="radio-box"><label class="' . $value[0] . '"><input type="radio" name="level" value="'
                . $key . '" ' . ($key == $id ? 'checked' : '') . '>' . $value[1] . '</label></div>';
        }
        View::assign(['Level' => $html]);
    }

    public static function managerIsActivationSelect($isActivation = [], $id = 0)
    {
        $html = '';
        foreach ($isActivation as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . ' style="color:' .
                $value[0] . ';">' . $value[1] . '</option>';
        }
        View::assign(['IsActivation' => $html]);
    }

    public static function managerIsActivationRadio($isActivation = [], $id = 0)
    {
        $html = '';
        foreach ($isActivation as $key => $value) {
            $html .= '<div class="radio-box"><label class="' . $value[0] .
                '"><input type="radio" name="is_activation" value="' . $key . '" ' . ($key == $id ? 'checked' : '') .
                '>' . $value[1] . '</label></div>';
        }
        View::assign(['IsActivation' => $html]);
    }

    public static function managerOrderPermitSelect($orderPermit = [], $id = 0)
    {
        $html = '';
        foreach ($orderPermit as $key => $value) {
            if ($key) {
                $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . ' style="color:' .
                    $value[0] . ';">' . $value[1] . '</option>';
            }
        }
        View::assign(['OrderPermit' => $html]);
    }

    public static function managerOrderPermitRadio($orderPermit = [], $id = 0)
    {
        $html = '';
        foreach ($orderPermit as $key => $value) {
            if ($key) {
                $html .= '<div class="radio-box"><label class="' . $value[0] .
                    '"><input type="radio" name="order_permit" value="' . $key . '" ' . ($key == $id ? 'checked' : '') .
                    '>' . $value[1] . '</label></div>';
            }
        }
        View::assign(['OrderPermit' => $html]);
    }

    public static function qq($qq = [], $id = 0)
    {
        $html = '';
        foreach ($qq as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . ' style="color:' . $value[0] .
                ';">' . $value[1] . '</option>';
        }
        View::assign(['Qq' => $html]);
    }

    public static function wechat($wechat = [], $id = 0)
    {
        $html = '';
        foreach ($wechat as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . ' style="color:' . $value[0] .
                ';">' . $value[1] . '</option>';
        }
        View::assign(['Wechat' => $html]);
    }

    public static function stateRadio($state = [], $id = 0)
    {
        $html = '';
        foreach ($state as $key => $value) {
            $html .= '<div class="radio-box"><label class="' . $value[0] . '"><input type="radio" name="state" value="'
                . $key . '" ' . ($key == $id ? 'checked' : '') . '>' . $value[1] . '</label></div>';
        }
        View::assign(['State' => $html]);
    }

    public static function validateFileExtension($extension = [], $id = 0)
    {
        $html = '';
        foreach ($extension as $key => $value) {
            $html .= '<div class="radio-box"><label><input type="radio" name="extension" value="' . $key . '" ' .
                ($key == $id ? 'checked' : '') . '>' . $value . '</label></div>';
        }
        View::assign(['Extension' => $html]);
    }

    public static function alipayScene($id = 0)
    {
        $html = '';
        foreach (Config::get('pay_scene.alipay') as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . '>' . $value . '</option>';
        }
        View::assign(['AlipayScene' => $html]);
    }

    public static function wxpayScene($id = 0)
    {
        $html = '';
        foreach (Config::get('pay_scene.wxpay') as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . '>' . $value . '</option>';
        }
        View::assign(['WxpayScene' => $html]);
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
        $html = '';
        foreach ($type as $key => $value) {
            $html .= is_array($value) ?
                '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . ' style="color:' . $value[0] .
                ';">' . $value[1] . '</option>' :
                '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . '>' . $value . '</option>';
        }
        View::assign(['Type' => $html]);
    }

    public static function typeRadio($type = [], $id = 0)
    {
        $html = '';
        foreach ($type as $key => $value) {
            $html .= is_array($value) ?
                '<div class="radio-box"><label class="' . $value[0] . '"><input type="radio" name="type" value="'
                . $key . '" ' . ($key == $id ? 'checked' : '') . '>' . $value[1] . '</label></div>' :
                '<div class="radio-box"><label><input type="radio" name="type" value="' . $key . '" ' .
                ($key == $id ? 'checked' : '') . '>' . $value . '</label></div>';
        }
        View::assign(['Type' => $html]);
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
        $html = '';
        foreach ((new model\ProductSort())->all2() as $value) {
            $html .= '<option value="' . $value['id'] . '" ' . ($value['id'] == $id ? 'selected' : '') .
                ' style="color:' . $value['color'] . ';">' . $value['name'] . '</option>';
        }
        View::assign(['ProductSort' => $html]);
    }

    public static function orderPaymentSelect($id = 0)
    {
        $html = '';
        foreach (Config::get('payment') as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . '>' . $value . '</option>';
        }
        View::assign(['Payment' => $html]);
    }

    public static function orderPaymentRadio($id = 0)
    {
        $html = '';
        foreach (Config::get('payment') as $key => $value) {
            $html .= '<div class="radio-box"><label><input type="radio" name="payment_id" value="' . $key . '" ' .
                ($key == $id ? 'checked' : '') . '>' . $value . '</label></div>';
        }
        View::assign(['Payment' => $html]);
    }

    public static function express($id = 0)
    {
        $html = '';
        foreach ((new model\Express())->all2() as $value) {
            $html .= '<option value="' . $value['id'] . '" ' . ($value['id'] == $id ? 'selected' : '') . '>' .
                $value['name'] . '</option>';
        }
        View::assign(['Express' => $html]);
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

    public static function template2($id = 0, $flag = 0)
    {
        $html = '';
        foreach ((new model\Template())->all2() as $value) {
            if ($id == 0) {
                $html .= '<option value="' . $value['id'] . '" ' . ($value['is_default'] && $flag ? 'selected' : '') .
                    '>' . $value['name'] . '</option>';
            } else {
                $html .= '<option value="' . $value['id'] . '" ' . ($value['id'] == $id ? 'selected' : '') . '>' .
                    $value['name'] . '</option>';
            }
        }
        View::assign(['Template' => $html]);
    }


    public static function templateStyle($id = 0)
    {
        $html = '';
        foreach ((new model\TemplateStyle())->all2() as $value) {
            $html .= '<option value="' . $value['id'] . '" ' . ($value['id'] == $id ? 'selected' : '') . '>' .
                $value['id'] . '号皮肤样式</option>';
        }
        View::assign(['TemplateStyle' => $html]);
    }

    public static function field($ids = [])
    {
        $html = '';
        $Field = new model\Field();
        $isDefault = arrToStr($Field->all3(), 'id');
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
        $html = '';
        foreach ((new model\OrderState())->all2() as $value) {
            $html .= '<option value="' . $value['id'] . '" ' . ($value['id'] == $id ? 'selected' : '') .
                ' style="color:' . $value['color'] . ';">' . $value['name'] . '</option>';
        }
        View::assign(['OrderState' => $html]);
    }
}
