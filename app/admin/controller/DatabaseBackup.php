<?php

namespace app\admin\controller;

use app\admin\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class DatabaseBackup extends Base
{
    private $linkId;
    private $sqlId;
    private $record;

    public function index()
    {
        $commonInfo = (new model\Common())->info();
        if (Request::isPost()) {
            if (Config::get('app.demo')) {
                return apiResponse('演示站，数据库无法备份！', 0);
            }
            if (Request::post('table_name')) {
                $table = [];
                foreach ($commonInfo as $value) {
                    $table[] = $value['Name'];
                }
                if (array_diff(Request::post('table_name'), $table)) {
                    return apiResponse('您勾选的数据表不存在！', 0);
                }
            }
            if (
                $this->backup(
                    'data',
                    date('YmdHis'),
                    Request::post('file_size', 0),
                    Request::post('table_name') ? Request::post('table_name') : []
                )
            ) {
                return apiResponse('数据库已成功备份到服务器，可联系客服人员下载！');
            }
        }
        $remainder = count($commonInfo) % 4;
        if ($remainder != 0) {
            for ($i = 0; $i < 4 - $remainder; $i++) {
                $commonInfo[]['Name'] = '';
            }
        }
        View::assign(['All' => $commonInfo]);
        return $this->view();
    }

    private function query($sql)
    {
        if (!!$this->sqlId = mysqli_query($this->linkId, $sql)) {
            return mysqli_query($this->linkId, $sql);
        } else {
            return apiResponse('SQL语句：' . $sql . '<br>错误信息：' . mysqli_error($this->linkId), 0);
        }
    }

    private function numFields()
    {
        return $this->sqlId ? mysqli_num_fields($this->sqlId) : false;
    }

    private function nextRecord($sqlId = '')
    {
        if (!$sqlId) {
            $sqlId = $this->sqlId;
        }
        return (!!$this->record = mysqli_fetch_array($sqlId)) ? $this->record : false;
    }

    private function f($name)
    {
        return $this->record[$name] ?: false;
    }

    private function getInfo($table)
    {
        $this->query('SHOW CREATE TABLE ' . $table);
        $this->nextRecord();
        return "\r\n" . str_replace("\n", "\r\n", $this->f('Create Table')) . ";\r\n";
    }

    private function getRecord($table, $numFields)
    {
        $comma = '';
        $sql = 'INSERT INTO `' . $table . '`(`' . implode(
            '`,`',
            array_map(
                'addslashes',
                array_keys(mysqli_fetch_assoc(mysqli_query($this->linkId, 'SELECT * FROM `' . $table . '` LIMIT 1')))
            )
        ) . '`) VALUES(';
        for ($i = 0; $i < $numFields; $i++) {
            $sql .= $comma . "'" . addslashes($this->record[$i]) . "'";
            $comma = ',';
        }
        $sql .= ");\r\n";
        return $sql;
    }

    private function write($sql, $filename)
    {
        if (!$fp = fopen($filename, 'w+')) {
            return apiResponse('文件打开失败！', 0);
        }
        if (!fwrite($fp, $sql)) {
            return apiResponse('文件写入失败！', 0);
        }
        if (!fclose($fp)) {
            return apiResponse('文件关闭失败！', 0);
        }
        return true;
    }

    //备份全部表（不分卷）
    private function all($path = '', $filename = '', $tableStatus = true)
    {
        if (
            !$tablesInfo = $this->query('SHOW TABLE STATUS FROM `' .
            Config::get('database.connections.mysql.database') . "` LIKE '" .
            Config::get('database.connections.mysql.prefix') . "%'")
        ) {
            return apiResponse('读数据库结构错误！', 0);
        }
        $sql = '';
        while ($this->nextRecord($tablesInfo)) {
            $table = $this->f('Name');
            if ($tableStatus) {
                $sql .= $this->getInfo($table);
            }
            $this->query('SELECT * FROM ' . $table);
            $numFields = $this->numFields();
            while ($this->nextRecord()) {
                $sql .= $this->getRecord($table, $numFields);
            }
        }
        $this->write(substr($sql, 0, -2), ROOT_DIR . '/' . $path . '/' . $filename . '_all.sql');
        return true;
    }

    //备份全部表（分卷）
    private function allPart($path = '', $filename = '', $fileSize = 2000, $tableStatus = true)
    {
        if (
            !$tablesInfo = $this->query('SHOW TABLE STATUS FROM `' .
            Config::get('database.connections.mysql.database') . "` LIKE '" .
            Config::get('database.connections.mysql.prefix') . "%'")
        ) {
            return apiResponse('读数据库结构错误！', 0);
        }
        $p = 1;
        $sql = '';
        while ($this->nextRecord($tablesInfo)) {
            $table = $this->f('Name');
            if ($tableStatus) {
                $sql .= $this->getInfo($table);
            }
            $this->query('SELECT * FROM ' . $table);
            $numFields = $this->numFields();
            while ($this->nextRecord()) {
                $sql .= $this->getRecord($table, $numFields);
                if (strlen($sql) >= $fileSize * 1024) {
                    $this->write(substr($sql, 0, -2), $path . '/' . $filename . '_all_part' . $p . '.sql');
                    $p++;
                    $sql = '';
                }
            }
        }
        if ($sql != '') {
            $this->write(substr($sql, 0, -2), ROOT_DIR . '/' . $path . '/' . $filename . '_all_part' . $p . '.sql');
        }
        return true;
    }

    //备份自定义多表（不分卷）
    private function n($path = '', $filename = '', $tables = [], $tableStatus = true)
    {
        $sql = '';
        foreach ($tables as $value) {
            if ($tableStatus) {
                $sql .= $this->getInfo($value);
            }
            $this->query('SELECT * FROM ' . $value);
            $numFields = $this->numFields();
            while ($this->nextRecord()) {
                $sql .= $this->getRecord($value, $numFields);
            }
        }
        $this->write($sql, ROOT_DIR . '/' . $path . '/' . $filename . '_n.sql');
        return true;
    }

    //备份自定义多表（分卷）
    private function nPart($path = '', $filename = '', $fileSize = 2000, $tables = [], $tableStatus = true)
    {
        $p = 1;
        $sql = '';
        foreach ($tables as $value) {
            if ($tableStatus) {
                $sql .= $this->getInfo($value);
            }
            $this->query('SELECT * FROM ' . $value);
            $numFields = $this->numFields();
            while ($this->nextRecord()) {
                $sql .= $this->getRecord($value, $numFields);
                if (strlen($sql) >= $fileSize * 1024) {
                    $sql = substr($sql, 0, -2);
                    $filename2 = $filename . '_n_part' . $p . '.sql';
                    if ($this->write($sql, $path . '/' . $filename2)) {
                        $info[] = $filename2;
                    } else {
                        $info[] = '备份表-' . $value . '-失败';
                    }
                    $p++;
                    $sql = '';
                }
            }
        }
        if ($sql != '') {
            $this->write(substr($sql, 0, -2), ROOT_DIR . '/' . $path . '/' . $filename . '_n_part' . $p . '.sql');
        }
        return true;
    }

    public function backup($path = '', $filename = '', $fileSize = 2000, $tables = [], $tableStatus = true)
    {
        if ($path == '') {
            return apiResponse('请设置保存数据库文件的目录！', 0);
        }
        if (!is_dir($path)) {
            return apiResponse($path . '目录不存在，请手工创建！', 0);
        }
        if ($filename == '') {
            return apiResponse('请设置数据库文件名！', 0);
        }
        if (!is_numeric($fileSize)) {
            return apiResponse('分卷大小必须是数字！', 0);
        }
        if (!is_array($tables)) {
            return apiResponse('数据表必须是数组！', 0);
        }
        if (
            !$this->linkId = mysqli_connect(
                Config::get('database.connections.mysql.hostname') .
                (Config::get('database.connections.mysql.hostport') ?
                    ':' . Config::get('database.connections.mysql.hostport') : ''),
                Config::get('database.connections.mysql.username'),
                Config::get('database.connections.mysql.password')
            )
        ) {
            return apiResponse('连接服务器失败', 0);
        }
        if (!mysqli_select_db($this->linkId, Config::get('database.connections.mysql.database'))) {
            return apiResponse('无法打开数据库：' . mysqli_error($this->linkId), 0);
        }
        if (!mysqli_query($this->linkId, 'SET NAMES ' . Config::get('database.connections.mysql.charset'))) {
            return apiResponse('字符集设置错误', 0);
        }

        set_time_limit(0);
        $path = str_replace('\\', '/', $path);
        if (count($tables) == 0) {
            if ($fileSize == 0) {
                return $this->all($path, $filename, $tableStatus);
            } else {
                return $this->allPart($path, $filename, $fileSize, $tableStatus);
            }
        } elseif ($fileSize == 0) {
            return $this->n($path, $filename, $tables, $tableStatus);
        } else {
            return $this->nPart($path, $filename, $fileSize, $tables, $tableStatus);
        }
    }
}
