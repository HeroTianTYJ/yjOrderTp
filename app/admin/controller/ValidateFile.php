<?php

namespace app\admin\controller;

use app\admin\library\Html;
use think\facade\Config;
use think\facade\Request;

class ValidateFile extends Base
{
    private array $extension = ['.txt', '.html', '.htm'];

    public function index()
    {
        if (Request::isAjax()) {
            $name = str_replace(["'", '"', '\\', '/', ':', '*', '?', '<', '>', '|'], '', Request::post('name'));
            if (!$name) {
                return showTip('文件名不得为空且不得包含特殊字符！', 0);
            }
            if (!isset($this->extension[Request::post('extension')])) {
                return showTip('无效的文件扩展名！', 0);
            }
            if (!Request::post('content')) {
                return showTip('文件内容不得为空！', 0);
            }
            if (
                file_put_contents(
                    ROOT_DIR . '/app/admin/config/validate_file.php',
                    "<?php

return [
    'name' => '" . $name . "',  //文件名
    'extension' => '" . Request::post('extension') . "',  //文件扩展名
    'content' => '" . str_replace("'", "\'", Request::post('content')) . "'  //文件内容
];
"
                ) && file_put_contents(
                    ROOT_DIR . '/' . $name . $this->extension[Request::post('extension')],
                    Request::post('content')
                )
            ) {
                if (
                    Config::get('validate_file.name') &&
                    (Config::get('validate_file.name') != $name ||
                        Config::get('validate_file.extension') != Request::post('extension')) &&
                    is_file(ROOT_DIR . '/' . Config::get('validate_file.name') .
                        $this->extension[Config::get('validate_file.extension')])
                ) {
                    unlink(ROOT_DIR . '/' . Config::get('validate_file.name') .
                        $this->extension[Config::get('validate_file.extension')]);
                }
                return showTip('验证文件生成成功！');
            } else {
                return showTip('验证文件生成失败，请检查app/admin/config目录权限以及根目录权限！', 0);
            }
        }
        Html::validateFileExtension($this->extension, Config::get('validate_file.extension'));
        return $this->view();
    }
}
