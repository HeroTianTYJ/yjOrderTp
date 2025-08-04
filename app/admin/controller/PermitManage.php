<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Request;
use think\facade\Route;
use think\facade\View;

class PermitManage extends Base
{
    public function index()
    {
        $PermitManage = new model\PermitManage();
        $permitManageAll = $PermitManage->all();
        if (Request::isAjax()) {
            foreach ($permitManageAll as $key => $value) {
                $permitManageAll[$key] = $this->listItem($permitManageAll[$key]);
                $child = [];
                foreach ($PermitManage->all3($value['id']) as $k => $v) {
                    $child[$k] = $this->listItem($v);
                }
                $permitManageAll[$key]['child'] = $child;
            }
            return $permitManageAll->items() ? apiResponse('', 1, $permitManageAll->items()) : '';
        }
        View::assign(['Total' => $permitManageAll->total()]);
        return $this->view();
    }

    public function isDefault()
    {
        if (Request::isAjax() && Request::post('id')) {
            $PermitManage = new model\PermitManage();
            $permitManageOne = $PermitManage->one();
            if (!$permitManageOne) {
                return apiResponse('不存在此管理权限！', 0);
            }
            if ($permitManageOne['is_default'] == 0) {
                return $PermitManage->isDefault(1) ? apiResponse('设置默认管理权限成功！') : apiResponse('设置默认管理权限失败！', 0);
            } else {
                return $PermitManage->isDefault(0) ? apiResponse('取消默认管理权限成功！') : apiResponse('取消默认管理权限失败！', 0);
            }
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function output()
    {
        $PermitManage = new model\PermitManage();
        $output = '<?php

return [';
        foreach ($PermitManage->all2() as $value) {
            $output .= "
    '" . $value['controller'] . "' => [
        '" . $value['action'] . "' => " . $value['id'];
            foreach ($PermitManage->all3($value['id']) as $v) {
                $output .= ",
        '" . $v['action'] . "' => " . $v['id'];
            }
                $output .= '
    ],';
        }
        $output = substr($output, 0, -1) . '
];
';
        return file_put_contents(ROOT_DIR . '/app/admin/config/permit_manage.php', $output) ?
            $this->succeed(Route::buildUrl('/' . parse_name(Request::controller()) . '/index'), '配置文件修改成功！') :
            $this->failed('配置文件修改失败！');
    }

    private function listItem($item)
    {
        $item['name'] = keyword($item['name']);
        $item['controller'] = keyword($item['controller']);
        $item['action'] = keyword($item['action']);
        return $item;
    }
}
