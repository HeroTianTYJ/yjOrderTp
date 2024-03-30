<?php

namespace app\admin\controller;

use app\admin\library\Html;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;
use ZipArchive;

class File extends Base
{
    private array $type = [
        'login_manager' => '登录记录',
        'visit' => '访问统计',
        'zip' => '压缩包'
    ];

    public function index()
    {
        $outputAll = [];
        $dirOutput = ROOT_DIR . '/' . Config::get('dir.output');
        if (is_dir($dirOutput)) {
            foreach (scandir($dirOutput) as $value) {
                if (!in_array($value, ['.', '..'])) {
                    $outputAll[] = $this->listItem($value);
                }
            }
        }
        $outputAll = $this->filter($outputAll);
        rsort($outputAll);
        if (Request::isAjax()) {
            $nowPage = intval(Request::post('page', 1));
            $nowPage = $nowPage > 0 ? $nowPage : 1;
            $firstRow = Config::get('app.page_size') * ($nowPage - 1);
            return $outputAll ? json_encode(array_slice($outputAll, $firstRow, Config::get('app.page_size'))) : '';
        }
        View::assign(['Total' => count($outputAll)]);
        Html::typeSelect($this->type, Request::get('type'));
        return $this->view();
    }

    public function zip()
    {
        if (Request::isAjax()) {
            if (strstr(Request::post('ids'), '.zip')) {
                return showTip('zip压缩包不能被再次打包！', 0);
            }
            $dirOutput = ROOT_DIR . '/' . Config::get('dir.output');
            $ZipArchive = new ZipArchive();
            if (
                $ZipArchive->open(
                    $dirOutput . 'zip_' . date('YmdHis') . '.zip',
                    ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CM_STORE
                )
            ) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (
                        substr($value, 0, 1) != '.' || !strstr($value, '/') || !strstr($value, '\\') ||
                        !strstr($value, '%5C') || !strstr($value, '%2F')
                    ) {
                        $ZipArchive->addFromString($value, file_get_contents($dirOutput . $value));
                        if (Request::post('is_delete')) {
                            unlink($dirOutput . $value);
                        }
                    }
                }
                $ZipArchive->close();
            }
            return showTip('文件打包成功！');
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function download()
    {
        if (Request::get('id')) {
            if (
                substr(Request::get('id'), 0, 1) == '.' || strstr(Request::get('id'), '/') ||
                strstr(Request::get('id'), '\\') || strstr(Request::get('id'), '%5C') ||
                strstr(Request::get('id'), '%2F')
            ) {
                return $this->failed('非法操作！');
            }
            $file = ROOT_DIR . '/' . Config::get('dir.output') . '/' . Request::get('id');
            if (!is_file($file)) {
                return $this->failed('不存在此文件！');
            }
            downloadFileToLocal(file_get_contents($file), Request::get('id'));
            return '';
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return showTip('演示站，文件无法删除！', 0);
            }
            $succeedFile = $failedFile = $noExistsFile = 0;
            foreach (explode(',', Request::post('id') ?: Request::post('ids')) as $value) {
                if (
                    substr($value, 0, 1) == '.' || strstr($value, '/') || strstr($value, '\\') ||
                    strstr($value, '%5C') || strstr($value, '%2F')
                ) {
                    return showTip('非法操作！', 0);
                }
                $file = ROOT_DIR . '/' . Config::get('dir.output') . '/' . $value;
                if (file_exists($file)) {
                    unlink($file) ? $succeedFile++ : $failedFile++;
                } else {
                    $noExistsFile++;
                }
            }
            if (Request::post('ids')) {
                $tip = [];
                if ($succeedFile) {
                    $tip[] = $succeedFile . '个文件删除成功';
                }
                if ($failedFile) {
                    $tip[] = $failedFile . '个文件删除失败';
                }
                if ($noExistsFile) {
                    $tip[] = $noExistsFile . '个文件不存在';
                }
                return showTip(implode('，', $tip) . '！');
            } else {
                return $noExistsFile == 0 ? $succeedFile == 1 ?
                    showTip('文件删除成功！') : showTip('文件删除失败！', 0) : showTip('不存在此文件！', 0);
            }
        } else {
            return showTip('非法操作！', 0);
        }
    }

    private function listItem($value)
    {
        $item['id'] = $value;
        $item['name'] = keyword($value);
        $size = filesize(ROOT_DIR . '/' . Config::get('dir.output') . '/' . $value);
        $item['size'] = $size < 1073741824 ?
            $size < 1048576 ?
                $size < 1024 ?
                    $size . ' 字节' :
                    number_format($size / 1024, 2) . ' KB' :
                number_format($size / 1048576, 2) . ' MB' :
            number_format($size / 1073741824, 2) . ' GB';
        $item['type_key'] = substr($value, 0, strrpos($value, '_'));
        $item['type'] = $this->type[substr($value, 0, strrpos($value, '_'))];
        return $item;
    }

    private function filter($outputAll)
    {
        if (!Request::get('type') && !Request::get('keyword')) {
            return $outputAll;
        }
        $result = [];
        foreach ($outputAll as $key => $value) {
            if (Request::get('type') && $value['type_key'] == Request::get('type')) {
                $result[$key] = $value;
            }
            if (Request::get('keyword') && strstr($value['name'], Request::get('keyword'))) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
