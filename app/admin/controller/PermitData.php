<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Request;
use think\facade\Route;
use think\facade\View;

class PermitData extends Base
{
    public function index()
    {
        $PermitData = new model\PermitData();
        $permitDataAll = $PermitData->all();
        if (Request::isAjax()) {
            foreach ($permitDataAll as $key => $value) {
                $permitDataAll[$key] = $this->listItem($permitDataAll[$key]);
                $child = [];
                foreach ($PermitData->all3($value['id']) as $k => $v) {
                    $child[$k] = $this->listItem($v);
                }
                $permitDataAll[$key]['child'] = $child;
            }
            return $permitDataAll->items() ? json_encode($permitDataAll->items()) : '';
        }
        View::assign(['Total' => $permitDataAll->total()]);
        return $this->view();
    }

    public function isDefault()
    {
        if (Request::isAjax() && Request::post('id')) {
            $PermitData = new model\PermitData();
            $permitDataOne = $PermitData->one();
            if (!$permitDataOne) {
                return apiResponse('不存在此数据权限！', 0);
            }
            if ($permitDataOne['is_default'] == 0) {
                return $PermitData->isDefault(1) ? apiResponse('设置默认数据权限成功！') : apiResponse('设置默认数据权限失败！', 0);
            } else {
                return $PermitData->isDefault(0) ? apiResponse('取消默认数据权限成功！') : apiResponse('取消默认数据权限失败！', 0);
            }
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function output()
    {
        $PermitData = new model\PermitData();
        $output = '<?php

return [';
        foreach ($PermitData->all2() as $value) {
            $output .= "
    '" . $value['alias'] . "' => [";
            foreach ($PermitData->all3($value['id']) as $v) {
                $output .= "
        '" . $v['alias'] . "' => " . $v['id'] . ',';
            }
            $output = substr($output, 0, -1) . '
    ],';
        }
        $output = substr($output, 0, -1) . '
];
';
        return file_put_contents(ROOT_DIR . '/app/admin/config/permit_data.php', $output) ?
            $this->succeed(Route::buildUrl('/' . parse_name(Request::controller()) . '/index'), '配置文件修改成功！') :
            $this->failed('配置文件修改失败！');
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        return $item;
    }
}
