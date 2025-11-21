<?php

namespace app\admin\controller;

use czdb\DbSearcher;
use think\facade\Config;
use think\facade\Filesystem;
use think\facade\Request;
use think\facade\Validate;

class Common extends Base
{
    //上传IP数据库
    public function czdb()
    {
        if (Request::isPost()) {
            if (Config::get('app.demo')) {
                return apiResponse('演示站，无法上传！', 0);
            }
            $Validate = Validate::rule(['file' => 'fileSize:51200000|fileExt:czdb'])
                ->message(['file.fileSize' => '文件不得大于50MB！', 'file.fileExt' => '文件类型必须是czdb！']);
            if ($Validate->check(Request::file())) {
                $file = Config::get(Request::post('czdb_version') == 0 ? 'file.czdb_v4' : 'file.czdb_v6');
                rename(ROOT_DIR . '/' . Config::get('dir.upload') . Filesystem::putFile(
                    date('Y-m'),
                    Request::file('file'),
                    function () {
                        return date('dHis') . rand(1000, 9999);
                    }
                ), $file);
                return apiResponse((new DbSearcher($file, Config::get('system.czdb_key')))->version());
            } else {
                return apiResponse($Validate->getError(), 0);
            }
        } else {
            return apiResponse('非法操作！', 0);
        }
    }
}
