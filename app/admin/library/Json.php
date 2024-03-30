<?php

namespace app\admin\library;

use app\admin\model;

class Json extends \app\common\library\Json
{
    public static function product($ids = '', $productSortId = 0)
    {
        $data = [];
        foreach ((new model\Product())->all2($productSortId) as $key => $value) {
            $data[$key] = [
                'value' => $value['id'],
                'name' => $value['name'],
                'selected' => in_array($value['id'], explode(',', $ids)),
                'color' => $value['color']
            ];
        }
        return json_encode($data);
    }

    public static function product2($ids = '')
    {
        $data = [];
        $productSortAll = (new model\ProductSort())->all2();
        if ($productSortAll) {
            $Product = new model\Product();
            foreach ($productSortAll as $key => $value) {
                $data[$key] = ['name' => $value['name'], 'color' => $value['color']];
                $productAll = $Product->all2($value['id']);
                if ($productAll) {
                    foreach ($productAll as $k => $v) {
                        $data[$key]['children'][$k] = [
                            'value' => $v['id'],
                            'name' => $v['name'],
                            'selected' => in_array($v['id'], explode(',', $ids)),
                            'color' => $v['color'],
                            'parent_value' => $value['id'],
                            'parent_name' => $value['name'],
                            'parent_color' => $value['color']
                        ];
                    }
                } else {
                    $data[$key]['children'][0] = ['name' => '此分类下暂无商品', 'disabled' => true];
                }
            }
        }
        return json_encode($data);
    }
}
