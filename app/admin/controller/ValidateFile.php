<?php

namespace app\admin\controller;

use think\facade\Request;
use think\facade\Config;
use think\facade\View;

class ValidateFile extends Base
{
    private $extension = ['.txt', '.html', '.htm'];

    public function index()
    {
        if (Request::isPost()) {
            $name = str_replace(["'", '"', '\\', '/', ':', '*', '?', '<', '>', '|'], '', Request::post('name'));
            if (!$name) {
                return $this->failed('文件名不得为空且不得包含特殊字符！');
            }
            if (!isset($this->extension[Request::post('extension')])) {
                return $this->failed('无效的文件扩展名！');
            }
            if (!Request::post('content')) {
                return $this->failed('文件内容不得为空！');
            }
            $output = "<?php return [
	'name'=>'" . $name . "',  //文件名
	'extension'=>'" . Request::post('extension') . "',  //文件扩展名
	'content'=>'" . str_replace("'", "\'", Request::post('content')) . "',  //文件内容
];";
            if (
                file_put_contents(
                    ROOT_PATH . '/' . Config::get('app.config_path_admin') . '/validate_file.php',
                    $output
                ) && file_put_contents(
                    ROOT_PATH . '/' . $name . $this->extension[Request::post('extension')],
                    Request::post('content')
                )
            ) {
                if (
                    Config::get('validate_file.name') &&
                    (Config::get('validate_file.name') != $name ||
                        Config::get('validate_file.extension') != Request::post('extension')) &&
                    is_file(ROOT_PATH . '/' . Config::get('validate_file.name') .
                        $this->extension[Config::get('validate_file.extension')])
                ) {
                    unlink(ROOT_PATH . '/' . Config::get('validate_file.name') .
                        $this->extension[Config::get('validate_file.extension')]);
                }
                return $this->success(Config::get('app.prev_url'), '验证文件生成成功！');
            } else {
                return $this->failed('验证文件生成失败，请检查' . Config::get('app.config_path_admin') . '目录权限以及根目录权限！');
            }
        }
        $this->extension(Config::get('validate_file.extension'));
        return $this->view();
    }

    private function extension($id = 0)
    {
        $html = '';
        foreach ($this->extension as $key => $value) {
            $html .= '<div class="radio-box"><label><input type="radio" name="extension" value="' . $key . '" ' .
                ($key == $id ? 'checked' : '') . '>' . $value . '</label></div>';
        }
        View::assign(['Extension' => $html]);
    }
}
