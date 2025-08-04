<?php

namespace app\admin\controller;

use app\admin\model;
use app\admin\library\Html;
use think\facade\Config;
use think\facade\Request;

class ValidateFile extends Base
{
    private array $extension = ['.txt', '.html', '.htm'];

    public function index()
    {
        if (Request::isAjax()) {
            $validateFileForm = (new model\ValidateFile())->form();
            if (is_array($validateFileForm)) {
                if (
                    file_put_contents(ROOT_DIR . '/app/admin/config/validate_file.php', "<?php

return [
    'name' => '" . $validateFileForm['name'] . "',  //文件名
    'extension' => '" . $validateFileForm['extension'] . "',  //文件扩展名
    'content' => '" . str_replace("'", "\'", $validateFileForm['content']) . "'  //文件内容
];
") &&
                    file_put_contents(
                        ROOT_DIR . '/' . $validateFileForm['name'] . $this->extension[$validateFileForm['extension']],
                        $validateFileForm['content']
                    )
                ) {
                    if (
                        Config::get('validate_file.name') &&
                        (Config::get('validate_file.name') != $validateFileForm['name'] ||
                            Config::get('validate_file.extension') != $validateFileForm['extension']) &&
                        is_file(ROOT_DIR . '/' . Config::get('validate_file.name') .
                            $this->extension[Config::get('validate_file.extension')])
                    ) {
                        unlink(ROOT_DIR . '/' . Config::get('validate_file.name') .
                            $this->extension[Config::get('validate_file.extension')]);
                    }
                    return apiResponse('验证文件生成成功！');
                } else {
                    return apiResponse('验证文件生成失败，请检查app/admin/config目录权限以及根目录权限！', 0);
                }
            } else {
                return apiResponse($validateFileForm, 0);
            }
        }
        Html::validateFileExtension($this->extension, Config::get('validate_file.extension'));
        return $this->view();
    }
}
