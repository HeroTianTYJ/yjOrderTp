<?php

namespace app\common\library;

use think\facade\View;

class Html
{
    protected static function radioArray($array = [], $name = '', $assignName = '', $id = 0)
    {
        $html = '';
        foreach ($array as $key => $value) {
            $html .= '<div class="radio-box"><label class="' .
                (is_array($value) && isset($value[0]) ? $value[0] : '') . '"><input type="radio" name="' . $name .
                '" value="' . $key . '" ' . ($key == $id ? 'checked' : '') . '>' .
                (is_array($value) && isset($value[1]) ? $value[1] : $value) . '</label></div>';
        }
        View::assign([$assignName => $html]);
    }

    protected static function selectArray($array = [], $assignName = '', $id = 0)
    {
        $html = '';
        foreach ($array as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($key == $id ? 'selected' : '') . ' style="color:' .
                (is_array($value) && isset($value[0]) ? $value[0] : '') . ';">' .
                (is_array($value) && isset($value[1]) ? $value[1] : $value) . '</option>';
        }
        View::assign([$assignName => $html]);
    }

    protected static function selectDataset($dataset = [], $assignName = '', $id = 0, $html = '', $isDefault = 0)
    {
        foreach ($dataset as $value) {
            $html .= '<option value="' . $value['id'] . '" ' .
                ($value['id'] == $id || ($id == 0 && $isDefault && $value['is_default']) ? 'selected' : '') .
                ' style="color:' . ($value['color'] ?? '') . ';">' . $value['name'] . '</option>';
        }
        View::assign([$assignName => $html]);
    }
}
