<?php

use czdb\DbSearcher;
use think\facade\Config;
use think\facade\Request;
use think\facade\Route;
use think\facade\Session;

//时间格式化
function timeFormat($time = '')
{
    if ($time) {
        return substr($time, 0, 10) == '1000-01-01' ? '' : $time;
    } else {
        return '';
    }
}

//时间戳格式化
function timestampFormat($timestamp = 0, $format = 'Y-m-d H:i:s')
{
    return $timestamp ? date($format, $timestamp) : '';
}

//判断时间格式，如果正确则返回此时间，否则返回当前时间
function checkTime($time)
{
    if ($time) {
        return strtotime($time) ? $time : now();
    } else {
        return '1000-01-01 00:00:00';
    }
}

//当前时间
function now()
{
    return date('Y-m-d H:i:s');
}

//密码加盐
function passEncode($pass = '', $passKey = '')
{
    $key = $passKey ?: Config::get('system.pass_key');
    return sha1(substr($key, 0, 10) . substr($key, 20, 10) . substr($key, 10, 10) . substr($key, 30, 10) . $pass);
}

//将二维数组中的某个字段转化成以逗号分隔的字符串
function arrayFieldToString($array, $field, $separator = ',')
{
    return implode($separator, array_column($array, $field));
}

//文件下载到本地
function downloadFileToLocal($content, $filename)
{
    ob_end_clean();
    header('Content-Encoding:none');
    header('Content-Type:application/octet' . (strpos(Request::server('HTTP_USER_AGENT'), 'MSIE') ? '' : '-') . 'stream');
    header('Content-Disposition:' .
        (strpos(Request::server('HTTP_USER_AGENT'), 'MSIE') ? 'inline' : 'attachment') . ';filename=' . $filename);
    header('Content-Length:' . strlen($content));
    header('Pragma:no-cache');
    header('Expires:0');
    echo $content;
    ob_get_contents();
}

//字符串截取
function truncate($string, $start = 0, $length = 80)
{
    if ($length == 0) {
        $length = strlen($string);
    }
    if ($start != 0) {
        $startV = ord(substr($string, $start, 1));
        if ($startV >= 128) {
            if ($startV < 192) {
                for ($i = $start - 1; $i > 0; $i--) {
                    $tempV = ord(substr($string, $i, 1));
                    if ($tempV >= 192) {
                        break;
                    }
                }
                $start = $i;
            }
        }
    }
    $aLen = $bLen = $length2 = 0;
    for ($i = $start; $i < strlen($string); $i++) {
        $cType = $cStep = 0;
        $cur = substr($string, $i, 1);
        if ($cur == '&') {
            if (substr($string, $i, 4) == '&lt;') {
                $cStep = 4;
                $length2 += 4;
                $i += 3;
                $aLen++;
            } elseif (substr($string, $i, 4) == '&gt;') {
                $cStep = 4;
                $length2 += 4;
                $i += 3;
                $aLen++;
            } elseif (substr($string, $i, 5) == '&amp;') {
                $cStep = 5;
                $length2 += 5;
                $i += 4;
                $aLen++;
            } elseif (substr($string, $i, 6) == '&quot;') {
                $cStep = 6;
                $length2 += 6;
                $i += 5;
                $aLen++;
            } elseif (preg_match('/&#(\d+);?/i', substr($string, $i, 8), $match)) {
                $cStep = strlen($match[0]);
                $length2 += strlen($match[0]);
                $i += strlen($match[0]) - 1;
                $cType = 1;
                $bLen++;
            }
        } else {
            if (ord($cur) >= 252) {
                $cStep = 6;
                $length2 += 6;
                $i += 5;
                $cType = 1;
                $bLen++;
            } elseif (ord($cur) >= 248) {
                $cStep = 5;
                $length2 += 5;
                $i += 4;
                $cType = 1;
                $bLen++;
            } elseif (ord($cur) >= 240) {
                $cStep = 4;
                $length2 += 4;
                $i += 3;
                $cType = 1;
                $bLen++;
            } elseif (ord($cur) >= 224) {
                $cStep = 3;
                $length2 += 3;
                $i += 2;
                $cType = 1;
                $bLen++;
            } elseif (ord($cur) >= 192) {
                $cStep = 2;
                $length2 += 2;
                $i += 1;
                $cType = 1;
                $bLen++;
            } elseif (ord($cur) >= 128) {
                $length2 += 1;
            } else {
                $cStep = 1;
                $length2 += 1;
                ord($cur) >= 65 && ord($cur) <= 90 ? $bLen++ : $aLen++;
            }
        }
        if ($bLen * 2 + $aLen == $length * 2) {
            break;
        }
        if ($bLen * 2 + $aLen == $length * 2 + 1) {
            if ($cType == 1) {
                $length2 -= $cStep;
            }
            break;
        }
    }
    return strlen($string) <= $length2 ? $string : substr($string, $start, $length2) . '...';
}

//关键词加亮
function keyword($string, $keyword = '')
{
    return str_replace(
        $keyword ?: Request::get('keyword', ''),
        '<span class="keyword">' . ($keyword ?: Request::get('keyword')) . '</span>',
        $string
    );
}

//生成随机字符串
function getKey($length, $type = 0)
{
    $key = '';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $number = '0123456789';
    switch ($type) {
        case 1:
            $charset = $lower;
            break;
        case 2:
            $charset = $upper;
            break;
        case 3:
            $charset = $number;
            break;
        case 4:
            $charset = $lower . $upper;
            break;
        case 5:
            $charset = $lower . $number;
            break;
        case 6:
            $charset = $upper . $number;
            break;
        default:
            $charset = $lower . $upper . $number;
    }
    for ($i = 0; $i < $length; $i++) {
        $key .= $charset[mt_rand(0, strlen($charset) - 1)];
    }
    return $key;
}

