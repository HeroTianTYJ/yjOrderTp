<?php

use app\admin\model;
use think\facade\Config;
use think\facade\Console;
use think\facade\Request;
use think\facade\Route;
use think\facade\Session;

//日期格式化
function dateFormat($timestamp = 0, $format = 'Y-m-d H:i:s')
{
    return date($format, $timestamp);
}

//密码加盐
function passEncode($pass = '', $passKey = '')
{
    $key = $passKey ?: Config::get('system.pass_key');
    return sha1(substr($key, 0, 10) . substr($key, 20, 10) . substr($key, 10, 10) . substr($key, 30, 10) . $pass);
}

//将二维数组中的某个字段转化成以逗号分隔的字符串
function arrToStr($arr, $field)
{
    $str = '';
    foreach ($arr as $value) {
        $str .= $value[$field] . ',';
    }
    return substr($str, 0, -1);
}

//网络文件下载到服务器
function downloadFileToServer($url, $path)
{
    $curl = curl_init();
    if (stripos($url, 'https://') !== false) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
    }
    curl_setopt($curl, CURLOPT_POST, 0);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $fileContent = curl_exec($curl);
    curl_close($curl);
    $file = fopen($path, 'w');
    fwrite($file, $fileContent);
    fclose($file);
}

//文件下载到本地
function downloadFileToLocal($content, $filename)
{
    ob_end_clean();
    header('Content-Encoding:none');
    header('Content-Type:application/octet' . (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? '' : '-') . 'stream');
    header('Content-Disposition:' .
        (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'inline' : 'attachment') . ';filename=' . $filename);
    header('Content-Length:' . strlen($content));
    header('Pragma:no-cache');
    header('Expires:0');
    echo $content;
    ob_get_contents();
}

