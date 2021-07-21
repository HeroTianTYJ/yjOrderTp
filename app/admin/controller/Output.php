<?php

namespace app\admin\controller;

use think\facade\Request;
use think\facade\Config;
use think\facade\View;
use ZipArchive;

class Output extends Base
{
    public function index()
    {
        $result = [];
        $dir = ROOT_PATH . '/' . Config::get('app.output_dir');
        if (is_dir($dir)) {
            $all = $temp = [];
            $dirPath = opendir($dir);
            while (!!$dirName = readdir($dirPath)) {
                if (!in_array($dirName, ['.', '..'])) {
                    $all[] = $dirName;
                }
            }
            closedir($dirPath);
            rsort($all);
            $type = [
                'login' => '登录记录',
                'visit' => '访问统计',
                'zip' => '压缩包'
            ];
            if ($all) {
                foreach ($all as $value) {
                    $size = filesize($dir . '/' . $value);
                    $temp['name'] = $value;
                    $temp['size'] = $size < 1073741824 ?
                        $size < 1048576 ?
                            $size < 1024 ?
                                $size . ' 字节' :
                                number_format($size / 1024, 2) . ' KB' :
                            number_format($size / 1048576, 2) . ' MB' :
                        number_format($size / 1073741824, 2) . ' GB';
                    foreach ($type as $k => $v) {
                        if (substr($value, 0, strrpos($value, '_')) == $k) {
                            $result[$k][$v][] = $temp;
                        }
                    }
                }
            }
            ksort($result);
        }
        View::assign(['All' => $result]);
        return $this->view();
    }

    public function zip()
    {
        if (Request::isPost()) {
            if (Request::post('files')) {
                $dir = ROOT_PATH . '/' . Config::get('app.output_dir');
                $ZipArchive = new ZipArchive();
                if (
                    $ZipArchive->open(
                        $dir . 'zip_' . time() . '.zip',
                        \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE | \ZIPARCHIVE::CM_STORE
                    )
                ) {
                    foreach (Request::post('files') as $value) {
                        if (
                            substr($value, 0, 1) != '.' || !strstr($value, '/') || !strstr($value, '\\') ||
                            !strstr($value, '%5C') || !strstr($value, '%2F')
                        ) {
                            $ZipArchive->addFromString($value, file_get_contents($dir . $value));
                        }
                    }
                    $ZipArchive->close();
                }
                if (Request::post('del')) {
                    foreach (Request::post('files') as $value) {
                        unlink($dir . $value);
                    }
                }
                return $this->success(Config::get('app.prev_url'), '文件压缩成功！');
            } else {
                return $this->failed('您未勾选任何文件！');
            }
        }
        return '';
    }

    public function delete()
    {
        if (Request::get('id')) {
            if (Request::isPost()) {
                if (Config::get('app.demo')) {
                    return $this->failed('演示站，数据无法删除！');
                }
                if (
                    substr(Request::get('id'), 0, 1) == '.' || strstr(Request::get('id'), '/') ||
                    strstr(Request::get('id'), '\\') || strstr(Request::get('id'), '%5C') ||
                    strstr(Request::get('id'), '%2F')
                ) {
                    return $this->failed('非法操作！');
                }
                $file = ROOT_PATH . '/' . Config::get('app.output_dir') . '/' . Request::get('id');
                return file_exists($file) ?
                    unlink($file) ?
                        $this->success(Request::post('prev'), '文件删除成功！') :
                        $this->failed('文件删除失败！') :
                    $this->failed('不存在此文件！');
            }
            return $this->confirm('您真的要删除这条数据么？');
        } else {
            return $this->failed('非法操作！');
        }
    }
}