//发送短信
function sendSms($mobile, $content)
{
    return file_get_contents(
        'https://api.smsbao.com/sms?u=' . Config::get('system.sms_bao_user') . '&p=' .
        md5(Config::get('system.sms_bao_pass')) . '&m=' . $mobile . '&c=' . urlencode($content)
    );
}

//透过代理获取用户真实IP
function getUserIp()
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (Request::server($key)) {
            return filter_var(explode(',', Request::server($key))[0], FILTER_VALIDATE_IP) ?: '127.0.0.1';
        }
    }
    return '127.0.0.1';
}

//删除数据表缓存
function databaseSchema()
{
    $cache = ROOT_DIR . '/runtime/cache';
    if (is_dir($cache)) {
        foreach (scandir($cache) as $value) {
            if (preg_match('/[a-z0-9]{32}\.php$/', $value)) {
                if (!unlink($cache . '/' . $value)) {
                    return false;
                }
            }
        }
    }
    return true;
}

//静态资源缓存后缀生成
function staticCache()
{
    if (Config::get('static.cache')) {
        return Config::get('static.cache');
    } else {
        $staticCache = time();
        file_put_contents(ROOT_DIR . '/config/diy/static.php', '<?php

return [
    \'cache\' => ' . $staticCache . '
];
');
        return $staticCache;
    }
}

//api响应
function apiResponse($message, $status = 1, $data = [])
{
    return json_encode(['message' => $message, 'status' => $status, 'data' => $data]);
}

//将数组的key组合成数组
function keyToArray($array)
{
    $result = [];
    foreach ($array as $key => $value) {
        $result[] = $key;
    }
    return $result;
}

//权限验证
function isPermission($action = '', $controller = '')
{
    $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
    return $session['level_id'] == 1 || in_array(
        permitId($controller ?: Request::controller(), $action ?: Request::action()),
        $session['permit_manage']
    );
}

//权限id
function permitId($controller = '', $action = 'index')
{
    return Config::get('permit_manage.' . ($controller ?: Request::controller()) . '.' . $action);
}

//权限交集
function permitIntersect($permit = [])
{
    $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
    if ($session['level_id'] == 1) {
        return true;
    }
    $permitId = [];
    foreach ($permit as $value) {
        $permitId[] = permitId($value);
    }
    return array_intersect($session['permit_manage'], $permitId);
}

//数据权限验证
function isDataPermission($parentName = '', $name = '')
{
    $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
    return $session['level_id'] == 1 || in_array(permitDataId($parentName, $name), $session['permit_data']);
}

//数据权限id
function permitDataId($parentName = '', $name = '')
{
    return Config::get('permit_data.' . $parentName . '.' . $name);
}

//数据权限交集
function permitDataIntersect($permitData = [])
{
    $session = Session::get(Config::get('system.session_key_admin') . '.manage_info');
    if ($session['level_id'] == 1) {
        return true;
    }
    $permitDataId = [];
    foreach ($permitData as $value) {
        $permitDataId[] = permitDataId($value[0], $value[1]);
    }
    return array_intersect($session['permit_data'], $permitDataId);
}

//获取用户设备
function device()
{
    if (strstr(Request::server('HTTP_USER_AGENT'), 'MicroMessenger')) {
        if (strstr(strtolower(Request::server('HTTP_USER_AGENT')), 'miniprogram')) {
            return 'wxxcx';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'OpenHarmony')) {
            return 'harmonyWechat';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'Android')) {
            return 'androidWechat';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'iPhone OS')) {
            return 'iphoneWechat';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'Windows NT')) {
            return 'windowsWechat';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'Mac OS')) {
            return 'macWechat';
        }
    } else {
        if (strstr(Request::server('HTTP_USER_AGENT'), 'OpenHarmony')) {
            return 'harmony';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'Android')) {
            return 'android';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'iPhone OS')) {
            return 'iphone';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'Windows NT')) {
            return 'windows';
        } elseif (strstr(Request::server('HTTP_USER_AGENT'), 'Mac OS')) {
            return 'mac';
        }
    }
    return 'unknown';
}

//第三方登录链接
function loginLink()
{
    $link = '';
    if (
        (in_array(device(), ['harmonyWechat', 'androidWechat', 'iphoneWechat', 'windowsWechat', 'macWechat']) &&
            Config::get('system.wechat_app_id') && Config::get('system.wechat_app_secret')) ||
        (in_array(device(), ['harmony', 'android', 'iphone', 'windows', 'mac']) &&
            Config::get('system.wechat_open_app_id') && Config::get('system.wechat_open_app_secret'))
    ) {
        $link .= '<a href="' . Route::buildUrl('/' . parse_name(Request::controller()) . '/wechat') .
            '"><span class="iconfont icon-wechat color"></span></a>';
    }
    if (Config::get('system.qq_app_id') && Config::get('system.qq_app_key')) {
        $link .= '<a href="' . Route::buildUrl('/' . parse_name(Request::controller()) . '/qq') .
            '"><span class="iconfont icon-qq color"></span></a>';
    }
    return $link;
}

//获取IP地理位置
function ipGeolocation($ip)
{
    $DbSearcher = new DbSearcher(
        Config::get(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 'file.czdb_v4' : 'file.czdb_v6'),
        Config::get('system.czdb_key')
    );
    $ipGeolocation = $DbSearcher->search($ip);
    $DbSearcher->close();
    return $ipGeolocation;
}