//字符串截取
function truncate($string, $start = 0, $len = 80, $etc = '...', $magic = true)
{
    if ($len == '') {
        $len = strlen($string);
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
    $aLen = $bLen = $realNum = $length = 0;
    for ($i = $start; $i < strlen($string); $i++) {
        $cType = $cStep = 0;
        $cur = substr($string, $i, 1);
        if ($cur == '&') {
            if (substr($string, $i, 4) == '&lt;') {
                $cStep = 4;
                $length += 4;
                $i += 3;
                $realNum++;
                if ($magic) {
                    $aLen++;
                }
            } elseif (substr($string, $i, 4) == '&gt;') {
                $cStep = 4;
                $length += 4;
                $i += 3;
                $realNum++;
                if ($magic) {
                    $aLen++;
                }
            } elseif (substr($string, $i, 5) == '&amp;') {
                $cStep = 5;
                $length += 5;
                $i += 4;
                $realNum++;
                if ($magic) {
                    $aLen++;
                }
            } elseif (substr($string, $i, 6) == '&quot;') {
                $cStep = 6;
                $length += 6;
                $i += 5;
                $realNum++;
                if ($magic) {
                    $aLen++;
                }
            } elseif (preg_match('/&#(\d+);?/i', substr($string, $i, 8), $match)) {
                $cStep = strlen($match[0]);
                $length += strlen($match[0]);
                $i += strlen($match[0]) - 1;
                $realNum++;
                if ($magic) {
                    $bLen++;
                    $cType = 1;
                }
            }
        } else {
            if (ord($cur) >= 252) {
                $cStep = 6;
                $length += 6;
                $i += 5;
                $realNum++;
                if ($magic) {
                    $bLen++;
                    $cType = 1;
                }
            } elseif (ord($cur) >= 248) {
                $cStep = 5;
                $length += 5;
                $i += 4;
                $realNum++;
                if ($magic) {
                    $cType = 1;
                    $bLen++;
                }
            } elseif (ord($cur) >= 240) {
                $cStep = 4;
                $length += 4;
                $i += 3;
                $realNum++;
                if ($magic) {
                    $cType = 1;
                    $bLen++;
                }
            } elseif (ord($cur) >= 224) {
                $cStep = 3;
                $length += 3;
                $i += 2;
                $realNum++;
                if ($magic) {
                    $cType = 1;
                    $bLen++;
                }
            } elseif (ord($cur) >= 192) {
                $cStep = 2;
                $length += 2;
                $i += 1;
                $realNum++;
                if ($magic) {
                    $cType = 1;
                    $bLen++;
                }
            } elseif (ord($cur) >= 128) {
                $length += 1;
            } else {
                $cStep = 1;
                $length += 1;
                $realNum++;
                if ($magic) {
                    ord($cur) >= 65 && ord($cur) <= 90 ? $bLen++ : $aLen++;
                }
            }
        }
        if ($magic) {
            if ($bLen * 2 + $aLen == $len * 2) {
                break;
            }
            if ($bLen * 2 + $aLen == $len * 2 + 1) {
                if ($cType == 1) {
                    $length -= $cStep;
                }
                break;
            }
        } else {
            if ($realNum == $len) {
                break;
            }
        }
    }
    return strlen($string) <= $length ? $string : substr($string, $start, $length) . $etc;
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

//判断时间格式
function checkTime($string)
{
    return strtotime($string) ?: time();
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

//显示数据库里的图片
function escapePic($string)
{
    return Config::get('system.big_pic') ? preg_replace(
        '/\[img=(.*)]/U',
        '<a href="' . (Config::get('system.qiniu_domain') ? Config::get('system.qiniu_domain') :
            Config::get('dir.upload')) . '\1?' . staticCache() .
            '" target="_blank"><img src="static/index/images/grey.gif?' . staticCache() . '" data-original="' .
            (Config::get('system.qiniu_domain') ? Config::get('system.qiniu_domain') : Config::get('dir.upload')) .
            '\1?' . staticCache() . '" alt="图片" class="lazy" width="100%" height="500"></a>',
        $string
    ) : preg_replace(
        '/\[img=(.*)]/U',
        '<img src="static/index/images/grey.gif?' . staticCache() . '" data-original="' .
            (Config::get('system.qiniu_domain') ? Config::get('system.qiniu_domain') : Config::get('dir.upload')) .
            '\1?' . staticCache() . '" alt="图片" class="lazy" width="100%" height="500">',
        $string
    );
}
function escapePic2($string)
{
    return preg_replace(
        '/\[img=(.*)]/U',
        '<img alt="图片" src="' . (Config::get('system.qiniu_domain') ? Config::get('system.qiniu_domain') :
            Config::get('url.web1') . Config::get('dir.upload')) . '\1" />',
        $string
    );
}
function unescapePic($string)
{
    return preg_replace(
        '/<img alt="图片" src="' . str_replace('/', '\/', (Config::get('system.qiniu_domain') ?
            Config::get('system.qiniu_domain') : Config::get('url.web1') .
            Config::get('dir.upload'))) . '(.*)" \/>/U',
        '[img=\1]',
        $string
    );
}

//html换行与空格
function htmlBrNbsp($string)
{
    return str_replace(["\r\n", "\n", "\r", '  '], ['<br>', '<br>', '<br>', '&nbsp;&nbsp;'], $string);
}

//发送短信
function sendSms($mobile, $content)
{
    return file_get_contents(
        'https://api.smsbao.com/sms?u=' . Config::get('system.sms_bao_user') . '&p=' .
        md5(Config::get('system.sms_bao_pass')) . '&m=' . $mobile . '&c=' . urlencode($content)
    );
}

//curl
function curlPost($url, $data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $content = curl_exec($curl);
    curl_close($curl);
    return $content;
}
function curlGet($url)
{
    $curl = curl_init();
    if (stripos($url, 'https://') !== false) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    if (intval($info['http_code']) == 200) {
        return $content;
    } else {
        return false;
    }
}

//压缩文件
function zip($dir, ZipArchive $ZipArchive, $targetDir)
{
    foreach (scandir($dir) as $value) {
        if (!in_array($value, ['.', '..'])) {
            if (is_dir($dir . '\\' . $value)) {
                zip($dir . '\\' . $value, $ZipArchive, $targetDir);
            } else {
                $ZipArchive->addFile($dir . '\\' . $value, $targetDir . '\\' . $value);
            }
        }
    }
}

//透过代理获取用户真实IP
function getUserIp()
{
    $ip = '';
    if (
        isset($_SERVER['HTTP_X_FORWARDED_FOR']) &&
        $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown')
    ) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (
        isset($_SERVER['REMOTE_ADDR']) &&
        $_SERVER['REMOTE_ADDR'] &&
        strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')
    ) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (false !== strpos($ip, ', ')) {
        $array = explode(', ', $ip);
        $ip = reset($array);
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }
    return $ip;
}

//生成数据表缓存
function databaseSchema()
{
    $Common = new model\Common();
    foreach ($Common->info() as $value) {
        if (trim(Console::call('optimize:schema', ['--table', $value['Name']])->fetch()) != '<info>Succeed!</info>') {
            return false;
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

//信息提示
function showTip($content, $state = 1)
{
    return json_encode(['content' => $content, 'state' => $state]);
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
    return $session['level'] == 1 || in_array(
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
    if ($session['level'] == 1) {
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
    return $session['level'] == 1 || in_array(permitDataId($parentName, $name), $session['permit_data']);
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
    if ($session['level'] == 1) {
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
    if (
        strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') &&
        strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'miniprogram')
    ) {
        return 'wxxcx';
    } elseif (
        strstr($_SERVER['HTTP_USER_AGENT'], 'OpenHarmony') && !strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')
    ) {
        return 'harmony';
    } elseif (
        strstr($_SERVER['HTTP_USER_AGENT'], 'Android') && !strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')
    ) {
        return 'android';
    } elseif (
        strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone OS') && !strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')
    ) {
        return 'iphone';
    } elseif (
        strstr($_SERVER['HTTP_USER_AGENT'], 'OpenHarmony') && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')
    ) {
        return 'harmonyWechat';
    } elseif (strstr($_SERVER['HTTP_USER_AGENT'], 'Android') && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
        return 'androidWechat';
    } elseif (
        strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone OS') && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')
    ) {
        return 'iphoneWechat';
    } elseif (
        strstr($_SERVER['HTTP_USER_AGENT'], 'Windows NT') && !strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')
    ) {
        return 'windows';
    } elseif (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac OS') && !strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
        return 'mac';
    } elseif (
        strstr($_SERVER['HTTP_USER_AGENT'], 'Windows NT') && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')
    ) {
        return 'windowsWechat';
    } elseif (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac OS') && strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
        return 'macWechat';
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
        (in_array(device(), ['harmony', 'android', 'iphone', 'windows', 'mac']) && Config::get('system.wechat_open_app_id') &&
            Config::get('system.wechat_open_app_secret'))
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

//价格格式化
function priceFormat($price = 0)
{
    if (strstr($price, '.')) {
        $temp = explode('.', $price);
        if ($temp[1] == '0' || $temp[1] == '00') {
            return $temp[0];
        } elseif (substr($temp[1], -1) == '0') {
            return substr($price, 0, -1);
        }
    }
    return $price;
}
