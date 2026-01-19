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
        'login_manager_merged' => '登录记录（已合并）',
        'visit' => '访问统计',
        'visit_merged' => '访问统计（已合并）',
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
            return $outputAll ?
                apiResponse('', 1, array_slice($outputAll, $firstRow, Config::get('app.page_size'))) : '';
        }
        View::assign(['Total' => count($outputAll)]);
        Html::typeSelect($this->type, Request::get('type'));
        return $this->view();
    }

    public function zip()
    {
        if (Request::isAjax()) {
            if (strstr(Request::post('ids'), '.zip')) {
                return apiResponse('zip压缩包不能被再次打包！', 0);
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
            return apiResponse('文件打包成功！');
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function merge()
    {
        if (Request::isAjax()) {
            $dirOutput = ROOT_DIR . '/' . Config::get('dir.output');
            $outputAll = [];
            if (is_dir($dirOutput)) {
                foreach (scandir($dirOutput) as $value) {
                    if (!in_array($value, ['.', '..'])) {
                        $key = substr($value, 0, strrpos($value, '_'));
                        if ($key != 'zip' && !strstr($key, '_merge')) {
                            $outputAll[$key][] = $value;
                        }
                    }
                }
            }
            $type = [];
            foreach ($this->type as $key => $value) {
                if ($key != 'zip' && !strstr($key, '_merge')) {
                    $type[$key] = $value . '（' . count($outputAll[$key] ?? []) . '个文件）';
                }
            }
            if (Request::get('action') == 'do') {
                if (!in_array(Request::post('type_id'), keyToArray($type))) {
                    return apiResponse('您选择的文件类型不存在！', 0);
                }
                $outputAll2 = array_reverse($outputAll[Request::post('type_id')] ?? []);
                if (count($outputAll2) < 2) {
                    return apiResponse('您选择的文件类型，文件个数小于2！', 0);
                }
                $output = '';
                foreach ($outputAll2 as $key => $value) {
                    $temp = mb_convert_encoding(file_get_contents($dirOutput . $value), 'UTF-8', 'GBK');
                    if ($key != 0) {
                        $temp = implode("\n", array_slice(explode("\n", $temp), 1));
                    }
                    $output .= $temp . "\n";
                    if (Request::post('is_delete')) {
                        unlink($dirOutput . $value);
                    }
                }
                if (
                    file_put_contents(
                        $dirOutput . Request::post('type_id') . '_merged_' . date('YmdHis') . '.csv',
                        mb_convert_encoding($output, 'GBK', 'UTF-8')
                    )
                ) {
                    return apiResponse('文件合并成功！');
                } else {
                    return apiResponse('文件合并失败，请检查' . Config::get('dir.output') . '目录权限！', 0);
                }
            }
            Html::typeRadio($type, array_key_first($type));
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
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
                return apiResponse('演示站，文件无法删除！', 0);
            }
            $succeedFile = $failedFile = $noExistsFile = 0;
            foreach (explode(',', Request::post('id') ?: Request::post('ids')) as $value) {
                if (
                    substr($value, 0, 1) == '.' || strstr($value, '/') || strstr($value, '\\') ||
                    strstr($value, '%5C') || strstr($value, '%2F')
                ) {
                    return apiResponse('非法操作！', 0);
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
                return apiResponse(implode('，', $tip) . '！');
            } else {
                return $noExistsFile == 0 ? $succeedFile == 1 ?
                    apiResponse('文件删除成功！') : apiResponse('文件删除失败！', 0) : apiResponse('不存在此文件！', 0);
            }
        } else {
            return apiResponse('非法操作！', 0);
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
        $item['type'] = $this->type[$item['type_key']];
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
